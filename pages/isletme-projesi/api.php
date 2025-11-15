<?php

require_once dirname(__DIR__, 2) . '/configs/bootstrap.php';

use App\Helper\Security;
use App\Helper\Date;
use App\Helper\Alert;
use Database\Db;
use Model\IsletmeProjesiModel;

$db = Db::getInstance();
$logger = \getLogger();

$Model = new IsletmeProjesiModel();

if (($_POST['action'] ?? '') === 'proje_kaydet') {
    $id = Security::decrypt($_POST['id'] ?? 0) ?? 0;
    $siteId = $_SESSION['site_id'] ?? 0;

    $project = [
        'site_id' => $siteId,
        'proje_adi' => $_POST['proje_adi'] ?? '',
        'apartman_site_adi' => $_POST['apartman_site_adi'] ?? '',
        'adres' => $_POST['adres'] ?? null,
        'donem_baslangic' => (Date::Ymd($_POST['donem_baslangic'] ?? '') ?: null),
        'donem_bitis' => (Date::Ymd($_POST['donem_bitis'] ?? '') ?: null),
        'kanuni_dayanak' => $_POST['kanuni_dayanak'] ?? null,
        'varsayimlar' => $_POST['varsayimlar'] ?? null,
        'metodoloji' => $_POST['metodoloji'] ?? null,
        'enflasyon_oran' => $_POST['enflasyon_oran'] ?? 0,
        'rezerv_tutar' => $_POST['rezerv_tutar'] ?? 0,
        'odeme_plani' => $_POST['odeme_plani'] ?? null,
        'takvim' => $_POST['takvim'] ?? null,
        'guncelleme_mekanizmasi' => $_POST['guncelleme_mekanizmasi'] ?? null,
        'durum' => 'aktif',
        'genel_kurul_turu' => $_POST['genel_kurul_turu'] ?? null,
        'genel_kurul_tarihi' => (Date::Ymd($_POST['genel_kurul_tarihi'] ?? '') ?: null),
        'kurul_onay_durumu' => $_POST['kurul_onay_durumu'] ?? 'beklemede',
        'kurul_onay_tarihi' => (Date::Ymd($_POST['kurul_onay_tarihi'] ?? '') ?: null),
        'divan_tutanak_no' => $_POST['divan_tutanak_no'] ?? null,
        'bildirim_yontemi' => $_POST['bildirim_yontemi'] ?? null,
        'bildirim_tarihi' => (Date::Ymd($_POST['bildirim_tarihi'] ?? '') ?: null),
        'kesinlesme_tarihi' => (Date::Ymd($_POST['kesinlesme_tarihi'] ?? '') ?: null),
        'itiraz_var_mi' => $_POST['itiraz_var_mi'] ?? 0,
        'itiraz_tarihi' => (Date::Ymd($_POST['itiraz_tarihi'] ?? '') ?: null),
        'itiraz_karar_tarihi' => (Date::Ymd($_POST['itiraz_karar_tarihi'] ?? '') ?: null),
        'itiraz_sonucu' => $_POST['itiraz_sonucu'] ?? null,
        'paylandirma_esasi' => $_POST['paylandirma_esasi'] ?? null,
        'yonetim_plani_referans' => $_POST['yonetim_plani_referans'] ?? null,
        'imza_orani' => $_POST['imza_orani'] ?? null,
        'iik_belge_mi' => empty($_POST['kesinlesme_tarihi']) ? 0 : 1,
    ];

    $gelirKalemleri = [];
    $giderKalemleri = [];

    $gelirKategori = $_POST['gelir_kategori'] ?? [];
    $gelirTutar = $_POST['gelir_tutar'] ?? [];
    foreach ($gelirKategori as $i => $kat) {
        if (trim((string)$kat) === '') continue;
        $gelirKalemleri[] = ['kategori' => $kat, 'tutar' => $gelirTutar[$i] ?? 0];
    }

    $giderKategori = $_POST['gider_kategori'] ?? [];
    $giderTutar = $_POST['gider_tutar'] ?? [];
    foreach ($giderKategori as $i => $kat) {
        if (trim((string)$kat) === '') continue;
        $giderKalemleri[] = ['kategori' => $kat, 'tutar' => $giderTutar[$i] ?? 0];
    }

    try {
        $project['id'] = $id;
        $Model->saveWithDetails($project, $gelirKalemleri, $giderKalemleri);
        $status = 'success';
        $message = $id > 0 ? 'Proje güncellendi' : 'Proje kaydedildi';
        $redirect = '/isletme-projesi';
    } catch (Throwable $ex) {
        $logger->error('İşletme projesi kaydet hatası', ['error' => $ex->getMessage()]);
        $status = 'error';
        $message = $ex->getMessage();
        $redirect = null;
    }

    echo json_encode([
        'status' => $status,
        'message' => $message,
        'redirect' => $redirect,
    ]);
    exit;
}

if (($_POST['action'] ?? '') === 'proje_sil') {
    $id = Security::decrypt($_POST['id'] ?? 0) ?? 0;
    try {
        $Model->delete($_POST['id'] ?? 0);
        Alert::success('Proje silindi');
    } catch (Throwable $ex) {
        Alert::error($ex->getMessage());
    }
    exit;
}