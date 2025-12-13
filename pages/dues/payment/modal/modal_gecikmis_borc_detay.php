<?php
require_once dirname(__DIR__, 4) . '/configs/bootstrap.php';

use App\Helper\Date;
use App\Helper\Helper;
use App\Helper\Security;
use Model\KisilerModel;
use Model\FinansalRaporModel;

$kisiEncId = $_GET['kisi_id'] ?? '';
$kisiId = Security::decrypt($kisiEncId);
$Kisi = new KisilerModel();
$Finansal = new FinansalRaporModel();
$kisi = $Kisi->find($kisiId);
$borclar = $Finansal->getKisiGecikenBorclar($kisiId);

$toplamAnapara = 0.0;
$toplamGecikme = 0.0;
$toplamKalan = 0.0;
foreach ($borclar as $b) {
    $toplamAnapara += (float)($b->kalan_anapara ?? 0);
    $toplamGecikme += (float)($b->hesaplanan_gecikme_zammi ?? 0);
    $toplamKalan   += (float)($b->toplam_kalan_borc ?? 0);
}
?>
<div class="modal-header">
    <h5 class="modal-title"><?php echo htmlspecialchars($kisi->adi_soyadi ?? ''); ?> | Gecikmiş Borç Detayı</h5>
    <div class="ms-auto d-flex align-items-center gap-2">
        <a href="/pages/dues/payment/export/gecikmis_borclar_excel.php?kisi_id=<?php echo (int)$kisiId; ?>&format=xlsx" class="avatar-text avatar-md">
            <i class="fa-regular fa-file-excel"></i>
        </a>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    </div>
<div class="modal-body">
    <div class="summary-grid mb-3">
        <div class="summary-card">
            <div class="summary-icon bg-soft-danger text-danger border-soft-danger"><i class="feather-trending-down"></i></div>
            <div class="summary-content">
                <div class="summary-label">Anapara</div>
                <div class="summary-value text-danger"><?php echo Helper::formattedMoney($toplamAnapara); ?></div>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon bg-soft-warning text-warning border-soft-warning"><i class="feather-alert-triangle"></i></div>
            <div class="summary-content">
                <div class="summary-label">Gecikme</div>
                <div class="summary-value text-warning"><?php echo Helper::formattedMoney($toplamGecikme); ?></div>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon bg-soft-primary text-primary border-soft-primary"><i class="feather-clipboard"></i></div>
            <div class="summary-content">
                <div class="summary-label">Toplam Kalan</div>
                <div class="summary-value text-primary"><?php echo Helper::formattedMoney($toplamKalan); ?></div>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Borç Adı</th>
                    <th>Açıklama</th>
                    <th>Son Ödeme</th>
                    <th class="text-end">Anapara</th>
                    <th class="text-end">Gecikme</th>
                    <th class="text-end">Toplam Kalan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($borclar as $b): ?>
                <tr>
                    <td><?php echo htmlspecialchars($b->borc_adi ?? ($b->aciklama ?? '')); ?></td>
                    <td class="text-muted"><?php echo htmlspecialchars($b->aciklama ?? ''); ?></td>
                    <td><?php echo Date::dmY($b->bitis_tarihi ?? ''); ?></td>
                    <td class="text-end"><?php echo Helper::formattedMoney((float)($b->kalan_anapara ?? 0)); ?></td>
                    <td class="text-end"><?php echo Helper::formattedMoney((float)($b->hesaplanan_gecikme_zammi ?? 0)); ?></td>
                    <td class="text-end"><?php echo Helper::formattedMoney((float)($b->toplam_kalan_borc ?? 0)); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($borclar)): ?>
                <tr><td colspan="6" class="text-center text-muted">Gecikmiş borç bulunamadı.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<style>
    .summary-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
    .summary-card{display:flex;align-items:center;background:#fff;border:1px solid rgba(0,0,0,.06);border-radius:12px;padding:14px 16px;box-shadow:0 2px 8px rgba(16,24,40,.04)}
    .summary-icon{display:flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:8px;margin-right:12px}
    .summary-label{font-size:12px;color:#6c757d}
    .summary-value{font-size:20px;font-weight:700}
</style>
