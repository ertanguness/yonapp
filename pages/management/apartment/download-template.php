<?php
// download-template.php
ob_start();
require_once dirname(__DIR__,levels: 3) . '/configs/bootstrap.php'; // Gerekli dosyaları yükle

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

use Model\BloklarModel;
use Model\DefinesModel;
use App\Helper\DefinesHelper;

// Gelen parametreleri al ve doğrula
$siteId = $_SESSION["site_id"] ?? null;
$blokId = filter_input(INPUT_GET, 'blok_id', FILTER_VALIDATE_INT);

if (!$siteId) {
    die("Geçersiz site seçimi.");
}
// BloklarModel'i kullanarak blokları al
$blokModel = new BloklarModel();
$definesModel = new DefinesModel();

$bloklar = $blokModel->SiteBloklari($siteId);

//Daire tiplerini al
$daireTipleri = $definesModel->getDefinesTypes($siteId, DefinesHelper::TYPE_APARTMENT);


// 2. PhpSpreadsheet nesnesi oluştur
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Daire Yukleme Sablonu');

// Daire Kodu*,Blok Adı*, Kat,Daire No, Daire Tipi,Brüt Alan, Net Alan Arsa Payı,Kullanım Durumu(Kullanımda, Boş),Açıklama
// 3. Başlıkları yazdır
$sheet->setCellValue('A1', 'Blok Adı*');
$sheet->setCellValue('B1', 'Daire No*');
$sheet->setCellValue('C1', 'Daire Kodu*(Boş bırakılabilir, sistem otomatik oluşturacak)');
$sheet->setCellValue('D1', 'Daire Tipi');
$sheet->setCellValue('E1', 'Kat');
$sheet->setCellValue('F1', 'Brüt Alan*');
$sheet->setCellValue('G1', 'Net Alan*');
$sheet->setCellValue('H1', 'Arsa Payı*');
$sheet->setCellValue('I1', 'Kullanım Durumu (Kullanımda, Boş)*');
$sheet->setCellValue('J1', 'Açıklama');




// Başlık satırını kalın yap
$sheet->getStyle('A1:O1')->getFont()->setBold(true);

// 4. Daireleri Excel'e yazdır
$rowNumber = 2;
foreach ($bloklar as $blok) {
    //Blok'un daire sayısını al ve o daire sayısı kadar satır ekle
    $daireSayisi = $blok->daire_sayisi ?? 0;
    for ($i = 1; $i <= $daireSayisi; $i++) {
        $sheet->setCellValue('A' . $rowNumber, $blok->blok_adi);
        $sheet->setCellValue('B' . $rowNumber, $i); // Daire No
        $sheet->setCellValue('C' . $rowNumber, str_replace(" Blok","",$blok->blok_adi) . "D" . $i); // Daire Kodu (boş bırakılacak)
        $sheet->setCellValue('D' . $rowNumber, ''); // Kat (boş bırakılacak)
        $sheet->setCellValue('E' . $rowNumber, ''); // Daire Tipi (Açılır Listeden seçilecek)
        $sheet->setCellValue('F' . $rowNumber, ''); // Brüt Alan (boş bırakılacak)
        $sheet->setCellValue('G' . $rowNumber, ''); // Net Alan (boş bırakılacak)
        $sheet->setCellValue('H' . $rowNumber, ''); // Arsa Payı (boş bırakılacak)
        $sheet->setCellValue('I' . $rowNumber, ''); // Kullanım Durumu (boş bırakılacak)
        $sheet->setCellValue('J' . $rowNumber, ''); // Açıklama (boş bırakılacak)
        if ($i < $daireSayisi) $rowNumber++;
    }


    // Diğer sütunlar boş kalacak, kullanıcı dolduracak
    $rowNumber++;
}




// --- 2. GİZLİ "VERİ" SAYFASINI OLUŞTUR ---
$dataSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'VeriSayfasi');
$spreadsheet->addSheet($dataSheet);

// Daire tiplerini bu gizli sayfaya yazdır
$dataSheet->setCellValue('A1', 'Daire Tipleri');
$dataSheet->getStyle('A1')->getFont()->setBold(true);
$typeRow = 2;
foreach ($daireTipleri as $tip) {
    $dataSheet->setCellValue('A' . $typeRow, $tip->define_name);
    
    $typeRow++;
}

//Kullanım Durumunu seçim listesi olarak ekle
$dataSheet->setCellValue('B1', 'Kullanım Durumu');
$dataSheet->getStyle('B1')->getFont()->setBold(true);
$dataSheet->setCellValue('B2', 'Dolu');
$dataSheet->setCellValue('B3', 'Boş');

// Bu "Veri" sayfasını kullanıcıdan gizle
$dataSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_VERYHIDDEN);


// --- 3. ANA SAYFAYA AÇILIR LİSTEYİ (VERİ DOĞRULAMA) EKLE ---
// Daire Tipi sütunu (C sütunu) için veri doğrulaması yapacağız.
// 2. satırdan 100. satıra kadar bu kuralı uygulayalım (ihtiyaca göre artırılabilir).
for ($i = 2; $i <= $rowNumber; $i++) {
    $validation = $sheet->getCell('D' . $i)->getDataValidation();
    
    $validation->setType(DataValidation::TYPE_LIST);
    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
    $validation->setAllowBlank(true); // Boş bırakılmasına izin ver
    $validation->setShowInputMessage(true);
    $validation->setShowErrorMessage(true);
    $validation->setShowDropDown(true); // Açılır oku göster
    
    // Açılır listenin formülü: Verileri 'VeriSayfasi'ndaki A2'den A'nın sonuna kadar al.
    // Dolar işaretleri ($) formülün satır kaydıkça değişmesini engeller.
    $validation->setFormula1("=VeriSayfasi!\$A\$2:\$A$" . ($typeRow - 1));
    

    // Kullanım Durumu sütunu (I sütunu) için de aynı şekilde veri doğrulaması yapalım
    $validation = $sheet->getCell('I' . $i)->getDataValidation();

    $validation->setType(DataValidation::TYPE_LIST);
    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
    $validation->setAllowBlank(true); // Boş bırakılmasına izin ver
    $validation->setShowInputMessage(true);
    $validation->setShowErrorMessage(true);
    $validation->setShowDropDown(true); // Açılır oku göster

    $validation->setFormula1("=VeriSayfasi!\$B\$2:\$B$3");


}


// Sütun genişliklerini otomatik ayarla
foreach (range('A', 'O') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
    //Sütundaki verileri ortala
    $sheet->getStyle($columnID)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
}
ob_end_clean(); 
// 5. Dosyayı tarayıcıya indirme olarak gönder
$writer = new Xlsx($spreadsheet);

$fileName = 'daire_yukleme_sablonu_' . date('Ymd') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
$writer->save('php://output');
exit();