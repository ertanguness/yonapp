<?php
require_once '../vendor/autoload.php';

use Model\KisilerModel;
$kisiModel = new KisilerModel();

$response = [];

try {
    if (isset($_POST["action"]) && $_POST["action"] == "daireKisileri") {
        $daire_id = $_POST['daire_id'];

        $response = $kisiModel->DaireKisileri($daire_id);
    } else {
        $response = ['error' => 'Daire ID gönderilmedi'];
    }
} catch (Exception $e) {
    $response = ['error' => $e->getMessage()];
}

echo json_encode($response);


