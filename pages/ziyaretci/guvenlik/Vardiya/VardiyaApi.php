<?php
require_once dirname(__DIR__, levels: 4) . '/configs/bootstrap.php';

use App\Helper\Security;
use App\Services\FlashMessageService;
use App\Controllers\AuthController; // AuthController'ı da kullanabiliriz
use Model\GuvenlikVardiyaModel;

$Vardiya = new GuvenlikVardiyaModel();

if ($_POST["action"] == "vardiya_kaydetme") {

    $id = Security::decrypt($_POST["id"]);
    $data = [
        "id"                         => $id,
        "gorev_yeri_id"              => $_POST["gorev_yeri_id"] ?? 0,
        "vardiya_adi"                => $_POST["vardiya_adi"] ?? '',
        "vardiya_baslangic"          => $_POST["vardiya_baslangic"] ?? '',
        "vardiya_bitis"              => $_POST["vardiya_bitis"] ?? 1,
        "aciklama"                   => $_POST["aciklama"] ?? '',
        "durum"                      => $_POST["durum"] ?? '',
        "kayit_tarihi"               => date('Y-m-d H:i:s'),
    ];
    if (!empty($id)) {
        $data["guncelleme_tarihi"] = date('Y-m-d H:i:s');
    }
    $lastInsertId = $Vardiya->saveWithAttr($data);

    $res = [
        "status" => "success",
        "message" => "Başarılı",
        "decrypted_id" => $id // çözümlenmiş ID’yi cevaba ekle

    ];
    echo json_encode($res);
}

if ($_POST["action"] == "sil-vardiya") {

    $logger = \getLogger();

    // Loglama için gerekli bilgileri topla
    $currentUser = AuthController::user(); // Giriş yapmış kullanıcıyı al
    $Vardiya->delete($_POST["id"]);

    $logger->info("Bir vardiya kaydı silindi.", [
        'deleted_vardiya_id' => Security::decrypt($_POST["id"]), // Şifreli ID'yi de loglamak iyi olabilir
        'deleted_by_user_id' => $currentUser->id,
        'user_email' => $currentUser->email,
        'ip_address' => $_SERVER['REMOTE_ADDR']
    ]);

    // --- BAŞARI MESAJI VE FLASH MESAJ ---
    // Kullanıcı bir sonraki sayfada bir başarı mesajı görecek.
    FlashMessageService::add(
        'success',
        'İşlem Başarılı',
        'Vardiya kaydı başarıyla silinmiştir.',
        'onay2.png'
    );

    $res = [
        "status" => "success",
        "message" => "Başarılı"
    ];
    echo json_encode($res);
}
