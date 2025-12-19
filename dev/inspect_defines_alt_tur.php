<?php
require_once __DIR__ . '/../configs/bootstrap.php';

use Model\DefinesModel;

// Bu script, tarayıcıdan veya CLI'dan çalıştırılabilir.
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

$siteId = (int)($_SESSION['site_id'] ?? 0);
echo "site_id={$siteId}\n";

if (!$siteId) {
    echo "Session site_id yok. Bu scripti, login olmuş bir session ile tarayıcıdan çağırın.\n";
    exit;
}

$d = new DefinesModel();
$gelir = $d->getDefinesTypes($siteId, 6);
$gider = $d->getDefinesTypes($siteId, 7);

echo "gelirCats=" . count($gelir) . "\n";
echo "giderCats=" . count($gider) . "\n";

$sample = $gelir[0] ?? $gider[0] ?? null;
if (!$sample) {
    echo "Defines bulunamadı.\n";
    exit;
}

echo "--- sample ---\n";
echo "define_name=" . ($sample->define_name ?? '') . "\n";
foreach (['alt_turler', 'alt_tur', 'islem_kodu'] as $k) {
    if (property_exists($sample, $k)) {
        echo $k . "=" . (string)($sample->{$k} ?? 'NULL') . "\n";
    } else {
        echo $k . "=(no_col)\n";
    }
}

// dump tüm kolonlar
// print_r($sample);
