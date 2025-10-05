<?php 


require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';

use App\Helper\Helper;
use App\Services\Gate;
use Model\KasaHareketModel;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Yetki kontrolü
//Gate::can('gelir_gider_export');


$format = $_GET['format'] ?? 'xlsx';
$kasa_id = $_SESSION['kasa_id'];

$KasaHareketModel = new KasaHareketModel();
$kasaHareketler = $KasaHareketModel->getKasaHareketleri($kasa_id);

if (empty($kasaHareketler)) {
    die('Dışarı aktarılacak veri bulunamadı.');
}

// Spreadsheet oluştur
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Başlık satırı
$headers = [
    'A1' => 'ID',
    'B1' => 'İşlem Tarihi', 
    'C1' => 'İşlem Tipi',
    'D1' => 'Tutar',
    'E1' => 'Para Birimi',
    'F1' => 'Kategori',
    'G1' => 'Açıklama',
    'H1' => 'Kaynak Tablo',
    'I1' => 'Kaynak ID',
    'J1' => 'Kayıt Yapan',
    'K1' => 'Oluşturma Tarihi'
];

// Başlıkları set et
foreach ($headers as $cell => $value) {
    $sheet->setCellValue($cell, $value);
}

// Başlık satırını formatla
$sheet->getStyle('A1:K1')->applyFromArray([
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF']
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '366092']
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

// Veri satırlarını doldur
$row = 2;
foreach ($kasaHareketler as $hareket) {
    $sheet->setCellValue('A' . $row, $hareket->id);
    $sheet->setCellValue('B' . $row, date('d.m.Y H:i', strtotime($hareket->islem_tarihi)));
    $sheet->setCellValue('C' . $row, ucfirst($hareket->islem_tipi));
    $sheet->setCellValue('D' . $row, number_format($hareket->tutar, 2, ',', '.'));
    $sheet->setCellValue('E' . $row, $hareket->para_birimi);
    $sheet->setCellValue('F' . $row, $hareket->kategori);
    $sheet->setCellValue('G' . $row, $hareket->aciklama ?: '-');
    $sheet->setCellValue('H' . $row, $hareket->kaynak_tablo ?: '-');
    $sheet->setCellValue('I' . $row, $hareket->kaynak_id ?: '-');
    $sheet->setCellValue('J' . $row, $hareket->kayit_yapan ?: '-');
    $sheet->setCellValue('K' . $row, date('d.m.Y H:i', strtotime($hareket->created_at)));
    
    // Satır rengini değiştir (çift satırlar açık gri)
    if ($row % 2 == 0) {
        $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F8F9FA']
            ]
        ]);
    }
    
    $row++;
}

// Sütun genişliklerini ayarla
$sheet->getColumnDimension('A')->setWidth(8);
$sheet->getColumnDimension('B')->setWidth(15);
$sheet->getColumnDimension('C')->setWidth(12);
$sheet->getColumnDimension('D')->setWidth(12);
$sheet->getColumnDimension('E')->setWidth(10);
$sheet->getColumnDimension('F')->setWidth(15);
$sheet->getColumnDimension('G')->setWidth(25);
$sheet->getColumnDimension('H')->setWidth(15);
$sheet->getColumnDimension('I')->setWidth(10);
$sheet->getColumnDimension('J')->setWidth(12);
$sheet->getColumnDimension('K')->setWidth(15);

// Tüm verilere border ekle
$sheet->getStyle('A1:K' . ($row - 1))->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ]
    ]
]);


// Print ayarları - Başlık satırını her sayfada tekrarla
$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);

// Header ve Footer ekle
$sheet->getHeaderFooter()->setOddHeader('&C&B&14Kasa Hareketleri');
$sheet->getHeaderFooter()->setOddFooter('&L&D &T&RPage &P of &N');

// Dosya adı
$filename = 'kasa_hareketleri_' . date('Y-m-d_H-i-s');

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
            header('Content-Disposition: attachment;filename="' . $filename . '.html"');
            
            $writer = new Html($spreadsheet);
            $writer->setSheetIndex(0);
            $writer->save('php://output');
            break;
            
        case 'pdf':
            // PDF için Dompdf kullanıyoruz
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
            header('Cache-Control: max-age=0');
            
            // PDF writer'ı ayarla
            IOFactory::registerWriter('Pdf', Dompdf::class);
            $writer = IOFactory::createWriter($spreadsheet, 'Pdf');
            
            // Kağıt boyutu ve oryantasyon
            $spreadsheet->getActiveSheet()->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
            $spreadsheet->getActiveSheet()->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
            
            $writer->save('php://output');
            break;

        case "xml":
           header('Content-Type: application/xml; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '.xml"');
            header('Cache-Control: max-age=0');

            // Manuel XML oluştur
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<KasaHareketleri>' . "\n";
            $xml .= '  <Rapor>' . "\n";
            $xml .= '    <Tarih>' . date('Y-m-d H:i:s') . '</Tarih>' . "\n";
            $xml .= '    <KasaId>' . $kasa_id . '</KasaId>' . "\n";
            $xml .= '  </Rapor>' . "\n";
            $xml .= '  <Hareketler>' . "\n";
            
            foreach ($kasaHareketler as $hareket) {
                $xml .= '    <Hareket>' . "\n";
                $xml .= '      <Id>' . htmlspecialchars($hareket->id, ENT_XML1, 'UTF-8') . '</Id>' . "\n";
                $xml .= '      <IslemTarihi>' . htmlspecialchars(date('Y-m-d H:i:s', strtotime($hareket->islem_tarihi)), ENT_XML1, 'UTF-8') . '</IslemTarihi>' . "\n";
                $xml .= '      <IslemTipi>' . htmlspecialchars(ucfirst($hareket->islem_tipi), ENT_XML1, 'UTF-8') . '</IslemTipi>' . "\n";
                $xml .= '      <Tutar>' . htmlspecialchars($hareket->tutar, ENT_XML1, 'UTF-8') . '</Tutar>' . "\n";
                $xml .= '      <ParaBirimi>' . htmlspecialchars($hareket->para_birimi, ENT_XML1, 'UTF-8') . '</ParaBirimi>' . "\n";
                $xml .= '      <Kategori>' . htmlspecialchars($hareket->kategori, ENT_XML1, 'UTF-8') . '</Kategori>' . "\n";
                $xml .= '      <Aciklama>' . htmlspecialchars($hareket->aciklama ?: '', ENT_XML1, 'UTF-8') . '</Aciklama>' . "\n";
                $xml .= '      <KaynakTablo>' . htmlspecialchars($hareket->kaynak_tablo ?: '', ENT_XML1, 'UTF-8') . '</KaynakTablo>' . "\n";
                $xml .= '      <KaynakId>' . htmlspecialchars($hareket->kaynak_id ?: '', ENT_XML1, 'UTF-8') . '</KaynakId>' . "\n";
                $xml .= '      <KayitYapan>' . htmlspecialchars($hareket->kayit_yapan ?: '', ENT_XML1, 'UTF-8') . '</KayitYapan>' . "\n";
                $xml .= '      <OlusturmaTarihi>' . htmlspecialchars(date('Y-m-d H:i:s', strtotime($hareket->created_at)), ENT_XML1, 'UTF-8') . '</OlusturmaTarihi>' . "\n";
                $xml .= '    </Hareket>' . "\n";
            }
            
            $xml .= '  </Hareketler>' . "\n";
            $xml .= '</KasaHareketleri>';
            
            echo $xml;
            break;
            
        default:
            throw new Exception('Geçersiz format: ' . $format);
    }
    
    exit();
    
} catch (Exception $e) {
    die('Export hatası: ' . $e->getMessage());
}