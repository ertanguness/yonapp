<?php
// App/Controllers/RegisterActivateController.php
namespace App\Controllers;

use Model\UserModel;
use App\Services\Gate;
use App\Helper\Security;
use App\Services\MailGonderService;
use App\Services\FlashMessageService;

class RegisterActivateController
{
    public static function handleActivation(array $request)
    {
        $User = new UserModel();
        $token_renegate = false;
        if (isset($request["action"]) && $request["action"] == 'token_renegate') {
            $email = $request["email"];
            $user = $User->checkToken($email);
            if (empty($user)) {
                FlashMessageService::add('error', 'Hata!', 'Kullanıcı Bulunamadı');
            } else {
                $token = Security::encrypt(time() + 3600);
                $data = [
                    'id' => $user->id,
                    'activate_token' => $token,
                    'status' => 0
                ];
                $User->setActivateToken($data);

                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $baseUrl = $protocol . '://' . $host;

                $activate_link = "$baseUrl/register-activate.php?email=" . ($email) . "&token=" . $token;

                // Burada mail gönderim servisi çağrılabilir
                if (MailGonderService::gonder([$email], $user->full_name, $activate_link)) {
                    FlashMessageService::add('success', 'Başarılı!', 'Yeni aktivasyon e-postası gönderildi.');
                } else {
                    FlashMessageService::add('error', 'Hata!', 'Aktivasyon e-postası gönderilemedi. Lütfen daha sonra tekrar deneyiniz.');
                }
                $token_renegate = true;
            }
        } else if (isset($request['token']) && isset($request['email'])) {
            $token = $request['token'];
            $email = $request['email'];
            $user = $User->checkToken($email);
            $token_dec = Security::decrypt($token);
            if (empty($user)) {
                FlashMessageService::add('error', 'Hata!', 'Kullanıcı Bulunamadı');
            } elseif ($token_dec < time() || $user->activate_token != urlencode($token)) {
                FlashMessageService::add('error', 'Hata!', 'Geçersiz Token!');
                $token_renegate = true;
            } elseif (empty($token_dec)) {
                FlashMessageService::add('error', 'Hata!', 'Token bilgisi boş');
            } elseif (empty($email)) {
                FlashMessageService::add('error', 'Hata!', 'Email bilgisi boş');
            } elseif ($user->status == 1) {
                FlashMessageService::add('info', 'Bilgi', 'Kullanıcı zaten aktif');
            } else {
                $User->ActivateUser($email);
                FlashMessageService::add('success', 'Başarılı!', 'Hesabınız başarı ile aktifleştirildi!',"onay2.png");
                
                /**Site sakini ise mail metnine sakin ekle */
                //$sakin = Gate::isResident() ? " (Site Sakini)" : "";

                MailGonderService::gonder(["beyzade83@gmail.com","bilgekazaz@gmail.com","ertanguness@gmail.com"], $user->full_name, $user->full_name .  $sakin . " isimli kullanıcı hesabını aktifleştirdi.");
            }
        }
        return $token_renegate;
    }
}
