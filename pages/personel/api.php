<?php

require_once dirname(__FILE__, 3) . '/configs/bootstrap.php';

use Model\PersonelOdemeModel;
use Model\PersonelModel;
use App\Helper\Security;
use App\Helper\Helper;

$PersonelOdeme = new PersonelOdemeModel();
$Personel = new PersonelModel();

// Ödeme Kaydet (Yeni veya Güncelle)
if ($_POST['action'] == 'save_personel_odeme') {
    $odeme_id = $_POST['odeme_id'] ?? '';
    $personel_id = Security::decrypt($_POST['personel_id'] ?? '');
    $odeme_tarihi = $_POST['odeme_tarihi'] ?? date('Y-m-d');
    $tutar = (float)Helper::formattedMoneyToNumber($_POST['tutar'] ?? 0);
    $odeme_turu = $_POST['odeme_turu'] ?? '';
    $aciklama = $_POST['aciklama'] ?? '';
    $yonetici_notu = $_POST['yonetici_notu'] ?? '';
    $kayit_yapan_id = $_SESSION['user']->id ?? null;

    // Validasyon
    if (!$personel_id || $tutar <= 0 || !$odeme_turu) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Zorunlu alanları eksiksiz doldurunuz (Personel, Tutar, Ödeme Türü)'
        ]);
        exit;
    }

    $data = [
        'personel_id' => $personel_id,
        'odeme_tarihi' => $odeme_tarihi,
        'tutar' => $tutar,
        'odeme_turu' => $odeme_turu,
        'aciklama' => $aciklama,
        'yonetici_notu' => $yonetici_notu,
        'kayit_yapan_id' => $kayit_yapan_id
    ];

    try {
        if ($odeme_id) {
            // Güncelle
            $data['id'] = Security::decrypt($odeme_id);
            $result = $PersonelOdeme->updateOdeme($data['id'], $data);
            $message = 'Ödeme başarıyla güncellendi';
        } else {
            // Kaydet
            $result = $PersonelOdeme->saveOdeme($data);
            $message = 'Ödeme başarıyla kaydedildi';
        }

        echo json_encode([
            'status' => 'success',
            'message' => $message,
            'id' => $result
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Hata: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Ödeme Sil
if ($_POST['action'] == 'delete_personel_odeme') {
    $odeme_id = Security::decrypt($_POST['odeme_id'] ?? '');

    if (!$odeme_id) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Ödeme ID gerekli'
        ]);
        exit;
    }

    try {
        $result = $PersonelOdeme->deleteOdeme($odeme_id);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Ödeme başarıyla silindi'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Hata: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Personel Sil
if ($_POST['action'] == 'delete_personel') {
    $personel_id = $_POST['personel_id'] ?? '';

    if (!$personel_id) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Personel ID gerekli'
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
