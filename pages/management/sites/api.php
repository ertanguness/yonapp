<?php
require_once dirname(__DIR__ ,levels: 3). '/configs/bootstrap.php';


use Model\SitelerModel;
use App\Helper\Security;
use App\Services\FlashMessageService;
use App\Controllers\AuthController; // AuthController'ı da kullanabiliriz
use App\Modules\Onboarding\Events\OnboardingEvents;

$Siteler = new SitelerModel();


if ($_POST["action"] == "save_sites") {
    $id = Security::decrypt($_POST["id"]);
        $data = [
        "id" => $id,
        "user_id" => $_SESSION["user"]->id,
        "site_adi" => $_POST["sites_name"],
        "telefon" => $_POST["phone"],
        "il" => $_POST["il"],
        "ilce" => $_POST["ilce"],
        "tam_adres" => $_POST["adres"],
        "aciklama" => $_POST["description"],
        "logo_path" => $_POST["selectedLogo"],
        "aktif_mi" => 1,
    ];

    $lastInsertId = $Siteler->saveWithAttr($data);

    $ilksiteMi = $Siteler->countWhere("user_id", $_SESSION["user"]->id );
    if ($ilksiteMi == 1) {
        $_SESSION["site_id"] = Security::decrypt($lastInsertId);
    }

    $res = [
        "status" => "success",
        "message" => "Site başarıyla kaydedildi.",
        "decrypted_id" => $id ,// çözümlenmiş ID’yi cevaba ekle,
        "ilkSiteMi" => $ilksiteMi == 1 ? true : false
        

    ];
    echo json_encode($res);

    try { OnboardingEvents::complete('create_site', $_SESSION["site_id"] ?? null); } catch (\Throwable $e) {}
}

if ($_POST["action"] == "delete-Siteler") {
    
        $logger = \getLogger();
    
        // Loglama için gerekli bilgileri topla
        $currentUser = AuthController::user(); // Giriş yapmış kullanıcıyı al
        $Siteler->delete($_POST["id"]);
            
            $logger->info("Bir site kaydı silindi.", [
                'deleted_site_id' =>Security::decrypt($_POST["id"]), // Şifreli ID'yi de loglamak iyi olabilir
                'deleted_by_user_id' => $currentUser->id,
                'user_email' => $currentUser->email,
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);

             // --- BAŞARI MESAJI VE FLASH MESAJ ---
            // Kullanıcı bir sonraki sayfada bir başarı mesajı görecek.
            FlashMessageService::add(
                'success',
                'İşlem Başarılı',
                'Site kaydı başarıyla silinmiştir.',
                'onay2.png'
            );

    $res = [
        "status" => "success",
        "message" => "Başarılı"
    ];
    echo json_encode($res);
}
