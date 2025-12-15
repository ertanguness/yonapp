<?php

use Model\SitelerModel;
use Model\UserModel;
use App\Helper\Helper;

$db = \getDbConnection();
$sitesModel = new SitelerModel();

// 1. İstatistikler
// --------------------------------------------------------------------------------

// A. Kilitli Site Sayısı ve Listesi
$sqlLocked = "SELECT s.id, s.site_adi, l.reason, l.created_at as lock_date
              FROM siteler s
              JOIN user_site_locks l ON s.id = l.site_id
              WHERE s.silinme_tarihi IS NULL
              AND l.id = (SELECT MAX(id) FROM user_site_locks WHERE site_id = s.id)
              AND l.locked = 1";
$lockedSites = $db->query($sqlLocked)->fetchAll(PDO::FETCH_OBJ);
$lockedCount = count($lockedSites);

// B. Toplam Daire Sayısı
$totalApartments = $db->query("SELECT COUNT(*) FROM daireler WHERE silinme_tarihi IS NULL")->fetchColumn();

// C. Finansal Veriler
// C1. Toplam Tahsil Edilen (Sitelerden Bize Gelen)
$totalPaid = $db->query("SELECT SUM(amount) FROM user_site_billing WHERE paid = 1")->fetchColumn() ?: 0;

// C2. Temsilcilere Ödenen Toplam Miktar
$totalPaidToReps = $db->query("SELECT SUM(amount) FROM representative_billing WHERE paid = 1")->fetchColumn() ?: 0;

// C3. Bekleyen Temsilci Ödemeleri (Siteler ödemiş ama biz temsilciye ödememişiz)
// Mantık: (Ödenen Site Faturaları * Komisyon Oranı) - (Temsilciye Ödenenler)
$sqlEarned = "SELECT SUM(b.amount * (COALESCE(rsa.commission_rate, 25.00) / 100))
              FROM user_site_billing b
              JOIN representative_site_assignments rsa ON b.site_id = rsa.site_id
              WHERE b.paid = 1";
$totalEarnedByReps = $db->query($sqlEarned)->fetchColumn() ?: 0;
$pendingRepPayments = $totalEarnedByReps - $totalPaidToReps;
if ($pendingRepPayments < 0) $pendingRepPayments = 0;

// C4. Zamanı Gelmiş (Vadesi Geçmiş/Gelmş) Alacaklar
// Bunun için tüm sitelerin fiyatlandırma planlarını ve ödeme durumlarını kontrol etmeliyiz.
$dueCount = 0;
$dueAmount = 0.0;

// Fiyatlandırma bilgisi olan siteleri ve daire sayılarını çek
$pricingStmt = $db->query("
    SELECT p.*, 
           (SELECT COUNT(*) FROM daireler d WHERE d.site_id = p.site_id AND d.silinme_tarihi IS NULL) as apt_count 
    FROM user_site_pricing p
    JOIN siteler s ON p.site_id = s.id
    WHERE s.silinme_tarihi IS NULL AND s.aktif_mi = 1
");
$pricings = $pricingStmt->fetchAll(PDO::FETCH_OBJ);

// Ödenmiş faturaları çek (site_id + period anahtarı ile)
$bills = $db->query("SELECT site_id, period FROM user_site_billing WHERE paid = 1")->fetchAll(PDO::FETCH_OBJ);
$paidMap = [];
foreach($bills as $b) {
    $paidMap[$b->site_id . '_' . $b->period] = true;
}

$currentDate = new DateTime();
// Ayın ilk günü baz alınarak döngü kurulur
$currentDate->modify('first day of next month'); 

foreach ($pricings as $p) {
    if (!$p->start_date || $p->unit_fee <= 0) continue;
    
    try {
        $start = new DateTime($p->start_date);
        // Başlangıç tarihi bugünden büyükse atla
        if ($start > new DateTime()) continue;

        $interval = new DateInterval('P1M');
        $periodRange = new DatePeriod($start, $interval, $currentDate);

        foreach ($periodRange as $dt) {
            $ym = $dt->format('Y-m');
            // Eğer ödenmemişse
            if (!isset($paidMap[$p->site_id . '_' . $ym])) {
                $dueCount++;
                $monthlyAmount = ($p->unit_fee * ($p->apt_count ?? 0));
                $dueAmount += $monthlyAmount;
            }
        }
    } catch (Exception $e) {
        // Tarih hatası olursa yoksay
    }
}

// 2. Diğer Temel İstatistikler (Mevcut olanlar)
$allSites = $sitesModel->getAllWithOwners();
$totalSites = count($allSites);

// Aktif Site Sayısı
$activeSites = 0;
foreach($allSites as $s) {
    if ($s->aktif_mi == 1) $activeSites++;
}

// Toplam Temsilci Sayısı
$ownerId = (int)($_SESSION['owner_id'] ?? 0);
$stmtRep = $db->prepare("
    SELECT COUNT(u.id) as total
    FROM users u
    LEFT JOIN user_roles r ON u.roles = r.id
    WHERE r.role_name = 'Temsilci' AND (r.owner_id = :owner OR r.owner_id IS NULL)
");
$stmtRep->execute(['owner' => $ownerId]);
$totalReps = $stmtRep->fetchColumn();

// Son Eklenen 5 Site
$stmtRecent = $db->query("SELECT * FROM siteler WHERE silinme_tarihi IS NULL ORDER BY kayit_tarihi DESC LIMIT 5");
$recentSites = $stmtRecent->fetchAll(PDO::FETCH_OBJ);

?>
<div class="container-xl">
    <?php
        $title = "Anasayfa";
        $text = "Süper Admin Paneli Genel Bakış";
        require_once 'pages/components/alert.php'
    ?>

    <!-- Üst İstatistik Kartları -->
    <div class="row row-deck row-cards mb-4">
        <!-- Site Sayıları -->
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="subheader">Toplam Site</div>
                        <div class="ms-auto">
                            <span class="badge bg-success"><?php echo $activeSites; ?> Aktif</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline">
                        <div class="h1 mb-0 me-2"><?php echo $totalSites; ?></div>
                    </div>
                    <div class="position-absolute bottom-0 end-0 mb-3 me-3 text-muted">
                        <i class="feather-grid" style="font-size: 2.5rem; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Daire Sayısı -->
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="subheader">Toplam Daire</div>
                    </div>
                    <div class="d-flex align-items-baseline">
                        <div class="h1 mb-0 me-2"><?php echo number_format($totalApartments); ?></div>
                    </div>
                    <div class="position-absolute bottom-0 end-0 mb-3 me-3 text-muted">
                        <i class="feather-home" style="font-size: 2.5rem; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Temsilci Sayısı -->
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="subheader">Toplam Temsilci</div>
                    </div>
                    <div class="d-flex align-items-baseline">
                        <div class="h1 mb-0 me-2"><?php echo $totalReps; ?></div>
                    </div>
                    <div class="position-absolute bottom-0 end-0 mb-3 me-3 text-muted">
                        <i class="feather-users" style="font-size: 2.5rem; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Kilitli Site Sayısı -->
        <div class="col-sm-6 col-lg-3">
            <div class="card <?php echo $lockedCount > 0 ? 'bg-danger-lt' : ''; ?>">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="subheader">Kilitli Site</div>
                        <?php if($lockedCount > 0): ?>
                        <div class="ms-auto">
                            <span class="badge bg-danger">Müdahale Gerekli</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex align-items-baseline">
                        <div class="h1 mb-0 me-2"><?php echo $lockedCount; ?></div>
                    </div>
                    <div class="position-absolute bottom-0 end-0 mb-3 me-3 text-muted">
                        <i class="feather-lock" style="font-size: 2.5rem; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Finansal İstatistikler -->
    <h3 class="card-title mb-3">Finansal Durum</h3>
    <div class="row row-deck row-cards mb-4">
        <!-- Zamanı Gelmiş Ödemeler -->
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="subheader text-warning">Bekleyen Tahsilat (Site)</div>
                    </div>
                    <div class="h2 mb-1"><?php echo number_format($dueAmount, 2, ',', '.'); ?> ₺</div>
                    <div class="text-muted small"><?php echo $dueCount; ?> adet ödenmemiş dönem</div>
                    <div class="position-absolute bottom-0 end-0 mb-3 me-3 text-warning">
                        <i class="feather-clock" style="font-size: 2.5rem; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Toplam Tahsil Edilen -->
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="subheader text-success">Toplam Tahsilat (Kasa)</div>
                    </div>
                    <div class="h2 mb-1"><?php echo number_format($totalPaid, 2, ',', '.'); ?> ₺</div>
                    <div class="text-muted small">Sitelerden alınan toplam</div>
                    <div class="position-absolute bottom-0 end-0 mb-3 me-3 text-success">
                        <i class="feather-dollar-sign" style="font-size: 2.5rem; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Temsilcilere Ödenen -->
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="subheader text-primary">Temsilci Hakediş (Ödenen)</div>
                    </div>
                    <div class="h2 mb-1"><?php echo number_format($totalPaidToReps, 2, ',', '.'); ?> ₺</div>
                    <div class="text-muted small">Temsilcilere ödenmiş tutar</div>
                    <div class="position-absolute bottom-0 end-0 mb-3 me-3 text-primary">
                        <i class="feather-check-circle" style="font-size: 2.5rem; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Bekleyen Temsilci Ödemesi -->
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="subheader text-info">Bekleyen Temsilci Ödemesi</div>
                    </div>
                    <div class="h2 mb-1"><?php echo number_format($pendingRepPayments, 2, ',', '.'); ?> ₺</div>
                    <div class="text-muted small">Tahsil edilmiş ama dağıtılmamış</div>
                    <div class="position-absolute bottom-0 end-0 mb-3 me-3 text-info">
                        <i class="feather-alert-circle" style="font-size: 2.5rem; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-deck row-cards">
        <!-- Kilitli Siteler Listesi -->
        <?php if($lockedCount > 0): ?>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h3 class="card-title text-white">Kilitli Siteler</h3>
                </div>
                <div class="list-group list-group-flush list-group-hoverable">
                    <?php foreach($lockedSites as $ls): ?>
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col-auto"><span class="badge bg-white text-danger"><i class="feather-lock"></i></span></div>
                            <div class="col text-truncate">
                                <a href="#" class="text-reset d-block"><?php echo htmlspecialchars($ls->site_adi); ?></a>
                                <div class="d-block text-muted text-truncate mt-n1">
                                    <?php echo htmlspecialchars($ls->reason ?: 'Sebep belirtilmemiş'); ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="text-muted small"><?php echo date('d.m.Y', strtotime($ls->lock_date)); ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Son Eklenen Siteler -->
        <div class="col-lg-<?php echo $lockedCount > 0 ? '8' : '12'; ?>">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Son Eklenen Siteler</h3>
                </div>
                <div class="table-responsive">
                    <table class="table card-table table-vcenter text-nowrap datatable">
                        <thead>
                            <tr>
                                <th>Site Adı</th>
                                <th>İl / İlçe</th>
                                <th>Telefon</th>
                                <th>Kayıt Tarihi</th>
                                <th>Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recentSites as $site): ?>
                            <tr>
                                <td><a href="#" class="text-reset"><?php echo htmlspecialchars($site->site_adi); ?></a></td>
                                <td>
                                    <?php echo htmlspecialchars($site->il . ' / ' . $site->ilce); ?>
                                </td>
                                <td><?php echo htmlspecialchars($site->telefon ?: '-'); ?></td>
                                <td><?php echo date('d.m.Y', strtotime($site->kayit_tarihi)); ?></td>
                                <td>
                                    <?php if($site->aktif_mi): ?>
                                        <span class="badge bg-success me-1"></span> Aktif
                                    <?php else: ?>
                                        <span class="badge bg-danger me-1"></span> Pasif
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($recentSites)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Henüz kayıtlı site yok.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
