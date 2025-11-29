<?php
require_once __DIR__ . '/../configs/bootstrap.php';

use Database\Db;

$t = $_GET['t'] ?? '';
$kid = $_GET['kid'] ?? null;

if (!$t) {
    header('Location: /');
    exit;
}

try {
    $pdo = Db::getInstance()->connect();
    $stmt = $pdo->prepare('SELECT target_url, expires_at FROM invite_links WHERE token = ?');
    $stmt->execute([$t]);
    $row = $stmt->fetch(PDO::FETCH_OBJ);
    if (!$row) { header('Location: /'); exit; }
    if ($row->expires_at && strtotime($row->expires_at) < time()) { header('Location: /'); exit; }
    $url = $row->target_url;
    $q = [];
    if ($kid) { $q['kisi_id'] = $kid; }
    if (!empty($q)) { $url .= (strpos($url,'?')===false?'?':'&') . http_build_query($q); }
    header('Location: ' . $url);
    exit;
} catch (\Throwable $e) {
    header('Location: /');
    exit;
}