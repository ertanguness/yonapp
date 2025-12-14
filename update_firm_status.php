<?php
require_once __DIR__ . '/configs/session-config.php';

require_once 'vendor/autoload.php';

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo "Oturum açmanız gerekiyor.";
    exit;
}

if (!isset($_POST['site_id']) || !isset($_POST['is_active'])) { 
    http_response_code(400);
    echo "Eksik parametre.";
    exit;
}

$site_id = intval($_POST['site_id']);
$status = intval($_POST['is_active']); // 

try {
    $result = $mysiteObj->updatesiteStatus($site_id, $status);
    if ($result) {
        echo "Başarılı";
    } else {
        echo "Güncelleme başarısız.";
    }
} catch (Exception $e) {
    http_response_code(500);
    echo "Hata: " . $e->getMessage();
}
