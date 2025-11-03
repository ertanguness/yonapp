<?php

namespace App\Services;


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;



class MailGonderService
{
    public static function gonder(
        array $kime,
        string $konu,
        string $icerik,
        array $ekler = [],
        array $cc = [],
        array $bcc = []
    ): bool {
        $mail = new PHPMailer(true);


        try {
            // Sunucu Ayarları
            $mail->isSMTP();

            $mail->Host       = $_ENV['SMTP_HOST']; // Kendi SMTP sunucunuz (örn: smtp.gmail.com)
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USER']; // SMTP kullanıcı adınız

            $mail->Password   = $_ENV['SMTP_PASSWORD'];           // SMTP şifreniz
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Veya ENCRYPTION_SMTPS
            $mail->Port       = $_ENV['SMTP_PORT']; // Veya 465


            // SSL Doğrulama Ayarları (Önemli!)
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            // KARAKTER SETİ AYARI (ÇOK ÖNEMLİ)
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64'; // İçeriği base64 ile kodlamak uyumluluğu artırır

            // Gönderen ve Alıcı Bilgileri
            $mail->setFrom('bilgi@yonapp.com.tr', 'YonApp'); // Gönderen e-posta ve isim

            // ÖNEMLİ DEĞİŞİKLİK: BCC kullan (alıcılar birbirini görmez)
            // TO alanına bir dummy adres koy (zorunlu)
           // $mail->addAddress('bilgi@yonapp.com.tr'); // Görünen alıcı (dummy)

            // Asıl alıcıları BCC'ye ekle, böylece birbirlerini görmezler
            if (is_array($kime)) {
                foreach ($kime as $email) {
                    $mail->addAddress(trim($email)); // BCC ile ekle - birbirlerini görmezler
                }
            } else {
                $mail->addAddress(trim($kime));
            }


            // CC ekleme
            if (!empty($cc)) {
               if (!is_array($cc)) {
                   $cc = [$cc];
               }
                foreach ($cc as $ccEmail) {
                    $mail->addCC(trim($ccEmail));
                }
            }

            // BCC ekleme
            if (!empty($bcc)) {
                if (!is_array($bcc)) {
                    $bcc = [$bcc];
                }
                foreach ($bcc as $bccEmail) {
                    $mail->addBCC(trim($bccEmail));
                }
            }

            //$mail->addAddress($kime); // Alıcı e-posta adresi

            // İçerik
            $mail->isHTML(true);
            $mail->Subject = $konu;         
            $mail->Body    = $icerik;
            $mail->AltBody = strip_tags($icerik); // HTML desteklemeyen istemciler için

            //eğer ekler boş değilse foreach ile ekleri ekle
            if (!empty($ekler)) {
                foreach ($ekler as $ek) {
                    $mail->addAttachment($ek);
                }
            }

            $mail->send();
            return true;
        } catch (Exception $e) {
            echo "Mail gönderme hatası: " . $mail->ErrorInfo . "<br>";
            echo "Exception: " . $e->getMessage() . "<br>";
            error_log("E-posta gönderilemedi. Hata: {$mail->ErrorInfo}");
            return false;
        }
    }
}
