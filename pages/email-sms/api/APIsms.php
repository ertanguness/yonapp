<?php

require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';

use App\Services\SmsGonderService;
use Model\SmsModel;
use App\Helper\Security;
use App\Services\Gate;
use Model\KisilerModel;
use Model\FinansalRaporModel;
use App\Helper\Site;
use App\Helper\Helper;



// Yanıt için standart bir yapı oluşturun.
$apiResponse = [
    'status' => 'error', // Varsayılan durum
    'message' => 'Bilinmeyen bir hata oluştu.',
    'data' => null
];


$postData = json_decode(file_get_contents('php://input'), true);

// Gelen verileri değişkenlere ata ve doğrula
$messageText = $postData['message'] ?? '';
$recipients = $postData['recipients'] ?? [''];
// senderID/senderId her iki anahtar desteklenir
$msgheader = $postData['senderID'] ?? ($postData['senderId'] ?? 'USKUPEVLSIT');
$testMode = !empty($_ENV['SMS_TEST_MODE']);
// CSRF doğrulama (test modunda bypass edilir)
$csrfToken = $postData['csrf_token'] ?? '';
if (!$testMode) {
    if (!$csrfToken || !hash_equals((string)$csrfToken, (string)Security::csrf())) {
        http_response_code(403);
        $apiResponse['message'] = 'Geçersiz CSRF token';
        echo json_encode($apiResponse, JSON_UNESCAPED_UNICODE);
        exit;
    }
    // Yetki kontrolü (test modunda bypass)
    if (!Gate::allows('email_sms_gonder')) {
        http_response_code(403);
        $apiResponse['message'] = 'Bu işlem için yetkiniz yok.';
        echo json_encode($apiResponse, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$pdo = \getDbConnection();
$SmsModel = new SmsModel();
$KisiModel = new KisilerModel();
$FinModel = new FinansalRaporModel();

if (empty($messageText) || !is_string($messageText)) {
    throw new Exception("Geçerli bir mesaj metni gönderilmedi.");
}
if (empty($recipients) || !is_array($recipients)) {
    throw new Exception("Alıcı listesi boş veya geçersiz formatta.");
}

// Oran sınırı: dakikada en fazla 60 SMS (test modunda gevşet)
$limitPerMinute = 300;
$siteId = $_SESSION['site_id'] ?? 0;
$recentCount = 0;
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE type='sms' AND site_id = :sid AND created_at >= (NOW() - INTERVAL 1 MINUTE)");
    $stmt->execute([':sid' => $siteId]);
    $recentCount = (int)$stmt->fetchColumn();
} catch (Exception $e) { /* yoksay */ }
// if (!$testMode && (($recentCount + count($recipients)) > $limitPerMinute)) {
//     http_response_code(429);
//     $apiResponse['message'] = 'SMS gönderim limiti aşıldı. Lütfen daha sonra tekrar deneyin.';
//     echo json_encode($apiResponse, JSON_UNESCAPED_UNICODE);
//     exit;
// }


// Dinamik değişkenler: {ADISOYADI}, {BORÇBAKİYESİ}, {SİTEADI}
$siteHelper = new Site();
$siteRow = $siteHelper->getCurrentSite();
$siteName = $siteRow->site_adi ?? '';

$recipientIds = $postData['recipient_ids'] ?? $postData['ids'] ?? [];

$success = 0; $fail = 0; $errors = [];
foreach ($recipients as $idx => $telRaw) {
    $telDigits = preg_replace('/\D/','', (string)$telRaw);
    $kisiId = 0;
    if (is_array($recipientIds) && isset($recipientIds[$idx])) {
        $kisiId = (int)$recipientIds[$idx];
    }
    $adiSoyadi = '';
    $borcBakiye = '';
    $daireKodu = '';
    if ($kisiId) {
        $kisi = $KisiModel->getKisiByDaireId($kisiId);
        $adiSoyadi = $kisi->adi_soyadi ?? '';
        $daireKodu = $kisi->daire_kodu ?? '';
        try {
            $ozet = $FinModel->getKisiGuncelBorcOzet($kisiId);
            $borcBakiye = Helper::formattedMoneyWithoutCurrency((float)($ozet->guncel_borc ?? 0));
        } catch (\Throwable $e) { $borcBakiye = ''; }
    } else if ($telDigits) {
        try {
            $matches = $KisiModel->findWhere(['telefon' => $telDigits]);
            if (!empty($matches)) {
                $k = $matches[0];
                $adiSoyadi = $k->adi_soyadi ?? $adiSoyadi;
                $kisiIdGuess = (int)($k->id ?? 0);
                if ($kisiIdGuess) {
                    $kfull = $KisiModel->getKisiByDaireId($kisiIdGuess);
                    $daireKodu = $kfull->daire_kodu ?? $daireKodu;
                    try {
                        $ozet = $FinModel->getKisiGuncelBorcOzet($kisiIdGuess);
                        $borcBakiye = Helper::formattedMoneyWithoutCurrency((float)($ozet->guncel_borc ?? 0));
                    } catch (\Throwable $e) { /* yoksay */ }
                }
            }
        } catch (\Throwable $e) { /* yoksay */ }
    }
    $msg = str_replace([
        '{ADISOYADI}',
        '{BORÇBAKİYESİ}',
        '{SİTEADI}',
        '{DAİREKODU}',
        '{DAIREKODU}'
    ], [
        ($adiSoyadi ?: ''),
        ($borcBakiye ?: ''),
        ($siteName ?: ''),
        ($daireKodu ?: ''),
        ($daireKodu ?: '')
    ], $messageText);
    $sent = SmsGonderService::gonder([$telDigits ?: $telRaw], $msg, $msgheader);
    if ($sent) { $success++; } else { $fail++; $errors[] = $telRaw; }
}

if ($success > 0) {
    $apiResponse['status'] = 'success';
    $apiResponse['message'] = $success . ' alıcıya başarıyla SMS gönderildi.' . ($fail ? (' / Başarısız: ' . $fail) : '');
    try {
        $data = [
            'type' => 'sms',
            'site_id' => $_SESSION['site_id'],
            'recipients' => json_encode($recipients, JSON_UNESCAPED_UNICODE),
            'subject' => null,
            'message' => $msg,
            'status' => $fail ? 'partial' : 'success',
        ];
        $SmsModel->saveWithAttr($data);
    } catch (Exception $e) {
        $apiResponse['message'] = 'SMS gönderildi ancak log yazılırken hata oluştu: ' . $e->getMessage();
    }
} else {
    $apiResponse['message'] = 'SMS gönderilemedi.';
}

echo json_encode($apiResponse, JSON_UNESCAPED_UNICODE);



exit;












// // Betiğin çıktısının JSON olacağını en başta belirtin.
// header('Content-Type: application/json; charset=utf-8');

// use Model\SettingsModel;


// //kontrol için json döndür
// //echo json_encode(["status"=>"ok","dir" => dirname(__DIR__, 2)]); exit;
// try {
//     // 1. Adım: JavaScript'ten gönderilen JSON verisini alın.
//     $postData = json_decode(file_get_contents('php://input'), true);

//     // Gelen verileri değişkenlere ata ve doğrula
//     $messageText = $postData['message'] ?? ''; // Varsayılan mesaj
//     $recipients = $postData['recipients'] ?? [''];
//     $msgheader = $postData['senderID'] ?? 'USKUPEVLSIT'; // Varsayılan başlık

//     // 2. Adım: Netgsm API kimlik bilgilerini ayarla.
//     $username = $postData['username'] ?? '8503070380'; // Varsayılan kullanıcı adı
//     $password = $postData['password'] ?? '633F8#7'; // Varsayılan şifre




//     if (empty($username) || empty($password) || empty($msgheader)) {
//         //eğer passsword ve username boş ise ayarlardan al
//         $Settings = new SettingsModel();
//         // $allSettings = $Settings->getAllSettingsAsKeyValue();
//         // $username = $allSettings['sms_api_kullanici'] ?? '';
//         // $password = $allSettings['sms_api_sifre'] ?? '';
//         // $msgheader = $allSettings['sms_baslik'] ?? '';

//     }






//     // 3. Adım: Netgsm'e gönderilecek ana veri yapısını oluşturun.
//     $data = [
//         "msgheader" => $msgheader, // Gönderici başlığı
//         "messages" => $messagesPayload, // Dinamik olarak oluşturulan diziyi burada kullan
//         "encoding" => "TR",
//         "iysfilter" => "",
//         "partnercode" => ""
//     ];

//     // API URL'si ve kimlik bilgileri
//     $url = "https://api.netgsm.com.tr/sms/rest/v2/send";


//     // cURL işlemleri...
//     $ch = curl_init();
//     curl_setopt($ch, CURLOPT_URL, $url);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_POST, true);
//     curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
//     curl_setopt($ch, CURLOPT_HTTPHEADER, [
//         'Content-Type: application/json',
//         'Authorization: Basic ' . base64_encode($username . ':' . $password)
//     ]);

//     $response = curl_exec($ch);

//     if (curl_errno($ch)) {
//         throw new Exception('cURL Hatası: ' . curl_error($ch));
//     }

//     $netgsmResult = json_decode($response, true);

//     if (isset($netgsmResult['code']) && $netgsmResult['code'] == '00') {
//         $apiResponse['status'] = 'success';
//         $apiResponse['message'] = count($recipients) . ' alıcıya SMS gönderim kuyruğuna alındı.';
//         $apiResponse['data'] = $netgsmResult;
//         $apiResponse["postdata"] = $data; // Gönderilen veriyi de yanıt olarak ekle
//     } else {
//         $apiResponse['message'] = 'Netgsm API Hatası: ' . ($netgsmResult['description'] ?? 'Bilinmeyen hata.');
//         $apiResponse['data'] = $netgsmResult;
//     }
// } catch (Exception $e) {
//     // Hataları yakala ve JSON olarak döndür
//     $apiResponse['message'] = $e->getMessage();
// } finally {
//     if (isset($ch) && is_resource($ch)) {
//         curl_close($ch);
//     }
// }

// // Son olarak, standart API yanıtını JSON formatında yazdır.
// echo json_encode($apiResponse, JSON_UNESCAPED_UNICODE);
