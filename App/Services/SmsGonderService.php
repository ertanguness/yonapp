<?php

namespace App\Services;

use Exception;
use Model\SettingsModel;

class SmsGonderService
{
    public static function gonder(array $alicilar, string $mesaj, string $gondericiBaslik = null): bool
    {
        $ch = null;
        
        try {
            // Validasyon
            if (empty($alicilar)) {
                throw new Exception("Alıcı listesi boş olamaz.");
            }
            if (empty(trim($mesaj))) {
                throw new Exception("Mesaj metni boş olamaz.");
            }

            // API bilgilerini al (öncelik: env > ayarlar > varsayılan)
            $Settings = new SettingsModel();
            $allSettings = $Settings->getAllSettingsAsKeyValue();

            $username = $_ENV['NETGSM_USER'] ?? $allSettings['sms_api_kullanici'] ?? '8503070380';
            $password = $_ENV['NETGSM_PASS'] ?? $allSettings['sms_api_sifre'] ?? '633F8#7';
            $msgheader = $gondericiBaslik ?? $_ENV['NETGSM_HEADER'] ?? $allSettings['sms_baslik'] ?? 'YONAPP';

            if (empty($username) || empty($password)) {
                throw new Exception("SMS API kimlik bilgileri eksik.");
            }

            // Mesaj dizisini oluştur
            $messagesPayload = [];
            foreach ($alicilar as $numara) {
                $messagesPayload[] = [
                    'msg' => $mesaj,
                    'no' => (string)$numara
                ];
            }
            
            // API verisini hazırla
            $data = [
                "msgheader" => $msgheader,
                "messages" => $messagesPayload,
                "encoding" => "TR",
            ];

            // cURL ile gönder
            $url = "https://api.netgsm.com.tr/sms/rest/v2/send";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode($username . ':' . $password)
            ]);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                throw new Exception('cURL Hatası: ' . curl_error($ch));
            }

            $netgsmResult = json_decode($response, true);

            if (isset($netgsmResult['code']) && $netgsmResult['code'] == '00') {
                echo count($alicilar) . " alıcıya SMS gönderildi.";
                return true;
            } else {
                throw new Exception('Netgsm API Hatası: ' . ($netgsmResult['description'] ?? 'Bilinmeyen hata.'));
            }

        } catch (Exception $e) {
            echo "SMS gönderme hatası: " . $e->getMessage() . "<br>";
            error_log("SMS gönderilemedi. Hata: " . $e->getMessage());
            return false;
        } finally {
            if (isset($ch) && is_resource($ch)) {
                curl_close($ch);
            }
        }
    }
}