<!-- Sms gönderim kodu Twilioya göre yapıldı hangi sağlayacı olursa ona göre ayarlanacak -->
<?php

require 'vendor/autoload.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipientIds = $_POST['recipientIds']; // Birden fazla kişi seçildiği için dizi gelir
    $message = $_POST['message'];
    $subject = $_POST['subject'];
    
    // SMS API entegrasyonu için döngü ile alıcılar üzerinde işlem yapılır
    foreach ($recipientIds as $recipientId) {
        // Veritabanından alıcının telefon numarasını al
        $recipient = getRecipientById($recipientId); // Örnek bir fonksiyon
        $phoneNumber = $recipient['phone'];

        // SMS gönderim işlemi
        sendSMS($phoneNumber, $subject, $message);
    }

    echo "Mesajınız seçilen alıcılara başarıyla gönderildi.";
}

function sendSMS($phoneNumber, $subject, $message) {
    // SMS sağlayıcı API bilgileri
    $apiKey = "SMS_API_KEY";
    $apiUrl = "https://smsprovider.com/api/send";

    // API isteği için veriler
    $data = [
        'phone' => $phoneNumber,
        'message' => $message,
        'sender' => 'MyCompany'
    ];

    // API isteği
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

?>
