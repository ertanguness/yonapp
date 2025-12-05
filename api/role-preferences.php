<?php
require_once __DIR__ . '/../configs/bootstrap.php';

use Model\UserModel;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
    exit;
}

$action = $_POST['action'] ?? '';
$token  = $_POST['token'] ?? '';
$userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

$candidates = $_SESSION['role_select_candidates'] ?? [];
$ids = array_map(function($c){ return (int)$c['id']; }, $candidates);

if ($action !== 'status') {
    if (empty($candidates) || empty($_SESSION['role_select_csrf']) || !hash_equals($_SESSION['role_select_csrf'], $token) || !in_array($userId, $ids, true)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'invalid_request']);
        exit;
    }
}

$model = new UserModel();
try { $model->ensureLoginPreferenceColumns(); } catch (\Exception $e) {}

if ($action === 'toggle_favorite') {
    $fav = isset($_POST['fav']) ? (int)$_POST['fav'] : 0;
    $done = $model->setLoginFavorite($userId, $fav);
    echo json_encode(['ok' => $done]);
    exit;
}

if ($action === 'inc_usage') {
    $done = $model->incLoginUsage($userId);
    echo json_encode(['ok' => $done]);
    exit;
}

if ($action === 'status') {
    $idsList = array_map(function($c){ return (int)$c['id']; }, $candidates);
    $out = $model->getLoginPreferenceStatus($idsList);
    echo json_encode(['ok' => true, 'data' => $out]);
    exit;
}

http_response_code(400);
echo json_encode(['ok' => false, 'error' => 'unknown_action']);
