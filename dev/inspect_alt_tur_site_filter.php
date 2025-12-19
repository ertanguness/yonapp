<?php
require_once __DIR__ . '/../configs/bootstrap.php';

use Model\DefinesModel;

session_start();

$siteId = (int)($_SESSION['site_id'] ?? 0);
echo "site_id={$siteId}\n";

if (!$siteId) {
    echo "No site_id in session.\n";
    exit(0);
}

$d = new DefinesModel();

$types = [6 => 'Gelir', 7 => 'Gider'];
$targetNames = ['AİDAT', 'AIDAT'];

foreach ($types as $type => $label) {
    $cats = $d->getDefinesTypes($siteId, $type);
    echo "{$label} cats=" . count($cats) . "\n";

    foreach ($cats as $c) {
        if (!in_array($c->define_name, $targetNames, true)) {
            continue;
        }

        echo "- {$label} category: id={$c->id} name={$c->define_name}\n";

        $rows = $d->getGelirGiderKalemleri($siteId, $type, (string)$c->define_name);
        echo "  alt_tur rows=" . count($rows) . "\n";

        $alts = [];
        foreach ($rows as $r) {
            $alt = trim((string)($r->alt_tur ?? ''));
            if ($alt !== '') {
                $alts[$alt] = true;
            }
        }
        $altList = array_keys($alts);
        sort($altList);

        $preview = array_slice($altList, 0, 10);
        foreach ($preview as $alt) {
            echo "   - {$alt}\n";
        }
        if (count($altList) > 10) {
            echo "   ... (" . (count($altList) - 10) . " more)\n";
        }
    }
}
