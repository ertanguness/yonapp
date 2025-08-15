<?php
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use App\Helper\Date;
use Model\BakimMaliyetModel;
use App\Helper\Security;
use App\Services\FlashMessageService;
use App\Controllers\AuthController;

$BakimMaliyetleri = new BakimMaliyetModel();

// --- Maliyet Kaydetme ---
if (isset($_POST["action"]) && $_POST["action"] === "maliyet_kaydetme") {
    $odemeTarihi = Date::Ymd($_POST["odemeTarihi"] ?? null);
    $id = Security::decrypt($_POST["maliyet_id"] ?? null);

    $data = [
        "id" => $id,
        "bakim_turu" => $_POST["bakimTuru"] ?? '',
        "talep_no" => $_POST["talepNo"] ?? '',
        "makbuz_turu" => $_POST["makbuzTuru"] ?? '',
        "toplam_maliyet" => $_POST["toplamMaliyet"] ?? 0,
        "makbuz_no" => $_POST["makbuzNo"] ?? '',
        "odenen_tutar" => $_POST["odenenTutar"] ?? 0,
        "kalan_borc" => $_POST["kalanBorc"] ?? 0,
        "odeme_durumu" => $_POST["odemeDurumu"] ?? '',
        "odeme_tarihi" => $odemeTarihi,
        "aciklama" => $_POST["aciklama"] ?? '',
        "makbuz_dosya_yolu" => $_POST["makbuzDosyaYolu"] ?? '',
        "olusturan" => $_SESSION["user"]->id,
        "kayit_tarihi" => date('Y-m-d H:i:s'),
    ];

    // Güncelleme ise, güncelleme_tarihi ekle
    if (!empty($id)) {
        $data["guncelleme_tarihi"] = date('Y-m-d H:i:s');
    }

    $lastInsertId = $BakimMaliyetleri->saveWithAttr($data);

    echo json_encode([
        "status" => "success",
        "message" => "Başarılı",
        "decrypted_id" => $id
    ]);
    exit;
}

// --- Maliyet Silme ---
if (isset($_POST["action"]) && $_POST["action"] === "sil-maliyet") {
    $logger = getLogger();
    $currentUser = AuthController::user();

    $id = $_POST["id"] ?? null;

    if ($id) {
        $BakimMaliyetleri->delete($id);

        $logger->info("Bir Bakım maliyet kaydı silindi.", [
            'deleted_maliyet_id' => Security::decrypt($id),
            'deleted_by_user_id' => $currentUser->id,
            'user_email' => $currentUser->email,
            'ip_address' => $_SERVER['REMOTE_ADDR']
        ]);

        FlashMessageService::add(
            'success',
            'İşlem Başarılı',
            'Bakım Maliyet kaydı başarıyla silinmiştir.',
            'onay2.png'
        );

        echo json_encode([
            "status" => "success",
            "message" => "Başarılı"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Geçersiz ID"
        ]);
    }
    exit;
}

// --- Bakım Türüne Göre Talep Numaraları Getir ---
if (isset($_GET["action"]) && $_GET["action"] === "get_talepler" && isset($_GET["bakimTuru"])) {
    $bakimTuru = (int) $_GET["bakimTuru"]; // güvenlik için integer cast

    $data = $BakimMaliyetleri->Bakimlar($bakimTuru);

    echo json_encode([
        "success" => true,
        "data" => $data
    ]);
    exit;
}
