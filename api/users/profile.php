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

    $action = $_POST['action'] ?? '';
    if ($action !== 'updateProfile') {
        throw new Exception('Geçersiz işlem.');
    }

    if (!isset($_SESSION['user'])) {
        throw new Exception('Oturum bulunamadı. Lütfen tekrar giriş yapın.');
    }

    $userId = (int)($_SESSION['user']->id ?? 0);
    if ($userId <= 0) {
        throw new Exception('Kullanıcı bilgisi bulunamadı.');
    }

    // Veritabanı bağlantısını al
    $db = getDbConnection();

    // Mevcut kullanıcıyı çek
    $stmtUser = $db->prepare('SELECT id, password FROM users WHERE id = :id LIMIT 1');
    $stmtUser->execute([':id' => $userId]);
    $currentUser = $stmtUser->fetch();

    if (!$currentUser) {
        throw new Exception('Kullanıcı bulunamadı.');
    }

    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $job = trim($_POST['job'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $sicil_no = trim($_POST['sicil_no'] ?? '');

    if ($full_name === '' || $email === '') {
        throw new Exception('Ad soyad ve e-posta zorunludur.');
    }

    // Email benzersizliği: başka bir kullanıcıda var mı?
    $stmt = $db->prepare('SELECT id FROM users WHERE email = :email AND id <> :id LIMIT 1');
    $stmt->execute([':email' => $email, ':id' => $userId]);
    if ($stmt->fetch()) {
        throw new Exception('Bu e-posta adresi başka bir kullanıcı tarafından kullanılıyor.');
    }

    $data = [
        'full_name' => $full_name,
        'email' => $email,
        'phone' => $phone,
        'job' => $job,
        'title' => $title,
        'sicil_no' => $sicil_no,
    ];

    // Şifre değişikliği isteniyor mu?
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $current_password = $_POST['current_password'] ?? '';

    if ($new_password !== '' || $confirm_password !== '' || $current_password !== '') {
        if ($new_password === '' || $confirm_password === '' || $current_password === '') {
            throw new Exception('Şifre değişikliği için tüm alanları doldurun.');
        }
        if ($new_password !== $confirm_password) {
            throw new Exception('Yeni şifreler uyuşmuyor.');
        }
        if (!password_verify($current_password, $currentUser->password)) {
            throw new Exception('Mevcut şifre hatalı.');
        }
        $data['password'] = password_hash($new_password, PASSWORD_DEFAULT);
    }

    // Güncelleme - Dinamik UPDATE sorgusu hazırla
    $setParts = [];
    $params = [':id' => $userId];
    foreach ($data as $col => $val) {
        $setParts[] = "$col = :$col";
        $params[":$col"] = $val;
    }
    $sql = 'UPDATE users SET ' . implode(', ', $setParts) . ' WHERE id = :id';
    $stmtUp = $db->prepare($sql);
    if (!$stmtUp->execute($params)) {
        throw new Exception('Güncelleme sırasında hata oluştu.');
    }

    // Session güncelle
    foreach ($data as $k => $v) {
        if ($k === 'password') {
            continue;
        }
        $_SESSION['user']->{$k} = $v;
    }

    ob_end_clean();
    echo json_encode(['status' => 'success', 'message' => 'Profil bilgileriniz güncellendi.']);
    exit;

} catch (\Throwable $e) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
