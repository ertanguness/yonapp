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

/* Excelden Ödeme Yüklememek için */
if ($_POST['action'] == 'payment_file_upload') {
    $file = $_FILES['payment_file'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];

    // Dosya uzantısını kontrol et
    $allowedExtensions = ['csv', 'xlsx', 'xls'];
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

    if (!in_array($fileExtension, $allowedExtensions)) {
        $status = 'error';
        $message = 'Geçersiz dosya uzantısı. Yalnızca CSV, XLSX veya XLS dosyaları yüklenebilir.';

        $res = [
            'status' => $status,
            'message' => $message
        ];
        echo json_encode($res);
        exit;
    }

    $successCount = 0;
    $eşleşmeyen_kayıtlar = 0;
    $failCount = 0;
    $failRows = [];
    $bulunan_daireler = [];

    // XLSX/XLS için PhpSpreadsheet kullanımı
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileTmpName);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();
    foreach ($rows as $i => $data) {
        if ($i == 0)
            continue;  // başlık satırı
        try {
            // Eğer Daire Kodu dolu ise
            if (!empty($data[2])) {
                $daire_id = $Daire->DaireId($data[2]) ?? 0;  // Daire ID'sini al, yoksa 0

                if($daire_id > 0){

                    $data = [
                        'id' => 0,
                        'islem_tarihi' => $data[0],  // İşlem tarihi sütunu
                        'daire_id' => $daire_id,
                        //'tutar' => Helper::formattedMoneyToNumber($data[1]),  // Tutar sütunu
                        // 'makbuz_no' => $data[3],  // Makbuz no sütunu
                        // 'aciklama' => $data[2],  // Açıklama sütunu
                    ];
                    // Veritabanına kaydet
                    $lastInsertId = $Tahsilat->saveWithAttr($data);
                    $bulunan_daireler[] = $apartmentInfo;
                    $successCount++;
                } else {
                    // Eşleşmeyen kayıtları tahsilat_havuzu tablosuna kaydet
                    $dt = [
                        'id' => 0,
                        'islem_tarihi' => Date::Ymd($data[0]),  // İşlem tarihi sütunu
                        'daire_id' => 0,  // Eşleşmeyen daire için 0
                        'tutar' => $data[1],  // Tutar sütunu
                        'makbuz_no' => $data[4],  // Makbuz no sütunu
                        'aciklama' => 'Daire Kodu eşleşmedi: ' . $data[2],  // Açıklama sütunu
                    ];
                    // Veritabanına kaydet
                    $lastInsertId = $TahsilatHavuzu->saveWithAttr($dt);

                    // Eşleşmeyen daire sayısını tut
                    $eşleşmeyen_kayıtlar++;
                }

            } else {
                // Örnek kullanım
                $aciklama = $data[3];  // Açıklama sütunu
                $apartmentInfo = Helper::extractApartmentInfo($aciklama);

                if ($apartmentInfo) {
                    // $daireId = findApartmentId($apartmentInfo['blok_kodu'], $apartmentInfo['daire_no']);
                    $daire_id = $Daire->DaireId($apartmentInfo) ?? 0;  // Daire ID'sini al, yoksa 0
                    if ($daire_id > 0) {
                        try {
                            // Verileri kontrol et
                            $data = [
                                'id' => 0,
                                'islem_tarihi' => $data[0],  // İşlem tarihi sütunu
                                'daire_id' => $daire_id,
                                // 'tutar' => Helper::formattedMoneyToNumber($data[1]),  // Tutar sütunu
                                // 'makbuz_no' => $data[3],  // Makbuz no sütunu
                                // 'aciklama' => $data[2],  // Açıklama sütunu
                            ];
                            // Veritabanına kaydet
                            $lastInsertId = $Tahsilat->saveWithAttr($data);
                            $bulunan_daireler[] = $apartmentInfo;
                            $successCount++;
                        } catch (PDOException $ex) {
                            $status = 'error';
                            $message = $ex->getMessage();
                        }
                    } else {
                        // Eşleşmeyen kayıtları tahsilat_havuzu tablosuna kaydet
                        $dt = [
                            'id' => 0,
                            'islem_tarihi' => Date::Ymd($data[0]),  // İşlem tarihi sütunu
                            'daire_id' => 0,  // Eşleşmeyen daire için 0
                            'tutar' => $data[1],  // Tutar sütunu
                            'makbuz_no' => $data[4],  // Makbuz no sütunu
                            'aciklama' => 'Bilgi var ' . $data[3],  // Açıklama sütunu
                        ];
                        // Veritabanına kaydet
                        $lastInsertId = $TahsilatHavuzu->saveWithAttr($dt);

                        // Eşleşmeyen daire sayısını tut
                        $eşleşmeyen_kayıtlar++;
                    }
                } else {
                    // Apartman Bilgisi çıkarılamayan kayıtları tahsilat_havuzu tablosuna kaydet
                    $dt = [
                        'id' => 0,
                        'islem_tarihi' => Date::Ymd($data[0]),  // İşlem tarihi sütunu
                        'tahsilat_tutari' => $data[1],  // Tutar sütunu
                        'ham_aciklama' => $data[3],  // Açıklama sütunu
                        'referans_no' => $data[4],  // Makbuz no sütunu
                        'aciklama' => 'Bilgi Yok, eşleşmedi: ' . $data[3],  // Hata mesajı
                    ];
                    // Veritabanına kaydet
                    $lastInsertId = $TahsilatHavuzu->saveWithAttr($dt);

                    // Eşleşmeyen daire sayısını tut
                    $eşleşmeyen_kayıtlar++;
                }
            }
        } catch (Exception $e) {
            $failCount++;
            $failRows[] = $i + 1;

            // Hatalı kayıtları tahsilat_havuzu tablosuna kaydet
            $dt = [
                'id' => 0,
                'islem_tarihi' => Date::Ymd($data[0]),  // İşlem tarihi sütunu
                'tahsilat_tutari' => $data[1],  // Tutar sütunu
                'ham_aciklama' => 'Hatalı' . $data[3],  // Açıklama sütunu
                'aciklama' => $e->getMessage(),  // Hata mesajı
            ];
            // Veritabanına kaydet
            $lastInsertId = $TahsilatHavuzu->saveWithAttr($dt);

            // Eşleşmeyen daire sayısını tut
            $eşleşmeyen_kayıtlar++;
        }
    }

    $status = 'success';
    $message = "Yükleme tamamlandı.<br> Başarılı: $successCount, <br>Hatalı: $failCount";

    if ($failCount > 0) {
        $message .= '. <br>Hatalı satırlar: ' . implode(', ', $failRows);
    }

    if ($eşleşmeyen_kayıtlar > 0) {
        $message .= '. <br>Eşleşmeyen kayıt sayısı: ' . $eşleşmeyen_kayıtlar;
    }

    $res = [
        'status' => $status,
        'message' => $message,
        'bulunan_daireler' => $bulunan_daireler
    ];
    echo json_encode($res);
}
