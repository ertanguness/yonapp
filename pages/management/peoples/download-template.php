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

// 3. Başlıkları yazdır
$sheet->setCellValue('A1', 'DaireKoduBenzersiz*');
$sheet->setCellValue('B1', 'AdSoyad*');
$sheet->setCellValue('C1', 'Telefon*');
$sheet->setCellValue('D1', 'IyelikTuru(Ev Sahibi/Kiracı)');
// ... diğer başlıklar ...

// Başlık satırını kalın yap
$sheet->getStyle('A1:D1')->getFont()->setBold(true);

// 4. Daireleri Excel'e yazdır
$rowNumber = 2;
foreach ($daireler as $daire) {
    $sheet->setCellValue('A' . $rowNumber, $daire->daire_kodu);
    // Diğer sütunlar boş kalacak, kullanıcı dolduracak
    $rowNumber++;
}

// Sütun genişliklerini otomatik ayarla
foreach (range('A', 'D') as $columnID) {
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