<?php
require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';

use App\Helper\Security;
use App\Helper\Date;
use App\Services\Gate;
use App\Helper\Helper;
use Model\KasaModel;
use Model\KasaHareketModel;
use Model\DefinesModel;
use Database\Db;

$KasaModel = new KasaModel();
$kasaHareketModel = new KasaHareketModel();
$Tanımlamalar = new DefinesModel();
$db = Db::getInstance();
$logger = \getlogger();

$action = $_POST['action'] ?? '';
$post = $_POST;

if ($_POST['action'] == 'gelir-gider-kaydet') {
    $islem_id = Security::decrypt($_POST['islem_id'] ?? 0);
    $site_id = $_SESSION['site_id'];
    $kasa_id = $_SESSION['kasa_id'];
    $islem_tipi = $_POST['islem_tipi'];

    /** Tutar gider ise - koy */
    if ($islem_tipi == 'Gider' || $islem_tipi == 'gider') {
        $tutar = -abs(Helper::formattedMoneyToNumber($_POST['tutar']));
    }

    $lastInsertId = 0;

    try {



        $data = [
            "id" => $islem_id,
            "site_id" => $site_id,
            "kasa_id" => $kasa_id,
            "islem_tarihi" => Date::YmdHis($_POST['islem_tarihi']),
            "islem_tipi" => $_POST['islem_tipi'],
            "kategori" => $_POST['kategori'],
            "alt_tur" => $_POST['gelir_gider_kalemi'] ?? '',
            "makbuz_no" => $_POST['makbuz_no'],
            "tutar" => ($tutar),
            "aciklama" => $_POST['aciklama'],
            "guncellenebilir" => 1
        ];

        $lastInsertId = $kasaHareketModel->saveWithAttr($data) ?? $_POST['islem_id'];

        //satır verisini tekrar çek
        $kasaHareket = $kasaHareketModel->findFromView($lastInsertId);


        //yürüyen bakiye olduğu için şimdilik kapattım
        // $rowData = [
        //     "id" => $kasaHareket->id,
        //     "islem_tipi" => $kasaHareket->islem_tipi == 'Gelir' ? '<span class="badge bg-success">Gelir</span>'
        //         : '<span class="badge bg-danger">Gider</span>',
        //     "daire_kodu" => "",
        //     "hesap_adi" => "",
        //     "tutar" => '<span class="' . (($kasaHareket->tutar ?? 0) >= 0 ? 'text-success' : 'text-danger') . '">' . Helper::formattedMoney($kasaHareket->tutar ?? 0) . '</span>',
        //     "yuruyen_bakiye" => Helper::formattedMoney($kasaHareket->yuruyen_bakiye ?? 0),
        //     "kategori" => $kasaHareket->kategori,
        //     "makbuz_no" => $kasaHareket->makbuz_no,
        //     "aciklama" => $kasaHareket->aciklama,
        //     "islem_tarihi" => Date::dmYHIS($kasaHareket->islem_tarihi),
        //     "islem_buttons" => '
        //         <div class="hstack gap-2 justify-content-center">
        //             <a href="#" class="avatar-text avatar-md gelirGiderGuncelle" data-id="'.$lastInsertId.'">
        //                 <i class="feather-edit"></i>
        //             </a>
        //             <a href="#" class="avatar-text avatar-md gelirGiderSil" data-id="'.$lastInsertId.'">
        //                 <i class="feather-trash-2"></i>
        //             </a>
        //         </div>
        //     '
        // ];

        $status = "success";
        $message = $islem_id > 0 ? "Güncelleme başarılı" : "Kayıt başarılı";
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


    $islem_id = $_POST['islem_id'];
    $kasa_id = $_SESSION['kasa_id'];
    $KasaFinansalDurum = null;

    try {
        $db->beginTransaction();

        //Önce kayıt var mı ve silinebilir mi kontrol et
        $kasaHareket = $kasaHareketModel->find($islem_id, true);
        $logger->info('Gelir Gider Silme İşlemi', ['kasa_hareket' => json_encode($kasaHareket)]);
        if (!$kasaHareket || (int)($kasaHareket->guncellenebilir ?? 0) !== 1) {
            throw new Exception("Kayıt bulunamadı veya silinemez.");
        }

        // Kasa Transferi ise eşleşen tüm kayıtları sil
        if (strtolower(trim((string)($kasaHareket->kategori ?? ''))) === 'kasa transferi' && !empty($kasaHareket->makbuz_no)) {
            $pairs = $kasaHareketModel->findWhere(['makbuz_no' => $kasaHareket->makbuz_no]);
            foreach ($pairs as $p) {
                if ((int)($p->guncellenebilir ?? 0) === 1) {
                    $logger->info('Gelir Gider Silme İşlemi başladı', ['kasa_hareket' => json_encode($p)]);
                    $kasaHareketModel->softDelete($p->id);
                }
            }
        } else {
            // Tekil silme
            $deleted = $kasaHareketModel->softDelete(Security::decrypt($islem_id));
            if (!$deleted) {
                $logger->error('Gelir Gider Silme İşlemi', ['kasa_hareket' => json_encode($kasaHareket)]);
                throw new Exception("Kayıt silinemedi.");
            }
        }

        //Kasa Ozet bilgilerini getir
        $KasaFinansalDurum = $KasaModel->KasaFinansalDurum($kasa_id);


        //Para formatında formatla
        if ($KasaFinansalDurum) {
            $KasaFinansalDurum->toplam_gelir = Helper::formattedMoney($KasaFinansalDurum->toplam_gelir ?? 0);
            $KasaFinansalDurum->toplam_gider = Helper::formattedMoney($KasaFinansalDurum->toplam_gider ?? 0);
            $KasaFinansalDurum->bakiye = Helper::formattedMoney($KasaFinansalDurum->bakiye ?? 0);
        }

        $db->commit();
        $status = "success";
        $message = "Kayıt başarıyla silindi.";
    } catch (Exception $ex) {
        try {
            $db->rollBack();
        } catch (Exception $e) {
        }
        $status = "error";
        $message = $ex->getMessage();
    }
    $res = [
        "status" => $status,
        "message" => $message,
        "data" => [
            'toplam_gelir' => $KasaFinansalDurum->toplam_gelir ?? "0,00 TL",
            'toplam_gider' => $KasaFinansalDurum->toplam_gider ?? "0,00 TL",
            'bakiye' => $KasaFinansalDurum->bakiye ?? "0,00 TL",
        ]
    ];

    echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}


//Gelir Gider tipi getir
if ($_POST['action'] == 'kategori-getir') {

    $islem_tipi = $_POST['islem_tipi']  ?? '';
    $type = $islem_tipi == 'gelir' ? 6 : ($islem_tipi == 'gider' ? 7 : 0);

    try {

        $kategoriler = $Tanımlamalar->getGelirGiderKategorileri($type);

        $status = "success";
        $message = "Kayıt bulundu.";
    } catch (Exception $ex) {
        $status = "error";
        $message = $ex->getMessage();
    }
    $res = [
        "status" => $status,
        "message" => $message,
        "kategoriler" => $kategoriler
    ];

    echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}


/** Gelir Gider Kalemleri */
if ($action == 'get-gelir-gider-kalemleri') {


    $type = isset($post['type']) ? (int)$post['type'] : 0;
    $kategori = isset($post['kategori']) ? trim($post['kategori']) : '';

    $kalemler = $Tanımlamalar->getGelirGiderKalemleri($type, $kategori);
    $res = [
        "status" => "success",
        "message" => "Kayıt bulundu.",
        "data" => $kalemler
    ];
    echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}