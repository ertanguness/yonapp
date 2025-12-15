<?php

require_once  'configs/bootstrap.php';
$_SESSION['site_id'] = '39';


use Model\KisilerModel;
use Model\DefinesModel;
use Model\DuyuruModel;

// use App\Services\SmsGonderService;

// echo SmsGonderService::gonder(
//     alicilar: ['5409432723', '5079432723'],
//     mesaj: 'Borcunuz bulunmaktadır. Detaylı bilgi için lütfen yönetici ile iletişime geçiniz. Teşekkürler.',
//     gondericiBaslik: 'USKUPEVLSIT'
// ) ? 'SMS başarıyla gönderildi.' : 'SMS gönderilemedi.'; 
$KisiModel = new KisilerModel();
$DefinesModel = new DefinesModel();
$DuyuruModel = new DuyuruModel();

//$duyuru = $DuyuruModel->sakinDuyurulari(171);

$gelirGiderTipiSelect = $DefinesModel->getGelirGiderTipiSelect("gelir_gider_grubu", 6, "");
echo '<pre>';
print_r($gelirGiderTipiSelect);
echo '</pre>';
