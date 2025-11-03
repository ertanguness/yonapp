<?php
require_once dirname(__DIR__, levels: 4) . '/configs/bootstrap.php';

use App\Services\FlashMessageService;
use App\Services\ExcelHelper;

use Model\KasaHareketModel;

$KasaHareketModel = new KasaHareketModel();


//Excelden Yükleme işlemi
if ($_POST["action"] == "excel_upload_transactions") {

    $file = $_FILES['excelFile'];
    $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $site_id = $_SESSION['site_id'];

    if ($fileType !== 'xlsx' && $fileType !== 'xls') {
        echo json_encode([
            "status" => "error",
            "message" => "Lütfen geçerli bir Excel dosyası yükleyin."
        ]);
        exit;
    }

    $result = $KasaHareketModel->excelUpload($file['tmp_name'], $site_id);
  

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
