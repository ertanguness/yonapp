<?php
require_once dirname(__DIR__ ,levels: 3). '/configs/bootstrap.php';

use App\Helper\Date;
use Model\PeriyodikBakimModel;
use App\Helper\Security;
use App\Services\FlashMessageService;
use App\Controllers\AuthController; // AuthController'ı da kullanabiliriz

$PeriyodikBakimlar = new PeriyodikBakimModel();


if ($_POST["action"] == "periyodikBakim_kaydetme") {
    $sonBakimTarihi     = Date::Ymd( $_POST["sonBakimTarihi"] ?? null);
    $planlananBakimTarihi     = Date::Ymd( $_POST["planlananBakimTarihi"] ?? null);

    $id = Security::decrypt($_POST["periyodikBakim_id"]);
    
   
        if (!empty($planlananBakimTarihi) && $planlananBakimTarihi < $sonBakimTarihi) {
            echo json_encode([
                "status" => "error",
                "message" => " Planlanan bakım tarihi, Son bakım tarihinden önce olamaz."
            ]);
            exit;
        }
        
    
    $data = [
        "id" => $id,
        "site_id" => $_SESSION["site_id"],
        "talep_no" => $_POST["talepNo"] ?? '',
        "bakim_adi" => $_POST["bakimAdi"] ?? '',
        "bakim_periyot" => $_POST["bakimPeriyot"] ?? '',
        "bakim_yeri" => $_POST["bakimYeri"] ?? '',
        "blok" => $_POST["blokSecimi"] ?? '',
        "sorumlu_firma" => $_POST["sorumluFirma"] ?? '',
        "sonBakim_tarihi" => $sonBakimTarihi   ?? null,
        "planlanan_bakim_tarihi" => $planlananBakimTarihi  ?? null,
        "aciklama" => $_POST["aciklama"] ?? '',
        "olusturan" => $_SESSION["user"]->id,
        ...( !empty($id) ? ["guncelleme_tarihi" => date('Y-m-d H:i:s')] : [] ),// $id boş veya null değilse güncelleme tarihi ekle
    ];

    $lastInsertId = $PeriyodikBakimlar->saveWithAttr($data);

    $res = [
        "status" => "success",
        "message" => "Başarılı",
        "decrypted_id" => $id // çözümlenmiş ID’yi cevaba ekle
        

    ];
    echo json_encode($res);
}

if ($_POST["action"] == "bakim_durumu_guncelle") {
    $id = Security::decrypt($_POST["id"] ?? null);
    $durum = intval($_POST["durum"] ?? 0);
    if (!$id) {
        echo json_encode([
            "status" => "error",
            "message" => "Geçersiz kayıt"
        ]);
        exit;
    }
    try {
        $result = $PeriyodikBakimlar->updateWhere('id', $id, [
            'bakim_durumu' => $durum,
            'guncelleme_tarihi' => date('Y-m-d H:i:s')
        ]);
        if ($result === true) {
            echo json_encode([
                "status" => "success",
                "message" => "Bakım durumu güncellendi"
            ]);
        } elseif ($result instanceof \Exception) {
            echo json_encode([
                "status" => "error",
                "message" => $result->getMessage()
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Güncelleme başarısız"
            ]);
        }
    } catch (\Throwable $e) {
        echo json_encode([
            "status" => "error",
            "message" => "Hata: " . $e->getMessage()
        ]);
    }
}
if ($_POST["action"] == "sil-periyodikBakim") {
    
        $logger = \getLogger();
    
        // Loglama için gerekli bilgileri topla
        $currentUser = AuthController::user(); // Giriş yapmış kullanıcıyı al
        $PeriyodikBakimlar->delete($_POST["id"]);
            
            $logger->info("Bir Periyodik Bakım kaydı silindi.", [
                'deleted_periyodikBakim_id' =>Security::decrypt($_POST["id"]), // Şifreli ID'yi de loglamak iyi olabilir
                'deleted_by_user_id' => $currentUser->id,
                'user_email' => $currentUser->email,
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);

             // --- BAŞARI MESAJI VE FLASH MESAJ ---
            // Kullanıcı bir sonraki sayfada bir başarı mesajı görecek.
            FlashMessageService::add(
                'success',
                'İşlem Başarılı',
                'Periyodik Bakım kaydı başarıyla silinmiştir.',
                'onay2.png'
            );

    $res = [
        "status" => "success",
        "message" => "Başarılı"
    ];
    echo json_encode($res);
}
