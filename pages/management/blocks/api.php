<?php

require_once '../../../vendor/autoload.php';

use Model\SitesModel;
use App\Helper\Security;

$Sites = new SitesModel();


if ($_POST["action"] == "save_sites") {
    $id = Security::decrypt($_POST["id"]);

    $data = [
        "id" => $id,
        "firm_name" => $_POST["sites_name"],
        "phone" => $_POST["phone"],
        "logo" => $_POST["selectedLogo"],
        "description" => $_POST["description"],
        "il" => $_POST["il"],
        "ilce" => $_POST["ilce"],
        "adres" => $_POST["adres"],
        "is_active" => 1,
    ];

    $lastInsertId = $Sites->saveWithAttr($data);

    $res = [
        "status" => "success",
        "message" => "Başarılı"
    ];
    echo json_encode($res);
}

if ($_POST["action"] == "delete_sites") {
    $Sites->delete($_POST["id"]);

    $res = [
        "status" => "success",
        "message" => "Başarılı"
    ];
    echo json_encode($res);
}
