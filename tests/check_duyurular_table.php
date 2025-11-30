<?php
require_once __DIR__ . '/../configs/bootstrap.php';
$pdo = getDbConnection();
$stmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'duyurular'");
$stmt->execute();
$exists = (int)$stmt->fetchColumn() === 1;
echo $exists ? "OK: duyurular tablosu mevcut\n" : "FAIL: duyurular tablosu yok\n";