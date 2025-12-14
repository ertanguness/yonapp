<?php
require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';

use App\Helper\Security;
use App\Helper\Date;
use App\Services\Gate;
use App\Helper\Helper;
use Database\Db;

use Model\DefinesModel;
use Model\KasaHareketModel;

$Tanimlamalar = new DefinesModel();
$KasaHareketModel = new KasaHareketModel();


// 1. Singleton Db nesnesini al
$db = Db::getInstance();
$logger = \getLogger();




if ($_POST["action"] == "gelir-gider-tipi-kaydet") {


    $id = Security::decrypt($_POST['gelir_gider_tipi_id'] ?? 0);

    try {

        $db->beginTransaction();


        if (!empty($kasaHareketleri)) {
            // Kasa hareketleri varsa silinmesine izin verme
            $status = "error";
            $message = "Bu gelir-gider tipi kasa hareketlerinde kullanıldığı için silinemez.";
            echo json_encode(["status" => $status, "message" => $message]);
            exit;
        }

        $tip = $_POST['gelir_gider_tipi'] ?? '';
        $tip_adi = $tip == 6 ? $_POST['gelir_tipi_name'] : $_POST['gider_tipi_name'] ;
        $data =  [
            "id" => $id,
            "define_name" => $tip_adi,
            "type" => $tip ,// 6 gelir - 7 gider,
            "islem_kodu" => $_POST['islem_kodu'] ?? null,
            'alt_tur' => $_POST['alt_tur'] ?? '',
            "description" => $_POST['description'] ?? '',
            "site_id" => $_SESSION['site_id'] ?? 0,
        ];

        $lastInsertId = $Tanimlamalar->saveWithAttr($data);


        $type = $_POST["gelir_gider_tipi"] == 6 ? "Gelir" : "Gider";

        $status = "success";
        $message = "$tip tipi başarıyla kaydedildi.";
        $db->commit();
    } catch (PDOException $ex) {
        $db->rollBack();
        $status = "error";
        $message = $ex->getMessage();
    }
    $res = [
        "status" => $status,
        "message" => $message,
        "lastInsertId" => $lastInsertId ?? null
    ];
    echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

if ($_POST['action'] == 'gelir-gider-tipi-sil') {

    $id = Security::decrypt($_POST['id'] ?? 0);

    try {

        //Kasa Hareketlerinde kullanılıyorsa silinmesine izin verme
        $kasaHareketleri = $KasaHareketModel->findWhere(["gelir_gider_tip_id" => $id]);


        if (!empty($kasaHareketleri)) {
            // Kasa hareketleri varsa silinmesine izin verme
            $status = "warning";
            $message = "Bu gelir-gider tipi kasa hareketlerinde kullanıldığı için silinemez.";
            echo json_encode(["status" => $status, "message" => $message]);
            exit;
        }



        $Tanimlamalar->softDelete($id);
        $status = "success";
        $message = "Gelir-gider tipi başarıyla silindi.";
    } catch (PDOException $ex) {
        $status = "error";
        $message = $ex->getMessage();
    }
    $res = [
        "status" => $status,
        "message" => $message
    ];
    echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
