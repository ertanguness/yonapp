<?php

namespace App\Services;

use Exception;
use Model\SettingsModel;
use App\Services\FileLogger;

$logger = new FileLogger('sms_gonder.log');



class SmsGonderService
{
    public static function gonder(array $alicilar, string $mesaj, string $gondericiBaslik = null): bool
    {
        $ch = null;
        $logger = \getLogger();
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
            $logger->info(json_encode([
                "site_id" => $_SESSION['site_id'],
                "settings" => $allSettings
            ]));

            $username =  $allSettings['sms_api_kullanici'] ?? '';
            $password =  $allSettings['sms_api_sifre'] ?? '';
            $msgheader = $gondericiBaslik ?? $allSettings['sms_baslik'] ?? 'YONAPP';
         
            if (empty($username) || empty($password)) {
                $response = json_encode([
                    'status' => 'error',
                    'message' => 'SMS API kimlik bilgileri eksik.'
                ]);
                echo ($response);
                exit;
                //throw new Exception("SMS API kimlik bilgileri eksik.");

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
                $logger->info(count($alicilar) . " alıcıya başarıyla SMS gönderildi.");
                return true;
            } else {
                throw new Exception('Netgsm API Hatası: ' . ($netgsmResult['description'] ?? 'Bilinmeyen hata.'));
            }

        } catch (Exception $e) {
            $logger->error("SMS gönderim hatası: " . $e->getMessage());
            return false;
        } finally {
            if (isset($ch) && is_resource($ch)) {
                curl_close($ch);
            }
        }
    }
}