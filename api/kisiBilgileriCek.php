<?php
require_once dirname(__DIR__ ,levels: 1). '/configs/bootstrap.php';

use Model\KisilerModel;

header('Content-Type: application/json');

if (isset($_POST['id'])) {
    $kisi_id = intval($_POST['id']);
    $kisilerModel = new KisilerModel();
    $kisi = $kisilerModel->KisiBilgileri($kisi_id); // Modelinde tek kişi getiren method

    if ($kisi) {
        echo json_encode([
            'status' => 'success',
            'kimlik_no' => $kisi->kimlik_no
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Kişi bulunamadı']);
    }
    exit;
}
echo json_encode(['status' => 'error', 'message' => 'ID gönderilmedi']);
