<?php
require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';

use App\Helper\Security;
use App\Helper\Date;
use App\Services\Gate;
use App\Helper\Helper;
use Model\KasaModel;
use Model\KasaHareketModel;

$KasaModel = new KasaModel();
$kasaHareketModel = new KasaHareketModel();

if ($_POST['action'] == 'gelir-gider-kaydet') {
    $islem_id = Security::decrypt($_POST['islem_id'] ?? 0);
    $site_id = $_SESSION['site_id'];
    $kasa_id =$_SESSION['kasa_id'] ;
    $lastInsertId = 0;

    try {



        $data = [
            "id" => $islem_id,
            "site_id" => $site_id,
            "kasa_id" => $kasa_id,
            "islem_tarihi" => Date::Ymd($_POST['islem_tarihi']),
            "islem_tipi" => $_POST['islem_tipi'],
            "kategori" => $_POST['kategori'],
            "tutar" => Helper::formattedMoneyToNumber($_POST['tutar']),
            "aciklama" => $_POST['aciklama'],
            "guncellenebilir" => 1
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
        "data" => $data,
        "row" => $lastInsertId
    ];

    echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}


//Gelir Gider Güncelleme için hareket bilgilerini getir
if ($_POST['action'] == 'gelir-gider-getir') {

    //Gate::can("gelir_gider_guncelle");

    $islem_id = $_POST['islem_id']  ?? 0;

    try {

        $kasaHareket = $kasaHareketModel->find($islem_id, true);
        if (!$kasaHareket) {
            throw new Exception("Kayıt bulunamadı." . $islem_id);
        }

        $status = "success";
        $message = "Kayıt bulundu.";

    } catch (Exception $ex) {
        $status = "error";
        $message = $ex->getMessage();
    }
    $res = [
        "status" => $status,
        "message" => $message,
        "data" => $kasaHareket
    ];

    echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

//silme İşlemi
if ($_POST['action'] == 'gelir-gider-sil') {

    //Bu işlemi yapabilme yetkisi var mı kontrol et
    Gate::can('gelir_gider_sil');


    $islem_id = $_POST['islem_id'] ;
    $kasa_id = $_SESSION['kasa_id'] ;
    $KasaFinansalDurum = null;

    try {

        //Önce kayıt var mı ve silinebilir mi kontrol et
        $kasaHareket = $kasaHareketModel->find($islem_id, true);
        if (!$kasaHareket || $kasaHareket->guncellenebilir != 1) {
            throw new Exception("Kayıt bulunamadı veya silinemez." );
        }

        //Kayıt varsa sil
        $deleted = $kasaHareketModel->delete($islem_id);
        if (!$deleted) {
            throw new Exception("Kayıt silinemedi.");
        }

        //Kasa Ozet bilgilerini getir
        $KasaFinansalDurum = $KasaModel->KasaFinansalDurum($kasaHareket->kasa_id);
      
        //Para formatında formatla
        $KasaFinansalDurum->toplam_gelir = Helper::formattedMoney($KasaFinansalDurum->toplam_gelir ?? 0);
        $KasaFinansalDurum->toplam_gider = Helper::formattedMoney($KasaFinansalDurum->toplam_gider ?? 0);
        $KasaFinansalDurum->bakiye = Helper::formattedMoney($KasaFinansalDurum->bakiye ?? 0);

        $status = "success";
        $message = "Kayıt başarıyla silindi.";


    } catch (Exception $ex) {
        $status = "error";
        $message = $ex->getMessage();
    }
    $res = [
        "status" => $status,
        "message" => $message,
        "data" => $KasaFinansalDurum
    ];

    echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}