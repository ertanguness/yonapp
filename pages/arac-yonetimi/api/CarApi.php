<?php
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use App\Helper\Security;
use Model\AraclarModel;

$Araclar = new AraclarModel();

$action = $_POST["action"] ?? '';
$plaka = $_POST["modalAracPlaka"] ?? '';

function log_car($message) {
    $logFile = dirname(__DIR__, 3) . '/logs/arac-yonetimi.log';
    @file_put_contents($logFile, '['.date('Y-m-d H:i:s').'] '.$message.PHP_EOL, FILE_APPEND);
}

if ($action === "AracEkle") {
    try {
        $id = Security::decrypt($_POST["id"] ?? 0);
        $isUpdate = !empty($id) || $id !== 0;

        if (!$isUpdate && !empty($plaka) && $Araclar->AracVarmi($plaka)) {
            echo json_encode(["status"=>"error","message"=>"$plaka plakası ile kayıt önceden yapılmıştır. Lütfen farklı plaka giriniz."]); exit;
        }

        $data = [
            "id" => $id,
            "kisi_id" => $_POST["kisi_id"],
            "plaka" => $_POST["modalAracPlaka"],
            "marka_model" => $_POST["modalAracMarka"],
            "kayit_yapan" => $_SESSION["user"]->id
        ];

        $lastInsertId = $Araclar->saveWithAttr($data);
        if (!$lastInsertId && $isUpdate) { $lastInsertId = $id; }
        if (!$lastInsertId) { echo json_encode(["status"=>"error","message"=>"Araç kaydedilemedi."]); exit; }

        $realId = $isUpdate ? $lastInsertId : Security::decrypt($lastInsertId);
        $yeniAracEkle = $Araclar->aracEkleTableRow($realId);

        echo json_encode(["status"=>"success","message"=>"Başarılı","id"=>$realId,"yeniAracEkle"=>$yeniAracEkle]);
    } catch (\Throwable $e) {
        log_car('AracEkle hata: '.$e->getMessage());
        echo json_encode(["status"=>"error","message"=>"Beklenmeyen bir hata oluştu."]);    
    }
    exit;
}

if ($action === "delete_car") {
    try {
        $Araclar->delete($_POST["id"]);
        echo json_encode(["status"=>"success","message"=>"Başarılı"]);
    } catch (\Throwable $e) {
        log_car('delete_car hata: '.$e->getMessage());
        echo json_encode(["status"=>"error","message"=>"Silme sırasında bir hata oluştu."]);    
    }
    exit;
}

echo json_encode(["status"=>"error","message"=>"Geçersiz işlem"]);