<?php

require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';

use App\Services\SmsGonderService;
use Model\SmsModel;
use Database\Db;



// Yanıt için standart bir yapı oluşturun.
$apiResponse = [
    'status' => 'error', // Varsayılan durum
    'message' => 'Bilinmeyen bir hata oluştu.',
    'data' => null
];


$postData = json_decode(file_get_contents('php://input'), true);

// Gelen verileri değişkenlere ata ve doğrula
$messageText = $postData['message'] ?? ''; // Varsayılan mesaj
$recipients = $postData['recipients'] ?? [''];
$msgheader = $postData['senderID'] ?? 'USKUPEVLSIT'; // Varsayılan başlık

$db = Db::getInstance();
$SmsModel = new SmsModel();

if (empty($messageText) || !is_string($messageText)) {
    throw new Exception("Geçerli bir mesaj metni gönderilmedi.");
}
if (empty($recipients) || !is_array($recipients)) {
    throw new Exception("Alıcı listesi boş veya geçersiz formatta.");
}


if (SmsGonderService::gonder(
    alicilar: $recipients,
    mesaj: $messageText,
    gondericiBaslik: $msgheader,
)) {
    $apiResponse['status'] = 'success';
    $apiResponse['message'] = count($recipients) . ' alıcıya başarıyla SMS gönderildi.';
    try {
        $db->beginTransaction();
        
        $data = [
            'type' => 'sms',
            'recipients' => json_encode($recipients, JSON_UNESCAPED_UNICODE),
            'subject' => null,
            'message' => $messageText,
            'status' => 'success',
        ];
        $SmsModel->saveWithAttr($data);
        $db->commit();
    } catch (Exception $e) {
        $db->rollBack();
        $apiResponse['message'] = 'SMS gönderilemedi. Hata: ' . $e->getMessage();
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
