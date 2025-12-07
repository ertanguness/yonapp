<?php
require_once dirname(__DIR__) . '/configs/bootstrap.php';

/** @var PDO $db */
$db = \getDbConnection();

$total = (int)($db->query('SELECT COUNT(*) AS c FROM user_onboarding_progress')->fetch(PDO::FETCH_OBJ)->c ?? 0);

echo json_encode([
    'total_rows' => $total,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";

