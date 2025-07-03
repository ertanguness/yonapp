<?php
require_once dirname(__DIR__ ,levels: 3). '/configs/bootstrap.php';


$site_id = $_SESSION["site_id"];

use Model\DairelerModel;
use App\Helper\Security;

$daireModel = new DairelerModel();


if ($_POST["action"] == "save_apartment") {
    $id = Security::decrypt($_POST["id"]);
    $block_id = $_POST["blockName"];
    $daire_no = $_POST["flatNumber"];
    $daire_kodu = $_POST["daire_kodu"] ?? null;


    // Sadece yeni kayıt (id 0 veya boş) ise daire var mı kontrolü yap
    if (empty($id) || $id == 0) {
        if ($daireModel->DaireVarmi($site_id, $block_id, $daire_no)) {
            $existing_apartment = $daire_no;
        }
        if (!empty($existing_apartment)) {
            echo json_encode([
                "status" => "error",
                "message" => $existing_apartment . " numaralı daire ilgili blokta zaten kayıtlı: "
            ]);
            exit;
        }
    }
    if ($daireModel->DaireKoduVarMi($site_id, $block_id, $daire_kodu, $id)) {
        $mevcut_kod = $daire_kodu;
    }
    

    if (!empty($mevcut_kod)) {
        echo json_encode([
            "status" => "error",
            "message" => $mevcut_kod . " kod önceden oluşturulmuş lütfen oluşturmak istediğini kodu giriniz:  "
        ]);
        exit;
    }

    $data = [
        "id" => $id,
        "site_id" => $site_id,
        "blok_id" => $block_id,
        "kat" => $_POST["floor"],
        "daire_no" => $daire_no,
        "daire_kodu" => (empty($id) || $id == 0) ? $daire_kodu : ($mevcut_kod ?? $daire_kodu),
        "daire_tipi" => $_POST["apartment_type"],
        "brut_alan" => $_POST["grossArea"],
        "net_alan" => $_POST["netArea"],
        "arsa_payi" => $_POST["landShare"],
        "aktif_mi" => isset($_POST["status"]) ? 1 : 0
    ];

    $lastInsertId = $daireModel->saveWithAttr($data);

    $res = [
        "status" => "success",
        "message" => "Başarılı! Daire başarıyla kaydedildi.",
    ];
    echo json_encode($res);
}

if ($_POST["action"] == "delete_apartment") {
    $daireModel->delete($_POST["id"]);

    $res = [
        "status" => "success",
        "message" => "Başarılı"
    ];
    echo json_encode($res);
}


//Excelden Yükleme işlemi
if ($_POST["action"] == "excel_upload_apartment") {
    $file = $_FILES['excelFile'];
    $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($fileType !== 'xlsx' && $fileType !== 'xls') {
        echo json_encode([
            "status" => "error",
            "message" => "Lütfen geçerli bir Excel dosyası yükleyin."
        ]);
        exit;
    }

    $result = $daireModel->excelUpload($file['tmp_name'], $site_id);
  
    if ($result['status'] === 'success') {
        echo json_encode([
            "status" => "success",
            "message" => $result['message'],
            "data" => $result['data']
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => $result['message']
        ]);
    }
}
