<?php

require_once '../../../vendor/autoload.php';
session_start();


use \PhpOffice\PhpSpreadsheet\IOFactory;
use \PhpOffice\PhpSpreadsheet\Style\NumberFormat;

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
use Model\TahsilatOnayModel;

$Borc = new BorclandirmaModel();
$BorcDetay = new BorclandirmaDetayModel();
$Due = new DueModel();
$Bloklar = new BloklarModel();
$Tahsilat = new TahsilatModel();
$TahsilatHavuzu = new TahsilatHavuzuModel();
$TahsilatOnay = new TahsilatOnayModel();
$Daire = new DairelerModel();
$Kisi = new KisilerModel();

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
    $eslesmeyen_daireler = [];

    /**
     * Başarılı eşleşen daire için tahsilat kaydını veritabanına ekler.
     * @param $Tahsilat TahsilatModel öği
     * @param $data Satır verisi (array)
     * @param $daire Eşleşen daire ID'si
     * @return mixed Son eklenen kaydın ID'si
     */
    function kaydetTahsilatOnay($TahsilatOnay, $data, $daire_id) {
        //

        $islem_tarihi = Date::YmdHIS($data[0]); // İşlem tarihi
        $tutar = $data[1]; // Tutar, sayıya dönüştürülür
        $tahsilat_tipi = $data[4] ?? ''; // Tahsilat tipi (Ödeme Türü)
        $aciklama = $data[5] ?? ''; // Açıklama alanı, varsa kullanılır


        // Gerekirse diğer alanlar eklenebilir
        return $TahsilatOnay->saveWithAttr([
            'id' => 0,
            'kisi_id' => $data['kisi_id'] ?? 0, // Kişi ID'si
            'site_id' => $_SESSION['site_id'], // Site ID'si
            'tahsilat_tipi' => $tahsilat_tipi, // Tahsilat tipi
            'islem_tarihi' => $islem_tarihi,
            'daire_id' => $daire_id,
             'tutar' => $tutar,
            // 'makbuz_no' => $data[4],
            'aciklama' => $aciklama,
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
        $islem_tarihi = Date::Ymd($data[0]); // İşlem tarihi
        $ham_aciklama = $data[5] ?? ''; // Ham açıklama alanı, varsa kullanılır
        $referans_no = $data[6] ?? ''; // Makbuz no, varsa kullanılır
       


        return $TahsilatHavuzu->saveWithAttr([
            'id' => 0,
            'islem_tarihi' => $islem_tarihi, 
            'tahsilat_tutari' => Helper::formattedMoneyToNumber($data[1]),  // Tutar
            'ham_aciklama' => $ham_aciklama, 
            'referans_no' => $referans_no,
            'aciklama' => $aciklamaEk,             // Ek açıklama veya hata
        ]);
    }

    // Excel dosyasını oku ve satırları işle
    $spreadsheet = IOFactory::load($fileTmpName);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    // B Sütununun veri türü SAyı olarak ayarlanması
    $sheet->getStyle('B')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);

    foreach ($rows as $i => $data) {
        if ($i == 0) continue; // Başlık satırını atla
        try {
            $daire_id = 0;
            $apartmentInfo = null;
            $daire_kodu = $data[2] ?? ''; // Daire kodu
            $iyelik_tipi = $data[3] ?? 'Ev Sahibi'; // Ödeyen tipi (Ev Sahibi, Kiracı)
            $aciklama = $data[5] ;

            // Öncelikle doğrudan daire kodu ile eşleşme dene
            if (!empty($daire_kodu)) {
                $daire_id = $Daire->DaireId($daire_kodu) ?? 0;
                $kisi_id = $Kisi->AktifKisiByDaireId($daire_id, $iyelik_tipi)->id ?? 0;
            }
            // Daire kodu yoksa açıklamadan blok/daire bilgisi çıkar
            else if (!empty($aciklama)) {
                $apartmentInfo = Helper::extractApartmentInfo($aciklama);
                if ($apartmentInfo) {
                    $daire_id = $Daire->DaireId($apartmentInfo) ?? 0;
                    $kisi_id = $Kisi->AktifKisiByDaireId($daire_id, $iyelik_tipi)->id ?? 0;
                     
                }
            }

            // Eşleşen daire bulunduysa tahsilat kaydet
            if ($daire_id > 0 && !empty($kisi_id)) {
                $data['kisi_id'] = $kisi_id; // Kişi ID'sini ekle
                kaydetTahsilatOnay($TahsilatOnay, $data, $daire_id);
                $bulunan_daireler[] = $apartmentInfo ?? $daire_kodu  . "kisi_id: " . $data['kisi_id'];
                $successCount++;
            } else {
                // Eşleşmeyen kayıtları havuza kaydet
                $aciklamaEk = !empty($daire_kodu)
                    ? 'Daire Kodu eşleşmedi: ' . $daire_kodu
                    : ('Bilgi var ' . ($aciklama ?? ''));
                kaydetHavuz($TahsilatHavuzu, $data, $aciklamaEk);
                $eslesmeyen_daireler[] = $apartmentInfo ?? $daire_kodu;
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
        'bulunan_daireler' => $bulunan_daireler,
        'eslesmeyen_daireler' => $eslesmeyen_daireler,
    ]);
}
