<?php
// JSON tabanlı davet gönderim API'si
use App\Helper\Security;
use App\Services\MailGonderService;
use App\Services\SmsGonderService;
use Model\KisilerModel;

require_once __DIR__ . '/../../../configs/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$logger = \getLogger();

// Basit yetki kontrolü: oturum ve site_id gerekli
if (!isset($_SESSION['user']) || !isset($_SESSION['site_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim.']);
    exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true) ?? [];
$action = $payload['action'] ?? '';

function ok($msg, $extra = []) {
    echo json_encode(array_merge(['status' => 'success', 'message' => $msg], $extra));
    exit;
}
function err($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['status' => 'error', 'message' => $msg]);
    exit;
}

try {
    switch ($action) {
        case 'send_invite_email': {
            $encKisiId = $payload['kisi_id'] ?? '';
            $email = trim($payload['email'] ?? '');
            $link = trim($payload['link'] ?? '');
            if (!$encKisiId || !$email || !$link) {
                err('Eksik parametre (kisi_id, email, link).');
            }
            $kisiId = (int) Security::decrypt($encKisiId);
            if ($kisiId <= 0) err('Kişi bulunamadı.');

            $Kisiler = new KisilerModel();
            $kisi = $Kisiler->KisiBilgileri($kisiId);
            $ad = $kisi->adi_soyadi ?? 'Sakin';

            $konu = 'YonApp - Programa Giriş Davetiniz';
            $icerik = '<p>Merhaba ' . htmlspecialchars($ad) . ',</p>' .
                '<p>YonApp sistemine giriş için davet linkiniz aşağıdadır. Kayıt veya giriş işleminizi bu link üzerinden tamamlayabilirsiniz.</p>' .
                '<p><a href="' . htmlspecialchars($link) . '" target="_blank">Davet Linki</a></p>' .
                '<p>Teşekkürler,<br>YonApp</p>';

            $ok = MailGonderService::gonder([$email], $konu, $icerik);
            if ($ok) {
                ok('E-posta gönderildi.');
            } else {
                err('E-posta gönderilemedi.');
            }
            break;
        }
        case 'send_invite_sms': {
            $encKisiId = $payload['kisi_id'] ?? '';
            $phone = preg_replace('/\D+/', '', (string)($payload['phone'] ?? ''));
            $link = trim($payload['link'] ?? '');
            if (!$encKisiId || !$phone || !$link) {
                err('Eksik parametre (kisi_id, phone, link).');
            }
            $kisiId = (int) Security::decrypt($encKisiId);
            if ($kisiId <= 0) err('Kişi bulunamadı.');

            // TR numara normalize
            if (strlen($phone) === 10) { $phone = '0' . $phone; }
            $mesaj = 'YonApp giris davet linkiniz: ' ;
            $logger->info("Davet SMS gönderimi için numara: $phone, mesaj: $mesaj");

            $ok = SmsGonderService::gonder([$phone], $mesaj);
            if ($ok) {
                ok('SMS gönderildi.');
            } else {
                err('SMS gönderilemedi.');
            }
            break;
        }
        default:
            err('Geçersiz aksiyon.');
    }
} catch (Throwable $e) {
    err('Sunucu hatası: ' . $e->getMessage(), 500);
}
