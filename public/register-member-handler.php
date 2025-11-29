<?php
require_once __DIR__ . '/../configs/bootstrap.php';

use App\Helper\Security;
use App\Services\FlashMessageService;
use App\Services\MailGonderService;
use App\Services\SmsGonderService;
use Model\UserModel;
use Database\Db;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /register-member.php');
    exit;
}

$action = $_POST['action'] ?? '';
if (!in_array($action, ['register_member', 'register_member_email', 'register_member_phone'])) {
    header('Location: /register-member.php');
    exit;
}

$fullName = trim($_POST['full_name'] ?? '');
$pass     = $_POST['password'] ?? '';
$pass2    = $_POST['password2'] ?? '';
$kisiEnc  = $_POST['kisi'] ?? '';

if ($fullName === '' || $pass === '' || $pass2 === '') {
    FlashMessageService::add('error', 'Hata!', 'Lütfen tüm alanları doldurunuz.');
    header('Location: /register-member.php?kisi=' . urlencode($kisiEnc));
    exit;
}
if ($pass !== $pass2) {
    FlashMessageService::add('error', 'Hata!', 'Şifreler eşleşmiyor.');
    header('Location: /register-member.php?kisi=' . urlencode($kisiEnc));
    exit;
}
if (!isset($_POST['terms_of_service'])) {
    FlashMessageService::add('error', 'Hata!', 'Üyelik koşullarını kabul etmelisiniz.');
    header('Location: /register-member.php?kisi=' . urlencode($kisiEnc));
    exit;
}

$User = new UserModel();

if ($action === 'register_member' || $action === 'register_member_email') {
    $email = trim($_POST['email'] ?? '');
    if ($email === '') {
        FlashMessageService::add('error', 'Hata!', 'E-posta alanı zorunludur.');
        header('Location: /register-member.php?kisi=' . urlencode($kisiEnc));
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        FlashMessageService::add('error', 'Hata!', 'Geçerli bir e-posta adresi giriniz.');
        header('Location: /register-member.php?email=' . urlencode($email) . '&kisi=' . urlencode($kisiEnc));
        exit;
    }
    if ($User->isEmailExists($email)) {
        FlashMessageService::add('error', 'Hata!', 'Bu email adresi ile daha önce kayıt olunmuş. Lütfen giriş yapın.');
        header('Location: /sign-in.php?email=' . urlencode($email));
        exit;
    }
}

// işlem

try {
    $db = Db::getInstance();
    $pdo = $db->connect();
    $pdo->beginTransaction();

    if ($action === 'register_member' || $action === 'register_member_email') {
        $email = Security::escape(trim($_POST['email'] ?? ''));
        $data = [
            'id' => 0,
            'full_name' => Security::escape($fullName),
            'email' => $email,
            'status' => 0,
            'roles' => 3,
            'is_main_user' => 0,
            'password' => password_hash($pass, PASSWORD_DEFAULT),
            'activate_token' => Security::encrypt(time() + 3600)
        ];

        $insertId = $User->saveWithAttr($data);

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $baseUrl = $protocol . '://' . $host;
        $activate_link = "$baseUrl/register-activate.php?email=" . ($email) . "&token=" . $data['activate_token'];
        MailGonderService::gonder([$email], $fullName, $activate_link);

        selfEnsureRegistrationMethodTable($pdo);
        selfUpsertRegistrationMethod($pdo, Security::decrypt($insertId), 'email');

        $pdo->commit();
        FlashMessageService::add('success', 'Kayıt Başarılı', 'Aktivasyon e-postası gönderildi. Lütfen e-postadaki bağlantı ile hesabınızı doğrulayın.', 'onay2.png');
        header('Location: /register-activate.php?email=' . urlencode($email));
        exit;
    } else { // phone
        $countryCode = trim($_POST['country_code'] ?? '');
        $phoneRaw    = trim($_POST['phone'] ?? '');
        $userName    = trim($_POST['user_name'] ?? '');

        if ($countryCode === '' || $phoneRaw === '' || $userName === '') {
            FlashMessageService::add('error', 'Hata!', 'Ülke kodu, telefon ve kullanıcı adı zorunludur.');
            header('Location: /register-member.php');
            exit;
        }

        $normalizedPhone = preg_replace('/\D+/', '', $phoneRaw);
        $normalizedCode  = preg_replace('/\D+/', '', $countryCode);
        $fullPhone       = '+' . $normalizedCode . $normalizedPhone;

        $existingByPhone = $User->findWhere(['phone' => $fullPhone]);
        if (!empty($existingByPhone)) {
            FlashMessageService::add('error', 'Hata!', 'Bu telefon numarası ile daha önce kayıt olunmuş.');
            header('Location: /sign-in.php');
            exit;
        }

        $pseudoEmail = 'phone_' . ($normalizedCode . $normalizedPhone) . '@yonapp.local';

        $data = [
            'id' => 0,
            'full_name' => Security::escape($fullName),
            'email' => $pseudoEmail,
            'phone' => $fullPhone,
            'status' => 0,
            'roles' => 3,
            'is_main_user' => 0,
            'password' => password_hash($pass, PASSWORD_DEFAULT)
        ];

        $insertId = $User->saveWithAttr($data);
        $userId   = Security::decrypt($insertId);

        selfEnsurePhoneVerifyTable($pdo);
        $code = (string)random_int(100000, 999999);
        $expiresAt = date('Y-m-d H:i:s', time() + 10 * 60);
        selfInsertPhoneVerify($pdo, $userId, $normalizedCode, $fullPhone, $code, $expiresAt, $userName);

        SmsGonderService::gonder([$fullPhone], 'YONAPP doğrulama kodunuz: ' . $code);

        selfEnsureRegistrationMethodTable($pdo);
        selfUpsertRegistrationMethod($pdo, $userId, 'phone');

        $pdo->commit();
        FlashMessageService::add('success', 'Kayıt Başarılı', 'SMS doğrulama kodu gönderildi. Lütfen kodu girerek hesabınızı doğrulayın.', 'onay2.png');
        header('Location: /register-member-phone-verify.php?uid=' . urlencode($insertId));
        exit;
    }
} catch (\Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) { $pdo->rollBack(); }
    FlashMessageService::add('error', 'Hata!', 'Kayıt sırasında bir hata oluştu: ' . $e->getMessage());
    header('Location: /register-member.php');
    exit;
}

function selfEnsurePhoneVerifyTable(\PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_phone_verifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        country_code VARCHAR(8) NOT NULL,
        phone VARCHAR(32) NOT NULL,
        code VARCHAR(10) NOT NULL,
        expires_at DATETIME NOT NULL,
        verified_at DATETIME NULL,
        user_name VARCHAR(100) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");
}

function selfInsertPhoneVerify(\PDO $pdo, int $userId, string $countryCode, string $phone, string $code, string $expiresAt, string $userName): void {
    $stmt = $pdo->prepare('INSERT INTO user_phone_verifications (user_id, country_code, phone, code, expires_at, user_name) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$userId, $countryCode, $phone, $code, $expiresAt, $userName]);
}

function selfEnsureRegistrationMethodTable(\PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_registration_methods (
        user_id INT PRIMARY KEY,
        method ENUM('email','phone') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");
}

function selfUpsertRegistrationMethod(\PDO $pdo, int $userId, string $method): void {
    $stmt = $pdo->prepare('INSERT INTO user_registration_methods (user_id, method) VALUES (?, ?) ON DUPLICATE KEY UPDATE method = VALUES(method)');
    $stmt->execute([$userId, $method]);
}
