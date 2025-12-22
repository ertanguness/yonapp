<?php


require_once dirname(__DIR__, 4) . '/configs/bootstrap.php';

use Dompdf\Options;
use App\Helper\Date;
use App\Helper\Helper;
use App\Helper\Security;

use App\Services\Gate;
use Model\KisilerModel;
use Model\SitelerModel;
use Dompdf\Dompdf as PDF;
use Model\KasaHareketModel;
use Model\FinansalRaporModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf; // Dompdf'i kullanmak için



// Yetki kontrolü
//Gate::can('gelir_gider_export');
$KisiModel = new KisilerModel();
$FinansalRaporModel = new FinansalRaporModel();

$format = $_GET['format'] ?? 'pdf'; // Varsayılan format pdf
$kisi_id = Security::decrypt($_GET['kisi_id'] ?? 0);


//Helper::dd($kisi_id);

//Kişi bilgilerini getir
$kisi = $KisiModel->getKisiByDaireId($kisi_id);
$kisiFinansalDurum = $FinansalRaporModel->kisiFinansalDurum($kisi_id);

$kisiHareketler = $FinansalRaporModel->kisiHesapHareketleri($kisi_id);

//echo '<pre>'; print_r($kisiHareketler); echo '</pre>'; exit;

if (empty($kisiHareketler)) {
    die('Dışarı aktarılacak veri bulunamadı.');
}
//echo '<pre>'; print_r($kisiHareketler); echo '</pre>'; exit;

// Spreadsheet oluştur
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$site_id_session = $_SESSION['site_id'] ?? 0;
$site = $site_id_session ? (new SitelerModel())->find($site_id_session) : null;
$logoPath = $site->logo_path ?? '';
$logoFile = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/logo/' . ($logoPath ?: 'default-logo.png');
if (!file_exists($logoFile)) {
    $logoFile = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/logo/default-logo.png';
}
$ext = strtolower(pathinfo($logoFile, PATHINFO_EXTENSION));
$imageCreated = null;
if ($ext === 'png') { $imageCreated = function_exists('imagecreatefrompng') ? @imagecreatefrompng($logoFile) : null; }
elseif ($ext === 'jpg' || $ext === 'jpeg') { $imageCreated = function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($logoFile) : null; }
elseif ($ext === 'gif') { $imageCreated = function_exists('imagecreatefromgif') ? @imagecreatefromgif($logoFile) : null; }
if ($imageCreated) {
    $md = new MemoryDrawing();
    $md->setName('Logo');
    $md->setDescription('Site Logo');
    $md->setImageResource($imageCreated);
    $md->setRenderingFunction(MemoryDrawing::RENDERING_PNG);
    $md->setMimeType(MemoryDrawing::MIMETYPE_DEFAULT);
    $md->setHeight(36);
    $md->setCoordinates('K1');
    $md->setOffsetX(2);
    $md->setOffsetY(2);
    $md->setWorksheet($sheet);
} else {
    $drawing = new Drawing();
    $drawing->setName('Logo');
    $drawing->setDescription('Site Logo');
    $drawing->setPath($logoFile);
    $drawing->setHeight(36);
    $drawing->setCoordinates('K1');
    $drawing->setOffsetX(2);
    $drawing->setOffsetY(2);
    $drawing->setWorksheet($sheet);
}

// Tüm spreadsheet için varsayılan fontu ayarla
$spreadsheet->getDefaultStyle()->getFont()->setName('DejaVu Sans');
$spreadsheet->getDefaultStyle()->getFont()->setSize(10);



/**Başlıkların yazılacağı satır no */
$headerRow = 10;

// Başlık satırı
$headers = [
    'A' . $headerRow => 'Sıra',
    'B' . $headerRow => 'İşlem Tarihi',
    'C' . $headerRow => 'Tahakkuk/ Tahsilat',
    'D' . $headerRow => 'Borç',
    'E' . $headerRow => 'Gecikme Zammı',
    'F' . $headerRow => 'Ödenen',
    'G' . $headerRow => 'Bakiye',
    'H' . $headerRow => 'Açıklama',
];

// Başlıkları set et
foreach ($headers as $cell => $value) {
    $sheet->setCellValue($cell, $value);
}

/* BAŞLIK AYARLARI */

$spreadsheet->setActiveSheetIndex(0);
$sheet->mergeCells('A1:K3');
$sheet->setCellValue('A1', $site->site_adi . "\n" . 'Kişi Hesap Hareketleri');

//Font Size ve Bold yap
//$sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);

//Ortala
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

//Kenarlık ekle
$sheet->getStyle('A1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

/**A4 Hücresine Daire No: ekle, Sağa yasla */
$sheet->mergeCells('A4:B4');
$sheet->setCellValue('A4', 'Adı Soyadı:');
// $sheet->getStyle('A4')->getFont()->setBold(true);
$sheet->getStyle('A4:B4')->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
    ->setVertical(Alignment::VERTICAL_CENTER);

//C4
$sheet->mergeCells('C4:F4');
$sheet->setCellValue('C4', $kisi->adi_soyadi ?? '');


$sheet->mergeCells('G4:J4');
$sheet->setCellValue('G4', 'Toplam Borç:  ');
$sheet->setCellValue('K4', Helper::formattedMoney($kisiFinansalDurum->toplam_borc ?? '0,00'));
//Bold yap

$sheet->getStyle('I4:J4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->getStyle('K4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

//Daire kodu
$sheet->getStyle('C5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
$sheet->mergeCells('A5:B5');
$sheet->setCellValue('A5', 'Daire Kodu:');
$sheet->mergeCells('C5:F5');
$sheet->setCellValue('C5', $kisi->daire_kodu ?? '');


$sheet->mergeCells('G5:J5');
$sheet->setCellValue('G5', 'Toplam Ödenen:  ');
$sheet->setCellValue('K5', Helper::formattedMoney($kisiFinansalDurum->toplam_tahsilat ?? '0,00'));
$sheet->getStyle('G5:J5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->getStyle('K5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);




$sheet->mergeCells('A6:B6');
$sheet->mergeCells('C6:F6');

$sheet->setCellValue('A6', 'Oturum Şekli:');
$sheet->setCellValue('C6', $kisi->uyelik_tipi ?? '');

$sheet->mergeCells('G6:J6');
$sheet->setCellValue('G6', 'Bakiye:  ');
$sheet->setCellValue('K6', Helper::formattedMoney($kisiFinansalDurum->bakiye ?? '0,00'));

$sheet->getStyle('I6:J6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->getStyle('K6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
//$sheet->getStyle('K6')->getFont()->setBold(true);

$sheet->mergeCells('A7:B7');
$sheet->mergeCells('C7:K7');

$sheet->setCellValue('A7', 'Çıkış Tarihi:');
$sheet->setCellValue('C7', Date::dmY($kisi->cikis_tarihi ?? ''));


$sheet->setCellValue('A8', 'Rapor Tarihi:');
$sheet->setCellValue('C8', date('d.m.Y H:i'));
$sheet->mergeCells('A8:B8');
$sheet->mergeCells('C8:K8');


$sheet->mergeCells('A9:K9');
$sheet->getStyle('A1:K9')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

//Satır yüksekliği 5 yap
$sheet->getRowDimension(9)->setRowHeight(15);
$sheet->getStyle('A2:K9')->getFont()->setSize(8);


$sheet->mergeCells('H10:K10');


// Son başlık hücresini bul
$lastHeaderColumn = 'K'; // 'A1'den 'G1'e kadar başlıklarınız var

// Başlık satırını formatla
$sheet->getStyle('A10:' . $lastHeaderColumn . '10')->applyFromArray([
    'font' => [
        'bold' => false,
        'color' => ['rgb' => '000000'],
        'size' => 9,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '9CAFAA']
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ]
    ]
]);

//site_id;kisi_id;adi_soyadi;daire_kodu;islem_id;islem_tarihi;islem_tipi;aciklama;anapara;gecikme_zammi;odenen


// Veri satırlarını doldur
$row = 11;
foreach ($kisiHareketler as $hareket) {
    $sheet->setCellValue('A' . $row, $row - 10); // 'islem_id' daha mantıklı olabilir
    $sheet->setCellValue('B' . $row, date('d.m.Y H:i', strtotime($hareket->islem_tarihi ?? '')));
    $sheet->setCellValue('C' . $row, ucfirst($hareket->borc_adi));
    $sheet->setCellValue('D' . $row, number_format((float)$hareket->anapara, 2, ',', '.')); // Float'a çevirme
    $sheet->setCellValue('E' . $row, number_format((float)$hareket->gecikme_zammi, 2, ',', '.')); // Float'a çevirme
    $sheet->setCellValue('F' . $row, number_format((float)$hareket->odenen, 2, ',', '.')); // Float'a çevirme
    $sheet->setCellValue('G' . $row, number_format((float)$hareket->yuruyen_bakiye, 2, ',', '.') ?: '-');

    //H ve K sütunlarını birleştir
    $sheet->mergeCells('H' . $row . ':K' . $row);
    $sheet->setCellValue('H' . $row, $hareket->aciklama ?: '-');

    $isDebt = ((float)$hareket->anapara > 0) || ((float)$hareket->gecikme_zammi > 0);
    $isPayment = ((float)$hareket->odenen > 0);

    
    // Satır rengini değiştir (çift satırlar açık gri)
    if ($row % 2 == 0) {
        $sheet->getStyle('A' . $row . ':' . $lastHeaderColumn . $row)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F8F9FA']
            ],
            'font' => [
                'size' => 9,
            ]
        ]);
    } else {
        // Tek satırlar için de font ayarla
        $sheet->getStyle('A' . $row . ':' . $lastHeaderColumn . $row)->applyFromArray([
            'font' => [
                'size' => 9,
            ]
        ]);
    }

    if ($isPayment) {
        $sheet->getStyle('A' . $row . ':' . $lastHeaderColumn . $row)->applyFromArray([
            'font' => [
                'color' => ['rgb' => '3F7D58'],
            ]
        ]);
    } elseif ($isDebt) {
        $sheet->getStyle('A' . $row . ':' . $lastHeaderColumn . $row)->applyFromArray([
            'font' => [
                'color' => ['rgb' => 'C51605'],
            ]
        ]);
    }

    $row++;
}

// // Sütun genişliklerini ayarla (sadece kullanılan sütunlar için)
// $sheet->getColumnDimension('A')->setWidth(15); // ID için artırıldı
$sheet->getColumnDimension('B')->setWidth(15); // İşlem Tarihi
$sheet->getColumnDimension('C')->setWidth(13); // İşlem Tipi
$sheet->getColumnDimension('D')->setWidth(10); // Anapara
$sheet->getColumnDimension('E')->setWidth(10); // Gecikme Zammı
$sheet->getColumnDimension('F')->setWidth(10); // Ödenen
$sheet->getColumnDimension('G')->setWidth(10); // Bakiye
$sheet->getColumnDimension('H')->setWidth(15); // Açıklama
$sheet->getColumnDimension('I')->setWidth(15); // Açıklama
$sheet->getColumnDimension('J')->setWidth(15); // Açıklama
$sheet->getColumnDimension('K')->setWidth(15); // Açıklama

//D'den G'ye kadar olan sütunları sağa hizala
$sheet->getStyle('D11:G' . ($row - 1))
    ->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

// Açıklama ve genel hücreler için satır kaydır
$sheet->getStyle('H11:H' . ($row - 1))
    ->getAlignment()
    ->setWrapText(true);


//H sütununa indent ekle
$sheet->getStyle('H11:H' . ($row - 1))
    ->getAlignment()
    ->setIndent(1);

$printLastRow = $row - 1;


// Tüm verilere border ekle (son kullanılan sütuna göre)
$sheet->getStyle('A10:' . $lastHeaderColumn . ($row - 1))->applyFromArray([ // Burası düzeltildi
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '222222']
        ],

    ]
]);


// Print ayarları - Başlık satırını her sayfada yinele
//$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(start: 1, end: 9);


$spreadsheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
$spreadsheet->getActiveSheet()->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);



// Dosya adı
$filename = $kisi->adi_soyadi . ' hesap_ozeti_' . date('Y-m-d_H-i-s');

// Document properties ayarla (HTML export için)
$spreadsheet->getProperties()
    ->setCreator($site->site_adi ?? 'YonApp')
    ->setTitle($filename)
    ->setSubject('Kişi Hesap Özeti')
    ->setDescription('Kişi borç ve tahsilat detayları');

// Format'a göre export yap
try {
    switch ($format) {
        case 'xlsx':
        case 'excel':
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');


            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            break;

        case 'csv':
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '.csv"');
            header('Cache-Control: max-age=0');

            $writer = new Csv($spreadsheet);


            $writer->setDelimiter(';');
            $writer->setEnclosure('"');
            $writer->setLineEnding("\r\n");
            $writer->setSheetIndex(0);
            $writer->save('php://output');
            break;

        case 'html':
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: inline');
            header('Cache-Control: max-age=0');

            ob_start();


            $sheet->getStyle('A3:K' . ($row - 1))->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'indent' => 1,
                    'wrapText' => true
                ]
            ]);

            $writer = new Html($spreadsheet);
            $writer->setSheetIndex(0);
            $writer->save('php://output');
            $htmlContent = ob_get_clean();

            $printScript = '
                <style>
                    @page {
                        size: A4 portrait;
                        margin: 1cm;
                    }
                    @media print {
                        body { margin: 0; padding: 0; }
                        table { page-break-inside: auto; }
                        tr { page-break-inside: avoid; page-break-after: auto; }
                    }
                </style>
                <script>
                    window.onload = function() {
                        window.print();
                    };
                </script>
                </body>';

            $htmlContent = str_replace('</body>', $printScript, $htmlContent);
            echo $htmlContent;
            break;



        case 'pdf':
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
            header('Cache-Control: max-age=0');


            $dataLastRow = $row - 1;

            // PDF writer'ı ayarla
            IOFactory::registerWriter('Pdf', Dompdf::class);

            //Sayfayı yatay yap
            $spreadsheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
            $spreadsheet->getActiveSheet()->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);

            // 1. Başlık tekrarını ayarla (bu satır bold sorununu tetikliyor)
            $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 9);

            // 2. Tekrarlanan tüm başlık alanını (A1:K9) ÖNCE normale (bold=false) zorla.
            $sheet->getStyle('A1:K9')->getFont()->setBold(false);



            $sheet->getStyle('A3:K' . ($row - 1))->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'indent' => 1,
                    'wrapText' => true
                ]
            ]);
            $sheet->getStyle('A3:K8')->applyFromArray([
                'font' => [
                    'bold' => false,
                ]
            ]);

            //$sheet->getStyle('K4:K6')->getFont()->setBold(true);

            $sheet->getStyle('A9:' . $lastHeaderColumn . '9')->applyFromArray([
                'font' => [
                    'regular' => true,
                    'color' => ['rgb' => '000000'],
                    'size' => 8,
                ]
            ]);


            //D'den G'ye kadar olan sütunları sağa hizala
            $sheet->getStyle('D10:G' . ($row - 1))
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $sheet->getStyle('A10:H' . ($row - 1))
                ->applyFromArray([
                    'font' => [
                        'size' => 8,
                        'name' => 'DejaVu Sans',
                    ]
                ]);

            $writer = IOFactory::createWriter($spreadsheet, 'Pdf');
            $writer->save('php://output');
            break;

        case "xml":
            header('Content-Type: application/xml; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '.xml"');
            header('Cache-Control: max-age=0');

            // Manuel XML oluştur
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<KisiHesapHareketleri>' . "\n"; // Kök etiketi düzeltildi
            $xml .= '  <Rapor>' . "\n";
            $xml .= '    <Tarih>' . date('Y-m-d H:i:s') . '</Tarih>' . "\n";
            $xml .= '    <KisiId>' . htmlspecialchars($kisi_id, ENT_XML1, 'UTF-8') . '</KisiId>' . "\n"; // KasaId yerine KisiId
            $xml .= '    <AdiSoyadi>' . htmlspecialchars($kisiHareketler[0]->adi_soyadi ?? '', ENT_XML1, 'UTF-8') . '</AdiSoyadi>' . "\n"; // İlk hareketten ad-soyad al
            $xml .= '    <DaireKodu>' . htmlspecialchars($kisiHareketler[0]->daire_kodu ?? '', ENT_XML1, 'UTF-8') . '</DaireKodu>' . "\n"; // İlk hareketten daire kodu al
            $xml .= '  </Rapor>' . "\n";
            $xml .= '  <Hareketler>' . "\n";

            foreach ($kisiHareketler as $hareket) {
                $xml .= '    <Hareket>' . "\n";
                $xml .= '      <ID>' . htmlspecialchars($hareket->islem_id, ENT_XML1, 'UTF-8') . '</ID>' . "\n"; // 'id' yerine 'islem_id'
                $xml .= '      <IslemTarihi>' . htmlspecialchars(date('Y-m-d H:i:s', strtotime($hareket->islem_tarihi)), ENT_XML1, 'UTF-8') . '</IslemTarihi>' . "\n";
                $xml .= '      <IslemTipi>' . htmlspecialchars(ucfirst($hareket->islem_tipi), ENT_XML1, 'UTF-8') . '</IslemTipi>' . "\n";
                $xml .= '      <Anapara>' . htmlspecialchars($hareket->anapara, ENT_XML1, 'UTF-8') . '</Anapara>' . "\n"; // Tutar yerine Anapara, Gecikme Zammı, Ödenen
                $xml .= '      <GecikmeZammi>' . htmlspecialchars($hareket->gecikme_zammi, ENT_XML1, 'UTF-8') . '</GecikmeZammi>' . "\n";
                $xml .= '      <Odenen>' . htmlspecialchars($hareket->odenen, ENT_XML1, 'UTF-8') . '</Odenen>' . "\n";
                $xml .= '      <Aciklama>' . htmlspecialchars($hareket->aciklama ?: '', ENT_XML1, 'UTF-8') . '</Aciklama>' . "\n";
                // KasaHareketModel'deki gibi kaynak tablo, kaynak id, kayıt yapan, oluşturma tarihi bu modelde yok.
                // Eğer olsaydı eklerdik. Şimdilik ilgili alanları XML'den çıkardım.
                $xml .= '    </Hareket>' . "\n";
            }

            $xml .= '  </Hareketler>' . "\n";
            $xml .= '</KisiHesapHareketleri>';

            echo $xml;
            break;

        default:
            throw new Exception('Geçersiz format: ' . $format);
    }

    exit();
} catch (Exception $e) {
    die('Export hatası: ' . $e->getMessage());
}
