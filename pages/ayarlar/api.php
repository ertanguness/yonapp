<?php
require_once dirname(__DIR__, levels: 2) . '/configs/bootstrap.php';

use Model\SettingsModel;
use App\Helper\Security;

$Settings = new SettingsModel();

$site_id = $_SESSION["site_id"] ?? 0;
$user_id = $_SESSION['user']->id ?? null;
$mevcut = $Settings->Ayarlar();
$ayar_id = $mevcut->id ?? null;

if ($_POST["action"] == "ayarlar_kaydet") {
    $data = [
        "id"                => $ayar_id,
        "site_id"           => (int)$site_id,
        "yetkili_adi_soyadi"=> trim($_POST["yetkiliAdiSoyadi"] ?? ''),
        "eposta"            => trim($_POST["eposta"] ?? ''),
        "telefon"           => trim($_POST["telefon"] ?? ''),
        "acil_iletisim"     => trim($_POST["acilIletisim"] ?? ''),

        "smtp_server"       => trim($_POST["smtpServer"] ?? ''),
        "smtp_port"         => trim($_POST["smtpPort"] ?? ''),
        "smtp_user"         => trim($_POST["smtpUser"] ?? ''),
        "smtp_password"     => trim($_POST["smtpPassword"] ?? ''),
        "smtp_durum"        => trim($_POST["emailDurum"] ?? '0'),

        "sms_provider"      => trim($_POST["smsProvider"] ?? ''),
        "sms_username"      => trim($_POST["smsUsername"] ?? ''),
        "sms_password"      => trim($_POST["smsPassword"] ?? ''),
        "sms_durum"         => trim($_POST["smsDurum"] ?? '0'),

        "whatsapp_api_url"  => trim($_POST["whatsappApiUrl"] ?? ''),
        "whatsapp_token"    => trim($_POST["whatsappToken"] ?? ''),
        "whatsapp_sender"   => trim($_POST["whatsappSender"] ?? ''),
        "whatsapp_durum"    => trim($_POST["whatsappDurum"] ?? '0'),
        "kayit_tarihi"      => date('Y-m-d H:i:s'),
    ];

    if (!empty($ayar_id)) {
        $data["guncelleme_tarihi"] = date('Y-m-d H:i:s');
    }

    $Settings->saveWithAttr($data);

    echo json_encode([
        'status' => 'success',
        'message' => 'Site Ayarları başarıyla kaydedildi.'
    ]);
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
