<?php
require_once __DIR__ . '/../configs/bootstrap.php';

use App\Helper\Security;
use App\Services\FlashMessageService;
use Model\UserModel;
use Database\Db;

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['action'] ?? '') !== 'register_member') {
    header('Location: /register-member.php');
    exit;
}

$fullName = trim($_POST['full_name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$pass     = $_POST['password'] ?? '';
$pass2    = $_POST['password2'] ?? '';
$kisiEnc  = $_POST['kisi'] ?? '';

if ($fullName === '' || $email === '' || $pass === '' || $pass2 === '') {
    FlashMessageService::add('error', 'Hata!', 'Lütfen tüm alanları doldurunuz.');
    header('Location: /register-member.php?email=' . urlencode($email) . '&kisi=' . urlencode($kisiEnc));
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    FlashMessageService::add('error', 'Hata!', 'Geçerli bir e-posta adresi giriniz.');
    header('Location: /register-member.php?email=' . urlencode($email) . '&kisi=' . urlencode($kisiEnc));
    exit;
}
if ($pass !== $pass2) {
    FlashMessageService::add('error', 'Hata!', 'Şifreler eşleşmiyor.');
    header('Location: /register-member.php?email=' . urlencode($email) . '&kisi=' . urlencode($kisiEnc));
    exit;
}
if (!isset($_POST['terms_of_service'])) {
    FlashMessageService::add('error', 'Hata!', 'Üyelik koşullarını kabul etmelisiniz.');
    header('Location: /register-member.php?email=' . urlencode($email) . '&kisi=' . urlencode($kisiEnc));
    exit;
}

$User = new UserModel();
if ($User->isEmailExists($email)) {
    FlashMessageService::add('error', 'Hata!', 'Bu email adresi ile daha önce kayıt olunmuş. Lütfen giriş yapın.');
    header('Location: /sign-in.php?email=' . urlencode($email));
    exit;
}

try {
    $db = Db::getInstance();
    $pdo = $db->connect();
    $pdo->beginTransaction();

    $data = [
        'id' => 0,
        'full_name' => Security::escape($fullName),
        'email' => Security::escape($email),
        'status' => 1, // davetli kullanıcıyı direkt aktif et
        'roles' => 3,  // not: sistemde "sakin" rol id'si 3 olarak varsayılmıştır
        'is_main_user' => 0,
        'password' => password_hash($pass, PASSWORD_DEFAULT),
    ];

    $insertId = $User->saveWithAttr($data);

    // ileri aşamada kisi ile kullanıcı eşlemesi gerekiyorsa burada yapılabilir
    // $kisiId = $kisiEnc ? (int) Security::decrypt($kisiEnc) : 0;

    $pdo->commit();

    FlashMessageService::add('success', 
                             'Kayıt Başarılı', 
                             'Hesabınız oluşturuldu. Giriş yapabilirsiniz.',
                             'onay2');
    header('Location: /sign-in.php?email=' . urlencode($email));
    exit;
} catch (\Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) { $pdo->rollBack(); }
    FlashMessageService::add('error', 'Hata!', 'Kayıt sırasında bir hata oluştu: ' . $e->getMessage());
    header('Location: /register-member.php?email=' . urlencode($email) . '&kisi=' . urlencode($kisiEnc));
    exit;
}
