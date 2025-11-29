<?php
require_once __DIR__ . '/../../../configs/bootstrap.php';

use App\Services\InviteLinkService;
use App\Services\MailGonderService;
use App\Services\SmsGonderService;
use App\Helper\Security;
use Model\UserModel;

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $input['action'] ?? '';
$encKisi = $input['kisi_id'] ?? '';
$email = trim($input['email'] ?? '');
$phone = preg_replace('/\D+/', '', (string)($input['phone'] ?? ''));
$kisiId = Security::decrypt($encKisi);

function jsonOut($arr){ header('Content-Type: application/json'); echo json_encode($arr); exit; }
if (!$kisiId) { jsonOut(['status'=>'error','message'=>'Geçersiz kişi']); }

$fullLink = InviteLinkService::buildFullLink($kisiId, $email ?: null, $phone ?: null, $action);
$shortLink = InviteLinkService::createShortLink($fullLink, $action);

if ($action === 'send_invite_email') {
    if (empty($email)) {
        if (!empty($phone)) {
            jsonOut(['status'=>'error','message'=>'E-posta yok, WhatsApp/SMS ile davet önerilir','alt_link'=>$shortLink]);
        } else {
            jsonOut(['status'=>'error','message'=>'Kişinin iletişim bilgisi yok']);
        }
    }
    $logo = InviteLinkService::baseUrl() . '/assets/images/logo/logo.svg';
    $html = '<div style="font-family:Arial,Helvetica,sans-serif;background:#f3f4f6;padding:32px">'
          . '<div style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;padding:32px;box-shadow:0 10px 24px rgba(0,0,0,.05)">'
          . '<div style="text-align:center;margin-bottom:16px"><img src="' . $logo . '" alt="YonApp" style="height:36px"></div>'
          . '<h1 style="margin:8px 0 16px;color:#111827;font-size:24px;line-height:1.25;text-align:center">YonApp’e Hoş Geldiniz!</h1>'
          . '<p style="margin:0 0 20px;color:#374151;font-size:15px;line-height:1.6;text-align:center">Site yönetiminiz tarafından size bir <strong>YonApp hesabı</strong> oluşturuldu. Aşağıdaki bağlantıya tıklayarak hesabınızı aktif edebilir ve giriş yapabilirsiniz.</p>'
          . '<div style="text-align:center;margin:24px 0"><a href="' . $fullLink . '" style="background:#2563eb;color:#fff;text-decoration:none;padding:14px 22px;border-radius:8px;display:inline-block;font-weight:600">Hesabımı Aktifleştir</a></div>'
          . '<p style="margin:0 0 20px;color:#6b7280;font-size:13px;line-height:1.6;text-align:center">Bu bağlantı yalnızca sizin için oluşturulmuştur ve güvenli bir şekilde şifre oluşturmanızı sağlar.</p>'
          . '<hr style="border:none;border-top:1px solid #e5e7eb;margin:20px 0">'
          . '<p style="margin:0;color:#9ca3af;font-size:12px;text-align:center">Bağlantı çalışmazsa bu URL’yi tarayıcınıza kopyalayın:<br><a href="' . $fullLink . '" style="color:#2563eb;text-decoration:underline">' . htmlspecialchars($fullLink) . '</a></p>'
          . '<p style="margin-top:16px;color:#9ca3af;font-size:12px;text-align:center">Bu e‑posta otomatik olarak gönderilmiştir. Yanıtlamanız gerekmez.<br>© 2025 YonApp — Site ve Apartman Yönetim Sistemi</p>'
          . '</div></div>';
    $ok = MailGonderService::gonder([$email], 'YonApp Davet', $html);
    jsonOut($ok?['status'=>'success','message'=>'E-posta gönderildi','link'=>$fullLink]:['status'=>'error','message'=>'E-posta gönderilemedi']);
}

if ($action === 'send_invite_sms') {
    if (empty($phone)) { jsonOut(['status'=>'error','message'=>'Telefon bulunamadı']); }
    if (strlen($phone) < 10) { jsonOut(['status'=>'error','message'=>'Telefon formatı hatalı']); }
    $msg = 'YonApp giriş davet linkiniz: ' . $shortLink;
    $ok = SmsGonderService::gonder(['+' . $phone], $msg);
    jsonOut($ok?['status'=>'success','message'=>'SMS gönderildi','link'=>$shortLink]:['status'=>'error','message'=>'SMS gönderilemedi']);
}

if ($action === 'auto_invite') {
    $Users = new UserModel();
    if ($email) {
        $ok = MailGonderService::gonder([$email], 'YonApp Davet', $fullLink);
        jsonOut($ok?['status'=>'success','message'=>'E-posta gönderildi','link'=>$fullLink]:['status'=>'error','message'=>'E-posta gönderilemedi','alt_sms'=>$shortLink]);
    } elseif ($phone) {
        $msg = 'YonApp davetiniz: ' . $shortLink;
        $ok = SmsGonderService::gonder(['+' . $phone], $msg);
        jsonOut($ok?['status'=>'success','message'=>'SMS gönderildi','link'=>$shortLink]:['status'=>'error','message'=>'SMS gönderilemedi']);
    } else {
        jsonOut(['status'=>'error','message'=>'Kişi bilgileri eksik']);
    }
}

if ($action === 'generate_short_link') {
    jsonOut(['status'=>'success','short_link'=>$shortLink,'full_link'=>$fullLink]);
}

jsonOut(['status'=>'error','message'=>'Geçersiz işlem']);
