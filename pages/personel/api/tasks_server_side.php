<?php
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';
use Model\PersonelGorevlerModel;
use App\Helper\Security;
$req = $_GET;
$rows = [];
$personId = isset($req['person_id']) ? (int)$req['person_id'] : 0;
if ($personId > 0) {
    $m = new PersonelGorevlerModel();
    $items = $m->listByPerson($personId);
    foreach ($items as $it) {
        $encId = Security::encrypt((int)$it->id);
        $rows[] = [
            'title' => htmlspecialchars($it->title ?? ''),
            'description' => htmlspecialchars($it->description ?? ''),
            'start_date' => htmlspecialchars($it->start_date ?? ''),
            'end_date' => htmlspecialchars($it->end_date ?? ''),
            'status' => htmlspecialchars($it->status ?? ''),
            'actions' => '<div class="hstack gap-2">'
                .'<a href="javascript:void(0);" class="avatar-text avatar-md task-edit" data-id="'.$encId.'" title="Düzenle"><i class="feather-edit"></i></a>'
                .'<a href="javascript:void(0);" class="avatar-text avatar-md task-delete" data-id="'.$encId.'" data-name="'.htmlspecialchars($it->title ?? '').'" title="Sil"><i class="feather-trash-2"></i></a>'
                .'</div>'
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