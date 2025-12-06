<?php

use App\Helper\Helper;
use Model\BorclandirmaModel;
use Model\FinansalRaporModel;
use Model\UserDashBoardModel;
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

$calendarTypes = [
    [
        'id' => 'genel',
        'name' => 'Genel',
        'color' => '#ffffff',
        'bgColor' => '#1abc9c',
        'borderColor' => '#1abc9c',
    ],
    [
        'id' => 'toplanti',
        'name' => 'Toplantı',
        'color' => '#ffffff',
        'bgColor' => '#3498db',
        'borderColor' => '#3498db',
    ],
    [
        'id' => 'dogum',
        'name' => 'Doğum Günü',
        'color' => '#ffffff',
        'bgColor' => '#e91e63',
        'borderColor' => '#e91e63',
    ],
    [
        'id' => 'odeme',
        'name' => 'Ödeme',
        'color' => '#ffffff',
        'bgColor' => '#00bcd4',
        'borderColor' => '#00bcd4',
    ],
    [
        'id' => 'bakim',
        'name' => 'Bakım',
        'color' => '#ffffff',
        'bgColor' => '#ff9800',
        'borderColor' => '#ff9800',
    ],
    [
        'id' => 'duyuru',
        'name' => 'Duyuru',
        'color' => '#ffffff',
        'bgColor' => '#673ab7',
        'borderColor' => '#673ab7',
    ],
    [
        'id' => 'sosyal',
        'name' => 'Sosyal Etkinlik',
        'color' => '#ffffff',
        'bgColor' => '#4caf50',
        'borderColor' => '#4caf50',
    ],
];

$calendarConfig = [
    'basePath' => $calendarBasePath,
    'types' => $calendarTypes,
    'endpoints' => [
        'events' => rtrim($calendarBasePath, '/') . '/api/calendar-events.php',
    ],
    'siteId' => (int) ($site_id ?? 0),
    'firmId' => $activeFirmId,
    'csrfToken' => Security::csrf(),
    'timezone' => date_default_timezone_get(),
];

ob_start();
?>
<script>
    window.calendarConfig = <?php echo json_encode($calendarConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
</script>
<!-- Takvim türleri verisi - JavaScript için -->
<script id="calendar-types-data" type="application/json">
<?php echo json_encode($calendarTypes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
</script>
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

        .calendar-filter-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .calendar-filter-entry {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .calendar-filter-entry .form-check-input {
            cursor: pointer;
            margin-top: 0;
            accent-color: var(--calendar-color, #0d6efd);
            border-color: var(--calendar-color, #0d6efd);
            background-color: transparent;
            transition: background-color 0.2s ease-in-out, border-color 0.2s ease-in-out, box-shadow 0.2s;
        }

        .calendar-filter-entry .form-check-input:checked {
            background-color: var(--calendar-color, #0d6efd);
            border-color: var(--calendar-color, #0d6efd);
        }

        .calendar-filter-entry .form-check-input:focus {
            box-shadow: 0 0 0 0.25rem rgba(30, 136, 229, 0.15);
        }

        .calendar-event-modal {
            max-width: 620px;
        }

        .calendar-event-modal .modal-content {
            border: none;
            border-radius: 18px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.15);
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        }

        .calendar-event-modal .modal-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            padding: 1.5rem 1.5rem 1rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #f0f2f5 100%);
            border-radius: 18px 18px 0 0;
        }

        .calendar-event-modal .modal-header .modal-title {
            color: #1976d2;
            font-weight: 700;
        }

        .calendar-event-modal .modal-header .text-muted {
            color: #6c757d !important;
        }

        .calendar-event-modal .modal-body {
            padding: 1.5rem 1.5rem 1rem;
            background: #ffffff;
        }

        .calendar-event-modal .modal-body .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .calendar-event-modal .modal-footer {
            border-top: 1px solid rgba(0, 0, 0, 0.06);
            padding: 1rem 1.5rem 1.5rem;
            background: #f8f9fa;
            border-radius: 0 0 18px 18px;
        }

        .calendar-event-modal .form-control {
            border-radius: 10px;
            border: 1px solid #e9ecef;
            background-color: #ffffff;
            transition: all 0.3s ease;
        }

        .calendar-event-modal .form-control:focus {
            border-color: #1976d2;
            box-shadow: 0 0 0 0.2rem rgba(25, 118, 210, 0.15);
            background-color: #ffffff;
        }

        .calendar-event-modal .form-select {
            border-radius: 10px;
            border: 1px solid #e9ecef;
            background-color: #ffffff;
            transition: all 0.3s ease;
        }

        .calendar-event-modal .form-select:focus {
            border-color: #1976d2;
            box-shadow: 0 0 0 0.2rem rgba(25, 118, 210, 0.15);
            background-color: #ffffff;
        }

        .calendar-event-modal .form-check-input {
            cursor: pointer;
            border-color: #dee2e6;
            width: 1.25em;
            height: 1.25em;
        }

        .calendar-event-modal .form-check-input:checked {
            background-color: #1976d2;
            border-color: #1976d2;
        }

        .calendar-event-modal .btn-primary {
            background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
            border: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .calendar-event-modal .btn-primary:hover {
            background: linear-gradient(135deg, #1565c0 0%, #1456b0 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(25, 118, 210, 0.3);
        }

        .calendar-event-modal .btn-outline-secondary {
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            color: #495057;
            border-color: #dee2e6;
        }

        .calendar-event-modal .btn-outline-secondary:hover {
            background-color: #f8f9fa;
            border-color: #adb5bd;
            color: #495057;
        }

        .calendar-event-modal .btn-outline-danger {
            border-radius: 10px;
            font-weight: 600;
            color: #dc3545;
            border-color: #dc3545;
            transition: all 0.3s ease;
        }

        .calendar-event-modal .btn-outline-danger:hover {
            background-color: #dc3545;
            color: white;
            border-color: #dc3545;
        }

        /* Takvim Container Stilleri */
        .apps-calendar {
            background: #ffffff;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .apps-calendar .content-area,
        .apps-calendar .content-area-body {
            height: auto !important;
            max-height: none !important;
            overflow: visible !important;
            background: #ffffff;
        }

        .apps-calendar .content-area-body #tui-calendar-init {
            min-height: 680px;
            background: #ffffff;
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

        /* Gün Başlıkları */
        #tui-calendar-init .tui-full-calendar-month-dayname-item {
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            width: 100%;
            padding: 8px 0;
            box-sizing: border-box;
            margin: 0 auto;
            background: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
            font-weight: 700;
            color: #495057;
        }

        #tui-calendar-init .tui-full-calendar-month-dayname {
            text-align: center;
            font-weight: 700;
        }

        #tui-calendar-init .tui-full-calendar-dayname-container,
        #tui-calendar-init .tui-full-calendar-dayname {
            overflow: hidden !important;
            text-align: center;
            background: #f8f9fa;
        }

        #tui-calendar-init .tui-full-calendar-dayname-item {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 8px 0;
            box-sizing: border-box;
            margin: 0 auto;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            font-weight: 600;
            color: #495057;
            font-size: 0.9rem;
        }

        /* Takvim hücrelerinin yüksekliği - 5 satır gösterim için */
        #tui-calendar-init .tui-full-calendar-day-grid-item {
            min-height: 100px;
            max-height: 100px;
            overflow: auto;
        }

        #tui-calendar-init .tui-full-calendar-day-grid-date {
            min-height: 100px;
        }

        .calendar-modal-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: nowrap;
            background: #fafbfc;
            border-top: 1px solid #e9ecef;
        }

        .calendar-modal-footer .action-buttons {
            display: flex;
            gap: 0.5rem;
            margin-left: auto;
        }

        .calendar-modal-footer .action-buttons .btn {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            border-radius: 8px;
        }

        .calendar-modal-footer .btn {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            border-radius: 8px;
        }

        /* Desktop - Hızlı İşlemler Card */
        .quick-actions-card {
            display: block;
        }

        /* Takvim layout düzenlemesi */
        .apps-calendar .main-content {
            display: flex;
            flex-direction: row;
            gap: 0;
            width: 100%;
            align-items: stretch;
        }

        .apps-calendar .content-sidebar {
            flex: 0 0 280px;
            width: 280px;
            border-right: 1px solid #e9ecef;
            padding: 1.5rem;
            overflow-y: auto;
        }

        .apps-calendar .content-area {
            flex: 1;
            min-width: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .apps-calendar .content-area-header {
            flex-shrink: 0;
        }

        .apps-calendar .content-area-body {
            flex: 1;
            overflow: visible;
            min-height: 680px;
        }

        .nxl-content {
            overflow: visible !important;
        }

        #tui-calendar-init {
            width: 100%;
            height: 100%;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .quick-actions-card {
                display: none;
            }

            .apps-calendar .content-sidebar {
                flex: 0 0 200px;
                max-width: 200px;
            }
        }
    </style>

    <?php
    $defaultOrder = [
        'quick-actions-card',
        'row-cards',
        'calendar-card',
        'payment-records-chart-card',
        'leads-inquiry-channel-card',
        'borc-listele',
    ];
    $userId = $_SESSION['user']->id ?? 0;
    $layout = [];
    $available = array_map(function($p){ return basename($p, '.php'); }, glob(__DIR__ . '/cards/*.php'));
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
        $orderCol1 = $defaultOrder;
        foreach ($available as $k) {
            if (!in_array($k, $orderCol1, true)) {
                $orderCol1[] = $k;
            }
        }
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

<!-- Etkinlik Önizleme Modal -->
<div class="modal fade" id="calendarEventPreviewModal" tabindex="-1" aria-labelledby="calendarEventPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered calendar-event-modal">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1" id="calendarEventPreviewModalLabel">Etkinlik Detayları</h5>
                    <p class="text-muted mb-0 small" id="calendarEventPreviewModalSubtitle">Etkinlik bilgileri</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <div class="d-flex align-items-center mb-3">
                        <div id="preview-event-color" class="rounded-2 me-3" style="width: 24px; height: 24px;"></div>
                        <h5 class="mb-0 fw-bold" id="preview-event-title"></h5>
                    </div>
                    
                    <div class="mb-3" id="preview-event-datetime"></div>
                    
                    <div class="mb-3 d-none" id="preview-event-location">
                        <i class="feather-map-pin feather fs-12 me-2"></i>
                        <span id="preview-location-text"></span>
                    </div>
                    
                    <div class="mb-3 d-none" id="preview-event-description-container">
                        <h6 class="text-muted mb-2 fw-semibold">Açıklama</h6>
                        <p class="mb-0" id="preview-event-description"></p>
                    </div>

                    <div class="pt-2">
                        <small id="preview-event-type"></small>
                    </div>
                </div>
            </div>
            <div class="modal-footer calendar-modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Vazgeç</button>
                <div class="action-buttons">
                    <button type="button" class="btn btn-outline-secondary" id="preview-edit-btn">Düzenle</button>
                    <button type="button" class="btn btn-outline-danger" id="preview-delete-btn">Sil</button>

                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="calendarEventModal" tabindex="-1" aria-labelledby="calendarEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered calendar-event-modal">
        <form class="modal-content" id="calendar-event-form" autocomplete="off">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1" id="calendarEventModalLabel">Etkinlik Kaydı</h5>
                    <p class="text-muted mb-0 small" id="calendarEventModalSubtitle">Yeni bir etkinlik oluşturun.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="calendar-event-id" name="event_id">
                <div class="alert alert-danger d-none" role="alert" id="calendar-form-alert"></div>
                <div class="mb-3">
                    <label for="calendar-event-title" class="form-label">Başlık</label>
                    <input type="text" class="form-control" id="calendar-event-title" name="title" maxlength="255" required>
                </div>
                <div class="mb-3">
                    <label for="calendar-event-type" class="form-label">Etkinlik Türü</label>
                    <select class="form-select" id="calendar-event-type" name="calendar_id" required>
                        <?php foreach ($calendarTypes as $type): ?>
                            <option value="<?php echo htmlspecialchars($type['id'], ENT_QUOTES); ?>">
                                <?php echo htmlspecialchars($type['name'], ENT_QUOTES); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="calendar-event-start" class="form-label">Başlangıç</label>
                        <input type="datetime-local" class="form-control" id="calendar-event-start" name="start" required>
                    </div>
                    <div class="col-md-6">
                        <label for="calendar-event-end" class="form-label">Bitiş</label>
                        <input type="datetime-local" class="form-control" id="calendar-event-end" name="end" required>
                    </div>
                </div>
                <div class="form-check form-switch my-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="calendar-event-all-day" name="is_all_day">
                    <label class="form-check-label" for="calendar-event-all-day">Tüm Gün</label>
                </div>
                <div class="mb-3">
                    <label for="calendar-event-location" class="form-label">Konum</label>
                    <input type="text" class="form-control" id="calendar-event-location" name="location" maxlength="255">
                </div>
                <div class="mb-0">
                    <label for="calendar-event-description" class="form-label">Açıklama</label>
                    <textarea class="form-control" id="calendar-event-description" name="description" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer calendar-modal-footer">
                <button type="button" class="btn btn-outline-danger d-none" id="calendar-delete-btn">Etkinliği Sil</button>
                <div class="action-buttons">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-primary" id="calendar-save-btn">Kaydet</button>
                </div>
            </div>
        </form>
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


<style>
    .drag-handle { cursor: grab; user-select: none; -webkit-user-drag: none; touch-action: none; display: inline-flex; align-items: center; gap: 6px; color: #6c757d; }
    .drag-handle:hover { color: #495057; }
    .drag-handle i { pointer-events: none; }
    /* Modal backdrop z-index otomatik yönetilecek */
    .stacked-backdrop {
        opacity: 0.3 !important;
    }

    /* Modal ve Offcanvas Backdrop Stilleri */
    .modal-backdrop {
        background-color: rgba(0, 0, 0, 0.5);
    }

    .offcanvas-backdrop {
        background-color: rgba(0, 0, 0, 0.5);
    }

    /* Takvim seçim stilleri - daha belirgin */
    .tui-full-calendar-month-view .selected-date,
    .tui-full-calendar-month-view [data-date].selected-date,
    .tui-full-calendar-month-view .tui-full-calendar-day-grid-date.selected-date,
    .tui-full-calendar-month-view .tui-full-calendar-dayname-date-area.selected-date,
    .tui-full-calendar-month-view .tui-full-calendar-month-day-grid-item.selected-date,
    .tui-full-calendar-month-view .tui-full-calendar-dayname-item.selected-date,
    .tui-full-calendar-month-view .tui-full-calendar-dayname-date.selected-date,
    .tui-full-calendar-month-view .tui-full-calendar-day-grid-item.selected-date {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
        box-shadow: 0 2px 4px rgba(25, 118, 210, 0.2) !important;
        color: #000000 !important;
        font-weight: 600 !important;
        border-radius: 4px !important;
        transition: all 0.2s ease !important;
    }

    /* Seçim için daha yüksek specificity */
    .tui-full-calendar-month-view .tui-full-calendar-day-grid-date[data-date].selected-date,
    .tui-full-calendar-month-view .tui-full-calendar-dayname-date-area[data-date].selected-date {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
        box-shadow: 0 2px 4px rgba(25, 118, 210, 0.2) !important;
        color: #000000 !important;
        font-weight: 600 !important;
        border-radius: 4px !important;
    }

    /* Etkinlik önizleme modal stilleri */
    #calendarEventPreviewModal .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 10px 40px rgba(15, 23, 42, 0.15);
        background: #ffffff;
    }

    #calendarEventPreviewModal .modal-header {
        border-bottom: 1px solid #e9ecef;
        padding: 1.25rem 1.5rem;
        background: #fafbfc;
        border-radius: 12px 12px 0 0;
    }

    #calendarEventPreviewModal .modal-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1976d2;
    }

    #calendarEventPreviewModal .modal-header .text-muted {
        color: #6c757d !important;
        font-size: 0.85rem;
    }

    #calendarEventPreviewModal .btn-close {
        opacity: 0.5;
    }

    #calendarEventPreviewModal .btn-close:hover {
        opacity: 0.8;
    }

    #calendarEventPreviewModal .modal-body {
        padding: 1.5rem;
        background: #ffffff;
    }

    #preview-event-color {
        min-width: 20px;
        min-height: 20px;
        border-radius: 4px;
        border: 2px solid #ffffff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    #preview-event-title {
        color: #1976d2;
        font-size: 1.3rem;
        font-weight: 700;
        line-height: 1.4;
    }

    #preview-event-datetime {
        font-size: 0.9rem;
        color: #495057;
        font-weight: 500;
        background: #e3f2fd;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        border-left: 4px solid #1976d2;
        margin: 0.75rem 0;
    }

    #preview-event-location {
        font-size: 0.9rem;
        color: #495057;
        background: #fafbfc;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        border-left: 4px solid #1976d2;
        margin: 0.75rem 0;
    }

    #preview-event-location i {
        color: #1976d2;
        font-size: 13px;
    }

    #preview-event-description {
        font-size: 0.9rem;
        line-height: 1.5;
        color: #495057;
        background: #fafbfc;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        max-height: 120px;
        overflow-y: auto;
    }

    #preview-event-type {
        font-size: 0.75rem;
        font-weight: 700;
        color: #1976d2;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        background: #e3f2fd;
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
        display: inline-block;
        border: 1px solid #90caf9;
    }

    #calendarEventPreviewModal .modal-footer {
        border-top: 1px solid #e9ecef;
        padding: 1rem 1.5rem;
        background: #fafbfc;
        border-radius: 0 0 12px 12px;
    }

    #calendarEventPreviewModal .btn-primary {
        background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
        border: none;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        color: white;
    }

    #calendarEventPreviewModal .btn-primary:hover {
        background: linear-gradient(135deg, #1565c0 0%, #1456b0 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(25, 118, 210, 0.3);
        color: white;
    }

    #calendarEventPreviewModal .btn-outline-secondary {
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        color: #495057;
        border-color: #dee2e6;
    }

    #calendarEventPreviewModal .btn-outline-secondary:hover {
        background-color: #f8f9fa;
        border-color: #adb5bd;
        color: #495057;
    }

    #calendarEventPreviewModal .btn-outline-danger {
        border-radius: 10px;
        font-weight: 600;
        color: #dc3545;
        border-color: #dc3545;
        transition: all 0.3s ease;
    }

    #calendarEventPreviewModal .btn-outline-danger:hover {
        background-color: #dc3545;
        border-color: #dc3545;
        color: white;
    }

    #preview-edit-btn, #preview-delete-btn {
        padding: 0.6rem 1.2rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.3s ease;
        min-width: 120px;
    }

    #preview-edit-btn {
        background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
        border: none;
        color: white !important;
    }

    #preview-edit-btn:hover {
        background: linear-gradient(135deg, #1565c0 0%, #1456b0 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(25, 118, 210, 0.3);
        color: white;
    }

    #preview-delete-btn {
        background: white;
        border: 1px solid #dc3545;
        color: #dc3545;
    }

    #preview-delete-btn:hover {
        background: #dc3545;
        border-color: #dc3545;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(220, 53, 69, 0.3);
    }

    #calendar-save-btn {
        background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
        border: none;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        color: white;
        padding: 0.6rem 1.2rem;
        font-size: 0.75rem;
        min-width: 120px;
    }

    #calendar-save-btn:hover {
        background: linear-gradient(135deg, #1565c0 0%, #1456b0 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(25, 118, 210, 0.3);
        color: white;
    }

    #calendar-delete-btn {
        border-radius: 10px;
        font-weight: 600;
        color: #dc3545;
        border-color: #dc3545;
        transition: all 0.3s ease;
        background: white;
        border: 1px solid #dc3545;
        padding: 0.6rem 1.2rem;
        font-size: 0.75rem;
        min-width: 120px;
    }

    #calendar-delete-btn:hover {
        background-color: #dc3545;
        border-color: #dc3545;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(220, 53, 69, 0.3);
    }
</style>

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
