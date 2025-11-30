<?php
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use Model\SettingsModel;

header('Content-Type: application/json; charset=utf-8');

$type = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : (isset($_POST['type']) ? strtolower(trim($_POST['type'])) : '');
$Settings = new SettingsModel();
$kv = $Settings->getAllSettingsAsKeyValue() ?? [];
$smtpActive = (int)($kv['smtp_durum'] ?? 0) === 1;
$smsActive  = (int)($kv['sms_durum'] ?? 0) === 1;

if ($type === 'email') {
    echo json_encode([
        'status' => 'success',
        'type' => 'email',
        'active' => $smtpActive,
    ]);
    exit;
}

if ($type === 'sms') {
    echo json_encode([
        'status' => 'success',
        'type' => 'sms',
        'active' => $smsActive,
    ]);
    exit;
}

echo json_encode([
    'status' => 'success',
    'active' => [
        'email' => $smtpActive,
        'sms' => $smsActive,
    ],
]);
?>
