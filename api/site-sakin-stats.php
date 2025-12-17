<?php
require_once dirname(__DIR__, levels: 1) . '/configs/bootstrap.php';

use Model\KisilerModel;

header('Content-Type: application/json; charset=utf-8');

$site_id = (int)($_SESSION['site_id'] ?? 0);

if ($site_id <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Geçerli site bulunamadı (session site_id yok).'
    ]);
    exit;
}

try {
    $m = new KisilerModel();
    $stats = $m->getSiteSakinStats($site_id);

    echo json_encode([
        'status' => 'success',
        'data' => $stats,
    ]);
} catch (Throwable $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'İstatistikler alınırken hata oluştu.',
    ]);
}
