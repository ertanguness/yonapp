<?php
require_once __DIR__ . '/../configs/bootstrap.php';

use App\Services\FlashMessageService;
use App\Helper\Security;
use App\Services\SmsGonderService;
use Database\Db;
use Model\UserModel;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /register-member.php');
    exit;
}

$action = $_POST['action'] ?? '';
$uidEnc = $_POST['uid'] ?? '';
$vidEnc = $_POST['vid'] ?? '';
$userId = $uidEnc ? Security::decrypt($uidEnc) : null;
$verifyId = $vidEnc ? Security::decrypt($vidEnc) : null;

if ($action === 'verify_phone') {
    $code   = trim($_POST['code'] ?? '');
    if ($code === '' || !preg_match('/^\d{6}$/', $code)) {
        FlashMessageService::add('error', 'Hata!', 'Geçerli bir doğrulama kodu giriniz.');
        $back = $vidEnc ? ('vid=' . urlencode($vidEnc)) : ('uid=' . urlencode($uidEnc));
        header('Location: /register-member-phone-verify.php?' . $back);
        exit;
    }

try {
    $db = Db::getInstance();
    $pdo = $db->connect();
    $pdo->beginTransaction();

    // Son doğrulama kaydını al
    if ($verifyId) {
        $stmt = $pdo->prepare('SELECT * FROM user_phone_verifications WHERE id = ?');
        $stmt->execute([$verifyId]);
    } else {
        $stmt = $pdo->prepare('SELECT * FROM user_phone_verifications WHERE user_id = ? ORDER BY id DESC LIMIT 1');
        $stmt->execute([$userId]);
    }
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
        // Kullanıcıyı oluştur ve doğrula (OTP sonrası kayıt tamamlanır)
        $User = new UserModel();
        // benzersizlik kontrolü
        $existByPhone = $User->findWhere(['phone' => $verify->phone]);
        if (!empty($existByPhone)) { throw new \Exception('Bu telefon zaten kayıtlı.'); }
        $pseudoEmail = $verify->pseudo_email ?: ('phone_' . $verify->country_code . preg_replace('/\D+/','',$verify->phone) . '@yonapp.local');
        $existByEmail = $User->isEmailExists($pseudoEmail);
        if ($existByEmail) { throw new \Exception('Email zaten kayıtlı.'); }

        $data = [
            'id' => 0,
            'full_name' => $verify->full_name,
            'email' => $pseudoEmail,
            'phone' => $verify->phone,
            'status' => 1,
            'roles' => 3,
            'is_main_user' => 0,
            'password' => $verify->password_hash
        ];
        $encUserId = $User->saveWithAttr($data);
        $createdUserId = Security::decrypt($encUserId);
        // doğrulama kaydını güncelle ve ilişkilendir
        $pdo->prepare('UPDATE user_phone_verifications SET verified_at = NOW(), user_id = ? WHERE id = ?')->execute([$createdUserId, $verify->id]);

        // kayıt yöntemi işaretle
        $pdo->exec("CREATE TABLE IF NOT EXISTS user_registration_methods (user_id INT PRIMARY KEY, method ENUM('email','phone') NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");
        $stmtUp = $pdo->prepare('INSERT INTO user_registration_methods (user_id, method) VALUES (?, ?) ON DUPLICATE KEY UPDATE method = VALUES(method)');
        $stmtUp->execute([$createdUserId, 'phone']);
        FlashMessageService::add('success', 'Başarılı!', 'Telefon doğrulandı. Artık giriş yapabilirsiniz.', 'onay2.png');
    }

    $pdo->commit();
    header('Location: /sign-in.php');
    exit;
} catch (\Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) { $pdo->rollBack(); }
    FlashMessageService::add('error', 'Hata!', 'Doğrulama başarısız: ' . $e->getMessage());
    $back = $vidEnc ? ('vid=' . urlencode($vidEnc)) : ('uid=' . urlencode($uidEnc));
    header('Location: /register-member-phone-verify.php?' . $back);
    exit;
}

}

if ($action === 'resend_code') {
    try {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $key = 'resend_count_' . $userId;
        $count = $_SESSION[$key] ?? 0;
        if ($count >= 3) {
            FlashMessageService::add('error', 'Hata!', 'Tekrar gönderme sınırına ulaşıldı.');
            header('Location: /register-member-phone-verify.php?uid=' . urlencode($uidEnc));
            exit;
        }

        $db = Db::getInstance();
        $pdo = $db->connect();

        if ($verifyId) {
            $stmt = $pdo->prepare('SELECT phone, country_code FROM user_phone_verifications WHERE id = ?');
            $stmt->execute([$verifyId]);
        } else {
            $stmt = $pdo->prepare('SELECT phone, country_code FROM user_phone_verifications WHERE user_id = ? ORDER BY id DESC LIMIT 1');
            $stmt->execute([$userId]);
        }
        $row = $stmt->fetch(\PDO::FETCH_OBJ);
        if (!$row) { throw new \Exception('Doğrulama kaydı bulunamadı'); }

        $code = (string)random_int(100000, 999999);
        $expiresAt = date('Y-m-d H:i:s', time() + 10*60);
        if ($verifyId) {
            $pdo->prepare('UPDATE user_phone_verifications SET code = ?, expires_at = ?, verified_at = NULL WHERE id = ?')->execute([$code, $expiresAt, $verifyId]);
        } else {
            $pdo->prepare('UPDATE user_phone_verifications SET code = ?, expires_at = ?, verified_at = NULL WHERE user_id = ? ORDER BY id DESC LIMIT 1')->execute([$code, $expiresAt, $userId]);
        }
        SmsGonderService::gonder([$row->phone], 'YONAPP doğrulama kodunuz: ' . $code);
        $_SESSION[$key] = $count + 1;

        FlashMessageService::add('success', 'Başarılı', 'Yeni doğrulama kodu gönderildi.', 'onay2.png');
        $back = $vidEnc ? ('vid=' . urlencode($vidEnc)) : ('uid=' . urlencode($uidEnc));
        header('Location: /register-member-phone-verify.php?' . $back);
        exit;
    } catch (\Throwable $e) {
        FlashMessageService::add('error', 'Hata!', 'Kod gönderilemedi: ' . $e->getMessage());
        $back = $vidEnc ? ('vid=' . urlencode($vidEnc)) : ('uid=' . urlencode($uidEnc));
        header('Location: /register-member-phone-verify.php?' . $back);
        exit;
    }
}