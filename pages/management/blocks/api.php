<?php
require_once dirname(__DIR__ ,levels: 3). '/configs/bootstrap.php';


use Model\BloklarModel;
use App\Helper\Security;

$Blocks = new BloklarModel();


if ($_POST["action"] == "save_blocks") {
    $id = Security::decrypt($_POST["id"]);
    $site_id = $_POST["site_id"];
   // $blocksNumber = $_POST["blocksNumber"];
    $block_names = $_POST["block_names"] ?? [];
    $apartment_counts = $_POST["apartment_counts"] ?? [];

    $existing_blocks = [];

    // Eğer $id 0 veya boş değilse, bu kısmı atla
    if (empty($id) || $id == 0) {
        foreach ($block_names as $key => $block_name) {
            if ($Blocks->BlokVarmi($site_id, $block_name)) {
                $existing_blocks[] = $block_name;
            }
        }
    }

    if (!empty($existing_blocks)) {
        echo json_encode([
            "status" => "error",
            "message" => "Aşağıdaki blok isimleri zaten kayıtlı: " . implode(", ", $existing_blocks)
        ]);
        exit;
    }

    foreach ($block_names as $key => $block_name) {
        $Blocks->saveWithAttr([
            "id" => $id,
            "site_id" => $site_id,
            "blok_adi" => $block_name,
            "daire_sayisi" => $apartment_counts[$key] ?? 0,
            "aktif_mi" => 1,

        ]);
    }

    echo json_encode([
        "status" => "success",
        "message" => "Bloklar başarıyla kaydedildi."
    ]);
}



if ($_POST["action"] == "delete_blocks") {
    $Blocks->delete($_POST["id"]);

    $res = [
        "status" => "success",
        "message" => "Başarılı"
    ];
    echo json_encode($res);
}
