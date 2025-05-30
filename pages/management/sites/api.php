<?php
session_start();
require_once '../../../vendor/autoload.php';

use Model\SitelerModel;
use App\Helper\Security;

$Siteler = new SitelerModel();


if ($_POST["action"] == "save_sites") {
    $id = Security::decrypt($_POST["id"]);

    $data = [
        "id" => $id,
        "user_id" => $_SESSION["user"]->id,
        "site_adi" => $_POST["sites_name"],
        "telefon" => $_POST["phone"],
        "il" => $_POST["il"],
        "ilce" => $_POST["ilce"],
        "tam_adres" => $_POST["adres"],
        "aciklama" => $_POST["description"],
        "logo_path" => $_POST["selectedLogo"],
        "aktif_mi" => 1,
    ];

    $lastInsertId = $Siteler->saveWithAttr($data);

    $res = [
        "status" => "success",
        "message" => "Başarılı"
    ];
    echo json_encode($res);
}

if ($_POST["action"] == "delete-Siteler") {
    $Siteler->delete($_POST["id"]);

    $res = [
        "status" => "success",
        "message" => "Başarılı"
    ];
    echo json_encode($res);
    session_destroy();
}
