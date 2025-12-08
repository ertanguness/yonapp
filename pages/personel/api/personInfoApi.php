<?php

require_once dirname(__FILE__, 4) . '/configs/bootstrap.php';

use Database\Db;
use App\Helper\Date;
use App\Helper\Helper;
use App\Helper\Security;
use Model\PersonelModel;
use Model\PersonelIzinlerModel;
use Model\PersonelOdemelerModel;
use Model\PersonelGorevlerModel;


$Personel = new PersonelModel();
$PersonelIzinler = new PersonelIzinlerModel();
$PersonelOdeme = new PersonelOdemelerModel();
$PersonelGorevModel = new PersonelGorevlerModel();
$db = Db::getInstance();

$action = $_POST['action'] ?? '';
$post = $_POST;
$site_id = $_SESSION['site_id'] ?? 0;

if ($action == "savePerson") {
    $id = Security::decrypt($post['personelId'] ?? 0);

    try {
        $db->beginTransaction();
        $data = [
            'id' => $id,
            'site_id' => $site_id,
            'adi_soyadi' => $post['adi_soyadi'] ?? '',
            'eposta' => $post['eposta'] ?? '',
            'telefon' => $post['telefon'] ?? '',
            'personel_tipi' => $post['personel_tipi'] ?? '',
            'ise_baslama_tarihi' => Date::Ymd($post['ise_baslama_tarihi'] ?? ''),
            'isten_ayrilma_tarihi' => Date::Ymd($post['isten_ayrilma_tarihi'] ?? ''),
        ];
        $lastInsertId = $Personel->saveWithAttr($data);
        $db->commit();
        $status = "success";
        $message = $id == 0 ? "Personel başarıyla kaydedildi" : "Personel başarıyla güncellendi";
    } catch (Exception $ex) {
        $db->rollBack();
        $status = "error";
        $message = $ex->getMessage();
    }


    echo json_encode([
        'status' => $status,
        'message' => $message,
        'id' => $lastInsertId ?? 0
    ]);
    exit();
}

/** Personel Sil */
if ($action == 'delete_personel') {
    $personel_id = Security::decrypt($_POST['personel_id'] ?? '');

    if (!$personel_id) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Personel ID gerekli'
        ]);
        exit;
    }

    /** Personele ait görev varsa silinmesini engelle */
    $gorevler = $PersonelGorevModel->hasGorev($personel_id);
    if ($gorevler) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Bu personele ait görevler mevcut, silme işlemi gerçekleştirilemez.'
        ]);
        exit;
    }


    /** Personelin izin kaydı varsa silinmesini engelle */
    $izinler = $PersonelIzinler->hasIzin($personel_id);
    if ($izinler) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Bu personele ait izinler mevcut, silme işlemi gerçekleştirilemez.'
        ]);
        exit;
    }


    /** Personele ait ödeme varsa silnmesini engelle */
    $odeme = $PersonelOdeme->hasOdeme($personel_id);
    if (!empty($odeme)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Bu personele ait ödemeler mevcut, silme işlemi gerçekleştirilemez.'
        ]);
        exit;
    }

    try {
        $result = $Personel->deletePersonel($personel_id);

        echo json_encode([
            'status' => 'success',
            'message' => 'Personel başarıyla silindi'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Hata: ' . $e->getMessage()
        ]);
    }
    exit;
}

echo json_encode([
    'status' => 'error',
    'message' => 'İşlem tanınmıyor'
]);
