<?php
require_once '../../../../vendor/autoload.php';

use App\Helper\Security;
use Model\AraclarModel;
use Model\SitelerModel;
use Model\BloklarModel;
use Model\KisilerModel;
use Model\DairelerModel;
use App\Helper\Helper;

$id = isset($_GET['id']) ? Security::decrypt($_GET['id']) : 0;

$Siteler = new SitelerModel();
$Bloklar = new BloklarModel();
$Kisiler = new KisilerModel();
$Daireler = new DairelerModel();
$Araclar = new AraclarModel();

$kisi = $Kisiler->KisiBilgileri($id);
$blok = $Bloklar->find($kisi->blok_id ?? 0);
$site = $Siteler->find($blok->site_id ?? 0);
$daire = $Daireler->DaireBilgisi($blok->site_id ?? 0, $kisi->daire_id ?? 0);
$araclar = $Araclar->KisiAracBilgileri($id ?? 0);
$arac_placa_list = [];
if (!empty($araclar) && is_array($araclar)) {
    foreach ($araclar as $arac) {
        if (!empty($arac->plaka)) {
            $arac_placa_list[] = htmlspecialchars_decode($arac->plaka);
        }
    }
}
$arac_plakalar = !empty($arac_placa_list) ? implode(', ', $arac_placa_list) : '🚫';
?>

<div class="offcanvas offcanvas-end" tabindex="-1" id="siteSakiniDetayOffcanvas" data-bs-backdrop="false">
    <div class="offcanvas-header ht-80 px-4 border-bottom border-gray-5">
        <div>
            <h2 class="fs-20 fw-bold text-truncate-1-line">
                <?= !empty($site->site_adi) ? htmlspecialchars($site->site_adi) : '🚫' ?>
            </h2>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="py-3 px-4 d-flex justify-content-between align-items-center border-bottom border-bottom-dashed border-gray-5 bg-gray-100">
        <div>
            <span class="fw-bold text-dark">Blok Adı:</span>
            <span class="fs-14 fw-medium fw-bold text-primary"><?= !empty($blok->blok_adi) ? htmlspecialchars($blok->blok_adi) : '🚫' ?></span>
        </div>
        <div>
            <span class="fw-bold text-dark">Daire No:</span>
            <span class="fs-14 fw-bold text-primary"><?= !empty($daire->daire_no) ? htmlspecialchars($daire->daire_no) : '🚫' ?></span>
        </div>
        <div>
            <span class="fw-bold text-dark">Daire Kodu:</span>
            <span class="fs-14 fw-medium fw-bold text-primary"><?= !empty($daire->daire_kodu) ? htmlspecialchars($daire->daire_kodu) : '🚫' ?></span>
        </div>
    </div>

    <div class="offcanvas-body px-4">
        <div class="mb-3 d-flex">
            <div style="width: 130px; font-weight: 600;">Kimlik No:</div>
            <div><?= !empty($kisi->kimlik_no) ? htmlspecialchars($kisi->kimlik_no) : '🚫' ?></div>
        </div>
        <div class="mb-3 d-flex">
            <div style="width: 130px; font-weight: 600;">Adı Soyadı:</div>
            <div><?= !empty($kisi->adi_soyadi) ? htmlspecialchars($kisi->adi_soyadi) : '🚫' ?></div>
        </div>
        <div class="mb-3 d-flex">
            <div style="width: 130px; font-weight: 600;">Telefon:</div>
            <div><?= !empty($kisi->telefon) ? htmlspecialchars($kisi->telefon) : '🚫' ?></div>
        </div>
        <div class="mb-3 d-flex">
            <div style="width: 130px; font-weight: 600;">Email:</div>
            <div><?= !empty($kisi->eposta) ? htmlspecialchars($kisi->eposta) : '🚫' ?></div>
        </div>
        <div class="mb-3 d-flex">
            <div style="width: 130px; font-weight: 600;">Doğum Tarihi:</div>
            <div><?= !empty($kisi->dogum_tarihi) ? htmlspecialchars($kisi->dogum_tarihi) : '🚫' ?></div>
        </div>
        <div class="mb-3 d-flex">
            <div style="width: 130px; font-weight: 600;">İkamet Türü:</div>
            <div>
                <?php
                // Helper::ikametTuru() fonksiyonu ikamet türü kodlarını ve açıklamalarını döndürmeli
                $ikametTurleri = Helper::ikametTuru;
                echo !empty($kisi->uyelik_tipi) && isset($ikametTurleri[$kisi->uyelik_tipi])? htmlspecialchars($ikametTurleri[$kisi->uyelik_tipi]): '🚫';
                ?>
            </div>
        </div>
        <div class="mb-3 d-flex">
            <div style="width: 130px; font-weight: 600;">Adres:</div>
            <div><?= !empty($kisi->adres) ? htmlspecialchars($kisi->adres) : '🚫' ?></div>
        </div>
        <div class="mb-3 d-flex">
            <div style="width: 130px; font-weight: 600;">Araç Plakası:</div>
            <div><?=  $arac_plakalar ;?></div>
        </div>
    </div>

    <div class="px-4 gap-2 d-flex align-items-center ht-80 border border-end-0 border-gray-2">
        <a href="index?p=management/peoples/manage&id=<?= Security::encrypt($id ?? 0) ?>" class="btn btn-primary w-50">Düzenle</a>
        <a href="javascript:void(0);" class="btn btn-danger w-50" data-bs-dismiss="offcanvas">Kapat</a>
    </div>
</div>