<?php
require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';

use App\Helper\Security;
use App\Helper\Date;
use Model\KasaModel;
use Model\KasaHareketModel;

$kasaHareketModel = new KasaHareketModel();

if ($_POST['action'] == 'gelir-gider-kaydet') {
    $islem_id = Security::decrypt($_POST['islem_id'] ?? 0);
    $site_id = $_SESSION['site_id'];

    try {



        $data = [
            "id" => $islem_id,
            "site_id" => $site_id,
            "kasa_id" => Security::decrypt($_POST['kasa']),
            "islem_tarihi" => Date::Ymd($_POST['islem_tarihi'], "Y-m-d H:i:s"),
            "islem_tipi" => $_POST['islem_tipi'],
            "kategori" => $_POST['kategori'],
            "tutar" => $_POST['tutar'],
            "aciklama" => $_POST['aciklama'],
        ];

        $lastInsertId = $kasaHareketModel->saveWithAttr($data);

        $status = "success";
        $message = $islem_id > 0 ? "Güncelleme başarılı" : "Kayıt başarılı";



    } catch (PDOException $ex) {
        $status = "error";
        $message = $ex->getMessage();
    }
    $res = [
        "status" => $status,
        "message" => $message,
        "data" => $data
    ];

    echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
