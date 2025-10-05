<?php
require_once dirname(__DIR__, levels: 1) . '/configs/bootstrap.php';

use App\Helper\Helper;
use Model\KisiKredileriModel;
use Model\BorclandirmaDetayModel;

$KisiKredileri = new KisiKredileriModel();
$BorclandirmaDetay = new BorclandirmaDetayModel();


echo Helper::extractApartmentInfo("YASİN TÜFEKCİ*0015*Üsküp evleri C2 blok 6 numara yasin Tüfekci Şubat aidatı*1597989044*FAST");