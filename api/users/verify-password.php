<?php
ob_start();
error_reporting(0);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

try {
    define('ROOT', $_SERVER['DOCUMENT_ROOT']);
    require_once ROOT . "/configs/bootstrap.php";

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Geçersiz istek yöntemi.');
    }

    if (!isset($_SESSION['user'])) {
        throw new Exception('Oturum bulunamadı.');
    }

    $password = $_POST['password'] ?? '';
    if ($password === '') {
        throw new Exception('Şifre gerekli.');
    }

    $userId = (int)($_SESSION['user']->id ?? 0);
    if ($userId <= 0) {
        throw new Exception('Kullanıcı geçersiz.');
    }

    // Veritabanı bağlantısını al
    $db = getDbConnection();

    // Mevcut kullanıcının şifresini kontrol et
    $stmt = $db->prepare('SELECT password FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception('Kullanıcı bulunamadı.');
    }

    // Session'da kilit denemelerini izle
    if (!isset($_SESSION['lock_attempts'])) {
        $_SESSION['lock_attempts'] = 0;
    }

    // Şifreyi doğrula
    if (!password_verify($password, $user->password)) {
        $_SESSION['lock_attempts']++;
        $remaining = max(0, 3 - $_SESSION['lock_attempts']);

        ob_end_clean();
        echo json_encode([
            'status' => 'error',
            'message' => 'Şifre hatalı.',
            'remaining' => $remaining
        ]);
        exit;
    }

    // Doğru şifre - denemeleri sıfırla
    $_SESSION['lock_attempts'] = 0;
    $_SESSION['profile_unlocked'] = true;

    ob_end_clean();
    echo json_encode([
        'status' => 'success',
        'message' => 'Profil kilidi açıldı.'
    ]);
    exit;

} catch (\Throwable $e) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
