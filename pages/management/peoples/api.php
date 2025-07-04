<?php
require_once dirname(__DIR__ ,levels: 3). '/configs/bootstrap.php';


$site_id = $_SESSION["site_id"];

use Model\KisilerModel;
use App\Helper\Security;
use App\Services\ExcelHelper;


$kisilerModel = new KisilerModel();




//Excelden Yükleme işlemi
if ($_POST["action"] == "excel_upload_peoples") {
    $file = $_FILES['excelFile'];
    $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($fileType !== 'xlsx' && $fileType !== 'xls') {
        echo json_encode([
            "status" => "error",
            "message" => "Lütfen geçerli bir Excel dosyası yükleyin."
        ]);
        exit;
    }

    $result = $kisilerModel->excelUpload($file['tmp_name'], $site_id);
  

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
