<?php

use App\Helper\Helper;
use App\Helper\Date;
use Model\FinansalRaporModel;
use Model\KisilerModel;

$Rapor = new FinansalRaporModel();
$Kisiler = new KisilerModel();
$site_id = (int) ($_SESSION['site_id'] ?? 0);
// Kullanıcıya ait muhtemel kişi kayıtları (eposta/telefon eşleşmesi)
$sessionEmail = trim((string)($_SESSION['user']->email ?? ''));
$sessionPhone = trim((string)($_SESSION['user']->phone ?? ''));
$sessionName  = trim((string)($_SESSION['user']->full_name ?? ''));
$tumKisiler = $Kisiler->SiteTumKisileri($site_id);
$kisiAdaylari = array_values(array_filter($tumKisiler, function ($k) use ($sessionEmail, $sessionPhone, $sessionName) {
    $e = trim((string)($k->eposta ?? ''));
    $p = trim((string)($k->telefon ?? ''));
    $n = trim((string)($k->adi_soyadi ?? ''));
    $nameMatch = ($sessionName && $n && mb_strtolower($sessionName) === mb_strtolower($n));
    $emailMatch = ($sessionEmail && $e && strcasecmp($sessionEmail, $e) === 0);
    $phoneMatch = ($sessionPhone && $p && $sessionPhone === $p);
    return $nameMatch || $emailMatch || $phoneMatch;
}));

$selectedParam = $_GET['kisi_id'] ?? '';
$selectedAll = ($selectedParam === 'all');
$selectedId = !$selectedAll ? (int)($selectedParam ?: 0) : 0;
$defaultUserId = (int)($_SESSION['user']->kisi_id ?? ($_SESSION['user']->id ?? 0));
$activeKisiId = $selectedId ?: $defaultUserId;

if ($selectedAll && !empty($kisiAdaylari)) {
    $hesap_ozet = (object)[
        'toplam_borc' => 0,
        'toplam_tahsilat' => 0,
        'bakiye' => 0,
    ];
    $hareketler = [];
    foreach ($kisiAdaylari as $k) {
        $oz = $Rapor->KisiFinansalDurum((int)$k->id);
        if ($oz) {
            $hesap_ozet->toplam_borc += (float)($oz->toplam_borc ?? 0);
            $hesap_ozet->toplam_tahsilat += (float)($oz->toplam_tahsilat ?? 0);
            $hesap_ozet->bakiye += (float)($oz->bakiye ?? 0);
        }
        $h = $Rapor->kisiHesapHareketleri((int)$k->id) ?: [];
        $hareketler = array_merge($hareketler, $h);
    }
} else {
    $hesap_ozet = $Rapor->KisiFinansalDurum($activeKisiId);
    $hareketler = $Rapor->kisiHesapHareketleri($activeKisiId);
    $kategoriOzet = $Rapor->getKisiKategoriOzet($activeKisiId);
}

$sonOdeme = null;
foreach ($hareketler as $it) {
    if (strtolower($it->islem_tipi ?? '') === 'Ödeme') {
        if (!$sonOdeme || strtotime($it->islem_tarihi) > strtotime($sonOdeme)) {
            $sonOdeme = $it->islem_tarihi;
        }
    }
}
$kisiInfo = $Kisiler->getKisiByDaireId((int)$activeKisiId);
?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Hesap Özeti</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/sakin/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Finans</li>
        </ul>
    </div>
   
    <div class="d-md-none d-flex align-items-center">
        <a href="javascript:void(0)" class="page-header-right-open-toggle">
            <i class="feather-align-right fs-20"></i>
        </a>
    </div>
</div>

<div class="main-content">
    <div class="p-4 mb-4 d-xxl-flex d-xl-block d-md-flex align-items-center justify-content-between gap-4 border border-dashed border-gray-5 rounded-1">
        <div>
            <div class="fs-14 fw-bold text-dark mb-1">
                <span class="me-2"><?php echo htmlspecialchars($kisiInfo->adi_soyadi ?? '-'); ?></span>
                <?php if (!empty($kisiInfo->uyelik_tipi)) { ?>
                <a href="javascript:void(0);" class="badge bg-primary text-white ms-2"><?php echo htmlspecialchars($kisiInfo->uyelik_tipi); ?></a>
                <?php } ?>
            </div>
            <div class="fs-12 text-muted">Daire: <span class="text-dark fw-medium"><?php echo htmlspecialchars($kisiInfo->daire_kodu ?? '-'); ?></span></div>
        </div>
        <div class="my-3 my-xxl-0 my-md-3 my-md-0">
            <div class="fs-20 text-dark"><span class="fw-bold"><?php echo Helper::formattedMoney($hesap_ozet->bakiye ?? 0); ?></span> / <em class="fs-11 fw-medium">Güncel Bakiye</em></div>
            <div class="fs-12 text-muted mt-1">Son ödeme: <strong class="text-dark"><?php echo $sonOdeme ? Date::dmy($sonOdeme) : '-'; ?></strong></div>
        </div>
        <div class="hstack gap-3">
            <a href="/pages/dues/payment/export/kisi_borc_tahsilat.php?kisi_id=<?php echo (int)$activeKisiId; ?>&format=pdf" class="text-danger">Ekstre İndir (PDF)</a>
            <a href="javascript:void(0);" class="btn btn-light-brand" disabled>Ödeme Yap</a>
        </div>
    </div>
    <div class="row g-4 mb-5">
        <div class="col-12 col-lg-4">
            <div class="card rounded-3">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="fw-semibold">Özet Bakiye</div>
                        <i class="feather-pie-chart"></i>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted">Toplam Borç</div>
                            <h4 class="text-danger mb-3"><?php echo Helper::formattedMoney($hesap_ozet->toplam_borc ?? 0); ?></h4>
                            <div class="text-muted">Son Ödeme</div>
                            <h6 class="text-success mb-0"><?php echo $sonOdeme ? Date::dmy($sonOdeme) : '-'; ?></h6>
                        </div>
                        <div class="text-end">
                            <div class="text-muted">Toplam Tahsilat</div>
                            <h4 class="text-success mb-3"><?php echo Helper::formattedMoney($hesap_ozet->toplam_tahsilat ?? 0); ?></h4>
                            <div class="text-muted">Kalan</div>
                            <h6 class="text-<?php echo (($hesap_ozet->bakiye ?? 0) < 0) ? 'danger' : 'success'; ?> mb-0"><?php echo Helper::formattedMoney($hesap_ozet->bakiye ?? 0); ?></h6>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card rounded-3 mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Hızlı Bağlantılar</h5>
                </div>
                <div class="card-body d-flex flex-column gap-2">
                    <a href="/pages/dues/payment/export/kisi_borc_tahsilat.php?kisi_id=<?php echo $activeKisiId; ?>&format=pdf" class="btn btn-light w-100">
                        <i class="bi bi-filetype-pdf me-2"></i>PDF Ekstre İndir
                    </a>
                    <a href="#" class="btn btn-primary w-100" disabled>
                        <i class="feather-credit-card me-2"></i>Online Ödeme
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="card rounded-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">12 Ay Ödeme Grafiği</h5>
                </div>
                <div class="card-body">
                    <div id="chartMonthlyCompare"></div>
                </div>
            </div>
        </div>



        <div class="col-12">
            <div class="card rounded-3 mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Tür Bazında Özet</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-12 col-lg-6">
                            <div class="accordion" id="katAccordion">
                                <?php $i = 0; foreach (($kategoriOzet ?? []) as $k): $cid = 'katItem'.(++$i); ?>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="h-<?php echo $cid; ?>">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c-<?php echo $cid; ?>" aria-expanded="false" aria-controls="c-<?php echo $cid; ?>">
                                                <?php echo htmlspecialchars($k->kategori ?? ''); ?>
                                            </button>
                                        </h2>
                                        <div id="c-<?php echo $cid; ?>" class="accordion-collapse collapse" aria-labelledby="h-<?php echo $cid; ?>" data-bs-parent="#katAccordion">
                                            <div class="accordion-body">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-muted">Toplam Borç</span>
                                                    <span class="text-danger fw-semibold"><?php echo Helper::formattedMoney($k->toplam_borc ?? 0); ?></span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-muted">Toplam Ödeme</span>
                                                    <span class="text-success fw-semibold"><?php echo Helper::formattedMoney($k->toplam_odeme ?? 0); ?></span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-3">
                                                    <span class="text-muted">Kalan</span>
                                                    <span class="fw-semibold text-<?php echo (($k->kalan ?? 0) < 0) ? 'danger' : 'success'; ?>"><?php echo Helper::formattedMoney($k->kalan ?? 0); ?></span>
                                                </div>
                                                <?php 
                                                    $borc = (float)($k->toplam_borc ?? 0); 
                                                    $odeme = (float)($k->toplam_odeme ?? 0); 
                                                    $rate = $borc > 0 ? max(0, min(100, round(($odeme/$borc)*100))) : 100; 
                                                ?>
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $rate; ?>%" aria-valuenow="<?php echo $rate; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (empty($kategoriOzet)): ?>
                                    <div class="text-muted">Kayıt bulunamadı.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div id="chartByCategory"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card rounded-3">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Borç/Alacak Tablosu</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Tarih</th>
                                    <th>İşlem</th>
                                    <th>Açıklama</th>
                                    <th class="text-end">Tutar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Yeniden → Eskiye sırala
                                usort($hareketler, function ($a, $b) {
                                    return strtotime($b->islem_tarihi) <=> strtotime($a->islem_tarihi);
                                });
                                ?>
                                <?php foreach (array_slice($hareketler, 0, 20) as $h): $tip = mb_strtolower($h->islem_tipi ?? '');
                                    $isPay = in_array($tip, ['ödeme', 'tahsilat']); ?>
                                    <tr class="<?php echo $isPay ? 'table-success' : ''; ?>">
                                        <td><?php echo Date::dmy($h->islem_tarihi); ?></td>
                                        <td><?php echo htmlspecialchars($h->islem_tipi ?? ''); ?></td>
                                        <td class="text-truncate" style="max-width:280px;">
                                            <?php echo htmlspecialchars($h->aciklama ?? ''); ?>
                                        </td>
                                        <td class="text-end fw-semibold text-<?php echo $isPay ? 'success' : 'danger'; ?>">
                                            <?php echo Helper::formattedMoneyWithoutCurrency($h->hareket_tutari ?? 0); ?> ₺
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="mb-5"></div>

<?php
// Aylık grafik verilerini gerçek hareketlerden üret
$months = [];
for ($i = 11; $i >= 0; $i--) {
    $key = date('Y-m', strtotime("-{$i} months"));
    $months[] = $key;
}
$labels = ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara'];
$labelsOrdered = [];
foreach ($months as $key) {
    $labelsOrdered[] = $labels[(int)date('n', strtotime($key)) - 1];
}

$acc = array_fill_keys($months, 0.0);
$pay = array_fill_keys($months, 0.0);
foreach ($hareketler as $h) {
    $k = date('Y-m', strtotime($h->islem_tarihi));
    if (!isset($acc[$k])) continue;
    $tip = mb_strtolower((string)($h->islem_tipi ?? ''));
    if ($tip === 'ödeme') {
        $pay[$k] += (float)($h->odenen ?? ($h->hareket_tutari < 0 ? -$h->hareket_tutari : 0));
    } else {
        $acc[$k] += (float)($h->anapara ?? 0) + (float)($h->gecikme_zammi ?? 0);
    }
}
$accVals = array_values($acc);
$payVals = array_values($pay);
?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (!window.ApexCharts) {
            var s = document.createElement('script');
            s.src = '/assets/vendors/js/apexcharts.min.js';
            s.onload = initChart;
            document.body.appendChild(s);
        } else {
            initChart();
        }

        function initChart() {
            if (!window.ApexCharts) return;
            var months = <?php echo json_encode($labelsOrdered, JSON_UNESCAPED_UNICODE); ?>;
            var accruals = <?php echo json_encode(array_map('floatval', $accVals)); ?>;
            var payments = <?php echo json_encode(array_map('floatval', $payVals)); ?>;
            new ApexCharts(document.querySelector('#chartMonthlyCompare'), {
                chart: {
                    type: 'line',
                    height: 300,
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                        name: 'Borç',
                        data: accruals
                    },
                    {
                        name: 'Ödeme',
                        data: payments
                    }
                ],
                xaxis: {
                    categories: months
                },
                colors: ['#dc3545', '#28a745'],
                stroke: {
                    width: 3
                },
                markers: {
                    size: 0
                },
                grid: {
                    strokeDashArray: 4
                }
            }).render();
        }
    });
</script>
<?php 
// Tür bazlı özet grafik verileri
$katLabels = []; $katBorc = []; $katOdeme = []; $katKalan = [];
foreach (($kategoriOzet ?? []) as $k) {
    $katLabels[] = (string)($k->kategori ?? '');
    $katBorc[]   = (float)($k->toplam_borc ?? 0);
    $katOdeme[]  = (float)($k->toplam_odeme ?? 0);
    $katKalan[]  = (float)($k->kalan ?? 0);
}
?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function initCategoryChart() {
            if (!window.ApexCharts) return;
            var cats = <?php echo json_encode($katLabels, JSON_UNESCAPED_UNICODE); ?>;
            var borc = <?php echo json_encode(array_map('floatval', $katBorc)); ?>;
            var odeme = <?php echo json_encode(array_map('floatval', $katOdeme)); ?>;
            var kalan = <?php echo json_encode(array_map('floatval', $katKalan)); ?>;
            var el = document.querySelector('#chartByCategory');
            if (!el) return;
            new ApexCharts(el, {
                chart: { type: 'bar', height: 300, stacked: false, toolbar: { show: false } },
                series: [
                    { name: 'Toplam Borç', data: borc },
                    { name: 'Toplam Ödeme', data: odeme },
                    { name: 'Kalan', data: kalan }
                ],
                xaxis: { categories: cats },
                colors: ['#dc3545', '#28a745', '#ffc107'],
                plotOptions: { bar: { horizontal: false } },
                dataLabels: { enabled: false },
                grid: { strokeDashArray: 4 }
            }).render();
        }
        if (!window.ApexCharts) {
            var s = document.createElement('script');
            s.src = '/assets/vendors/js/apexcharts.min.js';
            s.onload = initCategoryChart;
            document.body.appendChild(s);
        } else {
            initCategoryChart();
        }
    });
</script>
