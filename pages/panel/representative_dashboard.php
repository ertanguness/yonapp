<?php
use App\Controllers\AuthController;
$user = AuthController::user();

// Eğer kullanıcı yoksa login'e at
if (!$user) {
    header("Location: /sign-in");
    exit;
}

// Veritabanı bağlantısı
$db = getDbConnection();

// Temsilci ID (users.id)
$repId = $user->id;

// 1. ÖZET BİLGİLER
// Toplam Hak Ediş (Unpaid Commissions)
// Toplam Ödenen (Paid Commissions)
// Site Sayısı

// Site Sayısı
$stmtSites = $db->prepare("
    SELECT COUNT(*) 
    FROM representative_site_assignments 
    WHERE representative_id = ?
");
$stmtSites->execute([$repId]);
$totalSites = $stmtSites->fetchColumn();

// Ödeme Hesaplamaları
// Tüm atamaları çekelim, her biri için billing tablosundan durumları kontrol edeceğiz
$stmtAssignments = $db->prepare("
    SELECT rsa.*, s.site_adi, rsa.commission_rate
    FROM representative_site_assignments rsa
    JOIN siteler s ON rsa.site_id = s.id
    LEFT JOIN user_site_pricing usp ON s.id = usp.site_id
    WHERE rsa.representative_id = ?
");
$stmtAssignments->execute([$repId]);
$assignments = $stmtAssignments->fetchAll(PDO::FETCH_ASSOC);

$totalPaid = 0;
$totalPayable = 0; // Site ödemiş ama temsilciye ödenmemiş (Hak ediş)
$totalPotential = 0; // Gelecek/Site henüz ödememiş

// Ödeme geçmişi (Temsilciye yapılan ödemeler)
$stmtPayments = $db->prepare("
    SELECT rb.*, s.site_adi 
    FROM representative_billing rb
    LEFT JOIN siteler s ON rb.site_id = s.id
    WHERE rb.representative_id = ? AND rb.paid = 1
    ORDER BY rb.paid_at DESC
");
$stmtPayments->execute([$repId]);
$paymentHistory = $stmtPayments->fetchAll(PDO::FETCH_ASSOC);

// Toplam Ödenen'i geçmişten hesapla
foreach ($paymentHistory as $pay) {
    $totalPaid += (float)$pay['amount'];
}

// Hak ediş hesaplama (Biraz karmaşık olabilir, basitleştirilmiş mantık:
// Site Billing tablosunda site ödemiş (is_paid=1) ama Temsilci Billing'de bu aya ait kayıt yoksa HAK EDİŞTİR.
// Ancak mevcut yapıda 'billing_mark' ile site ödemesi takip ediliyor.
// Temsilci ödemesi manuel yapılıyor. 
// Şimdilik API'deki mantığı buraya taşıyalım:
// Site Ödemiş (usb.is_paid=1) VE Temsilciye Ödenmemiş -> Payable
// Site Ödememiş -> Potential

// Tüm siteler için son 12 ayın durumunu rep_manage ile aynı mantıkta hesapla
$commissionDetails = [];
if (!empty($assignments)) {
    $Pricing = new \Model\UserSitePricingModel();
    $Billing = new \Model\UserSiteBillingModel();
    $Daireler = new \Model\DairelerModel();
    $Siteler = new \Model\SitelerModel();
    foreach ($assignments as $assign) {
        $siteId = (int)$assign['site_id'];
        $site = $Siteler->SiteBilgileri($siteId);
        if (!$site) { continue; }
        $ownerId = (int)($site->user_id ?? 0);
        $pricing = null;
        try { $pricing = $Pricing->getByUserAndSite($ownerId, $siteId); } catch (\Throwable $e) { $pricing = null; }
        $unitFee = $pricing ? (float)$pricing->unit_fee : 0.0;
        $startDate = $pricing ? ($pricing->start_date ?: null) : null;
        $aptCount = 0;
        try { $aptCount = (int)$Daireler->countBySiteId($siteId); } catch (\Throwable $e) { $aptCount = 0; }
        $monthlyTotal = $unitFee * $aptCount;
        $rate = (float)($assign['commission_rate'] ?? 0);
        $sitePaidCount = 0;
        $siteTotalEarned = 0.0;
        if ($startDate) {
            try {
                $start = new \DateTime($startDate);
                $cur = new \DateTime();
                $periods = new \DatePeriod($start, new \DateInterval('P1M'), $cur->modify('first day of next month'));
                $cnt = 0;
                foreach ($periods as $p) {
                    if ($cnt >= 12) break;
                    $ym = $p->format('Y-m');
                    $sitePaid = false;
                    try { $sitePaid = $Billing->isPaid($ownerId, $siteId, $ym); } catch (\Throwable $e) { $sitePaid = false; }
                    if ($sitePaid) {
                        $sitePaidCount++;
                        $siteTotalEarned += round(($monthlyTotal * ($rate/100.0)), 2);
                    }
                    $cnt++;
                }
            } catch (\Throwable $e) {}
        }
        // Bu site için temsilciye ne kadar ödenmiş?
        $paidToRepForSite = 0.0;
        foreach ($paymentHistory as $ph) {
            if ((int)$ph['site_id'] === $siteId) {
                $paidToRepForSite += (float)$ph['amount'];
            }
        }
        $balance = $siteTotalEarned - $paidToRepForSite;
        if ($balance < 0) { $balance = 0; }
        $totalPayable += $balance;
        $commissionDetails[] = [
            'site_name' => $assign['site_adi'] ?? '',
            'rate' => $rate,
            'paid_months' => $sitePaidCount,
            'total_earnings' => $siteTotalEarned,
            'received' => $paidToRepForSite,
            'balance' => $balance
        ];
    }
}

?>

<style>
    .rep-card {
        transition: transform 0.2s;
    }
    .rep-card:hover {
        transform: translateY(-5px);
    }
</style>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Temsilci Paneli</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item">Ana Sayfa</li>
            <li class="breadcrumb-item">Panel</li>
        </ul>
    </div>
</div>

<div class="main-content">
    <!-- Özet Kartlar -->
    <div class="row">
        <div class="col-xxl-4 col-md-4">
            <div class="card rep-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Toplam Alacak (Hak Edilen)</h6>
                            <h3 class="mb-0 text-primary fw-bold"><?= number_format($totalPayable, 2) ?> ₺</h3>
                        </div>
                        <div class="avatar-text avatar-lg bg-primary-subtle text-primary rounded-3">
                            <i class="feather-trending-up"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-4 col-md-4">
            <div class="card rep-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Toplam Tahsil Edilen</h6>
                            <h3 class="mb-0 text-success fw-bold"><?= number_format($totalPaid, 2) ?> ₺</h3>
                        </div>
                        <div class="avatar-text avatar-lg bg-success-subtle text-success rounded-3">
                            <i class="feather-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-4 col-md-4">
            <div class="card rep-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Atanan Site Sayısı</h6>
                            <h3 class="mb-0 text-warning fw-bold"><?= $totalSites ?></h3>
                        </div>
                        <div class="avatar-text avatar-lg bg-warning-subtle text-warning rounded-3">
                            <i class="feather-home"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Site Bazlı Durum -->
        <div class="col-lg-8">
            <div class="card stretch stretch-full">
                <div class="card-header">
                    <h5 class="card-title">Site Bazlı Komisyon Durumu</h5>
                </div>
                <div class="card-body custom-card-action p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Site Adı</th>
                                    <th class="text-end">Komisyon Oranı</th>
                                    <th class="text-center">Ödenen Ay (Site)</th>
                                    <th class="text-end">Top. Hak Ediş</th>
                                    <th class="text-end">Tahsil Edilen</th>
                                    <th class="text-end">Kalan Alacak</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($commissionDetails)): ?>
                                    <tr><td colspan="6" class="text-center p-4 text-muted">Henüz atanmış site bulunmuyor.</td></tr>
                                <?php else: ?>
                                    <?php foreach($commissionDetails as $cd): ?>
                                    <tr>
                                        <td class="fw-bold"><?= htmlspecialchars($cd['site_name']) ?></td>
                                        <td class="text-end"><?= number_format($cd['rate'], 2) ?>%</td>
                                        <td class="text-center"><span class="badge bg-soft-info text-info"><?= $cd['paid_months'] ?> Ay</span></td>
                                        <td class="text-end fw-bold"><?= number_format($cd['total_earnings'], 2) ?> ₺</td>
                                        <td class="text-end text-success"><?= number_format($cd['received'], 2) ?> ₺</td>
                                        <td class="text-end text-danger"><?= number_format($cd['balance'], 2) ?> ₺</td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Son Ödemeler -->
        <div class="col-lg-4">
            <div class="card stretch stretch-full">
                <div class="card-header">
                    <h5 class="card-title">Son Ödeme Hareketleri</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Tarih</th>
                                    <th>Site</th>
                                    <th class="text-end">Tutar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($paymentHistory)): ?>
                                    <tr><td colspan="3" class="text-center p-3 text-muted">Kayıt yok.</td></tr>
                                <?php else: ?>
                                    <?php foreach(array_slice($paymentHistory, 0, 10) as $hist): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-dark"><?= date('d.m.Y', strtotime($hist['paid_at'] ?? $hist['created_at'])) ?></span>
                                                <small class="text-muted time"><?= date('H:i', strtotime($hist['paid_at'] ?? $hist['created_at'])) ?></small>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($hist['site_adi'] ?? 'Genel') ?></td>
                                        <td class="text-end fw-bold text-success">+<?= number_format($hist['amount'], 2) ?> ₺</td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
