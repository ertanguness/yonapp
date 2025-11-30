<?php

require_once dirname(__FILE__, 4) . '/configs/bootstrap.php';

use Database\Db;
use App\Helper\Date;
use App\Services\Gate;
use App\Helper\Helper;
use Model\PersonelModel;
use App\Helper\Security;
use Model\SettingsModel;
use Model\PersonelGorevlerModel;
use App\Services\SmsGonderService;

$PersonelGorevModel = new PersonelGorevlerModel();
$Settings           = new SettingsModel();
$db                 = Db::getInstance();
$logger             = \getLogger();

$action = $_POST['action'] ?? '';
$post = (object)$_POST;
$task_id = Security::decrypt($post->task_id ?? 0);


/** Görev Kaydetme/Güncelleme */
if ($action == 'saveTask') {
    Gate::can('gorev_ekle_guncelle_sil');
    try {
        $db->beginTransaction();
        $data = [
            'id' => $task_id,
            'person_id'         => $post->person_id ?? 0,
            'title'             => $post->task_title ?? '',
            'description'       => $post->task_desc ?? '',
            'start_date'        => Date::Ymd($post->task_start ?? ''),
            'end_date'          => Date::Ymd($post->task_end ?? ''),
            'status'            => $post->task_status ?? ''
        ];

        $lastInsertId  = $PersonelGorevModel->saveWithAttr($data);

        /** Personele bildirim yap ayarı açıksa mesaj gönder */
        $bildir = $Settings->getSettings("gorevi_personele_sms_ile_bildir");
        $logger->info("Görev kaydetme/güncelleme işlemi için ayarlar: " . json_encode($bildir));
        if ($bildir === '1') {
            SmsGonderService::gonder(
                ["5079432723"],
                "Tarafınıza yeni bir görev atandı. Görev başlığı: " . $post->task_title,
            );
        }



        $db->commit();
        $message = $task_id == 0 ? "Görev başarıyla kaydedildi" : "Görev başarıyla güncellendi";
        $status = "success";
    } catch (Exception $e) {
        $db->rollBack();
        $message = 'Hata: ' . $e->getMessage();
        $status = "error";
    }

    echo json_encode([
        'status' => $status,
        'message' => $message,
        'id' => $lastInsertId ?? 0
    ]);

    exit();
}

/** Görev Silme */
if ($action == 'deleteTask') {
    Gate::can('gorev_ekle_guncelle_sil');

    try {
        $db->beginTransaction();
        $PersonelGorevModel->delete($post->task_id);
        $db->commit();
        $message = "Görev başarıyla silindi";
        $status = "success";
    } catch (Exception $e) {
        $db->rollBack();
        $message = 'Hata: ' . $e->getMessage();
        $status = "error";
    }

    echo json_encode([
        'status' => $status,
        'message' => $message
    ]);

    exit();
}
