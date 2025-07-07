<?php
namespace App\Controllers;

use App\InterFaces\LoggerInterface;
use App\Services\FlashMessageService; 
use Model\UserModel;
use Model\SettingsModel;
use App\Helper\Date;

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
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $validationError = false;

        if (empty($email) || empty($password)) {
            FlashMessageService::add('error', 'Giriş Başarısız!', 'E-posta ve şifre alanları zorunludur.', 'ikaz2.png');
            $validationError = true;
        } else {
            $user = $this->userModel->getUserByEmail($email);

            if (!$user) {
                FlashMessageService::add('error', 'Giriş Başarısız!', 'Bu e-posta adresine kayıtlı bir kullanıcı bulunamadı.', 'ikaz2.png');
                $validationError = true;
            } elseif ($user->status == 0) {
                FlashMessageService::add('warning', 'Hesap Beklemede', 'Hesabınız henüz yönetici tarafından aktifleştirilmedi.', 'bilgi.png');
                $validationError = true;
            } elseif (!password_verify($password, $user->password)) {
                FlashMessageService::add('error', 'Giriş Başarısız!', 'Hatalı şifre girdiniz.', 'ikaz2.png');
                $this->logger->error("Başarısız giriş denemesi.", ['email' => $email, 'ip' => $_SERVER['REMOTE_ADDR']]);
                $validationError = true;
            } else {
                // Demo kontrolü (Bu metot hata durumunda zaten yönlendirme yapıyor)
                self::validateDemoPeriod($user);
            }
        }
        
        if ($validationError) {
            // Hata varsa, girilen e-postayı session'da saklayıp formu tekrar göster
            $_SESSION['old_form_input'] = ['email' => $email];
            header("Location: sign-in.php");
            exit();
        }
        
        // Hata yoksa ve demo süresi de dolmamışsa, giriş yap.
        // Bu metot zaten kendi içinde yönlendirme ve exit() içeriyor.
        self::performLogin($user);
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
            header("Location: sign-in.php?returnUrl={$returnUrl}");
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

    /**
     * Gerekli session'ları ayarlar, loglama yapar.
     * Bu metot artık private değil, public static.
     * @param object $user
     */
    public static function performLogin(object $user): void
    {
        session_regenerate_id(true);

        $_SESSION['user'] = $user;
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['full_name'] = $user->full_name;
        $_SESSION['user_role'] = $user->user_roles;
        $_SESSION["owner_id"] = $user->owner_id;

        // Model ve Servisler
        $userModel = new UserModel();
        $logger = \getLogger();

        // Loglama ve Token işlemleri
        $userModel->setToken($user->id, $_SESSION['csrf_token']);

        $logger->info("Kullanıcı başarıyla giriş yaptı.", [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $_SERVER['REMOTE_ADDR']
        ]);

        // E-posta gönderme (bu da statik bir metoda taşınabilir)
        self::sendLoginNotificationEmail($user);

        //eğer site_id oturumda yoksa, siteyi seçmesi için company-list.php sayfasına yönlendir
        if (!isset($_SESSION['site_id'])) {
            // Site seçimi için company-list.php sayfasına yönlendir
            header("Location: company-list.php");
            exit();
       
        }

        $returnUrl = !empty($_GET['returnUrl']) ? $_GET['returnUrl'] : 'company-list.php';
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
        $sendEmailSetting = $settingsModel->getSettingIdByUserAndAction($user->id, "loginde_mail_gonder");
        
        if (isset($sendEmailSetting) && $sendEmailSetting->set_value == 1) {
            try {
                // ... e-posta gönderme kodunuz ...
            } catch (\Exception $e) {
                // Hata loglama
                \getLogger()->error("Giriş bildirimi e-postası gönderilemedi.", ['error' => $e->getMessage()]);
            }
        }
    }
}