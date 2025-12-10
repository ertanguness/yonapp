<?php

use App\Helper\Helper;
use Model\BorclandirmaModel;
use Model\FinansalRaporModel;
use Model\UserDashBoardModel;
use Model\KasaModel;
use App\Helper\Security;
use App\Services\Gate;

/**Site sakini bu sayfayı görmeyecek */
Security::ensureNotResident();

$BorclandirmaModel = new BorclandirmaModel();
$FinansalRaporModel = new FinansalRaporModel();
$UserDashBoardModel = new UserDashBoardModel();

$site_id = $_SESSION['site_id'];
$activeFirmId = (int) ($_SESSION['firm_id'] ?? ($_SESSION['user']->firm_id ?? 0));

$toplam_aidat_geliri = $FinansalRaporModel->getToplamAidatGeliri($site_id);
$geciken_tahsilat_sayisi = $FinansalRaporModel->getGecikenTahsilatSayisi($site_id);
$toplam_gider = $FinansalRaporModel->getToplamGiderler($site_id);
$geciken_odeme_tutari = $FinansalRaporModel->getGecikenOdemeTutar($site_id);

$KasaModel = new KasaModel();
$curStart = date('Y-m-01');
$curEnd = date('Y-m-t');
$prevStart = date('Y-m-01', strtotime('-1 month'));
$prevEnd = date('Y-m-t', strtotime('-1 month'));

$aidatCur = 0.0;
$aidatPrev = 0.0;
$sumCur = $FinansalRaporModel->getBorclandirmaSummaryByDateRange($site_id, $curStart, $curEnd);
foreach ($sumCur as $row) { if (stripos($row->borc_adi ?? '', 'Aidat') !== false || stripos($row->aciklama ?? '', 'Aidat') !== false) { $aidatCur += (float)($row->toplam_tahsilat ?? 0); } }
$sumPrev = $FinansalRaporModel->getBorclandirmaSummaryByDateRange($site_id, $prevStart, $prevEnd);
foreach ($sumPrev as $row) { if (stripos($row->borc_adi ?? '', 'Aidat') !== false || stripos($row->aciklama ?? '', 'Aidat') !== false) { $aidatPrev += (float)($row->toplam_tahsilat ?? 0); } }

$overdueCur = $FinansalRaporModel->getGecikenOdemeTutarByDate($site_id, $curEnd);
$overduePrev = $FinansalRaporModel->getGecikenOdemeTutarByDate($site_id, $prevEnd);

$kasaId = $KasaModel->varsayilanKasa()->id ?? 0;
$giderCur = 0.0; $giderPrev = 0.0;
if ($kasaId) {
    $gdrCur = $KasaModel->KasaFinansalDurumByDateRange((int)$kasaId, $curStart, $curEnd, 'Gider');
    $gdrPrev = $KasaModel->KasaFinansalDurumByDateRange((int)$kasaId, $prevStart, $prevEnd, 'Gider');
    $giderCur = (float)($gdrCur->toplam_gider ?? 0);
    $giderPrev = (float)($gdrPrev->toplam_gider ?? 0);
}

$lateCountCur = $FinansalRaporModel->getGecikenTahsilatSayisiByDate($site_id, $curEnd);
$lateCountPrev = $FinansalRaporModel->getGecikenTahsilatSayisiByDate($site_id, $prevEnd);

$trend = function($cur, $prev) {
    $pct = 0.0; $icon = 'feather-minus'; $text = 'Sabit';
    if ($prev > 0) { $pct = (($cur - $prev) / $prev) * 100; }
    else { if ($cur > 0) { $pct = 100.0; } }
    if ($pct > 0.0001) { $icon = 'feather-trending-up'; $text = '+' . number_format(abs($pct), 1) . '% artış'; }
    elseif ($pct < -0.0001) { $icon = 'feather-trending-down'; $text = '-' . number_format(abs($pct), 1) . '% azalma'; }
    return ['pct' => $pct, 'icon' => $icon, 'text' => $text];
};

$aidat_tr = $trend($aidatCur, $aidatPrev);
$overdue_tr = $trend($overdueCur, $overduePrev);
$gider_tr = $trend($giderCur, $giderPrev);
$late_tr = $trend($lateCountCur, $lateCountPrev);

$documentRootPath = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : '';
$projectRootPath = realpath(PROJECT_ROOT);

$calendarBasePath = '';

if ($documentRootPath && $projectRootPath) {
    $documentRootPath = str_replace('\\', '/', $documentRootPath);
    $projectRootPath = str_replace('\\', '/', $projectRootPath);

    if (strpos($projectRootPath, $documentRootPath) === 0) {
        $relativePath = substr($projectRootPath, strlen($documentRootPath));
        $relativePath = '/' . ltrim($relativePath, '/');
        if ($relativePath === '/') {
            $relativePath = '';
        }
        $calendarBasePath = $relativePath;
    }
}



ob_start();
?>
<!-- <script>
    window.calendarConfig = <?php //echo json_encode($calendarConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
</script> -->

<?php
$script = ob_get_clean();

?>

<div class="main-content mb-5" id="dashboard-row">
    <style>
        .flex-fill {
            transition: background-color 0.3s ease-in-out;
            display: inline-block;
            align-items: center;
            text-align: center;
            margin-bottom: 4px;
        }

        .flex-fill:hover {
            background-color: #f8f9fa;
        }

        .flex-fill i {
            font-size: 24px;
        }

       

        /* Sidebar Stilleri */
        .content-sidebar {
            background: #ffffff;
            border-right: 1px solid #e9ecef;
            min-width: 280px;
            max-width: 280px;
        }

        .content-sidebar-header {
            background: #ffffff;
            border-bottom: 1px solid #e9ecef;
            padding: 1rem;
        }

        .content-sidebar-header h4 {
            color: #1976d2;
            font-weight: 700;
            font-size: 1rem;
        }

        .content-sidebar-body {
            padding: 1rem;
            background: #fafbfc;
        }

        
        /* Desktop - Hızlı İşlemler Card */
        .quick-actions-card {
            display: block;
        }

      
        
    </style>

    <?php
    $defaultOrder = [
        'quick-actions-card',
        'row-cards',
        'payment-records-chart-card',
        'leads-inquiry-channel-card',
        'borc-listele',
        'requests-card',
    ];
    $userId = $_SESSION['user']->id ?? 0;
    $layout = [];
    $available = array_map(function($p){ return basename($p, '.php'); }, glob(__DIR__ . '/cards/*.php'));
    // Calendar card hariç tutuluyor
    $available = array_values(array_filter($available, function($k){ return $k !== 'calendar-card'; }));
    if ($userId) {
        $layout = $UserDashBoardModel->getUserDashboardLayout((int)$userId);
    }
    $orderCol1 = [];
    $orderCol2 = [];
    if (!empty($layout)) {
        foreach ($layout as $it) {
            $k = $it['widget_key'];
            if (in_array($k, $available, true)) {
                if ((int)$it['column'] === 2) {
                    $orderCol2[] = $k;
                } else {
                    $orderCol1[] = $k;
                }
            }
        }
        foreach ($defaultOrder as $k) {
            if (!in_array($k, $orderCol1, true) && !in_array($k, $orderCol2, true)) {
                $orderCol1[] = $k;
            }
        }
        foreach ($available as $k) {
            if (!in_array($k, $orderCol1, true) && !in_array($k, $orderCol2, true)) {
                $orderCol1[] = $k;
            }
        }
    } else {
        // Eğer veritabanında sıralama yoksa, defaultOrder'ın yarısı 1. kolona, yarısı 2. kolona yerleşsin
        $half = (int) ceil(count($defaultOrder) / 2);
        $orderCol1 = array_slice($defaultOrder, 0, $half);
        $orderCol2 = array_slice($defaultOrder, $half);
    }
    ?>
    <div class="row mb-5">
        <div id="dashboard-col-1" class="col-xxl-6 col-md-6">
            <?php foreach ($orderCol1 as $cardKey): $file = __DIR__ . '/cards/' . $cardKey . '.php'; if (file_exists($file)) { include $file; } endforeach; ?>
        </div>
        <div id="dashboard-col-2" class="col-xxl-6 col-md-6">
            <?php foreach ($orderCol2 as $cardKey): $file = __DIR__ . '/cards/' . $cardKey . '.php'; if (file_exists($file)) { include $file; } endforeach; ?>
        </div>
    </div>
    <!-- [Inquiry Channel] end -->
</div>


<div class="modal fade-scale" id="composeMail" tabindex="-1" aria-labelledby="composeMail" aria-hidden="true" data-bs-dismiss="ou">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">

        </div>
    </div>
</div>




<div class="modal fade" id="SendMessage" tabindex="-1" role="dialog" aria-labelledby="modalTitleId"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content sms-gonder-modal">
            <!-- İçerik AJAX ile yüklenecek -->
        </div>
    </div>
</div>



<!-- Gelir Gider Modal -->
<div class="modal fade" id="gelirGiderModal" tabindex="-1" aria-labelledby="gelirGiderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- İçerik AJAX ile yüklenecek -->
        </div>
    </div>
</div>


<?php include './partials/calender-scripts.php' ?>
<!-- list.php'nin en altına ekle -->
<script src="/pages/email-sms/js/sms.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
    let url ="pages/home/api.php";
    const col1 = document.getElementById('dashboard-col-1');
    const col2 = document.getElementById('dashboard-col-2');
    const opts = { group: 'dashboard', animation: 150, handle: ".drag-handle",
        onEnd: function () {
            let items = [];
            let fd = new FormData();
            fd.append("action", "save_dashboard_layout");
            Array.from(col1.querySelectorAll('.card-wrapper')).forEach(function(el, i){
                items.push({ widget_key: el.getAttribute('data-card'), column: 1, position: i });
            });
            Array.from(col2.querySelectorAll('.card-wrapper')).forEach(function(el, i){
                items.push({ widget_key: el.getAttribute('data-card'), column: 2, position: i });
            });
            fd.append("items", JSON.stringify(items));
            fetch(url, { method: "POST", body: fd })
            .then(r => r.json()).then(d => { console.log("Layout kaydedildi:", d); });
        }
    };
    new Sortable(col1, opts);
    new Sortable(col2, opts);
</script>




<script>
    // Kişi telefon numarasını JavaScript'e aktar
    window.kisiTelefonNumarasi = '<?php echo htmlspecialchars($telefonNumarasi ?? '', ENT_QUOTES); ?>';

    // Modal ve Offcanvas Z-Index Yönetimi
    (function() {
        const BASE = 1050,
            STEP = 10;
        
        document.addEventListener('show.bs.modal', function(e) {
            const openCount = document.querySelectorAll('.modal.show').length;
            const modalZ = BASE + (STEP * (openCount + 1)) + 5;
            e.target.style.zIndex = modalZ;
            setTimeout(() => {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                const bd = backdrops[backdrops.length - 1];
                if (bd) {
                    bd.style.zIndex = modalZ - 5;
                    bd.classList.add('stacked-backdrop');
                }
                document.body.classList.add('modal-open');
            }, 10);
        });

        document.addEventListener('show.bs.offcanvas', function(e) {
            const offcanvasZ = BASE + 1050;
            e.target.style.zIndex = offcanvasZ;
            setTimeout(() => {
                const backdrops = document.querySelectorAll('.offcanvas-backdrop');
                const bd = backdrops[backdrops.length - 1];
                if (bd) {
                    bd.style.zIndex = offcanvasZ - 5;
                }
            }, 10);
        });

        document.addEventListener('hidden.bs.modal', function() {
            if (document.querySelectorAll('.modal.show').length === 0) {
                document.body.classList.remove('modal-open');
            }
        });
    })();



</script>
