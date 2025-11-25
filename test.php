<?php

require_once  'configs/bootstrap.php';
$_SESSION['site_id'] = '13';


use Model\KisilerModel;
// use App\Services\SmsGonderService;

// echo SmsGonderService::gonder(
//     alicilar: ['5409432723', '5079432723'],
//     mesaj: 'Borcunuz bulunmaktadır. Detaylı bilgi için lütfen yönetici ile iletişime geçiniz. Teşekkürler.',
//     gondericiBaslik: 'USKUPEVLSIT'
// ) ? 'SMS başarıyla gönderildi.' : 'SMS gönderilemedi.'; 
$KisiModel = new KisilerModel();

$kisiler = $KisiModel->BorclandirilacakAktifKisileriGetir(
    site_id: 24,
    borcBaslangicTarihi: '2025-11-01',
    borcBitisTarihi: '2025-11-30'
);

echo '<pre>';
print_r($kisiler);
echo '</pre>';
