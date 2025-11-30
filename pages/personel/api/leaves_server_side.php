<?php
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';
use Model\PersonelIzinlerModel;
$req = $_GET;
$rows = [];
$personId = isset($req['person_id']) ? (int)$req['person_id'] : 0;
if ($personId > 0) {
    $m = new PersonelIzinlerModel();
    $items = $m->listByPerson($personId);
    foreach ($items as $it) {
        $rows[] = [
            'type' => htmlspecialchars($it->type ?? ''),
            'start_date' => htmlspecialchars($it->start_date ?? ''),
            'end_date' => htmlspecialchars($it->end_date ?? ''),
            'days' => (int)($it->days ?? 0),
            'description' => htmlspecialchars($it->description ?? ''),
            'status' => htmlspecialchars($it->status ?? ''),
            'actions' => '<a href="javascript:void(0)" class="btn btn-sm btn-light leave-edit" data-id="'.(int)$it->id.'">Düzenle</a>'
        ];
    }
}
$recordsTotal = count($rows);
$recordsFiltered = $recordsTotal;
$start = isset($req['start']) ? (int)$req['start'] : 0;
$length = isset($req['length']) ? (int)$req['length'] : 10;
if ($length !== -1) { $rows = array_slice($rows, $start, $length); }
$resp = [
  'draw' => isset($req['draw']) ? (int)$req['draw'] : 0,
  'recordsTotal' => $recordsTotal,
  'recordsFiltered' => $recordsFiltered,
  'data' => array_values($rows),
];
header('Content-Type: application/json; charset=utf-8');
echo json_encode($resp);