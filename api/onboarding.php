<?php
require_once dirname(__DIR__) . '/configs/bootstrap.php';

use App\Modules\Onboarding\Controllers\OnboardingController;
use App\Modules\Onboarding\Policies\OnboardingPolicy;

header('Content-Type: application/json; charset=utf-8');

if (!OnboardingPolicy::canManage()) {
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim']);
    exit;
}

$user = $_SESSION['user'];
$userId = (int)$user->id;
$siteId = isset($_SESSION['site_id']) ? (int)$_SESSION['site_id'] : null;

$action = $_GET['action'] ?? $_POST['action'] ?? 'status';

$controller = new OnboardingController();

try {
    if ($action === 'status') {
        echo json_encode(['status' => 'success'] + $controller->status($userId, $siteId));
    } elseif ($action === 'complete') {
        $taskKey = $_POST['task_key'] ?? $_GET['task_key'] ?? '';
        if (!$taskKey) { echo json_encode(['status' => 'error', 'message' => 'task_key gerekli']); exit; }
        echo json_encode($controller->complete($userId, $siteId, $taskKey));
    } elseif ($action === 'dismiss') {
        echo json_encode($controller->dismiss($userId, $siteId));
    } elseif ($action === 'reset') {
        echo json_encode(['status' => 'error', 'message' => 'Reset desteklenmiyor']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz eylem']);
    }
} catch (\Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}