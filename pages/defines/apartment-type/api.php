<?php
session_start();
require_once '../../../vendor/autoload.php';

use Model\DefinesModel;
use App\Helper\Security;

$Defines = new DefinesModel();
$site_id = $_SESSION["site_id"] ?? null;

if ($_POST["action"] == "saveApartmentType") {
    $id = isset($_POST["id"]) ? Security::decrypt($_POST["id"]) : null;
    $apartment_type_name = trim($_POST["apartment_type_name"]);
    $description = trim($_POST["description"]);

    

    // Eğer ID yoksa (yeni kayıt), sadece isim kontrolü yap
    if (!$id && $Defines->isApartmentTypeNameExists($site_id, $apartment_type_name)) {
        echo json_encode([
            "status" => "error",
            "message" => "Bu daire tipi adı zaten kayıtlı."
        ]);
        exit;
    }
    if ($_POST["action"] == "saveApartmentType") {
        $id = Security::decrypt($_POST["id"]);
    
        $data = [
            "site_id" => $site_id,
            "define_name" =>  $apartment_type_name,
            "description" => $description,
            "type" => 3,    
        ];
    
        $lastInsertId = $Defines->saveWithAttr($data);
    
        $res = [
            "status" => "success",
            "message" => "Daire tipi başarıyla kaydedildi.",
        ];
        echo json_encode($res);
    }
}
if ($_POST["action"] == "delete-apartment-type") {
    $Defines->delete($_POST["id"]);

    $res = [
        "status" => "success",
        "message" => "Başarılı"
    ];
    echo json_encode($res);
}



