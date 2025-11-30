<?php
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use App\Helper\Date;
use Model\BakimMaliyetModel;
use App\Helper\Security;
use App\Services\FlashMessageService;
use App\Controllers\AuthController;

$BakimMaliyetleri = new BakimMaliyetModel();

if (isset($_POST["action"]) && $_POST["action"] === "maliyet_kaydetme") {
    // Şifreli ID geliyor, açıyoruz
    $encryptedId = $_POST["maliyet_id"] ?? null;
    $id = $encryptedId ? Security::decrypt($encryptedId) : null;

    $odemeTarihi = Date::Ymd($_POST["odemeTarihi"] ?? null);
    $talepNo = $_POST["talepNo"] ?? null;

    // Yeni kayıt yapılırken $talepNo ile gelen veriyi kontrol et
    if (empty($id) && !empty($talepNo)) {
        $existing = $BakimMaliyetleri->MaliyetNoVarmi($talepNo);
        if ($existing) {
            echo json_encode([
                "status" => "error",
                "message" => "Bu talep numarası ile daha önce kayıt yapılmış."
            ]);
            exit;
        }
    }
    $tm = $_POST["toplamMaliyet"] ?? 0;
    $od = $_POST["odenenTutar"] ?? 0;
    $kl = $_POST["kalanBorc"] ?? 0;
    foreach ([&$tm, &$od, &$kl] as &$val) {
        $val = preg_replace('/[^0-9.,-]/', '', (string)$val);
        if (strpos($val, ',') !== false) { $val = str_replace('.', '', $val); $val = str_replace(',', '.', $val); }
        $val = $val !== '' ? (float)$val : 0;
    }
    unset($val);

    $data = [
        "id" => $id,
        "bakim_turu" => $_POST["bakimTuru"] ?? '',
        "talep_no" => $talepNo ?? '',
        "makbuz_turu" => $_POST["makbuzTuru"] ?? '',
        "toplam_maliyet" => $tm,
        "makbuz_no" => $_POST["makbuzNo"] ?? '',
        "odenen_tutar" => $od,
        "kalan_borc" => $kl,
        "odeme_durumu" => $_POST["odemeDurumu"] ?? '',
        "odeme_tarihi" => $odemeTarihi,
        "aciklama" => $_POST["aciklama"] ?? '',
        "olusturan" => $_SESSION["user"]->id,
        "kayit_tarihi" => date('Y-m-d H:i:s'),
    ];

    if (!empty($id)) {
        $data["guncelleme_tarihi"] = date('Y-m-d H:i:s');
    }

    if (empty($id)) {

        $encryptedInsertId = $BakimMaliyetleri->saveWithAttr($data); // Yeni kayıt: insert yap, şifreli ID dönüyor
        $lastInsertId = Security::decrypt($encryptedInsertId); // Şifreli ID'yi aç 
    } else {
        // Güncelleme: update yap, $id zaten şifresiz
        $BakimMaliyetleri->saveWithAttr($data);
        $lastInsertId = $id;
    }

    // Dosya yükleme ve makbuz kaydetme
    if (isset($_FILES['makbuzEkle']) && !empty($_FILES['makbuzEkle']['name'][0])) {
        $uploadDir = dirname(__DIR__, 3) . '/files/Bakim_Makbuzlari/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $files = $_FILES['makbuzEkle'];

        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $dosyaAdi = uniqid('makbuz_') . '_' . basename($files['name'][$i]);
                $hedefYol = $uploadDir . $dosyaAdi;

                if (move_uploaded_file($files['tmp_name'][$i], $hedefYol)) {
                    // Gerçek ID ile kaydet
                    $BakimMaliyetleri->insertBakimMakbuz($lastInsertId, 'files/Bakim_Makbuzlari/' . $dosyaAdi);
                }
            }
        }
    }

    echo json_encode([
        "status" => "success",
        "message" => "Başarılı",
        "decrypted_id" => $lastInsertId
    ]);
    exit;
}

// --- Maliyet Silme ---
if (isset($_POST["action"]) && $_POST["action"] === "sil-maliyet") {
    $logger = getLogger();
    $currentUser = AuthController::user();

    $encryptedId = $_POST["id"] ?? null;
    $id = $encryptedId ? Security::decrypt($encryptedId) : null;

    if ($id) {
        $BakimMaliyetleri->delete($encryptedId); // delete fonksiyonunda decrypt yapılıyor zaten

        $logger->info("Bir Bakım maliyet kaydı silindi.", [
            'deleted_maliyet_id' => $id,
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
