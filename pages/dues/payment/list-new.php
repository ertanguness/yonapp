<?php
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use App\Helper\Helper;
use App\Helper\Security;
use App\Services\Gate;
use Model\FinansalRaporModel;
use Model\TahsilatModel;
use Model\KisilerModel;

Gate::authorizeOrDie('yonetici_aidat_odeme');

$siteId = (int)($_SESSION['site_id'] ?? 0);
if ($siteId <= 0) {
    echo '<div class="alert alert-danger m-3">Site oturumu bulunamadı.</div>';
    return;
}

$FinansalRapor = new FinansalRaporModel();
$Tahsilat = new TahsilatModel();

$borclular = $FinansalRapor->getSiteBorclular($siteId);

/** Kişileri Daire Koduna göre sırala A1D1, A1D2 */
usort($borclular, function ($a, $b) {
    $aa = (string)($a->daire_kodu ?? '');
    $bb = (string)($b->daire_kodu ?? '');
    // Natural sort: A1D2 < A1D10
    return strnatcasecmp($aa, $bb);
});

// Varsayılan seçim: GET ile geldiysa onu seç, yoksa ilk kişi.
$selectedKisiEnc = trim($_GET['kisi'] ?? '');
$selectedKisiId = null;

$selectedDaire = trim($_GET['q'] ?? '');


try {
    if ($selectedKisiEnc !== '') {
        $selectedKisiId = (int)Security::decrypt($selectedKisiEnc);
    }
} catch (\Throwable $e) {
    $selectedKisiId = null;
}
if (!$selectedKisiId && !empty($borclular)) {
    $selectedKisiId = (int)($borclular[0]->kisi_id ?? 0);
}

// Seçili kişi bilgileri ve KPI
$selectedPerson = null;
foreach ($borclular as $b) {
    if ((int)$b->kisi_id === (int)$selectedKisiId) {
        $selectedPerson = $b;
        break;
    }
   
}

$selectedEnc = $selectedKisiId ? Security::encrypt((int)$selectedKisiId) : '';
$selectedBorcOzet = $selectedKisiId ? $FinansalRapor->KisiFinansalDurum((int)$selectedKisiId) : null;

// İşlem geçmişi için: TahsilatModel'da fonksiyon adı repo içinde değişken olabilir.
// Güvenli yaklaşım: varsa kullan, yoksa boş liste.
$tahsilatlar = [];
if ($selectedKisiId) {
    if (method_exists($Tahsilat, 'KisiTahsilatlariWithDetails')) {
        $tahsilatlar = $Tahsilat->KisiTahsilatlariWithDetails((int)$selectedKisiId);
    } elseif (method_exists($Tahsilat, 'KisiTahsilatlari')) {
        $tahsilatlar = $Tahsilat->KisiTahsilatlari((int)$selectedKisiId);
    } else {
        $tahsilatlar = [];
    }
}

// Basit format helper
$fmt = function ($v) {
    return Helper::formattedMoney((float)($v ?? 0));
};

?>
<link rel="stylesheet" href="/pages/dues/payment/assets/style.css">
<div class="main-content">


    <div class="yd-wrap">
        <div class="d-flex align-items-center justify-content-between px-4 py-3 yd-surface" style="border-bottom:1px solid rgba(148,163,184,.25);">
            <div class="d-flex align-items-center gap-2">
                <div style="width:32px;height:32px;border-radius:10px;background:rgba(19,91,236,.1);color:#135bec;display:flex;align-items:center;justify-content:center;">
                    <i class="feather-home"></i>
                </div>
                <div>
                    <div class="fw-bold" style="line-height:1;">Site Borç Takip</div>
                    <div class="yd-muted" style="font-size:12px;">Borçluyu soldan seçin, sağdan detayları görün.</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="/yonetici-aidat-odeme" class="btn btn-outline-secondary btn-sm" title="Eski liste sayfası">
                    <i class="feather-list me-1"></i>Eski Liste
                </a>
            </div>
        </div>

        <div class="row g-0">
            <!-- Sol kolon: Borçlular listesi -->
            <div class="col-12 col-lg-4 yd-surface" style="border-right:1px solid rgba(148,163,184,.25);">
                <div class="p-4" style="border-bottom:1px solid rgba(148,163,184,.15);">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="fw-bold" style="font-size:18px;">Borçlular</div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="dropdown">
                                <button class="yd-sort-btn" type="button" id="ydSortBtn" data-bs-toggle="dropdown" aria-expanded="false" title="Sırala">
                                    <i class="feather-filter"></i>
                                    <span id="ydSortLabel">Sırala</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="ydSortBtn">
                                    <li>
                                        <button class="dropdown-item" type="button" data-yd-sort="amount_asc">Artan (Tutar)</button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item" type="button" data-yd-sort="amount_desc">Azalan (Tutar)</button>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button class="dropdown-item" type="button" data-yd-sort="unit_asc">A'dan Z'ye (Daire)</button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item" type="button" data-yd-sort="unit_desc">Z'den A'ya (Daire)</button>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button class="dropdown-item" type="button" data-yd-sort="name_asc">A'dan Z'ye (Ad)</button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item" type="button" data-yd-sort="name_desc">Z'den A'ya (Ad)</button>
                                    </li>
                                </ul>
                            </div>
                            <span class="yd-chip" title="Toplam kişi"><?= count($borclular) ?> kişi</span>
                        </div>
                    </div>
                    <div class="position-relative">
                        <i class="feather-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;"></i>
                        <input id="ydSearch"  type="text" class="form-control" style="padding-left:36px;padding-right:44px;border-radius:12px;" placeholder="Ad, daire, telefon..." />
                        <button type="button" id="ydSearchClear" class="yd-search-clear" aria-label="Aramayı temizle" title="Temizle">
                            <i class="feather-x"></i>
                        </button>
                    </div>
                    <div class="d-flex gap-2 mt-3" style="overflow:auto;">
                        <button type="button" class="yd-chip" data-yd-filter="all">Tümü</button>
                        <button type="button" class="yd-chip yd-chip-danger" data-yd-filter="has_debt">Borçlu</button>
                        <button type="button" class="yd-chip yd-chip-success" data-yd-filter="paid">Ödedi</button>
                    </div>
                </div>

                <div class="p-3 yd-list" id="ydPersonnelList">
                    <?php if (empty($borclular)) { ?>
                        <div class="alert alert-info m-2">Borç kaydı bulunamadı.</div>
                    <?php } else { ?>
                        <?php foreach ($borclular as $row) {
                            $kisiId = (int)($row->kisi_id ?? 0);
                            $enc = Security::encrypt($kisiId);
                            $adi = (string)($row->adi_soyadi ?? '');
                            $daire = (string)($row->daire_kodu ?? '');
                            $tel = (string)($row->telefon ?? '');

                            // Aktif/Pasif ve ek bilgiler (çıkış tarihi boşsa aktif)
                            $cikisTarihiRaw = trim((string)($row->cikis_tarihi ?? ''));
                            $isKisiActive = ($cikisTarihiRaw === '' || $cikisTarihiRaw === '0000-00-00' || $cikisTarihiRaw === '0000-00-00 00:00:00');
                            $uyelikTipi = trim((string)($row->uyelik_tipi ?? ''));
                            $daireTipi = trim((string)($row->daire_tipi ?? ''));

                            $kalan = (float)($row->bakiye ?? 0);
                            $net = $kalan;

                            $status = $net < 0 ? 'Borçlu' : ($net == 0 ? 'Borcu Yok' : 'Alacaklı');
                            $statusClass = $net < 0 ? 'yd-chip yd-chip-danger' : ($net == 0 ? 'yd-chip yd-chip-info' : 'yd-chip yd-chip-success');

                          
                            $initials = Helper::getInitials($adi);

                            $isActive = ((int)$kisiId === (int)$selectedKisiId);

                            $kisiDurumText = $isKisiActive ? 'Aktif' : 'Pasif';
                            $kisiDurumClass = $isKisiActive ? 'text-success' : 'text-danger';
                            $kisiDurumTooltip = $isKisiActive ? '' : ('Çıkış Tarihi: ' . $cikisTarihiRaw);

                            $uyelikBadgeText = $uyelikTipi ?: '';
                            $uyelikBadgeClass = $uyelikTipi == "Kat Maliki" ? 'text-teal' : 'text-warning';
                            if (mb_stripos($uyelikBadgeText, 'kirac') !== false) $uyelikBadgeClass .= ' yd-lbadge--kiraci';
                            else $uyelikBadgeClass .= ' yd-lbadge--katmaliki';
                        ?>
                            <a href="javascript:void(0);" class="yd-item <?= $isActive ? 'is-active' : '' ?>" href="/borc-odeme?kisi=<?= urlencode($enc) ?>"
                                data-yd-kisi="<?= htmlspecialchars((string)$enc) ?>"
                                data-yd-name="<?= htmlspecialchars($adi) ?>"
                                data-yd-unit="<?= htmlspecialchars($daire) ?>"
                                data-yd-phone="<?= htmlspecialchars($tel) ?>"
                                data-yd-net="<?= htmlspecialchars((string)$net) ?>"
                                style="color:inherit;text-decoration:none;">
                                <div class="yd-avatar"><?= htmlspecialchars($initials ?: 'K') ?></div>
                                <div class="flex-grow-1" style="min-width:0;">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="fw-bold text-truncate"><?= htmlspecialchars($adi) ?>
                                    
                                    </div>
                                    <span class="<?= $statusClass ?>"><?= htmlspecialchars($status) ?></span>
                                    
                                    
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-1">
                                    <div class="yd-muted text-truncate" style="font-size:12px;">
                                            <span class="badge bg-gray-200 text-dark text-bold"><?= htmlspecialchars($daire) ?></span>
                                            <span class="badge <?= $uyelikBadgeClass ?> border border-dashed border-gray-500"><?= htmlspecialchars($uyelikBadgeText) ?></span>
                                            <span class="badge <?= $kisiDurumClass ?> border border-dashed border-gray-500"
                                                <?= $kisiDurumTooltip ? 'data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-original-title="' . htmlspecialchars($kisiDurumTooltip) . '"' : '' ?>>
                                                <?= htmlspecialchars($kisiDurumText) ?>
                                            </span>
                                            <span class="badge text-warning border border-dashed border-gray-500"><?= htmlspecialchars($daireTipi) ?></span>
                                        </div>

                                        <div class="fw-bold yd-amount"><?= Helper::formattedMoney($net) ?></div>
                                    </div>

                                
                                </div>
                            </a>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>

            <!-- Sağ kolon: Detaylar -->
            <div class="col-12 col-lg-8" style="background:#f6f6f8;">
                <div class="p-4" style="border-bottom:1px solid rgba(148,163,184,.25);background:#ffffff;">
                    <?php if (!$selectedPerson) { ?>
                        <div class="alert alert-warning">Seçili kişi bulunamadı.</div>
                    <?php } else { ?>
                        <?php
                        $adi = (string)($selectedPerson->adi_soyadi ?? '');
                        $daire = (string)($selectedPerson->daire_kodu ?? '');
                        $tel = (string)($selectedPerson->telefon ?? '');

                        $kalan = (float)($selectedPerson->bakiye ?? 0);
                        $net =  $kalan;
                        $status = $net < 0 ? 'Durum: Borçlu' : 'Durum: Borcu Yok';
                        $statusClass = $net < 0 ? 'yd-chip yd-chip-danger' : 'yd-chip yd-chip-success';

                        $kpiToplamBorc = (float)($selectedBorcOzet->toplam_borc ?? 0);
                        $kpiTahsilEdilen = (float)($selectedBorcOzet->toplam_tahsilat ?? 0);
                        $kpiKalan = (float)($selectedBorcOzet->kalan_borc ?? max(0, -$net));
                        $kpiDurum = $kpiKalan > 0 ? 'Kalan Borç' : 'Alacak';
                        ?>
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <a class="fw-bold yd-link" style="font-size:24px;" id="ydSelectedName" href="/site-sakini-duzenle/<?= urlencode($selectedEnc) ?>"><?= htmlspecialchars($adi) ?></a>
                                    <span class="<?= $statusClass ?>" id="ydSelectedStatus"><?= htmlspecialchars($status) ?></span>
                                </div>
                                <div class="yd-muted mt-1" style="font-size:12px;">
                                    <i class="feather-home me-1"></i> <span id="ydSelectedUnit">Daire <?= htmlspecialchars($daire) ?></span>
                                    <span style="color:#cbd5e1;" class="mx-2">|</span>
                                    <i class="feather-phone me-1"></i> <span id="ydSelectedPhone"><?= htmlspecialchars($tel) ?></span>
                                </div>
                                <a class="yd-link" id="ydProfileLink" href="/site-sakini-duzenle/<?= urlencode($selectedEnc) ?>" style="display:none;"></a>
                                <div class="d-flex justify-content-start align-items-center gap-1 mt-2">
                                    <a href="javascript:void(0)" class="d-flex me-1 mesaj-gonder" data-alert-target="SendMessage"
                                        data-id="<?= (int)($selectedKisiId ?? 0) ?>"
                                        data-kisi-id="<?= htmlspecialchars($selectedEnc) ?>"
                                        data-phone="<?= htmlspecialchars(preg_replace('/[^0-9]/', '', (string)($tel ?? ''))) ?>"
                                        data-daire="<?= htmlspecialchars((string)($daire ?? '')) ?>">
                                        <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-original-title="Mesaj Gönder">
                                            <i class="feather feather-send"></i>
                                        </div>
                                    </a>

                                    <a href="#" target="_blank" class="d-flex me-1 whatsapp-mesaj-gonder" id="ydWhatsappLink"
                                        data-bs-toggle="tooltip" data-bs-original-title="WhatsApp'tan Mesaj Gönder"
                                        data-id="<?= (int)($selectedKisiId ?? 0) ?>"
                                        data-kisi-id="<?= htmlspecialchars($selectedEnc) ?>">
                                        <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-original-title="WhatsApp'tan Mesaj Gönder">
                                            <i class="fa-brands fa-whatsapp"></i>
                                        </div>
                                    </a>

                                    <a href="/pages/dues/payment/export/kisi_borc_tahsilat.php?kisi_id=<?= (int)($selectedKisiId ?? 0) ?>&format=html" target="_blank" class="d-flex me-1" id="ydPrintHtml">
                                        <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-original-title="Yazdır">
                                            <i class="feather feather-printer"></i>
                                        </div>
                                    </a>
                                
                                    <a href="/pages/dues/payment/export/kisi_borc_tahsilat.php?kisi_id=<?= (int)($selectedKisiId ?? 0) ?>" class="d-flex me-1 download">
                                        <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-original-title="PDF İndir">
                                            <i class="fa-solid fa-file-pdf"></i>
                                        </div>
                                    </a>

                                    <a href="/pages/dues/payment/export/kisi_borc_tahsilat.php?kisi_id=<?= (int)($selectedKisiId ?? 0) ?>&format=xlsx" class="d-flex me-1 download">
                                        <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-original-title="Excel İndir">
                                            <i class="fa-regular fa-file-excel"></i>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="yd-muted" style="font-size:12px;"><?= $kpiDurum ?></div>
                                <div class="fw-bold" style="font-size:28px;" id="ydKalanBorcHeader"><?= $fmt($kpiKalan) ?></div>

                            </div>
                        </div>
                    <?php } ?>
                </div>

                <div class="p-4">
                    <?php if ($selectedPerson) { ?>
                        <div class="row g-3" id="ydKpiRow">
                            <div class="col-12 col-md-4">
                                <div class="yd-card p-3" style="position:relative;overflow:hidden;">
                                    <div style="position:absolute;right:0;top:0;height:100%;width:4px;background:#6D94C5;"></div>
                                    <div class="yd-muted" style="font-size:12px;">Toplam Borç</div>

                                    <div class="yd-kpi" id="ydKpiToplamBorc"><?= $fmt($kpiToplamBorc) ?></div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="yd-card p-3" style="position:relative;overflow:hidden;">
                                    <div style="position:absolute;right:0;top:0;height:100%;width:4px;background:#A7C1A8;"></div>
                                    <div class="yd-muted" style="font-size:12px;">Tahsil Edilen</div>

                                    <div class="yd-kpi" id="ydKpiTahsilEdilen"><?= $fmt($kpiTahsilEdilen) ?></div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="yd-card p-3" style="position:relative;overflow:hidden;">
                                    <div style="position:absolute;right:0;top:0;height:100%;width:4px;background:#CD5656;"></div>
                                    <div class="yd-muted" style="font-size:12px;">Kalan</div>
                                    <div class="yd-kpi" id="ydKpiKalan"><?= $fmt($kpiKalan) ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="yd-card mt-4" id="ydTabsCard">
                            <div class="px-4 pt-3" style="background:#fff;">
                                <div class="yd-tabbar" role="tablist">
                                    <button type="button" class="yd-tab-btn is-active" data-yd-tab="debts" role="tab">Borçlandırma Detayları</button>
                                    <button type="button" class="yd-tab-btn" data-yd-tab="collections" role="tab">Tahsilatlar</button>
                                </div>
                            </div>

                            <div class="p-0" style="background:#fff;">
                                <!-- Tab: Borçlandırma Detayları -->
                                <div class="yd-tab-panel" data-yd-panel="debts">
                                    <div class="d-flex align-items-center justify-content-between px-4 py-3" style="border-bottom:1px solid rgba(148,163,184,.25)">
                                        <div class="fw-bold d-flex align-items-center gap-2">
                                            <span>Borçlandırmalar</span>
                                            <a href="javascript:void(0);" class="avatar-text avatar-md   yd-borc-add" title="Yeni Borç" aria-label="Yeni Borç">
                                                <i class="feather-plus"></i>
                                            </a>
                                        </div>
                                        <div class="d-flex gap-2 align-items-center">
                                            <div class="form-check form-switch me-2" style="margin-bottom:0;">
                                                <input class="form-check-input" type="checkbox" id="ydOnlyDebtsToggle">
                                                <label class="form-check-label" for="ydOnlyDebtsToggle" style="cursor:pointer;">Sadece borçları göster</label>
                                            </div>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="ydSelectAllDebts">Tümünü Seç</button>
                                            <button type="button" class="btn btn-primary btn-sm" id="ydCollectSelectedDebts" disabled>
                                                <i class="feather-credit-card me-1"></i>Seçilenleri Tahsil Et
                                            </button>
                                        </div>
                                    </div>
                                    <div class="table-responsive yd-debts-scroll">
                                        <table class="table mb-0 yd-table" id="ydDebtsTable">
                                            <thead>
                                                <tr>
                                                    <th class="px-4" style="width:42px;"></th>
                                                    <th class="px-4">Borç</th>
                                                    <th>Dönem</th>
                                                    <th class="text-end">Toplam</th>
                                                    <th class="text-end">Ödenen</th>
                                                    <th class="text-end">Kalan</th>
                                                    <th class="text-end" style="width:110px;">İşlem</th>
                                                </tr>
                                            </thead>
                                            <tbody id="ydDebtsTbody">
                                                <tr>
                                                    <td class="px-4" colspan="7"><span class="yd-muted">Yükleniyor...</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Tab: Tahsilatlar -->
                                <div class="yd-tab-panel" data-yd-panel="collections" style="display:none;">
                                    <div class="d-flex align-items-center justify-content-between px-4 py-3" style="border-bottom:1px solid rgba(148,163,184,.25)">
                                        <div class="fw-bold">İşlem Geçmişi</div>
                                        <div class="d-flex gap-2">
                                            <a class="btn btn-primary btn-sm tahsilat-ekle" href="#" title="Tahsilat Ekle">
                                                <i class="feather-check-square me-1"></i>Tahsilat Ekle
                                            </a>
                                        </div>
                                    </div>
                                    <div class="table-responsive yd-collections-scroll">
                                        <table class="table mb-0 yd-table" id="ydTahsilatTable">
                                            <thead>
                                                <tr>
                                                    <th class="px-4">Tarih</th>
                                                    <th class="yd-desc">Açıklama</th>
                                                    <th class="text-end">Tutar</th>
                                                    <th class="text-end" style="width:90px;">Detay</th>
                                                </tr>
                                            </thead>
                                            <tbody id="ydTahsilatTbody">
                                                <tr>
                                                    <td class="px-4" colspan="5"><span class="yd-muted">Yükleniyor...</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>


                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>




<!-- Yeni Tahsilat Modal (Dashboard) -->
<div class="modal fade" id="ydTahsilatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content tahsilat-modal-body" style="border-radius:16px; overflow:hidden;">

        </div>
    </div>
</div>

