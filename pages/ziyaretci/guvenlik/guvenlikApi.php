<?php
require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';

use App\Helper\Date;
use App\Helper\Security;
use App\Services\FlashMessageService;
use App\Controllers\AuthController; // AuthController'ı da kullanabiliriz
use Model\GuvenlikModel;

$Guvenlik = new GuvenlikModel();

if ($_POST["action"] == "guvenlik_kaydetme") {

    $baslama_tarihi     = Date::Ymd($_POST["gorev_baslangic"] ?? null);
    $bitis_tarihi     = Date::Ymd($_POST["gorev_bitis"] ?? null);

    // Bitiş tarihi başlangıç tarihinden önce ise hata ver
    if ($bitis_tarihi && $baslama_tarihi && $bitis_tarihi < $baslama_tarihi) {
        $res = [
            "status" => "error",
            "message" => "Bitiş tarihi, başlangıç tarihinden önce olamaz."
        ];
        echo json_encode($res);
        exit;
    }

    $id = Security::decrypt($_POST["id"]);
    $data = [
        "id"                => $id,
        "personel_id"       => $_POST["personel"] ?? '',
        "gorev_yeri_id"     => $_POST["gorev_yeri"] ?? '',
        "vardiya_id"        => $_POST["vardiya"] ?? '',
        "baslama_tarihi"    => $baslama_tarihi,
        "bitis_tarihi"      => $bitis_tarihi,
        "aciklama"          => $_POST["aciklama"] ?? '',
        "durum" =>          !empty($bitis_tarihi) ? 0 : ($_POST["durum"] ?? ''),
        "kayit_tarihi"      => date('Y-m-d H:i:s'),
    ];
    if (!empty($id)) {
        $data["guncelleme_tarihi"] = date('Y-m-d H:i:s');
    }
    $lastInsertId = $Guvenlik->saveWithAttr($data);

    $res = [
        "status" => "success",
        "message" => "Başarılı",
        "decrypted_id" => $id // çözümlenmiş ID’yi cevaba ekle
    ];
    echo json_encode($res);
}

if ($_POST["action"] == "sil-guvenlik") {

    $logger = \getLogger();

    // Loglama için gerekli bilgileri topla
    $currentUser = AuthController::user(); // Giriş yapmış kullanıcıyı al
    $Guvenlik->delete($_POST["id"]);

    $logger->info("Bir ziyaretçi kaydı silindi.", [
        'deleted_guvenlik_vardiya_id' => Security::decrypt($_POST["id"]), // Şifreli ID'yi de loglamak iyi olabilir
        'deleted_by_user_id' => $currentUser->id,
        'user_email' => $currentUser->email,
        'ip_address' => $_SERVER['REMOTE_ADDR']
    ]);

    // --- BAŞARI MESAJI VE FLASH MESAJ ---
    // Kullanıcı bir sonraki sayfada bir başarı mesajı görecek.
    FlashMessageService::add(
        'success',
        'İşlem Başarılı',
        'Görev vardiya kaydı başarıyla silinmiştir.',
        'onay2.png'
    );

    $res = [
        "status" => "success",
        "message" => "Başarılı"
    ];
    echo json_encode($res);
}
