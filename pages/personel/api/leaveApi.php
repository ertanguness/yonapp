<?php
require_once dirname(__FILE__, 4) . '/configs/bootstrap.php';

use Database\Db;
use App\Helper\Date;
use App\Services\Gate;
use App\Helper\Security;
use Model\PersonelIzinlerModel;

$model = new PersonelIzinlerModel();
$db = Db::getInstance();

$action = $_POST['action'] ?? '';
$post = (object)$_POST;
$leave_id = Security::decrypt($post->leave_id ?? 0);

if ($action === 'saveLeave') {

    Gate::can(permissionName: 'izin_ekle_guncelle_sil');

    try {
        $db->beginTransaction();
        $start = Date::Ymd($post->leave_start ?? '');
        $end = Date::Ymd($post->leave_end ?? '');
        $days = 0;
        if ($start !== '' && $end !== '') {
            $days = max(1, (int)Date::getDateDiff($start, $end) + 1);
        }
        $data = [
            'id' => $leave_id,
            'person_id'   => (int)($post->person_id ?? 0),
            'type'        => $post->leave_type ?? '',
            'start_date'  => $start,
            'end_date'    => $end,
            'days'        => $days,
            'description' => $post->leave_desc ?? '',
            'status'      => $post->leave_status ?? ''
        ];
        $lastInsertId = $model->saveWithAttr($data);
        $db->commit();
        $message = ($leave_id == 0) ? 'İzin başarıyla kaydedildi' : 'İzin başarıyla güncellendi';
        $status = 'success';
    } catch (\Throwable $e) {
        $db->rollBack();
        $message = 'Hata: ' . $e->getMessage();
        $status = 'error';
    }

    echo json_encode([
        'status' => $status,
        'message' => $message,
        'id' => $lastInsertId ?? 0,
    ]);
    exit;
}

if ($action === 'deleteLeave') {
    Gate::can(permissionName: 'izin_ekle_guncelle_sil');
    
    try {
        $db->beginTransaction();
        $model->delete($post->leave_id);
        $db->commit();
        $message = 'İzin başarıyla silindi';
        $status = 'success';
    } catch (\Throwable $e) {
        $db->rollBack();
        $message = 'Hata: ' . $e->getMessage();
        $status = 'error';
    }

    echo json_encode([
        'status' => $status,
        'message' => $message,
    ]);
    exit;
}
