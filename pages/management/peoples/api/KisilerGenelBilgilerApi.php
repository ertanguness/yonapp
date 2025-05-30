<?php
session_start();
require_once '../../../vendor/autoload.php';
$site_id = $_SESSION["site_id"];

use Model\DairelerModel;
use App\Helper\Security;

$Daireler = new DairelerModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['blok_id'])) {
    $blok_id = (int) $_POST['blok_id'];

    $Daireler = new DairelerModel();
    $daireler = $Daireler->BlokDaireleri($blok_id);

    $response = [];
    foreach ($daireler as $daire) {
        $response[] = [
            'id' => $daire->id,
            'no' => $daire->daire_no
        ];
    }

    echo json_encode($response);
    exit;
}

/*
if ($_POST["action"] == "save_apartment") {
    $id = Security::decrypt($_POST["id"]);
    $block_id = $_POST["blockName"];
    $daire_no = $_POST["flatNumber"];
    $daire_kodu = $_POST["daire_kodu"] ?? null;


    if ($Apartment->DaireVarmi($site_id, $block_id, $daire_no)) {
        $existing_apartment = $daire_no;
    }
    if (!empty($existing_apartment)) {
        echo json_encode([
            "status" => "error",
            "message" => $existing_apartment ." numaralı daire ilgili blokta zaten kayıtlı: "  
        ]);
        exit;
    }
    if ($Apartment->DaireKoduVarMi($site_id, $block_id, $daire_kodu)) {
        $mevcut_kod = $daire_kodu;
    }

    if (!empty($mevcut_kod)) {
        echo json_encode([
            "status" => "error",
            "message" => $mevcut_kod ." kod önceden oluşturulmuş lütfen oluşturmak istediğini kodu giriniz:  "  
        ]);
        exit;
    }

    $data = [
        "id" => $id,
        "site_id" => $site_id,
        "blok_id" => $block_id,
        "kat" => $_POST["floor"],
        "daire_no" => $daire_no,
        "daire_kodu" => $daire_kodu,
        "daire_tipi" => $_POST["apartment_type"],
        "brut_alan" => $_POST["grossArea"],
        "net_alan" => $_POST["netArea"],
        "arsa_payi" => $_POST["landShare"],
        "aktif_mi" => isset($_POST["status"]) ? 1 : 0
    ];

    $lastInsertId = $Apartment->saveWithAttr($data);

    $res = [
        "status" => "success",
        "message" => "Başarılı"
    ];
    echo json_encode($res);
}

if ($_POST["action"] == "delete_apartment") {
    $Apartment->delete($_POST["id"]);

    $res = [
        "status" => "success",
        "message" => "Başarılı"
    ];
    echo json_encode($res);
} 
    */
