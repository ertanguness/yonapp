<?php
namespace App\Controllers;

use App\Helper\Date;
use Model\UserModel;
use App\Helper\Helper;
use Model\SettingsModel;
use App\InterFaces\LoggerInterface;
use App\Services\FlashMessageService;
use App\Services\MailGonderService;
use Dompdf\Helpers;

/**
 * AuthController
 * 
 * Hem gelen istekleri işler (handleLoginRequest) hem de uygulama genelinde
 * statik metotlar aracılığıyla kimlik doğrulama hizmetleri sunar.
 */
class AuthController
{
    private $userModel;
    private $settingsModel;
    private LoggerInterface $logger;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->settingsModel = new SettingsModel();
        $this->logger = \getLogger();
    }

    // //================================================================
    // // İSTEK İŞLEYEN METOTLAR (Request Handlers)
    // //================================================================

  
/**
     * Gelen POST isteğini işler, doğrular ve kullanıcıyı yönlendirir.
     * Artık geriye bir şey döndürmez, tüm akışı kendisi yönetir.
     */
    public function handleLoginRequest(): void
    {
        /** Önce tüm session'u temizle */
        session_unset();
        session_regenerate_id(true);    

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $validationError = false;
        $searchEmail = null;
        $searchPhone = null;
        try { (new UserModel())->ensureLoginPreferenceColumns(); } catch (\Exception $e) {}

        if (empty($email) || empty($password)) {
            FlashMessageService::add('error', 'Giriş Başarısız!', 'E-posta veya telefon ve şifre zorunludur.', 'ikaz2.png');
            $validationError = true;
        } else {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $searchEmail = $email;
            } else {
                $identifier = preg_replace('/\s+/', '', $email);
                $digits = preg_replace('/\D+/', '', $identifier);
                if (!empty($digits)) {
                    if ($identifier[0] !== '+') {
                        if (strlen($digits) >= 10 && strlen($digits) <= 11) {
                            $candidate = '+90' . $digits;
                        } else {
                            $candidate = '+' . $digits;
                        }
                    } else {
                        $candidate = $identifier;
                    }
                    $searchPhone = $candidate;
                }
            }
        }
        if ($validationError) {
            // Hata varsa, girilen e-postayı session'da saklayıp formu tekrar göster
            $_SESSION['old_form_input'] = ['email' => $email];
            header("Location: /sign-in");
            exit();
        }
        $accounts = $this->userModel->getAccountsByEmailOrPhone($searchEmail, $searchPhone);
        
        // LOGGING START
        $this->logger->info("Login attempt", ['email' => $email, 'accounts_found' => count($accounts)]);
        // LOGGING END

        if (empty($accounts)) {
            FlashMessageService::add('error', 'Giriş Başarısız!', 'Kullanıcı bulunamadı.', 'ikaz2.png');
            $_SESSION['old_form_input'] = ['email' => $email];
            header("Location: /sign-in");
            exit();
        }

        $matched = [];
        foreach ($accounts as $acc) {
            if (password_verify($password, $acc->password)) {
                $matched[] = $acc;
            }
        }

        // LOGGING START
        $this->logger->info("Password verify", ['matched_count' => count($matched)]);
        // LOGGING END

        if (count($matched) === 0) {
            FlashMessageService::add('error', 'Giriş Başarısız!', 'Hatalı şifre girdiniz.', 'ikaz2.png');
            $this->logger->error("Başarısız giriş denemesi.", ['identifier' => $email, 'ip' => $_SERVER['REMOTE_ADDR']]);
            $_SESSION['old_form_input'] = ['email' => $email];
            header("Location: /sign-in");
            exit();
        }

        if (count($matched) > 1) {
            $eligible = array_values(array_filter($matched, function($u){ return self::canLoginForRole($u); }));
            
            // LOGGING START
            $this->logger->info("Multiple match eligible check", ['eligible_count' => count($eligible)]);
            // LOGGING END

            if (count($eligible) === 0) {
                FlashMessageService::add('error', 'Giriş Başarısız!', 'Uygun rol bulunamadı.', 'ikaz2.png');
                $_SESSION['old_form_input'] = ['email' => $email];
                header("Location: /sign-in");
                exit();
            }
            if (count($eligible) === 1) {
                $selectedUser = $eligible[0];
                self::validateDemoPeriod($selectedUser);
                self::performLogin($selectedUser);
            }
            $_SESSION['role_select_candidates'] = array_map(function ($a) {
                return [
                    'id' => (int)$a->id,
                    'role_id' => (int)$a->roles,
                    'role_name' => $a->role_name ?? null,
                    'full_name' => $a->full_name ?? null
                ];
            }, $eligible);
            $_SESSION['role_select_csrf'] = bin2hex(random_bytes(16));
            $_SESSION['old_form_input'] = ['email' => $email];
            $returnUrl = !empty($_GET['returnUrl']) ? $_GET['returnUrl'] : null;
            if ($returnUrl) {
                $_SESSION['role_select_returnUrl'] = $returnUrl;
            }
            
            $this->logger->info("Redirecting to role select (multiple match)", ['count' => count($eligible)]);
            
            $qs = 'chooseRole=1';
            if (!empty($returnUrl)) {
                $qs .= '&returnUrl=' . urlencode($returnUrl);
            }
            header("Location: sign-in.php?" . $qs);
            exit();
        }
        
        // Eğer tek hesap şifre ile eşleşti ama kullanıcıya ait birden fazla yetkili hesap varsa seçim ekranını yine göster
        if (count($matched) === 1) {
            $eligibleAll = array_values(array_filter($accounts, function($u){ return self::canLoginForRole($u); }));
            
            // LOGGING START
            $this->logger->info("Single match eligible check", ['eligible_all_count' => count($eligibleAll)]);
            // LOGGING END

            if (count($eligibleAll) > 1) {
                $_SESSION['role_select_candidates'] = array_map(function ($a) {
                    return [
                        'id' => (int)$a->id,
                        'role_id' => (int)$a->roles,
                        'role_name' => $a->role_name ?? null,
                        'full_name' => $a->full_name ?? null
                    ];
                }, $eligibleAll);
                $_SESSION['role_select_csrf'] = bin2hex(random_bytes(16));
                $_SESSION['old_form_input'] = ['email' => $email];
                $returnUrl = !empty($_GET['returnUrl']) ? $_GET['returnUrl'] : null;
                if ($returnUrl) {
                    $_SESSION['role_select_returnUrl'] = $returnUrl;
                }
                
                $this->logger->info("Redirecting to role select (single match, multiple accounts)", ['count' => count($eligibleAll)]);

                header("Location: sign-in.php?chooseRole=1");
                exit();
            }
        }

        $selectedUser = $matched[0];
        
        self::validateDemoPeriod($selectedUser);
        self::validateLoginEligibility($selectedUser);
        self::performLogin($selectedUser);
    }



    /*Kullanıcı bilgilerini alır.
     * @return object|null Kullanıcı nesnesi veya null
     */
    public static function user(): ?object
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Eğer oturumda kullanıcı bilgisi varsa, onu döndür
        if (isset($_SESSION['user'])) {
            return $_SESSION['user'];
        }

        // Oturumda kullanıcı bilgisi yoksa null döndür
        return null;
    }

    //================================================================
    // STATİK YARDIMCI METOTLAR (Uygulama Geneli Servisler)
    //================================================================

   /**
     * Kullanıcının oturum açıp açmadığını ve yetkili olup olmadığını kontrol eder.
     * Eğer bir sorun varsa (giriş yapılmamış, demo süresi dolmuş vb.),
     * kullanıcıyı uygun şekilde yönlendirir.
     * Bu metot, korumalı sayfaların en başında çağrılmalıdır.
     */
    public static function checkAuthentication(): void
    {
        
        //kayit-ol sayfafında oturum kontrolü yapma
        $currentUrl = $_SERVER['REQUEST_URI'];
        if (strpos($currentUrl, 'kayit-ol') !== false) {
            return;
        }
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        
        $logger = \getLogger();
           
        // 1. Adım: Kullanıcı giriş yapmış mı?
        if (!isset($_SESSION['user'])) {
            // Kullanıcı giriş yapmamışsa veya oturum süresi dolmuşsa, logla ve yönlendir.
            $logger->error("Kullanıcı oturum açmaya çalıştı, ancak oturum bilgisi bulunamadı.", [
                'ip' => $_SERVER['REMOTE_ADDR'],
                'requested_url' => $_SERVER['REQUEST_URI']
            ]);

            session_destroy(); // Oturumu temizle
            session_unset(); // Tüm session değişkenlerini temizle
           
            FlashMessageService::add(
                'error',
                'Giriş Gerekli',
                'Bu sayfayı görüntülemek için lütfen giriş yapın.',
                'ikaz2.png'
            );
         

            $returnUrl = urlencode($_SERVER['REQUEST_URI']);
            header("Location: /sign-in.php?returnUrl={$returnUrl}");
            exit();
        }

        //Sesion süresi dolmuş mu?


        // 2. Adım: Kullanıcı verisini al
        $user = $_SESSION['user'];
     

        // 3. Adım: Demo süresi kontrolünü yap
        // Eğer demo süresi dolmuşsa, bu metot kullanıcıyı yönlendirip programı sonlandıracak.
        self::validateDemoPeriod($user);

        // 4. Adım (İsteğe bağlı - Geleceğe yönelik): Diğer kontroller
        // Örneğin, kullanıcının IP adresi değişmişse tekrar şifre sor, vb.
        // self::validateSessionIntegrity($user);
    }

    /**
     * Bir kullanıcının demo süresinin dolup dolmadığını kontrol eder.
     * Eğer süre dolmuşsa, kullanıcıyı çıkışa zorlar ve programı sonlandırır.
     *
     * @param object $user Kontrol edilecek kullanıcı nesnesi.
     */
    private static function validateDemoPeriod(object $user): void
    {


        // Sadece user_type'ı 1 (demo kullanıcısı) olanları kontrol et
        if (isset($user->user_type) && $user->user_type == 1) {
          
            // Kullanıcının kayıt tarihi verisinin olduğundan emin ol
            if (!isset($user->created_at)) {
                // Kayıt tarihi yoksa ne yapılacağına karar verin.
                // Belki de bir hata loglayıp devam etmesine izin verebilirsiniz.
                \getLogger()->error("Demo kullanıcısının kayıt tarihi (created_at) bulunamadı.", ['user_id' => $user->id]);
                return;
            }

            try {
                // Date helper'ını kullanarak iki tarih arasındaki farkı gün olarak al
                $daysSinceRegistration = Date::getDateDiff($user->created_at);

                // Tanımlanan demo süresi (örneğin 15 gün)
                $demoLimitInDays = 15;

                if ($daysSinceRegistration >= $demoLimitInDays) {
                    // Demo süresi dolmuş!
                    
                    // Önce logla, sonra çıkış yaptır.
                    \getLogger()->info("Demo süresi dolan kullanıcı sisteme erişmeye çalıştı ve çıkışa yönlendirildi.", [
                        'user_id' => $user->id,
                        'email' => $user->email
                    ]);

                    FlashMessageService::add(
                        'warning',
                        'Demo Süreniz Doldu!',
                        'Sistemi kullanmaya devam etmek için lütfen bizimle iletişime geçin.',
                        'ikaz2.png'
                    );
                  
                    self::logout(false); // false -> tekrar loglama yapma demek


                }
            } catch (\Exception $e) {
                // Eğer tarih formatı bozuksa veya Date::getDateDiff hata verirse,
                // bu hatayı logla ve sistemin çökmesini engelle.
                \getLogger()->error("Demo süresi kontrolü sırasında tarih hatası.", [
                    'user_id' => $user->id,
                    'created_at_value' => $user->created_at,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    private static function validateLoginEligibility(object $user): void
    {
        try {
            $lockModel = new \Model\UserAccessLockModel();
            $locked = $lockModel->getLockStatusByUser((int)$user->id);
            if ($locked === 1) {
                \App\Services\FlashMessageService::add('error', 'Giriş Başarısız!', 'Ödemeniz geciktiği için erişim kilitli.', 'ikaz2.png');
                header("Location: /sign-in");
                exit();
            }
        } catch (\Throwable $e) {}
        $roleId = isset($user->roles) ? (int)$user->roles : null;
        $roleName = $user->role_name ?? '';
        $isResidentRole = ($roleId === 3) || (stripos((string)$roleName, 'sakin') !== false);

        $statusValue = isset($user->status) ? (int)$user->status : null;
        if ($statusValue === 0) {
            FlashMessageService::add('error', 'Giriş Başarısız!', 'Hesabınız pasif olduğu için giriş yapamazsınız.', 'ikaz2.png');
            header("Location: /sign-in");
            exit();
        }

        if ($isResidentRole) {
            if (!self::isResidentActive($user)) {
                FlashMessageService::add('error', 'Giriş Başarısız!', 'Çıkış tarihi dolu olduğu için giriş yapamazsınız.', 'ikaz2.png');
                header("Location: /sign-in");
                exit();
            }
            return;
        }

        $ownerId = isset($user->owner_id) ? (int)$user->owner_id : 0;
        if ($ownerId > 0) {
            $isActive = null;
            if (isset($user->is_active)) {
                $isActive = (int)$user->is_active;
            } elseif (isset($user->status)) {
                $isActive = (int)$user->status;
            }
            if ($isActive === 0) {
                FlashMessageService::add('error', 'Giriş Başarısız!', 'Hesabınız pasif olduğu için giriş yapamazsınız.', 'ikaz2.png');
                header("Location: /sign-in");
                exit();
            }
        }
    }

    private static function canLoginForRole(object $user): bool
    {
        $roleId = isset($user->roles) ? (int)$user->roles : null;
        $roleName = $user->role_name ?? '';
        $isResidentRole = ($roleId === 3) || (stripos((string)$roleName, 'sakin') !== false);
        if ($isResidentRole) {
            return self::isResidentActive($user);
        }
        $ownerId = isset($user->owner_id) ? (int)$user->owner_id : 0;
        if ($ownerId > 0) {
            $isActive = null;
            if (isset($user->is_active)) {
                $isActive = (int)$user->is_active;
            } elseif (isset($user->status)) {
                $isActive = (int)$user->status;
            }
            return $isActive !== 0;
        }
        return true;
    }

    private static function isResidentActive(object $user): bool
    {
        try {
            $pdo = \getDbConnection();
            $conditions = [];
            $params = [];
            if (!empty($user->email)) {
                $conditions[] = "LOWER(eposta) = LOWER(:email)";
                $params[':email'] = $user->email;
            }
            if (!empty($user->phone)) {
                $conditions[] = "telefon = :phone";
                $params[':phone'] = $user->phone;
            }
            if (!empty($user->full_name)) {
                $conditions[] = "LOWER(adi_soyadi) = LOWER(:name)";
                $params[':name'] = $user->full_name;
            }
            if (empty($conditions)) {
                return true;
            }
            $sql = "SELECT COUNT(*) FROM kisiler WHERE (" . implode(' OR ', $conditions) . ") AND silinme_tarihi IS NULL AND (uyelik_tipi IN ('Kiracı','Kat Maliki')) AND (cikis_tarihi IS NULL OR cikis_tarihi = '0000-00-00')";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->execute();
            $count = (int)$stmt->fetchColumn();
            return $count > 0;
        } catch (\Throwable $e) {
            return true;
        }
    }

    /**
     * Gerekli session'ları ayarlar, loglama yapar.
     * Bu metot artık private değil, public static.
     * @param object $user
     */
    public static function performLogin(object $user): void
    {
        self::validateDemoPeriod($user);
        self::validateLoginEligibility($user);
        session_regenerate_id(true);

        $_SESSION['user'] = $user;
        $_SESSION['user_id'] = $user->id;
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['full_name'] = $user->full_name;
        $_SESSION['user_role'] = $user->roles;
        $_SESSION["owner_id"] = $user->owner_id == 0 ? $user->id : $user->owner_id;

        // Model ve Servisler
        $userModel = new UserModel();
        $logger = \getLogger();

        try {
            $userModel->ensureOnboardingCompletedColumn();
            $_SESSION['onboarding_completed'] = $userModel->getOnboardingCompleted((int)$user->id) === 1;
        } catch (\Throwable $e) {
            $_SESSION['onboarding_completed'] = false;
        }

        // Loglama ve Token işlemleri
        $userModel->setToken($user->id, $_SESSION['csrf_token']);

        $logger->info("Kullanıcı başarıyla giriş yaptı.", [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $_SERVER['REMOTE_ADDR']
        ]);

        // E-posta gönderme (bu da statik bir metoda taşınabilir)
        self::sendLoginNotificationEmail($user);

        // SÜPER ADMIN KONTROLÜ
         if ((int)$user->roles === 10) {
              header("Location: /superadmin");
              exit();
         }
        
         // TEMSİLCİ KONTROLÜ (Role ID 15 veya rol adında 'Temsilci' geçiyorsa)
         $roleName = $user->role_name ?? '';
         if ((int)$user->roles === 15 || stripos($roleName, 'Temsilci') !== false) {
             header("Location: /temsilci-paneli");
             exit();
            }
            
            $returnUrl = !empty($_GET['returnUrl']) ? $_GET['returnUrl'] : 'ana-sayfa';

            //Helper::dd($returnUrl);
        //eğer site_id oturumda yoksa, siteyi seçmesi için company-list.php sayfasına yönlendir
        if (!isset($_SESSION['site_id'])) {
            // Site seçimi için company-list.php sayfasına yönlendir
            header("Location: company-list.php?returnUrl=" . urlencode($returnUrl));
            exit();
       
        }

        header("Location: " . $returnUrl);
        exit();
    }

    /**
     * Kullanıcı oturumunu sonlandırır.
     * @param bool $logAction Çıkış işleminin loglanıp loglanmayacağını belirtir.
     */
    public static function logout(bool $logAction = true): void
    {
        if ($logAction) {
            $logger = \getLogger();
            $userId = $_SESSION['user']->id ?? 'Bilinmiyor';

            $logger->info("Kullanıcı oturumu kapattı.", [
                'user_id' => $userId,
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);
        session_unset();
        session_destroy();    
            // --- DEĞİŞİKLİK BURADA ---
            // Normal çıkış için başarı mesajı ekle
            FlashMessageService::add(
                'success',
                'Başarılı',
                'Oturumunuz güvenli bir şekilde kapatıldı.',
                'onay2.png'
            );
        }
        
        
        
        // --- DEĞİŞİKLİK BURADA ---
        // Yönlendirmede artık ?status=... parametresi yok.
        header("Location: sign-in.php");
        exit();
    }


    /**
     * Giriş bildirimi e-postası gönderir.
     * @param object $user
     */
    private static function sendLoginNotificationEmail(object $user): void
    {
        $settingsModel = new SettingsModel();
        $sendEmailSetting = $settingsModel->getSettingIdByUserAndAction($user->id, "login_notification");
        $logger = \getLogger();

        $logger->info("Giriş bildirimi ayarı kontrol ediliyor.", ['user_id' => $user->id, 'email' => $user->email]);

        if (isset($sendEmailSetting) && $sendEmailSetting->set_value == 1) {
            try {

                $ip = $_SERVER['REMOTE_ADDR'] ?? 'Bilinmiyor';
                $userInfo = ($user->full_name ?? '') !== '' ? ($user->full_name . ' (' . ($user->email ?? '') . ')') : ($user->email ?? 'Bilinmiyor');

                $loginTime = date('d.m.Y H:i:s');
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

                // HTML Mail (MailGonderService isHTML(true) kullandığı için Body HTML olabilir)
                $safeUserInfo = htmlspecialchars((string)$userInfo, ENT_QUOTES, 'UTF-8');
                $safeIp = htmlspecialchars((string)$ip, ENT_QUOTES, 'UTF-8');
                $safeTime = htmlspecialchars((string)$loginTime, ENT_QUOTES, 'UTF-8');
                $safeUa = htmlspecialchars((string)$userAgent, ENT_QUOTES, 'UTF-8');

                $uaRowHtml = $userAgent !== ''
                    ? "<tr><td style=\"padding:10px 12px;color:#64748b;border-top:1px solid #e5e7eb;width:160px;\"><strong>Tarayıcı / Cihaz</strong></td><td style=\"padding:10px 12px;border-top:1px solid #e5e7eb;color:#0f172a;\">{$safeUa}</td></tr>"
                    : '';

                $icerik = '<!doctype html><html lang="tr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
                    . '<title>Yeni Giriş Bildirimi</title></head>'
                    . '<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">'
                    . '<div style="max-width:640px;margin:0 auto;padding:24px;">'
                    . '<div style="background:#ffffff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;">'
                    . '<div style="padding:18px 20px;background:linear-gradient(90deg,#0f172a,#1e293b);color:#ffffff;">'
                    . '<div style="font-size:14px;opacity:.9;">YonApp | Site Yönetim</div>'
                    . '<div style="font-size:20px;font-weight:700;margin-top:4px;">Yeni Giriş Bildirimi</div>'
                    . '</div>'
                    . '<div style="padding:20px;">'
                    . '<p style="margin:0 0 10px 0;font-size:15px;color:#0f172a;">Merhaba,</p>'
                    . '<p style="margin:0 0 16px 0;font-size:15px;color:#334155;">Hesabınıza yeni bir giriş algıladık. Aşağıdaki bilgileri kontrol edebilirsiniz:</p>'
                    . '<div style="border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">'
                    . '<div style="padding:10px 12px;background:#f8fafc;color:#0f172a;font-weight:700;">Giriş Bilgileri</div>'
                    . '<table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;">'
                    . '<tr><td style="padding:10px 12px;color:#64748b;border-top:1px solid #e5e7eb;width:160px;"><strong>Kullanıcı</strong></td><td style="padding:10px 12px;border-top:1px solid #e5e7eb;color:#0f172a;">' . $safeUserInfo . '</td></tr>'
                    . '<tr><td style="padding:10px 12px;color:#64748b;border-top:1px solid #e5e7eb;width:160px;"><strong>Giriş Saati</strong></td><td style="padding:10px 12px;border-top:1px solid #e5e7eb;color:#0f172a;">' . $safeTime . '</td></tr>'
                    . '<tr><td style="padding:10px 12px;color:#64748b;border-top:1px solid #e5e7eb;width:160px;"><strong>IP Adresi</strong></td><td style="padding:10px 12px;border-top:1px solid #e5e7eb;color:#0f172a;">' . $safeIp . '</td></tr>'
                    . $uaRowHtml
                    . '</table>'
                    . '</div>'
                    . '<div style="margin-top:16px;padding:14px 14px;background:#fff7ed;border:1px solid #fed7aa;border-radius:12px;color:#9a3412;">'
                    . '<strong>Bu siz değilseniz:</strong> Lütfen bizimle hemen iletişime geçin ve şifrenizi değiştirin.'
                    . '</div>'
                    . '<p style="margin:16px 0 0 0;font-size:12px;color:#64748b;">Not: Bu e-posta otomatik olarak gönderilmiştir.</p>'
                    . '</div>'
                    . '</div>'
                    . '<div style="text-align:center;margin-top:14px;font-size:12px;color:#94a3b8;">© ' . date('Y') . ' YonApp</div>'
                    . '</div>'
                    . '</body></html>';
                // ... e-posta gönderme kodunuz ...
                if(MailGonderService::gonder([$user->email], "Yeni Giriş Bildirimi", $icerik)) {
                    $logger->info("Giriş bildirimi e-postası gönderildi.", ['user_id' => $user->id, 'email' => $user->email]);
                } else {
                    $logger->error("Giriş bildirimi e-postası gönderilemedi.", ['user_id' => $user->id, 'email' => $user->email]);
                }
            } catch (\Exception $e) {
                // Hata loglama
                $logger->error("Giriş bildirimi e-postası gönderilemedi.", ['error' => $e->getMessage()]);
            }
        }
    }
}
