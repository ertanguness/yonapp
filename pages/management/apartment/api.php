<?php
require_once dirname(__DIR__ ,levels: 3). '/configs/bootstrap.php';


$site_id = $_SESSION["site_id"];

use Model\DairelerModel;
use App\Helper\Security;
use App\Services\ExcelHelper;
use App\Services\FlashMessageService;

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
  

    $errorFileUrl = null;
    
    // Eğer hatalı satır varsa ExcelHelper'ı kullan
    if (!empty($result['data']['error_rows'])) {
        try {
            // ExcelHelper nesnesini oluştur
            $excelHelper = new ExcelHelper();

            // 1. Orijinal başlıkları al
            $originalHeader = $excelHelper->getHeaders($file['tmp_name']);

            // 2. Hata dosyasını oluştur ve URL'sini al
            $errorFileUrl = $excelHelper->createErrorFile($result['data']['error_rows'], $originalHeader);
        
            FlashMessageService::add("error","Bilgi","Hatalı kayıtlar için bir Excel dosyası oluşturuldu. <a href='{$errorFileUrl}' target='_blank'>Dosyayı İndir</a>");


        } catch (Exception $e) {
             // Loglama zaten helper sınıfı içinde yapılıyor.   
             // Burada ek bir loglama yapabilir veya sessiz kalabilirsiniz.
             error_log("Controller: Hata Excel'i işlenirken bir sorun oluştu: " . $e->getMessage());
        }
    }



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
