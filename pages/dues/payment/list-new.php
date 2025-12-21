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

<style>
    .yd-wrap {
        background: #f6f6f8;
        border: 1px solid rgba(148, 163, 184, .35);
        border-radius: 10px;
        overflow: hidden;
        width: 100%;
        margin-bottom: 60px;


    }

    .yd-surface {
        background: #ffffff;
    }

    .yd-muted {
        color: #64748b;
    }

    .yd-chip {
        font-size: 12px;
        padding: 6px 10px;
        border-radius: 999px;
        border: 1px solid rgba(148, 163, 184, .35);
        background: #f1f5f9;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .yd-chip.yd-chip-danger {
        background: #fef2f2;
        border-color: #fee2e2;
        color: #dc2626;
    }

    .yd-chip.yd-chip-success {
        background: #ecfdf5;
        border-color: #d1fae5;
        color: #059669;
    }

    .yd-card {
        background: #fff;
        border: 1px solid rgba(148, 163, 184, .35);
        border-radius: 10px;
        padding: 6px;
    }

    .yd-kpi {
        font-size: 20px;
        font-weight: 800;
    }

    .yd-list {
        max-height: calc(100vh - 270px);
        overflow: auto;
    }

    .yd-debts-scroll {
        max-height: calc(100vh - 470px);
        overflow: auto;
    }

    /* Tahsilatlar tabı: borç tablosu ile aynı yükseklik ve yatay kaydırma olmasın */
    .yd-collections-scroll {
        max-height: calc(100vh - 470px);
        overflow-y: auto;
        overflow-x: hidden;
    }

    /* Açıklama hücresi satıra sığmazsa aşağı kaysın */
    #ydTahsilatTable td.yd-desc,
    #ydTahsilatTable th.yd-desc {
        white-space: normal;
        word-break: break-word;
    }

    /* Table responsive wrapper bazen x-scroll oluşturabiliyor; bunu kapat */
    .yd-collections-scroll.table-responsive {
        overflow-x: hidden;
    }

    /* Tahsilat detay satırı (dağılım) */
    .yd-tahsilat-detail-row {
        background: #f8fafc;
    }

    .yd-tahsilat-detail-box {
        border: 1px solid rgba(148, 163, 184, .25);
        border-radius: 12px;
        padding: 12px;
        background: #fff;
    }

    .yd-tahsilat-detail-title {
        font-weight: 800;
        font-size: 12px;
        color: #334155;
        letter-spacing: .02em;
        margin-bottom: 8px;
    }

    .yd-tahsilat-detail-item {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        justify-content: space-between;
        padding: 6px 0;
        border-bottom: 1px dashed rgba(148, 163, 184, .35);
    }

    .yd-tahsilat-detail-item:last-child {
        border-bottom: 0;
    }

    .yd-tahsilat-detail-item .name {
        font-weight: 700;
        color: #0f172a;
    }

    .yd-tahsilat-detail-item .desc {
        font-size: 12px;
        color: #64748b;
        white-space: normal;
        word-break: break-word;
    }

    .yd-tahsilat-detail-item .amt {
        font-weight: 800;
        white-space: nowrap;
    }

    .yd-item {
        border: 1px solid transparent;
        border-radius: 14px;
        padding: 12px;
        display: flex;
        gap: 12px;
        align-items: center;
        margin-bottom: 4px;

    }

    .yd-item:hover {
        background: #f8fafc;
        border-color: rgba(148, 163, 184, .25);
        cursor: pointer;


    }

    .yd-item.is-active {
        background: rgba(68, 93, 143, 0.09);
        border-color: rgba(147, 161, 184, 0.25);
        position: relative;
    }

    .yd-item.is-active:before {
        content: "";
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 32px;
        background: #135bec;
        border-radius: 0 8px 8px 0;
    }

    .yd-avatar {
        width: 44px;
        height: 44px;
        border-radius: 999px;
        background: #e2e8f0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #334155;
        flex: 0 0 auto;
    }

    .yd-item.is-active .yd-avatar {
        background: rgba(19, 91, 236, .1);
        color: #135bec;
    }



    .yd-pill-warn {
        background: #fff7ed;
        border-color: #fed7aa;
        color: #ea580c;
    }

    .yd-table thead {
        background: #f8fafc;
    }

    /* Borç tablosu: satırlar kart gibi (radius) */
    #ydDebtsTable {
        border-collapse: separate;
        border-spacing: 0 4px;
        /* satırlar arası boşluk */
    }

    #ydDebtsTable thead th {
        border-bottom: 0;
    }

    #ydDebtsTable tbody td {
        background: #fff;
        border-top: 1px solid rgba(148, 163, 184, .25);
        border-bottom: 1px solid rgba(148, 163, 184, .25);
    }

    #ydDebtsTable tbody td:first-child {
        border-left: 1px solid rgba(148, 163, 184, .25);
        border-top-left-radius: 8px;
        border-bottom-left-radius: 8px;
    }

    #ydDebtsTable tbody td:last-child {
        border-right: 1px solid rgba(148, 163, 184, .25);
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
    }

    .yd-link {
        color: #135bec;
        text-decoration: none;
    }

    .yd-link:hover {
        text-decoration: underline;
    }

    .yd-tab-btn {
        background: transparent;
        border: 0;
        padding: 10px 0;
        font-weight: 600;
        color: #64748b;
    }

    .yd-tab-btn.is-active {
        color: #135bec;
        border-bottom: 2px solid #135bec;
    }

    .yd-tabbar {
        display: flex;
        gap: 22px;
        border-bottom: 1px solid rgba(148, 163, 184, .15);
    }

    .yd-moneyline {
        display: flex;
        flex-direction: column;
        gap: 2px;
        align-items: flex-end;
    }

    .yd-moneyline .sub {
        font-size: 12px;
        color: #64748b;
        font-weight: 600;
    }

    .yd-sort-btn {
        border-radius: 999px;
        border: 1px solid rgba(148, 163, 184, .35);
        background: #f8fafc;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 600;
        color: #334155;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }

    .yd-sort-btn:hover {
        background: #f1f5f9;
    }

    #ydDebtsTbody tr:hover td {
        background: #f8fafc;
        cursor: pointer;
    }

    /* Borç satırı seçili görünümü (kişilerdeki active hissi) */
    #ydDebtsTbody tr.yd-debt-row.yd-is-active td {
        background: rgba(68, 93, 143, 0.09);
    }

    /* Sol tarafta mavi şerit (kişilerdeki gibi) */
    #ydDebtsTbody tr.yd-debt-row.yd-is-active td:first-child {
        box-shadow: inset 2px 0 0 #135bec;
    }

    /* Tahsilat tablosu: satırlar kart gibi (radius) */
    #ydTahsilatTable {
        border-collapse: separate;
        border-spacing: 0 4px;
    }

    #ydTahsilatTable thead th {
        border-bottom: 0;
    }

    /* Normal tahsilat satırları kart görünümü */
    #ydTahsilatTable tbody tr.yd-tahsilat-row td {
        background: #fff;
        border-top: 1px solid rgba(148, 163, 184, .25);
        border-bottom: 1px solid rgba(148, 163, 184, .25);
    }

    #ydTahsilatTable tbody tr.yd-tahsilat-row td:first-child {
        border-left: 1px solid rgba(148, 163, 184, .25);
        border-top-left-radius: 8px;
        border-bottom-left-radius: 8px;
    }

    #ydTahsilatTable tbody tr.yd-tahsilat-row td:last-child {
        border-right: 1px solid rgba(148, 163, 184, .25);
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
    }

    /* Hover: sadece normal satırlar */
    #ydTahsilatTable tbody tr.yd-tahsilat-row:hover td {
        background: #f8fafc;
    }

    /* Detay satırı kart görünümünü bozmasın */
    #ydTahsilatTable tbody tr.yd-tahsilat-detail-row td {
        border: 0;
        background: transparent;
        padding-top: 0;
    }


    /**mobilde */
    @media (max-width: 768px) {
        .yd-wrap {
            padding-bottom: 20px;
        }
        .ydTabsCard{
            margin-bottom: 30px;
        }

    
    }
</style>
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
                        <input id="ydSearch" type="text" class="form-control" style="padding-left:36px;border-radius:12px;" placeholder="Ad, daire, telefon..." />
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
                                
                                    <a href="/pages/dues/payment/export/kisi_borc_tahsilat.php?kisi_id=<?= (int)($selectedKisiId ?? 0) ?>" class="d-flex me-1">
                                        <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-original-title="PDF İndir">
                                            <i class="fa-solid fa-file-pdf"></i>
                                        </div>
                                    </a>

                                    <a href="/pages/dues/payment/export/kisi_borc_tahsilat.php?kisi_id=<?= (int)($selectedKisiId ?? 0) ?>&format=xlsx" class="d-flex me-1 printBTN">
                                        <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-original-title="Excel İndir">
                                            <i class="fa-regular fa-file-excel"></i>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="yd-muted" style="font-size:12px;">Kalan Borç</div>
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
                                            <a class="btn btn-outline-secondary btn-sm" href="/onay-bekleyen-tahsilatlar" title="Onay bekleyenler">
                                                <i class="feather-check-square me-1"></i>Onaylar
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



<script>
    (function() {
        const listEl = document.getElementById('ydPersonnelList');
        if (!listEl) return;

        const sortLabel = document.getElementById('ydSortLabel');
        const searchEl = document.getElementById('ydSearch');
        const sortButtons = document.querySelectorAll('[data-yd-sort]');
        const filterButtons = document.querySelectorAll('[data-yd-filter]');

        // Default: daire koduna göre (A1D2 < A1D10 gibi natural)
        let currentSort = 'unit_asc';
        let currentFilter = 'all';

        const labels = {
            amount_asc: 'Artan',
            amount_desc: 'Azalan',
            unit_asc: 'Daire A→Z',
            unit_desc: 'Daire Z→A',
            name_asc: "A→Z",
            name_desc: "Z→A",
        };

        const getItems = () => Array.from(listEl.querySelectorAll('.yd-item'));

        const normalizeText = (s) => (s || '').toString().toLocaleLowerCase('tr-TR').trim();

        const parseNet = (el) => {
            const raw = (el.getAttribute('data-yd-net') || '').toString();
            const n = Number(raw);
            return Number.isFinite(n) ? n : 0;
        };

        const naturalCompare = (a, b) => {
            // 1) Prefer Intl.Collator numeric sort when available
            try {
                if (typeof Intl !== 'undefined' && Intl.Collator) {
                    const collator = new Intl.Collator('tr-TR', {
                        numeric: true,
                        sensitivity: 'base'
                    });
                    return collator.compare(a, b);
                }
            } catch (e) {
                // ignore and fall back
            }
            // 2) Fallback: basic chunked numeric compare
            const ax = (a || '').toString().toLocaleLowerCase('tr-TR').match(/(\d+|\D+)/g) || [];
            const bx = (b || '').toString().toLocaleLowerCase('tr-TR').match(/(\d+|\D+)/g) || [];
            const len = Math.min(ax.length, bx.length);
            for (let i = 0; i < len; i++) {
                const ac = ax[i];
                const bc = bx[i];
                if (ac === bc) continue;
                const an = Number(ac);
                const bn = Number(bc);
                const aIsNum = Number.isFinite(an) && /^\d+$/.test(ac);
                const bIsNum = Number.isFinite(bn) && /^\d+$/.test(bc);
                if (aIsNum && bIsNum) return an - bn;
                return ac.localeCompare(bc, 'tr-TR', { sensitivity: 'base' });
            }
            return ax.length - bx.length;
        };

        const matchesSearch = (el, q) => {
            if (!q) return true;
            const name = normalizeText(el.getAttribute('data-yd-name'));
            const unit = normalizeText(el.getAttribute('data-yd-unit'));
            const phone = normalizeText(el.getAttribute('data-yd-phone'));
            return name.includes(q) || unit.includes(q) || phone.includes(q);
        };

        const matchesFilter = (el, filter) => {
            if (!filter || filter === 'all') return true;
            const net = parseNet(el);
            if (filter === 'has_debt') return net < 0;
            if (filter === 'paid') return net >= 0;
            return true;
        };

        const compareItems = (a, b) => {
            if (currentSort === 'amount_asc') return parseNet(a) - parseNet(b);
            if (currentSort === 'amount_desc') return parseNet(b) - parseNet(a);
            if (currentSort === 'unit_asc') {
                return naturalCompare(a.getAttribute('data-yd-unit'), b.getAttribute('data-yd-unit'));
            }
            if (currentSort === 'unit_desc') {
                return naturalCompare(b.getAttribute('data-yd-unit'), a.getAttribute('data-yd-unit'));
            }
            if (currentSort === 'name_asc') {
                return normalizeText(a.getAttribute('data-yd-name')).localeCompare(
                    normalizeText(b.getAttribute('data-yd-name')),
                    'tr-TR',
                    { sensitivity: 'base' }
                );
            }
            if (currentSort === 'name_desc') {
                return normalizeText(b.getAttribute('data-yd-name')).localeCompare(
                    normalizeText(a.getAttribute('data-yd-name')),
                    'tr-TR',
                    { sensitivity: 'base' }
                );
            }
            return 0;
        };

        const apply = () => {
            const q = normalizeText(searchEl ? searchEl.value : '');

            const items = getItems();
            for (const el of items) {
                const visible = matchesSearch(el, q) && matchesFilter(el, currentFilter);
                el.style.display = visible ? '' : 'none';
            }

            const visibleItems = items.filter((el) => el.style.display !== 'none');
            visibleItems.sort(compareItems);
            for (const el of visibleItems) listEl.appendChild(el);

            if (sortLabel) sortLabel.textContent = labels[currentSort] || 'Sırala';
        };

        // Default label
        if (sortLabel) sortLabel.textContent = labels[currentSort] || 'Sırala';

        // Sort controls
        sortButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                currentSort = btn.getAttribute('data-yd-sort') || currentSort;
                apply();
            });
        });

        // Filter chips: keep existing styles but make them act like toggles
        filterButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                currentFilter = btn.getAttribute('data-yd-filter') || 'all';
                // active visual
                filterButtons.forEach((b) => b.classList.remove('is-active'));
                btn.classList.add('is-active');
                apply();
            });
        });

        // Search input
        if (searchEl) {
            searchEl.addEventListener('input', apply);
        }

        // Initial apply
        apply();
    })();
</script>