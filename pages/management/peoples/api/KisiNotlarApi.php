<?php
require_once dirname(__DIR__, 4) . '/configs/bootstrap.php';

use App\Helper\Security;
use Model\KisiNotModel;

$Notlar = new KisiNotModel();

$action = $_POST["action"] ?? '';

if ($action === "NotEkle") {
    $id = Security::decrypt($_POST["id"] ?? 0);
    $isUpdate = !empty($id) || $id !== 0;

    $data = [
        "id" => $id,
        "kisi_id" => $_POST["kisi_id"],
        "icerik" => $_POST["icerik"],
        "kayit_yapan" => $_SESSION["user"]->id ?? null,
        "site_id" => $_SESSION["site_id"] ?? null,
    ];

    $lastInsertId = $Notlar->saveWithAttr($data);

    if (!$lastInsertId && $isUpdate) {
        $lastInsertId = $id;
    }

    if (!$lastInsertId) {
        echo json_encode([
            "status" => "error",
            "message" => "Not kaydedilemedi."
        ]);
        exit;
    }

    if ($isUpdate) {
        $realId = $lastInsertId;
        $sira = $_POST["sira_no"] ?? null;
        $yeniNotSatiri = $Notlar->notEkleTableRow($realId, $sira);
    } else {
        $realId = Security::decrypt($lastInsertId);
        $yeniNotSatiri = $Notlar->notEkleTableRow($realId);
    }

    echo json_encode([
        "status" => "success",
        "message" => "Başarılı",
        "id" => $realId,
        "yeniNotSatiri" => $yeniNotSatiri
    ]);
    exit;
}

if ($action === "delete_note") {
    $Notlar->delete($_POST["id"]);

    echo json_encode([
        "status" => "success",
        "message" => "Başarılı"
    ]);
    exit;
}
