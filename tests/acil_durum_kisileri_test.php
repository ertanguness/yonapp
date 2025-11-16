<?php

require_once __DIR__ . '/../configs/bootstrap.php';

use Model\AcilDurumKisileriModel;

$pdo = getDbConnection();
$m = new AcilDurumKisileriModel();
$res = ['insert'=>false,'update'=>false,'soft_delete'=>false];

try {
    $pdo->beginTransaction();
    $eid = $m->saveWithAttr([
        'kisi_id' => 1,
        'adi_soyadi' => 'Test Kişi',
        'telefon' => '5550000000',
        'yakinlik' => 'anne',
        'kayit_tarihi' => date('Y-m-d H:i:s'),
    ]);
    $id = (int)\App\Helper\Security::decrypt($eid);
    $res['insert'] = $id > 0;
    $m->updateSingle($id, ['adi_soyadi' => 'Test Kişi 2']);
    $row = $m->find($id);
    $res['update'] = ($row && $row->adi_soyadi === 'Test Kişi 2');
    $m->softDelete($id);
    $row2 = $m->find($id);
    $res['soft_delete'] = ($row2 && isset($row2->silinme_tarihi));
    $pdo->rollBack();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
}

header('Content-Type: application/json');
echo json_encode($res);