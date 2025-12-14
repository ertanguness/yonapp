<?php

require_once dirname(__FILE__, 4) . '/configs/bootstrap.php';

use Database\Db;
use App\Helper\Helper;
use App\Helper\Security;
use Model\DestekModel;

$DestekModel = new DestekModel();
$db = Db::getInstance();

$action = $_POST['action'] ?? '';
$post = (object)$_POST;
$support_id = Security::decrypt($post->support_id ?? 0);

if ($action === 'saveSupport') {
    try {
        $db->beginTransaction();
        $data = [
            'id'        => $support_id,
            'konu'      => $post->support_subject ?? '',
            'aciklama'  => $post->support_desc ?? ''
        ];

        $lastInsertId = $DestekModel->saveWithAttr($data);

        $db->commit();
        $message = ($support_id == 0) ? 'Talep başarıyla kaydedildi' : 'Talep başarıyla güncellendi';
        $status = 'success';
    } catch (\Exception $e) {
        $db->rollBack();
        $message = 'Hata: ' . $e->getMessage();
        $status = 'error';
    }

    echo json_encode([
        'status'  => $status,
        'message' => $message,
        'id'      => $lastInsertId ?? 0,
    ]);
    exit();
}

if ($action === 'deleteSupport') {
    try {
        $db->beginTransaction();
        $DestekModel->delete($post->support_id);
        $db->commit();
        $message = 'Talep başarıyla silindi';
        $status = 'success';
    } catch (\Exception $e) {
        $db->rollBack();
        $message = 'Hata: ' . $e->getMessage();
        $status = 'error';
    }

    echo json_encode([
        'status'  => $status,
        'message' => $message,
    ]);
    exit();
}

echo json_encode([
    'status'  => 'error',
    'message' => 'Geçersiz işlem',
]);
exit();

