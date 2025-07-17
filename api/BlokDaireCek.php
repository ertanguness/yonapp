<?php
require_once dirname(__DIR__ ,levels: 1). '/configs/bootstrap.php';


use Model\DairelerModel;


$Daireler = new DairelerModel();

$response = [];

try {
    if (isset($_POST["action"]) && $_POST["action"] == "blokDaireleri") {
        $blok_id = $_POST['blok_id'];

        $response = $Daireler->BlokDaireleri($blok_id);
    } else {
        $response = ['error' => 'Blok ID gönderilmedi'];
    }
} catch (Exception $e) {
    $response = ['error' => $e->getMessage()];
}

echo json_encode($response);


