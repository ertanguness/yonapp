<?php
session_start();
require_once '../../../../vendor/autoload.php';
$site_id = $_SESSION["site_id"];

use App\Helper\Security;
use Model\AcilDurumKisileriModel;

$AcilDurumKisi = new AcilDurumKisileriModel();

$telefon=$_POST["emergencyPhone"] ?? '';


if (!empty($telefon) && $AcilDurumKisi->AcilDurumKisiVarmi($telefon)) {
    echo json_encode([
        "status" => "error",
        "message" => $telefon . " telefon numarası ile kayıt önceden yapılmıştır. Lütfen farklı telefon giriniz."
    ]);
    exit;
}

if (isset($_POST["action"]) && $_POST["action"] == "AcilDurumEkle") {
    $id = Security::decrypt($_POST["id"]);

    $data = [
        "id"               => $id,
        "kisi_id"          => $_POST["kisi_id"],
        "adi_soyadi"            => $_POST["acilDurumKisi"],
        "telefon"      => $_POST["acilDurumKisiTelefon"],
        "yakinlik"      => $_POST["yakinlik"]
    ];

    $lastInsertId = $AcilDurumKisi->saveWithAttr($data);
if (!$lastInsertId) {
    echo json_encode([
        "status" => "error",
        "message" => "Kişi kaydedilemedi."
    ]);
    exit;
}

$realId = Security::decrypt($lastInsertId); // Şifre çözülüyor
$yeniAcilDurumKisiEkle = $AcilDurumKisi->acilDurumKisiEkleTableRow($realId); // Sayısal ID ile çalışılıyor

    $res = [
        "status" => "success",
        "message" => "Başarılı",
        "id" => $realId,
        "yeniAcilDurumKisiEkle" => $yeniAcilDurumKisiEkle
    ];
    echo json_encode($res);
}

if (isset($_POST["action"]) && $_POST["action"] == "delete_acilDurumKisi") {
    $AcilDurumKisi->delete($_POST["id"]);

    $res = [
        "status" => "success",
        "message" => "Başarılı"
    ];
    echo json_encode($res);
}
