<?php
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use App\Helper\Date;
use App\Helper\Security;
use App\Services\FlashMessageService;
use App\Controllers\AuthController;
use Model\IcraModel;

$Icra = new IcraModel();

// ================= Ödeme Planı Kaydet =================
if ($_POST["action"] == "odeme_plan_kaydet") {

    $id = Security::decrypt($_POST["id"]);

    $odemeBaslangicTarihi = Date::Ymd($_POST["odeme_baslangic_tarihi"] ?? null);

    $data = [
        "icra_id"                => $id,
        "icra_borcu"             => $_POST["borc_tutari"] ?? 0,
        "faiz_orani"             => $_POST["faiz_orani"] ?? 0,
        "taksit_sayisi"          => $_POST["taksit"] ?? 0,
        "taksit_odeme_tarihi"    => $odemeBaslangicTarihi,
        "aciklama"               => $_POST["aciklama"] ?? '',
        "kayit_tarihi"           => date('Y-m-d H:i:s'),
    ];

    $lastInsertId = $Icra->saveWithAttr($data);

    $res = [
        "status"  => "success",
        "message" => "Ödeme planı başarıyla kaydedildi."
        //"id"      => Security::encrypt($id)
    ];
    echo json_encode($res);
    exit;
}

// ================= Durum Güncelle =================
if ($_POST["action"] == "durum_guncelle") {

    $id = Security::decrypt($_POST["id"]);

    $data = [
        "id"             => $id,
        "dosya_durumu"   => $_POST["fileStatus"] ?? '',
        "icra_durumu"    => $_POST["icraStatus"] ?? '',
        "guncelleme_tarihi" => date('Y-m-d H:i:s')
    ];

    // IcraModel içerisinde örneğin updateStatus methodu olmalı
    $Icra->saveWithAttr($data);

    $res = [
        "status"  => "success",
        "message" => "Durum başarıyla güncellendi."
    ];
    echo json_encode($res);
    exit;
}

// ================= İcra Sil =================
if ($_POST["action"] == "sil-icra") {

    $logger = \getLogger();
    $currentUser = AuthController::user();

    $Icra->delete($_POST["id"]);

    $logger->info("Bir icra kaydı silindi.", [
        'deleted_icra_id'    => Security::decrypt($_POST["id"]),
        'deleted_by_user_id' => $currentUser->id,
        'user_email'         => $currentUser->email,
        'ip_address'         => $_SERVER['REMOTE_ADDR']
    ]);

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
