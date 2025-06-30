<?php
session_start();
require_once '../../../../vendor/autoload.php';

use App\Helper\Security;
use Model\AraclarModel;

$site_id = $_SESSION["site_id"];
$Araclar = new AraclarModel();

$plaka = $_POST["modalAracPlaka"] ?? '';
$action = $_POST["action"] ?? '';

if ($action === "AracEkle") {
    $id = Security::decrypt($_POST["id"] ?? 0);
    $isUpdate = !empty($id) || $id !== 0;

    // Eğer yeni kayıt yapılacaksa ve aynı plaka varsa uyarı ver
    if (!$isUpdate && !empty($plaka) && $Araclar->AracVarmi($plaka)) {
        echo json_encode([
            "status" => "error",
            "message" => "$plaka plakası ile kayıt önceden yapılmıştır. Lütfen farklı plaka giriniz."
        ]);
        exit;
    }

    // Veri dizisi hazırlanıyor
    $data = [
        "id" => $id,
        "kisi_id" => $_POST["kisi_id"],
        "plaka" => $_POST["modalAracPlaka"],
        "marka_model" => $_POST["modalAracMarka"]
    ];

    // Kayıt işlemi
    $lastInsertId = $Araclar->saveWithAttr($data);

    // Eğer güncelleme ise, lastInsertId false döner; bu durumda mevcut ID'yi al
    if (!$lastInsertId && $isUpdate) {
        $lastInsertId = $id;
    }

    // Eğer kayıt başarısızsa
    if (!$lastInsertId) {
        echo json_encode([
            "status" => "error",
            "message" => "Araç kaydedilemedi."
        ]);
        exit;
    }
    // Yeni veya güncellenmiş satırı tabloya döndür
    if ($isUpdate) {
        $realId = $lastInsertId;
        $sira = $_POST["sira_no"] ?? null;
        $yeniAracEkle = $Araclar->aracEkleTableRow($realId, $sira);
    } else {
        $realId = Security::decrypt($lastInsertId);
        $yeniAracEkle = $Araclar->aracEkleTableRow($realId); // sira null => #
    }


    $yeniAracEkle = $Araclar->aracEkleTableRow($realId);

    echo json_encode([
        "status" => "success",
        "message" => "Başarılı",
        "id" => $realId,
        "yeniAracEkle" => $yeniAracEkle
    ]);
    exit;
}

if ($action === "delete_car") {
    $Araclar->delete($_POST["id"]);

    echo json_encode([
        "status" => "success",
        "message" => "Başarılı"
    ]);
    exit;
}
