<?php

require_once __DIR__ . '/../configs/bootstrap.php';

use Model\KisilerModel;

header('Content-Type: application/json; charset=utf-8');

$site_id = (int)($_SESSION['site_id'] ?? 0);

// Smoke test: session yoksa varsayılan 1 ile dener (lokalde hızlı kontrol için)
if ($site_id <= 0) {
    $site_id = 1;
}

try {
    $m = new KisilerModel();
    $stats = $m->getSiteSakinStats($site_id);

    $ok = true;
    foreach (['sakin', 'owner', 'tenant'] as $k) {
        if (!isset($stats[$k]) || !is_array($stats[$k])) { $ok = false; }
        foreach (['total', 'active', 'passive'] as $f) {
            if (!isset($stats[$k][$f])) { $ok = false; }
        }
    }

    echo json_encode([
        'ok' => $ok,
        'site_id' => $site_id,
        'stats' => $stats,
    ]);
} catch (Throwable $e) {
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
    ]);
}
