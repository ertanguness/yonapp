<?php
require_once dirname(__DIR__ ,levels: 2). '/configs/bootstrap.php';

use App\Helper\Date;
use App\Helper\Security;
use App\Services\FlashMessageService;
use App\Controllers\AuthController;
use Model\IcraModel;

$Icra = new IcraModel();

if ($_POST["action"] == "icra_kaydetme") {
    
    $baslangic_tarihi = Date::Ymd($_POST["baslangic_tarihi"] ?? null);

    $id = Security::decrypt($_POST["id"]);
    $data = [
        "id"                => $id,
        "dosya_no"          => $_POST["dosya_no"] ?? '',
        "kisi_id"           => $_POST["kisi_id"] ?? null,
        "tc"                => $_POST["tc"] ?? '',
        "borc_tutari"       => $_POST["borc_tutari"] ?? '',
        "faiz_orani"        => $_POST["faiz_orani"] ?? '',
        "baslangic_tarihi"  => $baslangic_tarihi,
        "icra_dairesi"      => $_POST["icra_dairesi"] ?? '',
        "aciklama"          => $_POST["aciklama"] ?? '',
        "durum"             => $_POST["icra_durumu"] ?? '',
        "kayit_tarihi"      => date('Y-m-d H:i:s'),
    ];

    if (!empty($id)) {
        $data["guncelleme_tarihi"] = date('Y-m-d H:i:s');
    }

    $lastInsertId = $Icra->saveWithAttr($data);

    // Yeni kayıt eklenmişse lastInsertId kullanılacak
    $realId = !empty($id) ? $id : $lastInsertId;

    $res = [
        "status"   => "success",
        "message"  => "İcra kaydı başarıyla kaydedildi.",
        "id"       => Security::encrypt($realId) // yönlendirme için lazım olacak
    ];
    echo json_encode($res);
    exit;
    
}

if ($_POST["action"] == "sil-icra") {
    
    $logger = \getLogger();
    
    // Loglama için gerekli bilgileri topla
    $currentUser = AuthController::user();
    $Icra->delete($_POST["id"]);
        
    $logger->info("Bir icra kaydı silindi.", [
        'deleted_icra_id'   => Security::decrypt($_POST["id"]),
        'deleted_by_user_id'=> $currentUser->id,
        'user_email'        => $currentUser->email,
        'ip_address'        => $_SERVER['REMOTE_ADDR']
    ]);

    // Flash mesaj
    FlashMessageService::add(
        'success',
        'İşlem Başarılı',
        'İcra kaydı başarıyla silinmiştir.',
        'onay2.png'
    );

    $res = [
        "status"  => "success",
        "message" => "Başarılı"
    ];
    echo json_encode($res);
    exit;
}
