<?php
require_once dirname(__DIR__, levels: 2) . '/configs/bootstrap.php';

use Model\AyarlarModel;
use App\Helper\Security;

$Ayarlar = new AyarlarModel();
$AyarlarBilgileri = $Ayarlar->Ayarlar();

$site_id = $_SESSION["site_id"] ?? null;
$ayar_id = $AyarlarBilgileri->id?? null; 

if ($_POST["action"] == "ayarlar_kaydet") {

    

    $data = [
        "id"                => $ayar_id,
        "site_id"           => $site_id,
        "eposta"            => trim($_POST["eposta"] ?? ''),
        "telefon"           => trim($_POST["telefon"] ?? ''),
        "acil_iletisim"     => trim($_POST["acilIletisim"] ?? ''),
        "smtp_server"       => trim($_POST["smtpServer"] ?? ''),
        "smtp_port"         => trim($_POST["smtpPort"] ?? ''),
        "smtp_user"         => trim($_POST["smtpUser"] ?? ''),
        "smtp_password"     => trim($_POST["smtpPassword"] ?? ''),
        "smtp_durum"        => trim($_POST["emailDurum"] ?? ''),
        "sms_provider"      => trim($_POST["smsProvider"] ?? ''),
        "sms_username"      => trim($_POST["smsUsername"] ?? ''),
        "sms_password"      => trim($_POST["smsPassword"] ?? ''),
        "sms_durum"         => trim($_POST["smsDurum"] ?? ''),
        "whatsapp_api_url"  => trim($_POST["whatsappApiUrl"] ?? ''),
        "whatsapp_token"    => trim($_POST["whatsappToken"] ?? ''),
        "whatsapp_sender"   => trim($_POST["whatsappSender"] ?? ''),
        "whatsapp_durum"    => trim($_POST["whatsappDurum"] ?? ''),
        "kayit_tarihi"      => date('Y-m-d H:i:s'),
    ];
    if (!empty($id)) {
        $data["guncelleme_tarihi"] = date('Y-m-d H:i:s');
    }
    $lastInsertId = $Ayarlar->saveWithAttr($data);

    $res = [
        "status" => "success",
        "message" => "Site Ayarları başarıyla kaydedildi.",
    ];
    echo json_encode($res);
}

if ($_POST["action"] == "sil-ayarlar") {
    $Defines->delete($_POST["id"]);

    $res = [
        "status" => "success",
        "message" => "Başarılı"
    ];
    echo json_encode($res);
}
