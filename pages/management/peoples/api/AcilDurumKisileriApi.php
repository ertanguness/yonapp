<?php
session_start();
require_once '../../../../vendor/autoload.php';

use App\Helper\Security;
use Model\AcilDurumKisileriModel;

$site_id = $_SESSION["site_id"];
$AcilDurumKisi = new AcilDurumKisileriModel();

$telefon = $_POST["emergencyPhone"] ?? '';
$action = $_POST["action"] ?? '';

if ($action === "AcilDurumEkle") {
    $id = Security::decrypt($_POST["id"]);
    $isUpdate = !empty($id) || $id !== 0;

    if (!$isUpdate && !empty($telefon) && $AcilDurumKisi->AcilDurumKisiVarmi($telefon)) {
        echo json_encode([
            "status" => "error",
            "message" => $telefon . " telefon numarası ile kayıt önceden yapılmıştır. Lütfen farklı telefon giriniz."
        ]);
        exit;
    }

    $data = [
        "id"               => $id,
        "kisi_id"          => $_POST["kisi_id"],
        "adi_soyadi"            => $_POST["acilDurumKisi"],
        "telefon"      => $_POST["acilDurumKisiTelefon"],
        "yakinlik"      => $_POST["yakinlik"]
    ];

    $lastInsertId = $AcilDurumKisi->saveWithAttr($data);

    if (!$lastInsertId && $isUpdate) {
        $lastInsertId = $id;
    }

    if (!$lastInsertId) {
        echo json_encode([
            "status" => "error",
            "message" => "Kişi kaydedilemedi."
        ]);
        exit;
    }
    // Yeni veya güncellenmiş satırı tabloya döndür
    if ($isUpdate) {
        $realId = $lastInsertId;
        $sira = $_POST["sira_no"] ?? null;
        $acilDurumKisiEkle = $AcilDurumKisi->acilDurumKisiEkleTableRow($realId, $sira);
    } else {
        $realId = Security::decrypt($lastInsertId);
        $acilDurumKisiEkle = $AcilDurumKisi->acilDurumKisiEkleTableRow($realId); // sira null => #
    }

    $acilDurumKisiEkle = $AcilDurumKisi->acilDurumKisiEkleTableRow($realId);

    echo json_encode([
        "status" => "success",
        "message" => "Başarılı",
        "id" => $realId,
        "acilDurumKisiEkle" => $acilDurumKisiEkle
    ]);
    exit;
}

if (isset($_POST["action"]) && $_POST["action"] == "delete_acilDurumKisi") {
    $AcilDurumKisi->delete($_POST["id"]);

    $res = [
        "status" => "success",
        "message" => "Başarılı"
    ];
    echo json_encode($res);
}
