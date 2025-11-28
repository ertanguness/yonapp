<?php
require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';

use App\Helper\Date;
use Model\LoginLogsModel;

header('Content-Type: application/json; charset=utf-8');

$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
if ($userId <= 0) {
    echo json_encode([
        'draw' => intval($_GET['draw'] ?? 0),
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$columns = [
    [ 'db' => 'login_time',  'dt' => 0, 'formatter' => function($v){ return Date::dmYHIS($v); } ],
    [ 'db' => 'logout_time', 'dt' => 1, 'formatter' => function($v){ return ($v && trim((string)$v) !== '') ? Date::dmYHIS($v) : '-'; } ],
    [ 'db' => 'ip_address',  'dt' => 2 ],
    [ 'db' => 'user_agent',  'dt' => 3, 'formatter' => function($v){ return '<span style="display:inline-block;max-width:300px;white-space:wrap;">'.htmlspecialchars((string)$v).'</span>'; } ],
];

$model = new LoginLogsModel();
$response = $model->getSSPResponse(
    $_GET,
    $columns,
    null,
    [
        'condition' => 'user_id = :user_id',
        'bindings' => [ ':user_id' => $userId ]
    ]
);

echo json_encode($response, JSON_UNESCAPED_UNICODE);