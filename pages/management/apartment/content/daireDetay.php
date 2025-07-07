<?php
require_once dirname(__DIR__, levels: 4) . '/configs/bootstrap.php';


use App\Helper\Security;
use Model\SitelerModel;
use Model\BloklarModel;
use Model\KisilerModel;
use Model\DairelerModel;
use Model\DefinesModel;
use App\Helper\Helper;

$id = $_GET['id'] ?? null;
$id = Security::decrypt($id);

$Siteler = new SitelerModel();
$Bloklar = new BloklarModel();
$Kisiler = new KisilerModel();
$Daireler = new DairelerModel();
$DaireTipi = new DefinesModel();

$daire = $Daireler->DaireBilgisi($_SESSION['site_id'], $id);
$blok = $Bloklar->Blok($daire->blok_id);
$kisiler = $Kisiler->DaireKisileri($id);
$daireTürü = $DaireTipi->daireTipiGetir($_SESSION['site_id'], $daire->daire_tipi);

$site = $Siteler->find($daire->site_id ?? 0);
if (!$site) {
    echo '<div class="text-danger p-4">Site bilgisi bulunamadı.</div>';
    exit;
}
?>

<div class="offcanvas offcanvas-end" tabindex="-1" id="daireDetayOffcanvas" data-bs-backdrop="true">
    <div class="offcanvas-header ht-80 px-4 border-bottom border-gray-5">
        <div>
            <h2 class="fs-20 fw-bold text-truncate-1-line"><?= htmlspecialchars($site->site_adi) ?></h2>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="py-3 px-4 d-flex justify-content-between align-items-center border-bottom border-bottom-dashed border-gray-5 bg-gray-100">
        <div>
            <span class="fw-bold text-dark">Blok Adı:</span>
            <span class="fs-14 fw-medium fw-bold text-primary"><?= $blok->blok_adi ?></span>
        </div>
        <div>
            <span class="fw-bold text-dark">Daire No:</span>
            <span class="fs-14 fw-bold text-primary"><?= $daire->daire_no ?></span>
        </div>
        <div>
            <span class="fw-bold text-dark">Daire Kodu:</span>
            <span class="fs-14 fw-medium fw-bold text-primary"><?= $daire->daire_kodu ?></span>
        </div>
    </div>

    <div class="offcanvas-body px-4">

        <div class="mb-3 d-flex">
            <div style="width: 130px; font-weight: 600;">Kişiler:</div>
            <div>

                <?php
                if (!empty($kisiler)) {
                    foreach ($kisiler as $kisi) {
                        $adiSoyadi = htmlspecialchars($kisi->adi_soyadi);
                        $uyelikTipi = $kisi->uyelik_tipi ?? null;
                        $ikametLabel = Helper::ikametTuru[$uyelikTipi] ?? '';

                        echo '<div>';
                        echo $ikametLabel ? "<strong>{$ikametLabel}:</strong> " : '';
                        echo $adiSoyadi;
                        echo '</div>';
                    }
                } else {
                    echo '<div>Kişi bulunamadı.</div>';
                }

                ?>
            </div>
        </div>



        <div class="mb-3 d-flex">
            <div style="width: 130px; font-weight: 600;">Daire Tipi:</div>
            <div><?= $daireTürü->define_name ?></div>
        </div>
        <div class="mb-3 d-flex">
            <div style="width: 130px; font-weight: 600;">Brüt Alan:</div>
            <div><?= htmlspecialchars($daire->brut_alan) ?></div>
        </div>
        <div class="mb-3 d-flex">
            <div style="width: 130px; font-weight: 600;">Net Alan:</div>
            <div><?= htmlspecialchars($daire->net_alan) ?></div>
        </div>
        <div class="mb-3 d-flex">
            <div style="width: 130px; font-weight: 600;">Arsa Payı:</div>
            <div><?= htmlspecialchars($daire->arsa_payi) ?></div>
        </div>


    </div>

    <div class="px-4 gap-2 d-flex align-items-center ht-80 border border-end-0 border-gray-2">
        <a href="javascript:void(0);" class="btn btn-danger w-50" data-bs-dismiss="offcanvas">Kapat</a>
        <a href="index?p=management/apartment/manage&id=<?= Security::encrypt($daire->id) ?>" class="btn btn-primary w-50">Düzenle</a>
    </div>
</div>