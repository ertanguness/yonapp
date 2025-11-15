<?php
// Gelir-Gider Raporu - Resimdeki gibi çıktı verecek şekilde düzenlendi
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';












use Model\SitelerModel;
use App\Helper\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Style\Color;
// Settings sınıfı mevcut ancak hücre cache konfigürasyonu v5'te farklıdır; burada kullanmıyoruz

// --- Veri Çekme ve Hazırlık (Bu kısım projenize göre aynı kalabilir) ---
$site_id = $_SESSION['site_id'] ?? 0;
$format = strtolower($_GET['format'] ?? 'pdf');
// Raporun Eylül 2025'e ait olduğunu varsayarak tarihleri ayarlıyoruz.
// Gerçek kullanımda bu tarihleri dinamik olarak almanız gerekebilir.
$start = $_GET['start'] ?? date('Y-m-01', strtotime('first day of last month'));
$end = $_GET['end'] ?? date('Y-m-t', strtotime('last day of last month'));

// Site bilgilerini al


$Siteler = new SitelerModel();
$site = $Siteler->find($site_id);
if (!$site) {
    die('Site bulunamadı');
}

// Model'den verileri çekme
use Model\KasaModel;
use Model\KasaHareketModel;

$KasaModel = new KasaModel();

$varsayilan_kasa_id = $KasaModel->varsayilanKasa();

$KasaHareketModel = new KasaHareketModel();
$selected_kasa_id = isset($_GET['kasa_id']) ? intval($_GET['kasa_id']) : ($varsayilan_kasa_id->id ?? 0);

// Gelir ve Gider verilerini ayrı ayrı çek
$gelirler_raw = $KasaHareketModel->getKasaHareketleriByDateRange($selected_kasa_id, $start, $end, 'Gelir');
$giderler_raw = $KasaHareketModel->getKasaHareketleriByDateRange($selected_kasa_id, $start, $end, 'Gider');
// --- Verileri liste görünümünde hazırlama (resimdeki gibi satır satır) ---
$gelirler_list = $gelirler_raw;  // Objeler doğrudan kullanılacak
$giderler_list = $giderler_raw;
$toplam_gelir = 0;
foreach ($gelirler_list as $v) {
    $toplam_gelir += floatval($v->tutar ?? 0);
}
$toplam_gider = 0;
foreach ($giderler_list as $v) {
    $toplam_gider += floatval($v->tutar ?? 0);
}


// --- Spreadsheet Oluşturma ---
// Performans: bellek sınırı ve maksimum çalışma süresi
@ini_set('memory_limit', '1024M');
@set_time_limit(180);

$ss = new Spreadsheet();
$sheet = $ss->getActiveSheet();
$ss->getDefaultStyle()->getFont()->setName('Arial');
$ss->getDefaultStyle()->getFont()->setSize(10);
$sheet->setTitle('Gelir Gider Raporu');

// Sayfa düzeni: yatay, A4, minimum kenar boşlukları ve tek sayfaya sığdırma (yatayda)
$sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
$sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
$sheet->getPageSetup()->setFitToWidth(1);
$sheet->getPageSetup()->setFitToHeight(0); // yükseklik serbest, satırlar sayfalar boyunca devam eder
$sheet->getPageMargins()->setTop(0.2);
$sheet->getPageMargins()->setRight(0.2);
$sheet->getPageMargins()->setLeft(0.2);
$sheet->getPageMargins()->setBottom(0.2);
$sheet->getPageMargins()->setHeader(0);
$sheet->getPageMargins()->setFooter(0);

// Kolon Genişlikleri (Sol: Giderler | Sağ: Gelirler)
$sheet->getColumnDimension('A')->setWidth(4);   // Sıra (biraz daha dar)
$sheet->getColumnDimension('B')->setWidth(9);   // Tarih
$sheet->getColumnDimension('C')->setWidth(9);   // Evrak No
$sheet->getColumnDimension('D')->setWidth(28);  // Cari Hesap Adı
$sheet->getColumnDimension('E')->setWidth(14);  // Tutar
$sheet->getColumnDimension('F')->setWidth(2);   // Ayırıcı
$sheet->getColumnDimension('G')->setWidth(4);   // Sıra (biraz daha dar)
$sheet->getColumnDimension('H')->setWidth(9);   // Tarih
$sheet->getColumnDimension('I')->setWidth(9);   // Fiş No
$sheet->getColumnDimension('J')->setWidth(18);  // Daire No / Hesap Adı
$sheet->getColumnDimension('K')->setWidth(36);  // Açıklama
$sheet->getColumnDimension('L')->setWidth(14);  // Tutar

// Kenarlık stili yardımcı dizi
$thinBorder = [
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
    ]
];

// --- Başlıklar ---
// Sol üstte site adı
$sheet->mergeCells('A1:I1');
$sheet->setCellValue('A1', mb_strtoupper($site->site_adi ?? 'SİTE ADI', 'UTF-8'));
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Sağ üstte tarih-saat kutusu
$sheet->mergeCells('J1:L1');
$sheet->mergeCells('J2:L2');
$sheet->setCellValue('J1', 'Tarih-Saat');
$sheet->setCellValue('J2', date('d F Y H:i'));
$sheet->getStyle('J1:L2')->applyFromArray($thinBorder);
$sheet->getStyle('J1:L1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('J2:L2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Alt başlık (rapor dönemi)
$sheet->mergeCells('A2:I2');
$sheet->setCellValue('A2', 'Site Gelir-Gider Raporu [' . date('d.m.Y', strtotime($start)) . ']-[' . date('d.m.Y', strtotime($end)) . ']');
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Bölüm başlıkları
$sheet->mergeCells('A4:E4');
$sheet->setCellValue('A4', 'GİDERLER');
$sheet->getStyle('A4')->getFont()->setBold(true);
$sheet->mergeCells('G4:L4');
$sheet->setCellValue('G4', 'GELİRLER');
$sheet->getStyle('G4')->getFont()->setBold(true);

// Sütun başlıkları: 3 satırlı başlık düzeni
// Giderler başlıkları
$sheet->mergeCells('A5:C5');
$sheet->mergeCells('A6:C6');
$sheet->mergeCells('A7:C7');
$sheet->setCellValue('A5', 'Sıra No');
$sheet->setCellValue('A6', 'Tarih');
$sheet->setCellValue('A7', 'Evrak No');
$sheet->mergeCells('D5:D7');
$sheet->setCellValue('D5', 'Cari Hesap Adı');
$sheet->mergeCells('E5:E7');
$sheet->setCellValue('E5', 'Tutar');

// Gelirler başlıkları
$sheet->mergeCells('G5:I5');
$sheet->mergeCells('G6:I6');
$sheet->mergeCells('G7:I7');
$sheet->setCellValue('G5', 'Sıra No');
$sheet->setCellValue('G6', 'Tarih');
$sheet->setCellValue('G7', 'Fiş No');
$sheet->setCellValue('J5', 'Daire No');
$sheet->setCellValue('J6', 'Hesap Adı');
$sheet->setCellValue('J7', '');
$sheet->mergeCells('K5:K7');
$sheet->setCellValue('K5', 'Açıklama');
$sheet->mergeCells('L5:L7');
$sheet->setCellValue('L5', 'Tutar');

$sheet->getStyle('A5:E7')->getFont()->setBold(true);
$sheet->getStyle('G5:L7')->getFont()->setBold(true);
// $sheet->getStyle('A5:E7')->applyFromArray($thinBorder);
// $sheet->getStyle('G5:L7')->applyFromArray($thinBorder);
// Başlık hizalamaları
$sheet->getStyle('A5:E7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
$sheet->getStyle('G5:L7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
// Başlık dış kenarlıkları kalın
$sheet->getStyle('A5:E7')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);
$sheet->getStyle('G5:L7')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);

//Başlık satırlarını yinele (yalnızca tablo başlıkları 5-7)
$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 7);

// --- 🔹 Üst Bilgi (Header) ve Alt Bilgi (Footer) ---
$headerFooter = $sheet->getHeaderFooter();

// Alt bilgiye sayfa numarası ekle
$headerFooter->setOddFooter('&C&B Sayfa &P / &N');



// --- Veri Satırları (Her kayıt için 3 satır) ---
$rowStart = 8; // 3 satırlı başlık sonrası veri 8. satırda başlar
$max = max(count($giderler_list), count($gelirler_list));
$gi = 0;
$ge = 0; // sıra numaraları
$r = $rowStart;
for ($i = 0; $i < $max; $i++) {
    $top = $r;
    $mid = $r + 1;
    $bot = $r + 2; // 3 satırlı blok

    // Giderler (sol blok: A..E)
    if (isset($giderler_list[$i])) {
        $g = $giderler_list[$i];

        // Hücre değer yerleşimi (3 satır, tek sütun A..C)
        $sheet->mergeCells('A' . $top . ':C' . $top);
        $sheet->setCellValue('A' . $top, ++$gi); // Sıra No (üst satır)
        $sheet->mergeCells('A' . $mid . ':C' . $mid);
        $sheet->setCellValue('A' . $mid, date('d.m.Y H:i', strtotime($g->islem_tarihi ?? 'now'))); // Tarih (orta satır)
        $sheet->mergeCells('A' . $bot . ':C' . $bot);
        $sheet->setCellValue('A' . $bot, (string)($g->makbuz_no ?? '')); // Evrak No (alt satır)
        // 1. sütundaki verileri sola hizala
        $sheet->getStyle('A' . $top)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('A' . $mid)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('A' . $bot)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Cari/Açıklama hücresi 3 satır birleştirilir
        $sheet->mergeCells('D' . $top . ':D' . $bot);
        $aciklamaSol = trim((string)($g->adi_soyadi ?? ''));
        if (!empty($g->aciklama)) {
            $aciklamaSol .= ($aciklamaSol ? "\n" : '') . str_replace(['\r\n', '\r'], "\n", (string)$g->aciklama);
        }
        $sheet->setCellValue('D' . $top, $aciklamaSol);
        $sheet->getStyle('D' . $top)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_CENTER);

        // Tutar 3 satır birleştirilir
        $sheet->mergeCells('E' . $top . ':E' . $bot);
        // Numeric yaz, biçimi daha sonra kolon bazında ver
        $sheet->setCellValue('E' . $top, (float)($g->tutar ?? 0));
        $sheet->getStyle('E' . $top)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
        
        // Stil: Döngü sonunda ince grid uygulayacağız; burada dikey ortalama ve girinti
        $rangeLeft = 'A' . $top  . ':E' . $bot;
        $sheet->getStyle($rangeLeft)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rangeLeft)->getAlignment()->setIndent(1);



    // Blok alt çizgisi kalın
    $sheet->getStyle('A' . $bot . ':E' . $bot)
        ->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);

    }

    // Gelirler (sağ blok: G..L)
    if (isset($gelirler_list[$i])) {
        $g2 = $gelirler_list[$i];

        // Hücre değer yerleşimi (3 satır)
        // 1. sütun: G..I birleştirilmiş
        $sheet->mergeCells('G' . $top . ':I' . $top);
        $sheet->setCellValue('G' . $top, ++$ge); // Sıra No (üst)
        $sheet->mergeCells('G' . $mid . ':I' . $mid);
        $sheet->setCellValue('G' . $mid, date('d.m.Y H:i', strtotime($g2->islem_tarihi ?? 'now'))); // Tarih (orta)
        $sheet->mergeCells('G' . $bot . ':I' . $bot);
        $sheet->setCellValue('G' . $bot, (string)($g2->makbuz_no ?? '')); // Fiş No (alt)
        // 1. sütundaki verileri sola hizala
        $sheet->getStyle('G' . $top)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('G' . $mid)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('G' . $bot)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // 2. sütun: J (Daire No ve Hesap Adı)
        $sheet->setCellValue('J' . $top, (string)($g2->daire_kodu ?? ''));
        $sheet->setCellValue('J' . $mid, (string)($g2->adi_soyadi ?? ''));
        $sheet->getStyle('J' . $top . ':J' . $mid)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // 3. sütun: K (Açıklama) 3 satır birleştirilir
      $sheet->mergeCells('K' . $top . ':K' . $bot);
      $acikSag = (string)($g2->aciklama ?? '');
      $sheet->setCellValue('K' . $top, $acikSag);
      $sheet->getStyle('K' . $top)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_CENTER);

        // Tutar 3 satır birleştirilir
        $sheet->mergeCells('L' . $top . ':L' . $bot);
        // Numeric yaz, biçimi daha sonra kolon bazında ver
        $sheet->setCellValue('L' . $top, (float)($g2->tutar ?? 0));
        $sheet->getStyle('L' . $top)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);

        // Stil: Döngü sonunda ince grid uygulanacak; burada sadece girinti
        $rangeRight = 'G' . $top . ':L' . $bot;
        $sheet->getStyle($rangeRight)->getAlignment()->setIndent(1);

        // Blok sonu çizgisi kalın
        $sheet->getStyle('G' . $bot . ':L' . $bot)
            ->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);


        // Satırdaki verileri yatayda ortala
        //$sheet->getStyle($rangeLeft)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

    }

    // Satır yükseklikleri (görünürlük için az biraz yükselt)
    $sheet->getRowDimension($top)->setRowHeight(16);
    $sheet->getRowDimension($mid)->setRowHeight(16);
    $sheet->getRowDimension($bot)->setRowHeight(16);

    // Sonraki kayıt için 3 satır aşağı in
    $r += 3;
}




// --- Toplam Satırları ---
$totalRow = $r + 1;
// Gider toplamı sol
$sheet->mergeCells('A' . $totalRow . ':D' . $totalRow);
$sheet->setCellValue('A' . $totalRow, 'Giderler Toplamı');
$sheet->setCellValue('E' . $totalRow, (float)$toplam_gider);
$sheet->getStyle('A' . $totalRow . ':E' . $totalRow)->getFont()->setBold(true);
$sheet->getStyle('A' . $totalRow . ':E' . $totalRow)->applyFromArray($thinBorder);
$sheet->getStyle('E' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Gelir toplamı sağ
$sheet->mergeCells('G' . $totalRow . ':K' . $totalRow);
$sheet->setCellValue('G' . $totalRow, 'Gelirler Toplamı');
$sheet->setCellValue('L' . $totalRow, (float)$toplam_gelir);
$sheet->getStyle('G' . $totalRow . ':L' . $totalRow)->getFont()->setBold(true);
$sheet->getStyle('G' . $totalRow . ':L' . $totalRow)->applyFromArray($thinBorder);
$sheet->getStyle('L' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Yazdırma alanı ve tekrar eden başlık satırı
$sheet->getPageSetup()->setPrintArea('A1:L' . $totalRow);
$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 7);

// Sürekli kalın hatlar için: dış çerçeve ve orta ayırıcı çizgiler
// Tüm tablo dış kenarlığı kalın
$sheet->getStyle('A5:L'.$totalRow)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);
// Orta ayırıcı: E'nin sağ kenarı ve G'nin sol kenarı kalın
$sheet->getStyle('E5:E'.$totalRow)->getBorders()->getRight()->setBorderStyle(Border::BORDER_MEDIUM);
$sheet->getStyle('G5:G'.$totalRow)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_MEDIUM);
// Toplam satır bloklarının da dış kenarlığı kalın olsun
$sheet->getStyle('A'.$totalRow.':E'.$totalRow)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);
$sheet->getStyle('G'.$totalRow.':L'.$totalRow)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);

// Blok sonu çizgisi kalın
$sheet->getStyle('A5:E7')
    ->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);

// İnce grid (açık gri) kenarlıkları veri bölgelerine tek seferde uygula
$dataLastRow = $r - 1;
if ($dataLastRow >= $rowStart) {
    $leftDataRange  = 'A' . $rowStart . ':E' . $dataLastRow;
    $rightDataRange = 'G' . $rowStart . ':L' . $dataLastRow;
    $thinGrid = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'CCCCCC']
            ]
        ]
    ];
    $sheet->getStyle($leftDataRange)->applyFromArray($thinGrid);
    $sheet->getStyle($rightDataRange)->applyFromArray($thinGrid);
}

// Para kolonlarına sayı biçimi uygula
$sheet->getStyle('E' . $rowStart . ':E' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
$sheet->getStyle('L' . $rowStart . ':L' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
    
// --- Çıktı Oluşturma (Bu kısım projenize göre aynı kalabilir) ---
$filename = ($site->site_adi ?? 'site') . '_gelir_gider_raporu_' . date('Y_m');

// Büyük veri seti için PDF yerine otomatik XLSX'e geçiş
if ($format === 'pdf' && $max > 1500) {
    $format = 'xlsx';
}

try {
    switch ($format) {
        case 'xlsx':
        case 'excel':
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');
            if (ob_get_length()) ob_end_clean();
            $writer = new Xlsx($ss);
            if (method_exists($writer, 'setPreCalculateFormulas')) {
                $writer->setPreCalculateFormulas(false);
            }
            if (method_exists($writer, 'setUseDiskCaching')) {
                $writer->setUseDiskCaching(true, sys_get_temp_dir());
            }
            $writer->save('php://output');
            break;

        case 'html':
            header('Content-Type: text/html; charset=utf-8');
            if (ob_get_length()) {
                ob_end_clean();
            }
            (new Html($ss))->save('php://output');
            break;
        case 'pdf':
        default:
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
            header('Cache-Control: max-age=0');
            IOFactory::registerWriter('Pdf', \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf::class); // VEYA Dompdf
            if (ob_get_length()) ob_end_clean();
            $writer = IOFactory::createWriter($ss, 'Pdf');
            $writer->save('php://output');
            break;
    }
    exit;
} catch (\Exception $e) {
    die('Export hatası: ' . $e->getMessage());
}
