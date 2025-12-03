<?php
require_once dirname(__DIR__, 2) . '/configs/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

use App\Helper\Date;
use App\Helper\Security;
use App\Services\FlashMessageService;
use App\Controllers\AuthController;
use Model\IcraModel;
use Model\IcraOdemeModel;

$Icra = new IcraModel();
$IcraOdeme = new IcraOdemeModel();
$action = $_POST["action"] ?? '';
if ($action === "icra_kaydetme") {
    try {

    $baslangic_tarihi = Date::Ymd($_POST["baslangic_tarihi"] ?? null);

    $id = Security::decrypt($_POST["id"]);
    $id = ($id && (int)$id > 0) ? (int)$id : null;
    $dosya_no = $_POST["dosya_no"] ?? '';

    // Aynı dosya_no var mı kontrol et (güncellemede kendi kaydını hariç tutacak)
    $oncekiKayit = $Icra->findByDosyaNo($dosya_no, $id);

    if ($oncekiKayit) {
        echo json_encode([
            "status"  => "error",
            "message" => "Bu dosya numarasıyla daha önce kayıt yapılmış."
        ]);
        exit;
    }

    $data = [
        "id"                => $id,
        "dosya_no"          => $dosya_no,
        "kisi_id"           => $_POST["kisi_id"] ?? null,
        "tc"                => $_POST["tc"] ?? '',
        "borc_tutari"       => $_POST["borc_tutari"] ?? '',
        "faiz_orani"        => $_POST["faiz_orani"] ?? '',
        "icra_baslangic_tarihi"  => $baslangic_tarihi,
        "icra_dairesi"      => $_POST["icra_dairesi"] ?? '',
        "aciklama"          => $_POST["aciklama"] ?? '',
        "durum"             => $_POST["icra_durumu"] ?? '',
        "kayit_tarihi"      => date('Y-m-d H:i:s'),
    ];

    if (!empty($id)) {
        $data["guncelleme_tarihi"] = date('Y-m-d H:i:s');
    }

    $lastInsertId = $Icra->saveWithAttr($data);

    if (empty($id)) {
        $realId = $lastInsertId; // Yeni kayıt -> DB ID
    } else {
        $realId = Security::encrypt($id); // Güncelleme -> şifreli ID
    }

    $res = [
        "status"   => "success",
        "message"  => "İcra kaydı başarıyla kaydedildi.",
        "id"       => $realId
    ];
    echo json_encode($res);
    exit;
    } catch (\Throwable $e) {
        echo json_encode(["status"=>"error","message"=>"Kaydetme sırasında hata oluştu","detail"=>$e->getMessage()]);
        exit;
    }
}

if ($action === "odeme_plan_kaydet") {
    try {

    $id = Security::decrypt($_POST["id"]);
    $icraOdemeBilgileri = $IcraOdeme->IcraOdemeBilgileri($id);

    // Ödeme başlangıç tarihi
    $odemeBaslangicTarihi = Date::Ymd($_POST["odeme_baslangic_tarihi"] ?? null);

    // İcra başlangıç tarihi
    $icraBilgileri = $Icra->IcraBilgileri($id);
    $icraBaslangicTarihi = Date::Ymd($icraBilgileri->icra_baslangic_tarihi ?? null);

    // Kontrol: Ödeme başlangıcı icra başlangıcından önce olamaz
    if ($icraBaslangicTarihi && $odemeBaslangicTarihi < $icraBaslangicTarihi) {
        echo json_encode([
            "status"  => "error",
            "message" => "Ödeme başlangıç tarihi, icra başlangıç tarihinden önce olamaz."
        ]);
        exit;
    }

    // Ödenen var mı kontrolü
    $odenenVar = false;
    foreach ($icraOdemeBilgileri as $odeme) {
        if (isset($odeme->durumu) && $odeme->durumu === "Ödendi") {
            $odenenVar = true;
            break;
        }
    }
    if ($odenenVar) {
        echo json_encode([
            "status"  => "error",
            "message" => "Taksitlerden ödenen olduğu için güncelleme yapamazsınız."
        ]);
        exit;
    }

    $data = [
        "id"                         => $id,
        "borc_tutari"                => $_POST["borc_tutari"] ?? 0,
        "faiz_orani"                 => $_POST["faiz_orani"] ?? 0,
        "taksit"                     => $_POST["taksit"] ?? 0,
        "odeme_baslangic_tarihi"     => $odemeBaslangicTarihi,
        "odeme_aciklama"             => $_POST["aciklama"] ?? '',
        "odeme_kayit_tarihi"         => date('Y-m-d H:i:s'),
    ];

    $lastInsertId = $Icra->saveWithAttr($data);
    $enc_id = Security::encrypt($lastInsertId);

    echo json_encode([
        "status" => "success",
        "message" => "İcra kaydı başarıyla eklendi.",
        "id" => $enc_id
    ]);
    exit;
    } catch (\Throwable $e) {
        echo json_encode(["status"=>"error","message"=>"Ödeme planı kaydedilemedi","detail"=>$e->getMessage()]);
        exit;
    }
}

if ($action === "durum_guncelle") {
    try {

    $id = Security::decrypt($_POST["id"]);


    $data = [
        "id"                         => $id,
        "dosya_durumu"                => $_POST["dosya_durumu"] ?? "",
        "icra_durumu"                 => $_POST["icra_durumu"] ?? "",
    ];

    $lastInsertId = $Icra->saveWithAttr($data);

    $res = [
        "status"  => "success",
        "message" => "Ödeme planı başarıyla kaydedildi.",
        "id"      => Security::encrypt($id)
    ];
    echo json_encode($res);
    exit;
    } catch (\Throwable $e) {
        echo json_encode(["status"=>"error","message"=>"Durum güncellenemedi","detail"=>$e->getMessage()]);
        exit;
    }
}
if ($action === "odeme_durum_guncelle") {
    try {
    $id = Security::decrypt($_POST["id"]);
    $status = $_POST["status"];
    $bugun = date("Y-m-d");

    $odeme = $IcraOdeme->IcraTaksitBilgileri($id);

    if ($status === "Ödendi") {
        $durum = 1; // Ödendi
        $taksit_odenen_tarih = $bugun;
    } else {
        $durum = 0; // Ödenmedi
        $taksit_odenen_tarih = null;
    }

    $data = [
        "id" => $id,
        "durumu" => $durum,
        "taksit_odenen_tarih" => $taksit_odenen_tarih
    ];

    $IcraOdeme->saveWithAttr($data);

    echo json_encode([
        "status" => "success",
        "durum"  => $durum,
        "taksit_odenen_tarih" => $taksit_odenen_tarih ?? "-"
    ]);
    exit;
    } catch (\Throwable $e) {
        echo json_encode(["status"=>"error","message"=>"Ödeme durumu güncellenemedi","detail"=>$e->getMessage()]);
        exit;
    }
}

if ($action === "sil-icra") {
    try {

    $logger = \getLogger();

    // Loglama için gerekli bilgileri topla
    $currentUser = AuthController::user();
    $Icra->delete($_POST["id"]);

    $logger->info("Bir icra kaydı silindi.", [
        'deleted_icra_id'   => Security::decrypt($_POST["id"]),
        'deleted_by_user_id' => $currentUser->id,
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
    } catch (\Throwable $e) {
        echo json_encode(["status"=>"error","message"=>"İcra silinemedi","detail"=>$e->getMessage()]);
        exit;
    }
}

echo json_encode(["status" => "error", "message" => "Geçersiz istek"]);
