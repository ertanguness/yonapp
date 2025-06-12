<?php
session_start();
require_once '../../../../vendor/autoload.php';
$site_id = $_SESSION["site_id"];

use App\Helper\Security;
use Model\AraclarModel;
use Model\KisilerModel;

$Araclar = new AraclarModel();

if (isset($_POST["action"]) && $_POST["action"] == "AracEkle") {
    $id = Security::decrypt($_POST["id"]);

    $data = [
        "id"               => $id,
        "kisi_id"          => $_POST["kisiSec"],
        "plaka"            => $_POST["modalAracPlaka"],
        "marka_model"      => $_POST["modalAracMarka"]
        
    ];

    $lastInsertId = $Araclar->saveWithAttr($data);
if (!$lastInsertId) {
    echo json_encode([
        "status" => "error",
        "message" => "Araç kaydedilemedi."
    ]);
    exit;
}

$realId = Security::decrypt($lastInsertId); // Şifre çözülüyor
$yeniAracEkle = $Araclar->aracEkleTableRow($realId); // Sayısal ID ile çalışılıyor

    $res = [
        "status" => "success",
        "message" => "Başarılı",
        "id" => $realId,
        "yeniAracEkle" => $yeniAracEkle
    ];
    echo json_encode($res);
}

if (isset($_POST["action"]) && $_POST["action"] == "delete_car") {
    $Araclar->delete($_POST["id"]);

    $res = [
        "status" => "success",
        "message" => "Başarılı"
    ];
    echo json_encode($res);
}
