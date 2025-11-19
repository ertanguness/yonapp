<?php
require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';

use Database\Db;
use App\Services\Gate;

$format = strtolower($_GET['format'] ?? 'csv');

//Gate::can('bildirimler_export');

$pdo = Db::getInstance()->connect();

// Build filters from query
$q = trim($_GET['q'] ?? '');
$columns = ['id','type','recipients','subject','message','status','created_at'];
$conds = [];
$binds = [];

foreach ($columns as $col) {
    $key = 'f_' . $col;
    if (isset($_GET[$key]) && $_GET[$key] !== '') {
        $val = trim($_GET[$key]);
        if ($col === 'id' && ctype_digit($val)) {
            $conds[] = "id = :id_exact";
            $binds[':id_exact'] = (int)$val;
        } else {
            $conds[] = "`$col` LIKE :$key";
            $binds[":$key"] = "%$val%";
        }
    }
}

if ($q !== '') {
    $likeCols = ['type','recipients','subject','message','status'];
    $orParts = [];
    foreach ($likeCols as $c) { $orParts[] = "`$c` LIKE :q"; }
    $conds[] = '(' . implode(' OR ', $orParts) . ')';
    $binds[':q'] = "%$q%";
}

$where = '';
if ($conds) { $where = 'WHERE ' . implode(' AND ', $conds); }

$sql = "SELECT id, type, recipients, subject, message, status, created_at FROM notifications $where ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
foreach ($binds as $k => $v) { $stmt->bindValue($k, $v); }
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($format === 'json') {
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="notifications_' . date('Ymd_His') . '.json"');
    echo json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="notifications_' . date('Ymd_His') . '.csv"');

$bom = "\xEF\xBB\xBF";
echo $bom;

$out = fopen('php://output', 'w');

$headers = ['id','type','recipients','subject','message','status','created_at'];
fputcsv($out, $headers, ';');

foreach ($rows as $r) {
    $rec = $r['recipients'];
    try { $arr = json_decode($rec, true); } catch (\Throwable $e) { $arr = []; }
    if (is_array($arr)) { $rec = implode('|', $arr); }
    $line = [
        $r['id'],
        $r['type'],
        $rec,
        $r['subject'],
        $r['message'],
        $r['status'],
        $r['created_at'],
    ];
    fputcsv($out, $line, ';');
}

fclose($out);
exit;