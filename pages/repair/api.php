<?php
require_once dirname(__DIR__ ,levels: 2). '/configs/bootstrap.php';

use App\Helper\Date;
use Model\BakimModel;
use App\Helper\Security;
use App\Services\FlashMessageService;
use App\Controllers\AuthController; // AuthController'ı da kullanabiliriz

$Bakimlar = new BakimModel();


if ($_POST["action"] == "bakim_kaydetme") {
    $talepTarihi     = Date::Ymd($_POST["taleptarihi"] ?? null);

    $id = Security::decrypt($_POST["id"]);
        $data = [
        "id" => $id,
        "site_id" => $_SESSION["site_id"],
        "talep_no" => $_POST["talepno"] ?? '',
        "talep_eden" => $_POST["talepeden"] ?? '',
        "talep_tarihi" => $talepTarihi,
        "kategori" => $_POST["kategori"] ?? '',
        "durum" => $_POST["state"] ?? '',
        "firma_kisi" => $_POST["firmakisi"] ?? '',
        "atama_durumu" => $_POST["atandimi"] ?? '',
        "aciklama" => $_POST["aciklama"] ?? '',
        "olusturan" => $_SESSION["user"]->id,
    ];

    $lastInsertId = $Bakimlar->saveWithAttr($data);

    $res = [
        "status" => "success",
        "message" => "Başarılı",
        "decrypted_id" => $id // çözümlenmiş ID’yi cevaba ekle
        

    ];
    echo json_encode($res);
}

if ($_POST["action"] == "sil-Bakim") {
    
        $logger = \getLogger();
    
        // Loglama için gerekli bilgileri topla
        $currentUser = AuthController::user(); // Giriş yapmış kullanıcıyı al
        $Bakimlar->delete($_POST["id"]);
            
            $logger->info("Bir bakım kaydı silindi.", [
                'deleted_bakim_id' =>Security::decrypt($_POST["id"]), // Şifreli ID'yi de loglamak iyi olabilir
                'deleted_by_user_id' => $currentUser->id,
                'user_email' => $currentUser->email,
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);

             // --- BAŞARI MESAJI VE FLASH MESAJ ---
            // Kullanıcı bir sonraki sayfada bir başarı mesajı görecek.
            FlashMessageService::add(
                'success',
                'İşlem Başarılı',
                'Bakım/Onarım kaydı başarıyla silinmiştir.',
                'onay2.png'
            );

    $res = [
        "status" => "success",
        "message" => "Başarılı"
    ];
    echo json_encode($res);
}
