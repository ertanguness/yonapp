<?php
require_once dirname(__DIR__ ,levels: 4). '/configs/bootstrap.php';

use App\Helper\Date;
use App\Helper\Security;
use App\Services\FlashMessageService;
use App\Controllers\AuthController; // AuthController'ı da kullanabiliriz
use Model\GuvenlikPersonelModel;

$Personel = new GuvenlikPersonelModel();

if ($_POST["action"] == "guvenlikPersonel_kaydetme") {
    
    $baslama_tarihi     = Date::Ymd($_POST["baslama_tarihi"] ?? '');
    $dogum_tarihi      = Date::Ymd($_POST["dogum_tarihi"] ?? '');
    $bitis_tarihi      = Date::Ymd($_POST["bitis_tarihi"] ?? '');

    $id = Security::decrypt($_POST["id"]);
    $data = [
        "id"                => $id,
        "site_id"           => $_SESSION["site_id"] ?? 0,
        "adi_soyadi"        => $_POST["adi_soyadi"] ?? '',
        "tc_kimlik_no"      => $_POST["tc_kimlik_no"] ?? '',
        "dogum_tarihi"      => $dogum_tarihi,
        "cinsiyet"          => $_POST["cinsiyet"] ?? '',
        "telefon"           => $_POST["telefon"] ?? '',
        "eposta"            => $_POST["eposta"] ?? '',
        "adres"             => $_POST["adres"] ?? '',
        "gorev_yeri"        => $_POST["gorev_yeri"] ?? '',
        "durum"             => $_POST["durum"] ?? '1',
        "baslama_tarihi"    => $baslama_tarihi,
        "bitis_tarihi"      => $bitis_tarihi,
        "acil_kisi"         => $_POST["acil_kisi"] ?? '',
        "yakinlik"          => $_POST["yakınlik"] ?? '',
        "acil_telefon"      => $_POST["acil_telefon"] ?? '',
        "kayit_tarihi"      => date('Y-m-d H:i:s'),
    ];
    if (!empty($id)) {
        $data["guncelleme_tarihi"] = date('Y-m-d H:i:s');
    }
    $lastInsertId = $Personel->saveWithAttr($data);

    $res = [
        "status" => "success",
        "message" => "Başarılı",        
        "decrypted_id" => $id // çözümlenmiş ID’yi cevaba ekle

    ];
    echo json_encode($res);
}

if ($_POST["action"] == "sil-guvenlikPersoneli") {
    
        $logger = \getLogger();
    
        // Loglama için gerekli bilgileri topla
        $currentUser = AuthController::user(); // Giriş yapmış kullanıcıyı al
        $Personel->delete($_POST["id"]);
            
            $logger->info("Güvenlik personel kaydı silindi.", [
                'deleted_personel_id' =>Security::decrypt($_POST["id"]), // Şifreli ID'yi de loglamak iyi olabilir
                'deleted_by_user_id' => $currentUser->id,
                'user_email' => $currentUser->email,
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);

             // --- BAŞARI MESAJI VE FLASH MESAJ ---
            // Kullanıcı bir sonraki sayfada bir başarı mesajı görecek.
            FlashMessageService::add(
                'success',
                'İşlem Başarılı',
                'Güvenlik Personel kaydı başarıyla silinmiştir.',
                'onay2.png'
            );

    $res = [
        "status" => "success",
        "message" => "Başarılı"
    ];
    echo json_encode($res);
}
