<?php

require_once dirname(__DIR__) . '/configs/bootstrap.php';

use App\Helper\Security;
use Model\SiteEventModel;

Security::checkLogin();

header('Content-Type: application/json; charset=UTF-8');

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
    $payload = $_POST ?? [];
}

$action = $payload['action'] ?? null;
if (!$action) {
    respondError('İşlem tipi belirtilmedi.', 400);
}

$firmId = isset($_SESSION['firm_id']) ? (int) $_SESSION['firm_id'] : 0;
$siteId = isset($_SESSION['site_id']) ? (int) $_SESSION['site_id'] : 0;

$userId = 0;
if (isset($_SESSION['user'])) {
    $sessionUser = $_SESSION['user'];
    if (is_object($sessionUser) && isset($sessionUser->id)) {
        $userId = (int) $sessionUser->id;
    } elseif (is_array($sessionUser) && isset($sessionUser['id'])) {
        $userId = (int) $sessionUser['id'];
    }
}

// Oturumda yoksa istemci tarafından gönderilen değerleri dene
if ($firmId === 0 && isset($payload['firmId'])) {
    $firmId = (int) $payload['firmId'];
}

if ($siteId === 0 && isset($payload['siteId'])) {
    $siteId = (int) $payload['siteId'];
}

if ($firmId === 0 || $siteId === 0) {
    respondError('Aktif site bilgisi bulunamadı.', 403);
}

$model = new SiteEventModel();

try {
    switch ($action) {
        case 'list':
            handleList($model, $payload, $firmId, $siteId);
            break;
        case 'create':
            handleCreate($model, $payload, $firmId, $siteId, $userId);
            break;
        case 'update':
            handleUpdate($model, $payload, $firmId, $siteId, $userId);
            break;
        case 'delete':
            handleDelete($model, $payload, $firmId, $siteId, $userId);
            break;
        default:
            respondError('Geçersiz işlem tipi.', 400);
    }
} catch (\Throwable $throwable) {
    respondError($throwable->getMessage(), 500);
}

die();

function handleList(SiteEventModel $model, array $payload, int $firmId, int $siteId): void
{
    $start = $payload['start'] ?? '';
    $end = $payload['end'] ?? '';
    if ($start === '' || $end === '') {
        respondError('Listeleme için tarih aralığı gerekli.', 400);
    }

    $calendars = $payload['calendars'] ?? [];
    if (!is_array($calendars)) {
        $calendars = [];
    }

    if (empty($calendars)) {
        respondSuccess([]);
    }

    $events = $model->getEventsForRange($firmId, $siteId, $start, $end, $calendars);

    if (!empty($_ENV['APP_DEBUG'])) {
        error_log(sprintf(
            '[calendar-events] list firm:%d site:%d start:%s end:%s calendars:%s count:%d',
            $firmId,
            $siteId,
            $start,
            $end,
            json_encode($calendars),
            is_array($events) ? count($events) : 0
        ));
    }

    respondSuccess($events);
}

function handleCreate(SiteEventModel $model, array $payload, int $firmId, int $siteId, int $userId): void
{
    $data = normaliseEventPayload($payload);
    validateEventPayload($data);

    $event = $model->createEvent($data, $firmId, $siteId, $userId);

    respondSuccess($event, 'Etkinlik başarıyla oluşturuldu.');
}

function handleUpdate(SiteEventModel $model, array $payload, int $firmId, int $siteId, int $userId): void
{
    if (empty($payload['id'])) {
        respondError('Etkinlik bilgisi bulunamadı.', 400);
    }

    $data = normaliseEventPayload($payload);
    validateEventPayload($data);

    $event = $model->updateEvent($payload['id'], $data, $firmId, $siteId, $userId);

    respondSuccess($event, 'Etkinlik güncellendi.');
}

function handleDelete(SiteEventModel $model, array $payload, int $firmId, int $siteId, int $userId): void
{
    if (empty($payload['id'])) {
        respondError('Silinecek etkinlik seçilmedi.', 400);
    }

    $model->deleteEvent($payload['id'], $firmId, $siteId, $userId);

    respondSuccess(['id' => $payload['id']], 'Etkinlik silindi.');
}

function normaliseEventPayload(array $payload): array
{
    return [
        'title' => isset($payload['title']) ? trim((string) $payload['title']) : '',
        'calendarId' => isset($payload['calendarId']) ? trim((string) $payload['calendarId']) : 'genel',
        'start' => isset($payload['start']) ? trim((string) $payload['start']) : '',
        'end' => isset($payload['end']) ? trim((string) $payload['end']) : '',
        'isAllDay' => !empty($payload['isAllDay']),
        'location' => isset($payload['location']) ? trim((string) $payload['location']) : null,
        'description' => isset($payload['description']) ? trim((string) $payload['description']) : null,
    ];
}

function validateEventPayload(array $data): void
{
    if ($data['title'] === '') {
        respondError('Etkinlik başlığı zorunludur.', 400);
    }

    if ($data['start'] === '' || $data['end'] === '') {
        respondError('Başlangıç ve bitiş tarihleri zorunludur.', 400);
    }

    if (!in_array($data['calendarId'], SiteEventModel::allowedCalendars(), true)) {
        respondError('Geçersiz etkinlik türü seçildi.', 400);
    }

    if (mb_strlen($data['title']) > 255) {
        respondError('Başlık en fazla 255 karakter olabilir.', 400);
    }

    if ($data['location'] !== null && mb_strlen($data['location']) > 255) {
        respondError('Konum en fazla 255 karakter olabilir.', 400);
    }
}

function respondSuccess($data = [], string $message = 'Başarılı'): void
{
    if (ob_get_length()) {
        ob_clean();
    }
    echo json_encode([
        'status' => 'success',
        'message' => $message,
        'data' => $data,
    ], JSON_UNESCAPED_UNICODE);
    die();
}

function respondError(string $message, int $status = 400): void
{
    http_response_code($status);
    if (ob_get_length()) {
        ob_clean();
    }
    echo json_encode([
        'status' => 'error',
        'message' => $message,
    ], JSON_UNESCAPED_UNICODE);
    die();
}
