<?php
require_once dirname(__DIR__, levels: 4) . '/configs/bootstrap.php';

use App\Helper\Security;
use App\Services\FlashMessageService;
use App\Controllers\AuthController; // AuthController'ı da kullanabiliriz
use Model\GuvenlikGorevYeriModel;

$GorevYeri = new GuvenlikGorevYeriModel();

if ($_POST["action"] == "gorevYeri_kaydetme") {

    $id = Security::decrypt($_POST["id"]);
    $data = [
        "id"                => $id,
        "site_id"           => $_SESSION["site_id"] ?? 0,
        "ad"                => $_POST["ad"] ?? '',
        "aciklama"          => $_POST["aciklama"] ?? '',
        "durum"             => $_POST["durum"] ?? 1,
        "kayit_tarihi"      => date('Y-m-d H:i:s'),
    ];
    if (!empty($id)) {
        $data["guncelleme_tarihi"] = date('Y-m-d H:i:s');
    }
    $lastInsertId = $GorevYeri->saveWithAttr($data);

    $res = [
        "status" => "success",
        "message" => "Başarılı",
        "decrypted_id" => $id // çözümlenmiş ID’yi cevaba ekle

    ];
    echo json_encode($res);
}

if ($_POST["action"] == "sil-gorevYeri") {

    $logger = \getLogger();

    // Loglama için gerekli bilgileri topla
    $currentUser = AuthController::user(); // Giriş yapmış kullanıcıyı al
    $GorevYeri->delete($_POST["id"]);

    $logger->info("Bir ziyaretçi kaydı silindi.", [
        'deleted_gorevYeri_id' => Security::decrypt($_POST["id"]), // Şifreli ID'yi de loglamak iyi olabilir
        'deleted_by_user_id' => $currentUser->id,
        'user_email' => $currentUser->email,
        'ip_address' => $_SERVER['REMOTE_ADDR']
    ]);

    // --- BAŞARI MESAJI VE FLASH MESAJ ---
    // Kullanıcı bir sonraki sayfada bir başarı mesajı görecek.
    FlashMessageService::add(
        'success',
        'İşlem Başarılı',
        'Görev Yeri kaydı başarıyla silinmiştir.',
        'onay2.png'
    );

    $res = [
        "status" => "success",
        "message" => "Başarılı"
    ];
    echo json_encode($res);
}
