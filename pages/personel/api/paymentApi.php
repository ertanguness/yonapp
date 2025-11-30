<?php
require_once dirname(__FILE__, 4) . '/configs/bootstrap.php';

use Database\Db;
use App\Helper\Date;
use App\Services\Gate;
use App\Helper\Security;
use Model\PersonelOdemelerModel;

$model = new PersonelOdemelerModel();
$db = Db::getInstance();

$action = $_POST['action'] ?? '';
$post = (object)$_POST;
$payment_id = Security::decrypt($post->payment_id ?? 0);

if ($action === 'savePayment') {
    Gate::can('odeme_ekle_guncelle_sil');

    try {
        $db->beginTransaction();
        $data = [
            'id' => $payment_id,
            'person_id'   => (int)($post->person_id ?? 0),
            'amount'      => $post->payment_amount ?? '',
            'date'        => Date::Ymd($post->payment_date ?? ''),
            'description' => $post->payment_desc ?? '',
            'status'      => $post->payment_status ?? ''
        ];
        $lastInsertId = $model->saveWithAttr($data);
        $db->commit();
        $message = ($payment_id == 0) ? 'Ödeme başarıyla kaydedildi' : 'Ödeme başarıyla güncellendi';
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

if ($action === 'deletePayment') {
    Gate::can('odeme_ekle_guncelle_sil');
    try {
        $db->beginTransaction();
        $model->delete($post->payment_id);
        $db->commit();
        $message = 'Ödeme başarıyla silindi';
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

