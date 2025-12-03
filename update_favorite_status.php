<?php
session_start();

require_once __DIR__ . '/configs/bootstrap.php';

use Model\SitelerModel;

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Oturum açmanız gerekiyor.']);
    exit;
}

if (!isset($_POST['site_id']) || !isset($_POST['is_favorite'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Eksik parametre.']);
    exit;
}

$siteId = intval($_POST['site_id']);
$isFavorite = intval($_POST['is_favorite']) ? 1 : 0;

try {
    $model = new SitelerModel();
    $ok = $model->setFavorite($siteId, $isFavorite);
    if ($ok) {
        echo json_encode(['ok' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'Güncelleme başarısız']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
}
