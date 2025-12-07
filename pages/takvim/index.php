<?php
use App\Helper\Security;
use App\Services\Gate;

Security::ensureNotResident();

$site_id = $_SESSION['site_id'] ?? 0;
$activeFirmId = (int)($_SESSION['firm_id'] ?? ($_SESSION['user']->firm_id ?? 0));

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
    ['id' => 'genel','name' => 'Genel','color' => '#ffffff','bgColor' => '#1abc9c','borderColor' => '#1abc9c'],
    ['id' => 'toplanti','name' => 'Toplantı','color' => '#ffffff','bgColor' => '#3498db','borderColor' => '#3498db'],
    ['id' => 'dogum','name' => 'Doğum Günü','color' => '#ffffff','bgColor' => '#e91e63','borderColor' => '#e91e63'],
    ['id' => 'odeme','name' => 'Ödeme','color' => '#ffffff','bgColor' => '#00bcd4','borderColor' => '#00bcd4'],
    ['id' => 'bakim','name' => 'Bakım','color' => '#ffffff','bgColor' => '#ff9800','borderColor' => '#ff9800'],
    ['id' => 'duyuru','name' => 'Duyuru','color' => '#ffffff','bgColor' => '#673ab7','borderColor' => '#673ab7'],
    ['id' => 'sosyal','name' => 'Sosyal Etkinlik','color' => '#ffffff','bgColor' => '#4caf50','borderColor' => '#4caf50'],
];

$calendarConfig = [
    'basePath' => $calendarBasePath,
    'types' => $calendarTypes,
    'endpoints' => [
        'events' => rtrim($calendarBasePath, '/') . '/api/calendar-events.php',
    ],
    'siteId' => (int)$site_id,
    'firmId' => $activeFirmId,
    'csrfToken' => Security::csrf(),
    'timezone' => date_default_timezone_get(),
];

?>
<script>
    window.calendarConfig = <?php echo json_encode($calendarConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
</script>
<script id="calendar-types-data" type="application/json">
<?php echo json_encode($calendarTypes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
</script>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Takvim</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Takvim</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <a href="#" id="btn-new-schedule" class="btn btn-primary">
            <i class="feather-calendar me-2"></i>
            Yeni Etkinlik
        </a>
    </div>
    
</div>

<div class="main-content">
    <div class="row mb-5">
        <div class="container-xl">
            <style>#tui-calendar-init{min-height:680px;background:#ffffff}</style>
            <?php include __DIR__ . '/../home/cards/calendar-card.php'; ?>
        </div>
    </div>
</div>

<?php include './partials/calender-scripts.php'; ?>
