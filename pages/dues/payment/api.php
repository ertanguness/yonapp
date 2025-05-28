<?php

require_once '../../../vendor/autoload.php';

use App\Helper\Date;
use App\Helper\Error;
use App\Helper\Helper;
use App\Helper\Security;
use Model\BloklarModel;
use Model\BorclandirmaDetayModel;
use Model\BorclandirmaModel;
use Model\DairelerModel;
use Model\DueModel;
use Model\KisilerModel;
use Model\TahsilatHavuzuModel;
use Model\TahsilatModel;

$Borc = new BorclandirmaModel();
$BorcDetay = new BorclandirmaDetayModel();
$Due = new DueModel();
$Bloklar = new BloklarModel();
$Tahsilat = new TahsilatModel();
$TahsilatHavuzu = new TahsilatHavuzuModel();
$Daire = new DairelerModel();

// CREATE TABLE `tahsilatlar` (
// 	`id` INT(11) NOT NULL AUTO_INCREMENT,
// 	`borc_id` INT(11) NOT NULL,
// 	`person_id` INT(11) NOT NULL,
// 	`kasa_id` INT(11) NOT NULL,
// 	`tutar` DECIMAL(10,2) NOT NULL,
// 	`islem_tarihi` DATE NOT NULL,
// 	`tahsilat_tipi` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
// 	`makbuz_no` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
// 	`taksit_no` INT(11) NULL DEFAULT NULL,
// 	`toplam_taksit` INT(11) NULL DEFAULT NULL,
// 	`aciklama` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
// 	`deleted_at` DATETIME NULL DEFAULT NULL,
// 	`delete_user` INT(11) NULL DEFAULT NULL,
// 	`created_at` DATETIME NULL DEFAULT current_timestamp(),
// 	`create_user` INT(11) NULL DEFAULT NULL,
// 	`updated_at` DATETIME NULL DEFAULT NULL ON UPDATE current_timestamp(),
// 	`update_user` INT(11) NULL DEFAULT NULL,
// 	PRIMARY KEY (`id`) USING BTREE,
// 	INDEX `FK_tahsilatlar_peoples` (`person_id`) USING BTREE,
// 	CONSTRAINT `FK_tahsilatlar_peoples` FOREIGN KEY (`person_id`) REFERENCES `peoples` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
// )
// COLLATE='utf8mb4_general_ci'
// ENGINE=InnoDB
// ;

/* Excel dosyasından toplu ödeme yükleme işlemi */
if ($_POST['action'] == 'payment_file_upload') {
    $file = $_FILES['payment_file'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];

    // Yalnızca belirli dosya uzantılarına izin ver
    $allowedExtensions = ['csv', 'xlsx', 'xls'];
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

    if (!in_array($fileExtension, $allowedExtensions)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Geçersiz dosya uzantısı. Yalnızca CSV, XLSX veya XLS dosyaları yüklenebilir.'
        ]);
        exit;
    }

    // Sayaçlar ve sonuçlar
    $successCount = 0;
    $failCount = 0;
    $failRows = [];
    $bulunan_daireler = [];
    $eşleşmeyen_kayıtlar = 0;

    /**
     * Başarılı eşleşen daire için tahsilat kaydını veritabanına ekler.
     * @param $Tahsilat TahsilatModel örneği
     * @param $data Satır verisi (array)
     * @param $daire_id Eşleşen daire ID'si
     * @return mixed Son eklenen kaydın ID'si
     */
    function kaydetTahsilat($Tahsilat, $data, $daire_id) {
        // Gerekirse diğer alanlar eklenebilir
        return $Tahsilat->saveWithAttr([
            'id' => 0,
            'islem_tarihi' => $data[0], // İşlem tarihi
            'daire_id' => $daire_id,
            // 'tutar' => Helper::formattedMoneyToNumber($data[1]),
            // 'makbuz_no' => $data[4],
            // 'aciklama' => $data[3],
        ]);
    }

    /**
     * Eşleşmeyen veya hatalı kayıtları tahsilat havuzuna ekler.
     * @param $TahsilatHavuzu TahsilatHavuzuModel örneği
     * @param $data Satır verisi (array)
     * @param $aciklamaEk Açıklama veya hata mesajı
     * @return mixed Son eklenen kaydın ID'si
     */
    function kaydetHavuz($TahsilatHavuzu, $data, $aciklamaEk = '') {
        return $TahsilatHavuzu->saveWithAttr([
            'id' => 0,
            'islem_tarihi' => Date::Ymd($data[0]), // İşlem tarihi
            'tahsilat_tutari' => $data[1],         // Tutar
            'ham_aciklama' => $data[3] ?? '',      // Açıklama
            'referans_no' => $data[4] ?? '',       // Makbuz no
            'aciklama' => $aciklamaEk,             // Ek açıklama veya hata
        ]);
    }

    // Excel dosyasını oku ve satırları işle
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileTmpName);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    foreach ($rows as $i => $data) {
        if ($i == 0) continue; // Başlık satırını atla
        try {
            $daire_id = 0;
            $apartmentInfo = null;

            // Öncelikle doğrudan daire kodu ile eşleşme dene
            if (!empty($data[2])) {
                $daire_id = $Daire->DaireId($data[2]) ?? 0;
            }
            // Daire kodu yoksa açıklamadan blok/daire bilgisi çıkar
            else if (!empty($data[3])) {
                $apartmentInfo = Helper::extractApartmentInfo($data[3]);
                if ($apartmentInfo) {
                    $daire_id = $Daire->DaireId($apartmentInfo) ?? 0;
                }
            }

            // Eşleşen daire bulunduysa tahsilat kaydet
            if ($daire_id > 0) {
                kaydetTahsilat($Tahsilat, $data, $daire_id);
                $bulunan_daireler[] = $apartmentInfo ?? $data[2];
                $successCount++;
            } else {
                // Eşleşmeyen kayıtları havuza kaydet
                $aciklamaEk = !empty($data[2])
                    ? 'Daire Kodu eşleşmedi: ' . $data[2]
                    : ('Bilgi var ' . ($data[3] ?? ''));
                kaydetHavuz($TahsilatHavuzu, $data, $aciklamaEk);
                $eşleşmeyen_kayıtlar++;
            }
        } catch (Exception $e) {
            // Hatalı satırları havuza kaydet ve sayaçları güncelle
            $failCount++;
            $failRows[] = $i + 1;
            kaydetHavuz($TahsilatHavuzu, $data, $e->getMessage());
            $eşleşmeyen_kayıtlar++;
        }
    }

    // Sonuç mesajı oluştur
    $status = 'success';
    $message = "Yükleme tamamlandı.<br> Başarılı: $successCount, <br>Hatalı: $failCount";
    if ($failCount > 0) $message .= '. <br>Hatalı satırlar: ' . implode(', ', $failRows);
    if ($eşleşmeyen_kayıtlar > 0) $message .= '. <br>Eşleşmeyen kayıt sayısı: ' . $eşleşmeyen_kayıtlar;

    // Sonuçları JSON olarak döndür
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'bulunan_daireler' => $bulunan_daireler
    ]);
}
