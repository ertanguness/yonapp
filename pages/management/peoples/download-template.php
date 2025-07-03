<?php
// download-template.php
ob_start();
require_once dirname(__DIR__,levels: 3) . '/configs/bootstrap.php'; // Gerekli dosyaları yükle

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Model\DairelerModel;

// Gelen parametreleri al ve doğrula
$siteId = $_SESSION["site_id"] ?? null;
$blokId = filter_input(INPUT_GET, 'blok_id', FILTER_VALIDATE_INT);

if (!$siteId) {
    die("Geçersiz site seçimi.");
}

// 1. Veritabanından ilgili daireleri çek
$daireModel = new DairelerModel();
// DairelerModel'e siteId ve/veya blokId'ye göre daireleri getiren bir metot ekleyin.
// Örnek: $daireler = $daireModel->getDairelerForTemplate($siteId, $blokId);
$daireler = $daireModel->getDairelerForTemplate($siteId, $blokId ?? null);



// 2. PhpSpreadsheet nesnesi oluştur
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Kisi Yukleme Sablonu');

// id;site_id;blok_id;daire_id;kimlik_no;adi_soyadi;dogum_tarihi;cinsiyet;uyelik_tipi;telefon;eposta;adres;notlar;satin_alma_tarihi;giris_tarihi;cikis_tarihi;aktif_mi;kayit_tarihi;guncelleme_tarihi;silinme_tarihi;silen_kullanici
// 3. Başlıkları yazdır
$sheet->setCellValue('A1', 'Blok Adı*');
$sheet->setCellValue('B1', 'Daire No*');
$sheet->setCellValue('C1', 'Kimlik No*');
$sheet->setCellValue('D1', 'Adı Soyadı*');
$sheet->setCellValue('E1', 'Doğum Tarihi (gg.aa.yyyy)');
$sheet->setCellValue('F1', 'Cinsiyet (Erkek/Kadın)');
$sheet->setCellValue('G1', 'Uyeliği (Ev Sahibi/Kiracı)');
$sheet->setCellValue('H1', 'Telefon');
$sheet->setCellValue('I1', 'Eposta');
$sheet->setCellValue('J1', 'Adres');
$sheet->setCellValue('K1', 'Notlar');
$sheet->setCellValue('L1', 'Satin Alma Tarihi');
$sheet->setCellValue('M1', 'Giriş Tarihi');
$sheet->setCellValue('N1', 'Çıkış Tarihi');
$sheet->setCellValue('O1', 'Aktiflik Durumu');


$sheet->setCellValue('A2', ($blokId));

$sheet->setCellValue('D1', 'IyelikTuru(Ev Sahibi/Kiracı)');
// ... diğer başlıklar ...

// Başlık satırını kalın yap
$sheet->getStyle('A1:Z1')->getFont()->setBold(true);

// 4. Daireleri Excel'e yazdır
$rowNumber = 2;
foreach ($daireler as $daire) {
    $sheet->setCellValue('A' . $rowNumber, $daire->blok_adi);
    $sheet->setCellValue('B' . $rowNumber, $daire->daire_no);
    // Diğer sütunlar boş kalacak, kullanıcı dolduracak
    $rowNumber++;
}

// Sütun genişliklerini otomatik ayarla
foreach (range('A', 'Z') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}
ob_end_clean(); 
// 5. Dosyayı tarayıcıya indirme olarak gönder
$writer = new Xlsx($spreadsheet);

$fileName = 'kisi_yukleme_sablonu_' . date('Ymd') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
$writer->save('php://output');
exit();