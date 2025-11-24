<?php
require_once dirname(__DIR__ ,levels: 3). '/configs/bootstrap.php';

use Model\DefinesModel;
use App\Helper\Security;
use App\Modules\Onboarding\Events\OnboardingEvents;

$Defines = new DefinesModel();
$site_id = $_SESSION["site_id"] ?? null;

if ($_POST["action"] == "saveApartmentType") {

    $id = Security::decrypt($_POST["id"]);
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
    
        $data = [
            "id" => $id,
            "site_id" => $site_id,
            "define_name" =>  $apartment_type_name,
            'mulk_tipi' => $_POST['mulk_tipi'],
            "description" => $description,
            "type" => 3,    
        ];
    
        $lastInsertId = $Defines->saveWithAttr($data);
    
        $res = [
            "status" => "success",
            "message" => "Daire tipi başarıyla kaydedildi.",
        ];
        echo json_encode($res);
        try { OnboardingEvents::complete('add_flat_types', $site_id ?? null); } catch (\Throwable $e) {}
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



