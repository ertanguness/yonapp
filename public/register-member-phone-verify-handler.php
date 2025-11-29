<?php
require_once __DIR__ . '/../configs/bootstrap.php';

use App\Services\FlashMessageService;
use App\Helper\Security;
use Database\Db;
use Model\UserModel;

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['action'] ?? '') !== 'verify_phone') {
    header('Location: /register-member.php');
    exit;
}

$uidEnc = $_POST['uid'] ?? '';
$code   = trim($_POST['code'] ?? '');

if ($uidEnc === '' || $code === '' || !preg_match('/^\d{6}$/', $code)) {
    FlashMessageService::add('error', 'Hata!', 'Geçerli bir doğrulama kodu giriniz.');
    header('Location: /register-member-phone-verify.php?uid=' . urlencode($uidEnc));
    exit;
}

$userId = Security::decrypt($uidEnc);
if (!$userId) {
    FlashMessageService::add('error', 'Hata!', 'Geçersiz kullanıcı.');
    header('Location: /register-member.php');
    exit;
}

try {
    $db = Db::getInstance();
    $pdo = $db->connect();
    $pdo->beginTransaction();

    // Son doğrulama kaydını al
    $stmt = $pdo->prepare('SELECT * FROM user_phone_verifications WHERE user_id = ? ORDER BY id DESC LIMIT 1');
    $stmt->execute([$userId]);
    $verify = $stmt->fetch(\PDO::FETCH_OBJ);

    if (!$verify) {
        throw new \Exception('Doğrulama kaydı bulunamadı');
    }

    if ($verify->verified_at !== null) {
        FlashMessageService::add('info', 'Bilgi', 'Numara zaten doğrulanmış.');
    } elseif ($verify->code !== $code) {
        throw new \Exception('Kod hatalı');
    } elseif (strtotime($verify->expires_at) < time()) {
        throw new \Exception('Kodun süresi dolmuş');
    } else {
        // Doğrula
        $pdo->prepare('UPDATE user_phone_verifications SET verified_at = NOW() WHERE id = ?')->execute([$verify->id]);
        // Kullanıcıyı aktif et
        $pdo->prepare('UPDATE users SET status = 1 WHERE id = ?')->execute([$userId]);
        FlashMessageService::add('success', 'Başarılı!', 'Telefon doğrulandı. Artık giriş yapabilirsiniz.', 'onay2.png');
    }

    $pdo->commit();
    header('Location: /sign-in.php');
    exit;
} catch (\Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) { $pdo->rollBack(); }
    FlashMessageService::add('error', 'Hata!', 'Doğrulama başarısız: ' . $e->getMessage());
    header('Location: /register-member-phone-verify.php?uid=' . urlencode($uidEnc));
    exit;
}