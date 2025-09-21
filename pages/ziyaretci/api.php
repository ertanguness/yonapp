<?php
require_once dirname(__DIR__ ,levels: 2). '/configs/bootstrap.php';

use App\Helper\Date;
use App\Helper\Security;
use App\Services\FlashMessageService;
use App\Controllers\AuthController; // AuthController'ı da kullanabiliriz
use Model\ZiyaretciModel;

$Ziyaretci = new ZiyaretciModel();

if ($_POST["action"] == "ziyaretci_kaydetme") {
    
    $giris_tarihi     = Date::Ymd($_POST["giris_tarihi"] ?? null);

    $id = Security::decrypt($_POST["id"]);
    $data = [
        "id"                => $id,
        "ad_soyad"          => $_POST["ad-soyad"] ?? '',
        "telefon"           => $_POST["ziyaretci-tel"] ?? '',
        "plaka"             => $_POST["plaka"] ?? '',
        "giris_tarihi"      => $giris_tarihi,
        "giris_saati"       => isset($_POST["giris_saati"]) ? $_POST["giris_saati"] : null,
        "cikis_saati"       => isset($_POST["cikis_saati"]) ? $_POST["cikis_saati"] : null,
        "durum"             => isset($_POST["cikisSaatiSwitch"]) ? '1' : '0',
        "ziyaret_edilen_id" => $_POST["kisi_id"] ?? null,
        "kayit_tarihi"      => date('Y-m-d H:i:s'),
    ];
    if (!empty($id)) {
        $data["guncelleme_tarihi"] = date('Y-m-d H:i:s');
    }
    $lastInsertId = $Ziyaretci->saveWithAttr($data);

    $res = [
        "status" => "success",
        "message" => "Başarılı",        
        "decrypted_id" => $id // çözümlenmiş ID’yi cevaba ekle

    ];
    echo json_encode($res);
}

if ($_POST["action"] == "sil-Ziyaretci") {
    
        $logger = \getLogger();
    
        // Loglama için gerekli bilgileri topla
        $currentUser = AuthController::user(); // Giriş yapmış kullanıcıyı al
        $Ziyaretci->delete($_POST["id"]);
            
            $logger->info("Bir ziyaretçi kaydı silindi.", [
                'deleted_ziyaretci_id' =>Security::decrypt($_POST["id"]), // Şifreli ID'yi de loglamak iyi olabilir
                'deleted_by_user_id' => $currentUser->id,
                'user_email' => $currentUser->email,
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);

             // --- BAŞARI MESAJI VE FLASH MESAJ ---
            // Kullanıcı bir sonraki sayfada bir başarı mesajı görecek.
            FlashMessageService::add(
                'success',
                'İşlem Başarılı',
                'Ziyaretçi kaydı başarıyla silinmiştir.',
                'onay2.png'
            );

    $res = [
        "status" => "success",
        "message" => "Başarılı"
    ];
    echo json_encode($res);
}
