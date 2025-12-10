<?php
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use Model\DestekModel;
use App\Helper\Security;

$req = $_GET;
$rows = [];

$m = new DestekModel();
$items = $m->all();
foreach ($items as $it) {
    $encId = Security::encrypt((int)($it->id ?? 0));
    $rows[] = [
        'konu'      => htmlspecialchars($it->konu ?? ''),
        'aciklama'  => htmlspecialchars($it->aciklama ?? ''),
        'actions'   => '<div class="hstack gap-2" style="min-width:120px">'
            . '<a href="javascript:void(0);" class="avatar-text avatar-md support-edit" data-id="' . $encId . '" title="Düzenle"><i class="feather-edit"></i></a>'
            . '<a href="javascript:void(0);" class="avatar-text avatar-md support-delete" data-id="' . $encId . '" data-name="' . htmlspecialchars($it->konu ?? '') . '" title="Sil"><i class="feather-trash-2"></i></a>'
            . '</div>'
    ];
}

$recordsTotal = count($rows);
$recordsFiltered = $recordsTotal;
$start = isset($req['start']) ? (int)$req['start'] : 0;
$length = isset($req['length']) ? (int)$req['length'] : 10;
if ($length !== -1) {
    $rows = array_slice($rows, $start, $length);
}
$resp = [
    'draw' => isset($req['draw']) ? (int)$req['draw'] : 0,
    'recordsTotal' => $recordsTotal,
    'recordsFiltered' => $recordsFiltered,
    'data' => array_values($rows),
];
header('Content-Type: application/json; charset=utf-8');
echo json_encode($resp);

