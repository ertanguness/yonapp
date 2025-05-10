<?php
session_start();
require_once "Model/MyFirmModel.php";
$myFirmObj = new MyFirmModel();

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo "Oturum açmanız gerekiyor.";
    exit;
}

if (!isset($_POST['firm_id']) || !isset($_POST['is_active'])) { 
    http_response_code(400);
    echo "Eksik parametre.";
    exit;
}

$firm_id = intval($_POST['firm_id']);
$status = intval($_POST['is_active']); // 

try {
    $result = $myFirmObj->updateFirmStatus($firm_id, $status);
    if ($result) {
        echo "Başarılı";
    } else {
        echo "Güncelleme başarısız.";
    }
} catch (Exception $e) {
    http_response_code(500);
    echo "Hata: " . $e->getMessage();
}
