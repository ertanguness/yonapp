<?php

require_once  dirname(__DIR__, 1) . '/configs/bootstrap.php';
$_SESSION['site_id'] = '1';


use Model\KisilerModel;
use Model\DefinesModel;
use Model\DuyuruModel;
use App\Helper\Date;
use App\Helper\Helper;
use Model\SettingsModel;

// use App\Services\SmsGonderService;

// echo SmsGonderService::gonder(
//     alicilar: ['5409432723', '5079432723'],
//     mesaj: 'Borcunuz bulunmaktadır. Detaylı bilgi için lütfen yönetici ile iletişime geçiniz. Teşekkürler.',
//     gondericiBaslik: 'USKUPEVLSIT'
// ) ? 'SMS başarıyla gönderildi.' : 'SMS gönderilemedi.'; 
$KisiModel = new KisilerModel();
$DefinesModel = new DefinesModel();
$DuyuruModel = new DuyuruModel();
$SettingsModel = new SettingsModel();


$settings = $SettingsModel->getAllSettingsAsKeyValue(1, 147);

//$duyuru = $DuyuruModel->sakinDuyurulari(171);

//$gelirGiderTipiSelect = $DefinesModel->getGelirGiderTipiSelect("gelir_gider_grubu", 6, "");
Helper::dd($settings);
