<?php
require_once dirname(__DIR__,levels: 4) . '/configs/bootstrap.php';


use App\Helper\Security;
use Model\SitelerModel;
use Model\BloklarModel;
use Model\KisilerModel;
use App\Helper\Cities;

$id = $_GET['id'] ?? null;
$id = Security::decrypt($id);

$Siteler = new SitelerModel();
$Bloklar = new BloklarModel();
$Kisiler = new KisilerModel();
$cities = new Cities();

$site = $Siteler->find($id);

if (!$site) {
    echo '<div class="text-danger p-4">Site bilgisi bulunamadı.</div>';
    exit;
}

$blokSayisi = $Bloklar->SitedekiBloksayisi($id);
$daireSayisi = $Bloklar->SitedekiDaireSayisi($id);
$kisiler = $Kisiler->sitedekiKisiSayisi($id);
$il = $cities->getCityName($site->il ?? null);
$ilce = $cities->getTownName($site->ilce ?? null);
?>

<div class="offcanvas offcanvas-end" tabindex="-1" id="siteDetayOffcanvas" data-bs-backdrop="true">
    <div class="offcanvas-header ht-80 px-4 border-bottom border-gray-5">
        <div>
            <h2 class="fs-20 fw-bold text-truncate-1-line"><?= htmlspecialchars($site->site_adi) ?></h2>
            <small class="fs-12 text-muted"><?= htmlspecialchars($site->aciklama ?? 'Açıklama bulunamadı.') ?></small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="py-3 px-4 d-flex justify-content-between align-items-center border-bottom border-bottom-dashed border-gray-5 bg-gray-100">
        <div>
            <span class="fw-bold text-dark">Blok Sayısı:</span>
            <span class="fs-14 fw-medium fw-bold text-primary"><?= $blokSayisi ?></span>
        </div>
        <div>
            <span class="fw-bold text-dark">Daire Sayısı:</span>
            <span class="fs-14 fw-bold text-primary"><?= $daireSayisi ?></span>
        </div>
        <div>
            <span class="fw-bold text-dark">Kişi Sayısı:</span>
            <span class="fs-14 fw-medium fw-bold text-primary"><?= $kisiler ?></span>
        </div>
    </div>

    <div class="offcanvas-body px-4">

        <div class="mb-3 d-flex">
            <div style="width: 130px; font-weight: 600;">Telefon:</div>
            <div><?= htmlspecialchars($site->telefon) ?></div>
        </div>


        <div class="mb-3 d-flex">
            <div style="width: 130px; font-weight: 600;">Email:</div>
            <div><?= htmlspecialchars($site->email ?? '-') ?></div>
        </div>

        <div class="mb-3 d-flex">
            <div style="width: 130px; font-weight: 600;">Web Sitesi:</div>
            <div>
                <?php if (!empty($site->web_sitesi)): ?>
                    <a href="<?= htmlspecialchars($site->web_sitesi) ?>" target="_blank"><?= htmlspecialchars($site->web_sitesi) ?></a>
                <?php else: ?>
                    -
                <?php endif; ?>
            </div>
        </div>
        <div class="mb-3 d-flex">
            <div style="width: 130px; font-weight: 600;">İl:</div>
            <div><?= htmlspecialchars($il) ?></div>
        </div>

        <div class="mb-3 d-flex">
            <div style="width: 130px; font-weight: 600;">İlçe:</div>
            <div><?= htmlspecialchars($ilce) ?></div>
        </div>
        <div class="mb-3 d-flex">
            <div style="width: 130px; font-weight: 600;">Adres:</div>
            <div style="white-space: pre-line;"><?= htmlspecialchars($site->tam_adres ?? '') ?></div>
        </div>
        <div class="mb-3 d-flex">
            <div style="width: 130px; font-weight: 600;">Açıklama:</div>
            <div><?= htmlspecialchars($site->aciklama ?? '-') ?></div>
        </div>

        <div class="mb-3 d-flex">
            <div style="width: 130px; font-weight: 600;">Yönetici:</div>
            <div><?= htmlspecialchars($site->yonetici ?? '-') ?></div>
        </div>

        <div class="mb-3 d-flex">
            <div style="width: 130px; font-weight: 600;">Yönetici Telefon:</div>
            <div><?= htmlspecialchars($site->yonetici_telefon ?? '-') ?></div>
        </div>

    </div>

    <div class="px-4 gap-2 d-flex align-items-center ht-80 border border-end-0 border-gray-2">
        <a href="javascript:void(0);" class="btn btn-danger w-100" data-bs-dismiss="offcanvas">Kapat</a>
    </div>
</div>