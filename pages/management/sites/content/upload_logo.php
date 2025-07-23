<?php
session_start();

use App\Helper\Security;

$id = 0;
if (isset($_POST['site_id'])) {
    $id = $_POST['site_id'];
}

$uploadDir = dirname(__DIR__, 4) . '/assets/images/logo/';

if (!empty($_FILES['logoFile']['name'])) {
    $fileName = basename($_FILES['logoFile']['name']);
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($ext, $allowed)) {
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz dosya türü']);
        exit;
    }

    $uniqueName = $id . '-' . uniqid() . '.' . $ext;
    $targetFile = $uploadDir . $uniqueName;

    if (move_uploaded_file($_FILES['logoFile']['tmp_name'], $targetFile)) {
        echo json_encode(['status' => 'success', 'filename' => $uniqueName]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Yükleme başarısız']);
    }
}
