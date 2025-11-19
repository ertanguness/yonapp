<?php

use App\Helper\Helper;
use Model\BorclandirmaModel;
use Model\FinansalRaporModel;
use App\Helper\Security;

/**Site sakini bu sayfayı görmeyecek */
Security::ensureNotResident();

$BorclandirmaModel = new BorclandirmaModel();
$FinansalRaporModel = new FinansalRaporModel();

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
        'bgColor' => '#e74c3c',
        'borderColor' => '#e74c3c',
    ],
    [
        'id' => 'odeme',
        'name' => 'Ödeme',
        'color' => '#ffffff',
        'bgColor' => '#f39c12',
        'borderColor' => '#f39c12',
    ],
    [
        'id' => 'bakim',
        'name' => 'Bakım',
        'color' => '#ffffff',
        'bgColor' => '#9b59b6',
        'borderColor' => '#9b59b6',
    ],
    [
        'id' => 'duyuru',
        'name' => 'Duyuru',
        'color' => '#ffffff',
        'bgColor' => '#e67e22',
        'borderColor' => '#e67e22',
    ],
    [
        'id' => 'sosyal',
        'name' => 'Sosyal Etkinlik',
        'color' => '#ffffff',
        'bgColor' => '#e74c3c',
        'borderColor' => '#e74c3c',
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

<div class="main-content mb-5">
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
            background: #ffffff;
        }

        .apps-calendar .content-sidebar,
        .apps-calendar .content-area {
            align-self: stretch;
        }

        .apps-calendar .content-area {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        .apps-calendar .content-area-body {
            flex: 1;
            overflow: auto;
            min-height: 680px;
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

        #tui-calendar-init .tui-full-calendar-weekday-grid-line:first-child {
            border-left: 1px solid #e9ecef !important;
        }

        #tui-calendar-init .tui-full-calendar-month-week-item:last-child .tui-full-calendar-weekday-grid-line {
            border-bottom: 1px solid #e9ecef !important;
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
            min-height: 680px;
        }

        .apps-calendar .content-sidebar {
            flex: 0 0 280px;
            width: 280px;
            padding: 1.5rem;
            overflow: hidden;
            max-height: none !important;
            height: auto;
            display: flex;
            flex-direction: column;
            border-right: none;
        }

        .apps-calendar .content-sidebar .content-sidebar-body {
            flex: 1;
            overflow-y: auto;
        }

        .apps-calendar .content-area {
            flex: 1;
            min-width: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            border: 1px solid #e9ecef;
            border-left: none;
            border-radius: 0 12px 12px 0;
            background: #ffffff;
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

    <!-- Desktop Hızlı İşlemler Card -->
    <div class="col-xxl-12 quick-actions-card">
        <div class="card stretch stretch-full">
            <div class="card-header">
                <h5 class="card-title">Hızlı İşlemler</h5>
            </div>
            <div class="card-body">


                <a href="site-ekle"
                    class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5">
                    <i class="bi bi-diagram-3"></i>
                    <p class="fs-12 text-muted mb-0">Site Ekle</p>
                </a>
                <a href="blok-ekle"
                    class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5">
                    <i class="bi bi-building"></i>
                    <p class="fs-12 text-muted mb-0">Blok Ekle</p>
                </a>
                <a href="daire-ekle"
                    class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5">
                    <i class="bi bi-textarea"></i>
                    <p class="fs-12 text-muted mb-0">Daire Ekle</p>
                </a>
                <a href="/site-sakini-ekle"
                    class="flex-fill py-3 px-4 me-4 rounded-1 cursor-pointer border border-dashed border-gray-5">
                    <i class="feather-user-plus"></i>
                    <p class="fs-12 text-muted mb-0">Kişi Ekle</p>
                </a>


                <a href="#"
                    class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5 gelir-ekle">
                    <i class="bi bi-credit-card"></i>
                    <p class="fs-12 text-muted mb-0">Gelir Ekle</p>
                </a>
                <a href="#" class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5 gider-ekle">
                    <i class="bi bi-credit-card-2-back"></i>
                    <p class="fs-12 text-muted mb-0">Gider Ekle</p>
                </a>
                <a href="/gelir-gider-islemleri"
                    class="flex-fill py-3 px-4 me-4 rounded-1 cursor-pointer border border-dashed border-gray-5">
                    <i class="bi bi-wallet2"></i>
                    <p class="fs-12 text-muted mb-0">Finansal İşlemler</p>
                </a>


                <a href="/aidat-turu-tanimlama" class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5">
                    <i class="bi bi-folder-plus"></i>
                    <p class="fs-12 text-muted mb-0">Aidat Tanımla</p>
                </a>
                <a href="/borclandirma-yap" class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5">
                    <i class="bi bi-clipboard-plus"></i>
                    <p class="fs-12 text-muted mb-0">Borçlandırma Yap</p>
                </a>
                <a href="/yonetici-aidat-odeme"
                    class="flex-fill py-3 px-4 me-4 rounded-1 cursor-pointer border border-dashed border-gray-5">
                    <i class="bi bi-person-workspace"></i>
                    <p class="fs-12 text-muted mb-0">Yönetici Aidat Ödeme</p>
                </a>


                <a href="#" class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5 mail-gonder">
                    <i class="bi bi-envelope"></i>
                    <p class="fs-12 text-muted mb-0">Email Gönder</p>
                </a>
                <a href="#" class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5 sms-gonder">
                    <i class="bi bi-send-plus"></i>
                    <p class="fs-12 text-muted mb-0">Sms Gönder</p>
                </a>




            </div>

        </div>
    </div>

    <div class="row row-cards">
        <!-- [Mini Card] start -->
        <div class="col-xxl-3 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="hstack justify-content-between">
                        <div>
                            <h4 class="text-success"><?php echo Helper::formattedMoney($toplam_aidat_geliri); ?></h4>
                            <div class="text-muted">Toplam Aidat Geliri</div>
                        </div>
                        <div class="text-end">
                            <i class="feather-credit-card fs-2"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-success py-3">
                    <div class="hstack justify-content-between">
                        <p class="text-white mb-0">+5% artış</p>
                        <div class="text-end">
                            <i class="feather-trending-up text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="hstack justify-content-between">
                        <div>
                            <h4 class="text-danger"><?php echo Helper::formattedMoney($geciken_odeme_tutari); ?></h4>
                            <div class="text-muted">Gecikmiş Ödemeler</div>
                        </div>
                        <div class="text-end">
                            <i class="feather-alert-triangle fs-2"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-danger py-3">
                    <div class="hstack justify-content-between">
                        <p class="text-white mb-0">+2.5% artış</p>
                        <div class="text-end">
                            <i class="feather-trending-up text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="hstack justify-content-between">
                        <div>
                            <h4 class="text-warning"><?php echo Helper::formattedMoney($toplam_gider); ?></h4>
                            <div class="text-muted">Toplam Giderler</div>
                        </div>
                        <div class="text-end">
                            <i class="feather-dollar-sign fs-2"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-warning py-3">
                    <div class="hstack justify-content-between">
                        <p class="text-white mb-0">-1.2% azalma</p>
                        <div class="text-end">
                            <i class="feather-trending-down text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="hstack justify-content-between">
                        <div>
                            <h4 class="text-danger"><?php echo Helper::formattedMoney($geciken_tahsilat_sayisi); ?></h4>
                            <div class="text-muted">Gecikmiş Aidat Sayısı</div>
                        </div>
                        <div class="text-end">
                            <i class="feather-alert-circle fs-2"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-info py-3">
                    <div class="hstack justify-content-between">
                        <p class="text-white mb-0">Sabit</p>
                        <div class="text-end">
                            <i class="feather-minus text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- [Mini Card] end -->
    </div>
    <!-- Takvim -->
    <div class="card stretch stretch-full">
        <div class="apps-container apps-calendar">
            <div class="nxl-content without-header nxl-full-content">
                <!-- [ Main Content ] start -->
                <div class="main-content d-flex" style="gap: 0;">
                    <!-- [ Content Sidebar ] start -->
                    <div class="content-sidebar content-sidebar-md" data-scrollbar-target="#psScrollbarInit">
                        <div class="content-sidebar-header bg-white hstack justify-content-between mb-3" style="padding: 0; border: none;">
                            <h4 class="fw-bolder mb-0">Etkinlik Takvimi</h4>
                            <a href="javascript:void(0);" class="app-sidebar-close-trigger d-flex">
                                <i class="feather-x"></i>
                            </a>
                        </div>
                        <div class="content-sidebar-header" style="padding: 0; border: none; margin-bottom: 1rem;">
                            <a href="#" id="btn-new-schedule" class="btn btn-primary w-100">
                                <i class="feather-calendar me-2"></i>
                                <span>Yeni Etkinlik</span>
                            </a>
                        </div>

                        <div class="content-sidebar-body" style="padding: 0; background: transparent;">
                            <div id="lnb-calendars" class="lnb-calendars">
                                <div class="lnb-calendars-item">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="viewAllSchedules"
                                            value="all" checked="checked">
                                        <label class="custom-control-label c-pointer" for="viewAllSchedules">
                                            <span class="fs-13 fw-semibold lh-lg" style="margin-top: -2px">Tüm
                                                Etkinlikleri Göster</span>
                                        </label>
                                    </div>
                                </div>
                                <div id="calendarList" class="lnb-calendars-d1">
                                    <?php foreach ($calendarTypes as $type): ?>
                                        <?php
                                        $typeIdRaw = (string) $type['id'];
                                        $inputId = 'calendar-filter-' . preg_replace('/[^a-z0-9_-]+/i', '-', $typeIdRaw);
                                        ?>
                                      <div class="form-check calendar-filter-entry mb-2" style="--calendar-color: <?php echo htmlspecialchars($type['bgColor'], ENT_QUOTES); ?>;">
                                            <input type="checkbox" class="form-check-input calendar-filter" id="<?php echo htmlspecialchars($inputId, ENT_QUOTES); ?>"
                                                value="<?php echo htmlspecialchars($type['id'], ENT_QUOTES); ?>" checked="checked">
                                            <label class="form-check-label calendar-filter-item" for="<?php echo htmlspecialchars($inputId, ENT_QUOTES); ?>">
                                                <span><?php echo htmlspecialchars($type['name'], ENT_QUOTES); ?></span>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Content Sidebar  ] end -->
                    <!-- [ Main Area  ] start -->
                    <div class="content-area" data-scrollbar-target="#psScrollbarInit">
                        <div class="content-area-header sticky-top">
                            <div class="page-header-left d-flex align-items-center gap-2">

                                <div id="menu" class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex calendar-action-btn">
                                        <div class="dropdown me-1">
                                            <button id="dropdownMenu-calendarType"
                                                class="dropdown-toggle calendar-dropdown-btn" type="button"
                                                data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                                data-bs-offset="0,17">
                                                <i id="calendarTypeIcon"
                                                    class="feather-grid calendar-icon fs-12 me-1"></i>
                                                <span id="calendarTypeName">Görünüm</span>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenu-calendarType">
                                            <li><button class="dropdown-item calendar-view-option" type="button"
                                                data-view="day">Günlük Görünüm</button></li>
                                            <li><button class="dropdown-item calendar-view-option" type="button"
                                                data-view="week">Haftalık Görünüm</button></li>
                                            <li><button class="dropdown-item calendar-view-option" type="button"
                                                data-view="month">Aylık Görünüm</button></li>
                                            <li><button class="dropdown-item calendar-view-option" type="button"
                                                data-view="2weeks">2 Haftalık Görünüm</button></li>
                                            <li><button class="dropdown-item calendar-view-option" type="button"
                                                data-view="3weeks">3 Haftalık Görünüm</button></li>
                                            </ul>
                                        </div>
                                        <div class="menu-navi d-none d-sm-flex">
                                            <button type="button" class="move-today" data-action="move-today">
                                                <i class="feather-clock calendar-icon me-1 fs-12"
                                                    data-action="move-today"></i>
                                                <span>Bugün</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="page-header-right ms-auto">
                                <div class="hstack gap-2">
                                    <div id="renderRange" class="render-range d-none d-sm-flex"></div>
                                    <div class="btn-group gap-1 menu-navi" role="group">
                                        <button type="button" class="avatar-text avatar-md move-day"
                                            data-action="move-prev">
                                            <i class="feather-chevron-left fs-12" data-action="move-prev"></i>
                                        </button>
                                        <button type="button" class="avatar-text avatar-md move-day"
                                            data-action="move-next">
                                            <i class="feather-chevron-right fs-12" data-action="move-next"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-area-body p-0">
                            <div id="tui-calendar-init"></div>
                        </div>

                        <!-- [ Footer ] end -->
                    </div>
                    <!-- [ Content Area ] end -->
                </div>
                <!-- [ Main Content Calendar ] end -->
            </div>
        </div>
    </div>

    <!-- [Takvim] end -->
    <!-- [Aylık Gelir Gider Tablosu] -->
    <div class="col-xxl-12">
        <div class="card stretch stretch-full">
            <div class="card-header">
                <h5 class="card-title">Yıllık Gelir-Gider Grafiği</h5>
                <div class="card-header-action">
                    <div class="card-header-btn">
                        <div data-bs-toggle="tooltip" title="Delete">
                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-danger"
                                data-bs-toggle="remove"> </a>
                        </div>
                        <div data-bs-toggle="tooltip" title="Refresh">
                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-warning"
                                data-bs-toggle="refresh"> </a>
                        </div>
                        <div data-bs-toggle="tooltip" title="Maximize/Minimize">
                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success"
                                data-bs-toggle="expand"> </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body custom-card-action">
                <div id="payment-records-chart"></div>
                <div class="d-none d-md-flex flex-wrap pt-4 border-top">
                    <div class="flex-fill">
                        <p class="fs-11 fw-medium text-uppercase text-muted mb-2">Toplam Gelir</p>
                        <h2 class="fs-20 fw-bold mb-0">$65,658 USD</h2>
                    </div>
                    <div class="vr mx-4 text-gray-600"></div>
                    <div class="flex-fill">
                        <p class="fs-11 fw-medium text-uppercase text-muted mb-2">Toplam Gider</p>
                        <h2 class="fs-20 fw-bold mb-0">$34,54 USD</h2>
                    </div>
                    <div class="vr mx-4 text-gray-600"></div>
                    <div class="flex-fill">
                        <p class="fs-11 fw-medium text-uppercase text-muted mb-2">Kar / Zarar</p>
                        <h2 class="fs-20 fw-bold mb-0">$20,478 USD</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- [Payment Records] end -->
    <!-- [Inquiry Channel] start -->
    <div class="col-xxl-12 mb-5">
        <div class="card stretch stretch-full">
            <div class="card-header">
                <h5 class="card-title">Yıllık Aidat Ödeme Grafiği</h5>
                <div class="card-header-action">
                    <div class="card-header-btn">
                        <div data-bs-toggle="tooltip" title="Delete">
                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-danger"
                                data-bs-toggle="remove"> </a>
                        </div>
                        <div data-bs-toggle="tooltip" title="Refresh">
                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-warning"
                                data-bs-toggle="refresh"> </a>
                        </div>
                        <div data-bs-toggle="tooltip" title="Maximize/Minimize">
                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success"
                                data-bs-toggle="expand"> </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body custom-card-action">
                <div id="leads-inquiry-channel"></div>
            </div>
        </div>
    </div>
    <hr>
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


<style>
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
        color: #1976d2 !important;
        font-weight: 600 !important;
        border-radius: 4px !important;
        transition: all 0.2s ease !important;
    }

    /* Seçim için daha yüksek specificity */
    .tui-full-calendar-month-view .tui-full-calendar-day-grid-date[data-date].selected-date,
    .tui-full-calendar-month-view .tui-full-calendar-dayname-date-area[data-date].selected-date {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
        box-shadow: 0 2px 4px rgba(25, 118, 210, 0.2) !important;
        color: #1976d2 !important;
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