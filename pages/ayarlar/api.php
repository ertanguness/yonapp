<?php
require_once dirname(__DIR__, levels: 2) . '/configs/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

use Model\SettingsModel;
use App\Helper\Security;

$Settings = new SettingsModel();

$site_id = $_SESSION["site_id"] ?? 0;
$user_id = (isset($_SESSION['user']) && isset($_SESSION['user']->id)) ? (int)$_SESSION['user']->id : 0;

if (($_POST["action"] ?? '') === "ayarlar_kaydet") {
    try {
        $kv = $Settings->getAllSettingsAsKeyValue() ?? [];
        $smtpDurum = array_key_exists('emailDurum', $_POST) ? (trim($_POST['emailDurum']) === '1' ? '1' : '0') : (string)($kv['smtp_durum'] ?? '0');
        $smsDurum  = array_key_exists('smsDurum', $_POST) ? (trim($_POST['smsDurum']) === '1' ? '1' : '0') : (string)($kv['sms_durum'] ?? '0');
        $waDurum   = array_key_exists('whatsappDurum', $_POST) ? (trim($_POST['whatsappDurum']) === '1' ? '1' : '0') : (string)($kv['whatsapp_durum'] ?? '0');

        $pairs = [
            'yetkili_adi_soyadi' => ['value' => trim($_POST['yetkiliAdiSoyadi'] ?? ''), 'aciklama' => 'Genel: Yetkili adı soyadı'],
            'eposta'             => ['value' => trim($_POST['eposta'] ?? ''), 'aciklama' => 'Genel: E-posta adresi'],
            'telefon'            => ['value' => trim($_POST['telefon'] ?? ''), 'aciklama' => 'Genel: Telefon'],
            'acil_iletisim'      => ['value' => trim($_POST['acilIletisim'] ?? ''), 'aciklama' => 'Genel: Acil iletişim'],

            'smtp_server'        => ['value' => trim($_POST['smtpServer'] ?? ''), 'aciklama' => 'E-posta: SMTP sunucusu'],
            'smtp_port'          => ['value' => trim($_POST['smtpPort'] ?? ''), 'aciklama' => 'E-posta: SMTP port'],
            'smtp_user'          => ['value' => trim($_POST['smtpUser'] ?? ''), 'aciklama' => 'E-posta: kullanıcı'],
            'smtp_password'      => ['value' => trim($_POST['smtpPassword'] ?? ''), 'aciklama' => 'E-posta: şifre'],
            'smtp_durum'         => ['value' => $smtpDurum, 'aciklama' => 'E-posta: aktif mi'],

            'sms_provider'       => ['value' => trim($_POST['smsProvider'] ?? ''), 'aciklama' => 'SMS: servis sağlayıcı'],
            'sms_username'       => ['value' => trim($_POST['smsUsername'] ?? ''), 'aciklama' => 'SMS: kullanıcı adı'],
            'sms_password'       => ['value' => trim($_POST['smsPassword'] ?? ''), 'aciklama' => 'SMS: şifre'],
            'sms_durum'          => ['value' => $smsDurum, 'aciklama' => 'SMS: aktif mi'],

            'whatsapp_api_url'   => ['value' => trim($_POST['whatsappApiUrl'] ?? ''), 'aciklama' => 'WhatsApp: API URL'],
            'whatsapp_token'     => ['value' => trim($_POST['whatsappToken'] ?? ''), 'aciklama' => 'WhatsApp: token'],
            'whatsapp_sender'    => ['value' => trim($_POST['whatsappSender'] ?? ''), 'aciklama' => 'WhatsApp: gönderen'],
            'whatsapp_durum'     => ['value' => $waDurum, 'aciklama' => 'WhatsApp: aktif mi'],
        ];

        $Settings->upsertPairs((int)$site_id, $user_id, $pairs);

        echo json_encode([
            'status' => 'success',
            'message' => 'Site Ayarları başarıyla kaydedildi.'
        ]);
    } catch (\Throwable $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Ayarlar kaydedilirken hata oluştu',
        ]);
    }
}

if (($_GET["action"] ?? '') === 'iletisim_list') {
    $items = [];
    $rows = $Settings->getAllRowsBySite((int)$site_id);
    $map = [];
    foreach ($rows as $r) {
        $name = $r['set_name'] ?? '';
        if (!preg_match('/^(.*)_(email|sms|whatsapp)$/', $name, $m)) { continue; }
        $key = $m[1];
        $ch = $m[2];
        if (!isset($map[$key])) { $map[$key] = ['key'=>$key,'label'=>str_replace('_',' ',$key),'email'=>'0','sms'=>'0','whatsapp'=>'0']; }
        $map[$key][$ch] = ($r['set_value'] ?? '0');
    }
    foreach ($map as $it) { $items[] = $it; }
    echo json_encode(['status'=>'success','items'=>$items]);
    exit;
}

if (($_POST["action"] ?? '') === 'iletisim_upsert') {
    $key = strtolower(trim($_POST['key'] ?? ''));
    $label = trim($_POST['label'] ?? str_replace('_',' ',$key));
    if ($key === '') { echo json_encode(['status'=>'error']); exit; }
    $pairs = [
        $key.'_email'    => ['value' => '0', 'aciklama' => $label.' email'],
        $key.'_sms'      => ['value' => '0', 'aciklama' => $label.' sms'],
        $key.'_whatsapp' => ['value' => '0', 'aciklama' => $label.' whatsapp'],
    ];
    $Settings->upsertPairs((int)$site_id, $user_id, $pairs);
    echo json_encode(['status'=>'success']);
    exit;
}

if (($_POST["action"] ?? '') === 'iletisim_toggle') {
    $key = strtolower(trim($_POST['key'] ?? ''));
    $ch = strtolower(trim($_POST['channel'] ?? ''));
    $val = trim($_POST['value'] ?? '0') === '1' ? '1' : '0';
    if ($key === '' || !in_array($ch, ['email','sms','whatsapp'], true)) { echo json_encode(['status'=>'error']); exit; }
    $Settings->upsertPairs((int)$site_id, $user_id, [ $key.'_'.$ch => ['value' => $val, 'aciklama' => $key.' '.$ch] ]);
    echo json_encode(['status'=>'success']);
    exit;
}

if (($_POST["action"] ?? '') === 'iletisim_delete') {
    $key = strtolower(trim($_POST['key'] ?? ''));
    if ($key === '') { echo json_encode(['status'=>'error']); exit; }
    $ok = $Settings->deleteBySetNamePrefix((int)$site_id, $key.'_');
    echo json_encode(['status' => $ok ? 'success' : 'error']);
    exit;
}

if ($_POST["action"] == "sil-ayarlar") {
    // Opsiyonel: belirli bir set_name silme
    // $Settings->deleteByColumn('id', Security::decrypt($_POST['id']));

    $res = [
        "status" => "success",
        "message" => "Başarılı"
    ];
    echo json_encode($res);
}
