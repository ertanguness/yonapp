<?php
require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';

use Model\Model;
use Database\Db;

header('Content-Type: application/json; charset=utf-8');

// DataTables server-side isteği mi?
$isDataTables = isset($_GET['draw']) || isset($_POST['draw']);

if ($isDataTables) {
    $request = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;

    $columns = [
        [ 'db' => 'id',         'dt' => 0 ],
        [ 'db' => 'type',       'dt' => 1 ],
        [ 'db' => 'recipients', 'dt' => 2 ],
        [ 'db' => 'subject',    'dt' => 3 ],
        [ 'db' => 'message',    'dt' => 4 ],
        [ 'db' => 'created_at', 'dt' => 5 ],
        [ 'db' => 'status',     'dt' => 6 ],
    ];

    try {
        $model = new Model('notifications');
        echo $model->serverProcessing($request, $columns, null, null, true, null, 'id');
    } catch (\Throwable $e) {
        $draw = intval($request['draw'] ?? 0);
        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'error' => 'notifications verisi getirilemedi: ' . $e->getMessage(),
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// Basit listeleme (server-side değilse)
$db = Db::getInstance()->connect();
try {
    $stmt = $db->prepare("SELECT id, type, recipients, subject, message, status, created_at FROM notifications ORDER BY id DESC");
    $stmt->execute();
    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    echo json_encode($rows, JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    echo json_encode([], JSON_UNESCAPED_UNICODE);
}
exit;