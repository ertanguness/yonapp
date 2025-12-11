<?php

require_once dirname(__FILE__, 4) . '/configs/bootstrap.php';

use Database\Db;
use App\Helper\Security;
use App\Services\MailGonderService;
use Model\DestekModel;
use Model\DestekMesajModel;
use Model\SettingsModel;

$Threads = new DestekModel();
$Messages = new DestekMesajModel();
$Settings = new SettingsModel();
$db = Db::getInstance();

$action = $_REQUEST['action'] ?? '';
$post = (object)$_POST;
$get  = (object)$_GET;
$thread_id = Security::decrypt($post->thread_id ?? $get->thread_id ?? 0);

function supportEmail(SettingsModel $settings): string {
    $envEmail = $_ENV['SUPPORT_EMAIL'] ?? '';
    if ($envEmail) return $envEmail;
    $fromSettings = $settings->getSettings('support_email');
    return $fromSettings ?: 'bilgi@yonapp.com.tr';
}

if ($action === 'createThread') {
    try {
        $db->beginTransaction();
        $data = [
            'id'               => 0,
            'konu'             => $post->subject ?? '',
            'aciklama'         => $post->message ?? '',
            'durum'            => 'Açık',
            'son_guncelleme'   => date('Y-m-d H:i:s'),
        ];
        $encId = $Threads->saveWithAttr($data);
        $newId = Security::decrypt($encId);

        $Messages->saveWithAttr([
            'id'           => 0,
            'talep_id'     => $newId,
            'mesaj'        => $post->message ?? '',
        ]);

        $db->commit();

        $subject = '[Destek] #' . $newId . ' - ' . ($post->subject ?? 'Yeni Bildirim');
        $body    = nl2br(($post->message ?? ''));
        $to      = [supportEmail($Settings)];
        MailGonderService::gonder($to, $subject, $body);

        echo json_encode(['status' => 'success', 'message' => 'Bildirim oluşturuldu', 'id' => $encId]);
    } catch (\Exception $e) {
        $db->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Hata: ' . $e->getMessage()]);
    }
    exit();
}

if ($action === 'addMessage') {
    try {
        $db->beginTransaction();
        $Messages->saveWithAttr([
            'id'           => 0,
            'talep_id'     => $thread_id,
            'gonderen_tip' => $post->sender_type ?? 'musteri',
            'mesaj'        => $post->message ?? '',
            'tarih'        => date('Y-m-d H:i:s'),
        ]);
        $Threads->saveWithAttr([
            'id' => $thread_id,
            'son_guncelleme' => date('Y-m-d H:i:s'),
        ]);
        $db->commit();

        echo json_encode(['status' => 'success', 'message' => 'Mesaj gönderildi']);
    } catch (\Exception $e) {
        $db->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Hata: ' . $e->getMessage()]);
    }
    exit();
}

if ($action === 'closeThread') {
    try {
        $db->beginTransaction();
        $Threads->saveWithAttr([
            'id' => $thread_id,
            'durum' => 'Kapandı',
            'kapanma_tarihi' => date('Y-m-d H:i:s'),
        ]);
        $db->commit();
        echo json_encode(['status' => 'success', 'message' => 'Bildirim kapatıldı']);
    } catch (\Exception $e) {
        $db->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Hata: ' . $e->getMessage()]);
    }
    exit();
}

if ($action === 'getThread') {
    $thread = $Threads->find($get->thread_id ?? 0, true);
    $messages = [];
    if ($thread) {
        $all = $Messages->findWhere(['talep_id' => Security::decrypt($get->thread_id ?? 0)], 'id ASC');
        foreach ($all as $m) {
            $messages[] = [
                'sender' => $m->gonderen_tip ?? 'musteri',
                'message' => htmlspecialchars($m->mesaj ?? ''),
                'date' => $m->tarih ?? '',
            ];
        }
    }
    echo json_encode(['status' => 'success', 'thread' => $thread, 'messages' => $messages]);
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem']);
exit();

