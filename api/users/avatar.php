<?php
ob_start();
error_reporting(0);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

try {
    define('ROOT', $_SERVER['DOCUMENT_ROOT']);
    require_once ROOT . "/configs/bootstrap.php";

    if (!isset($_SESSION['user'])) throw new Exception('Oturum bulunamadı.');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Geçersiz istek yöntemi.');
    if (!isset($_FILES['avatar']) || !is_uploaded_file($_FILES['avatar']['tmp_name']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) throw new Exception('Dosya yüklenemedi.');

    $uid = (int)($_SESSION['user']->id ?? 0);
    if ($uid <= 0) throw new Exception('Kullanıcı geçersiz.');

    // 2MB boyut limiti
    $maxSize = 2 * 1024 * 1024;
    if ($_FILES['avatar']['size'] > $maxSize) throw new Exception('Dosya boyutu 2MB\'ı aşamaz.');
    
    // Resim çözünürlüğü kontrolü (max 4000x4000)
    if (function_exists('getimagesize')) {
        $imgInfo = @getimagesize($_FILES['avatar']['tmp_name']);
        if ($imgInfo && isset($imgInfo[0], $imgInfo[1])) {
            if ($imgInfo[0] > 4000 || $imgInfo[1] > 4000) {
                throw new Exception('Resim en fazla 4000x4000 piksel olabilir.');
            }
        }
    }
    
    // MIME tespiti
    $mime = null;
    $ext = null;
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    
    if (function_exists('finfo_open')) {
        $finfo = @finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime = @finfo_file($finfo, $_FILES['avatar']['tmp_name']);
            @finfo_close($finfo);
        }
    }
    
    if (!$mime) {
        $info = @getimagesize($_FILES['avatar']['tmp_name']);
        if ($info && isset($info['mime'])) {
            $mime = $info['mime'];
        }
    }
    
    if (!$mime || !isset($allowed[$mime])) {
        throw new Exception('Yalnızca JPG, PNG veya WEBP yükleyin.');
    }
    $ext = $allowed[$mime];

    // Upload dizini
    $uploadDir = rtrim(ROOT, '\\/') . '/uploads/avatars/';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0775, true);
    }

    // Eski avatar dosyasını sil
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $e) {
        $old = $uploadDir . "user_{$uid}.{$e}";
        if (file_exists($old)) {
            @unlink($old);
        }
    }

    // Yeni dosyayı taşı
    $target = $uploadDir . "user_{$uid}.{$ext}";
    if (!@move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
        throw new Exception('Dosya kaydedilemedi.');
    }

    // Oturumda saklayalım (opsiyonel)
    $url = "/uploads/avatars/user_{$uid}.{$ext}";
    $_SESSION['user']->avatar_url = $url;

    ob_end_clean();
    echo json_encode(['status' => 'success', 'message' => 'Profil resmi güncellendi.', 'url' => $url]);
    exit;

} catch (\Throwable $e) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
