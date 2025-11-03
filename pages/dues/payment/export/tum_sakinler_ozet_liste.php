<?php

require_once dirname(__DIR__, 4) . '/configs/bootstrap.php';

use App\Helper\Date;
use App\Helper\Helper;
use Model\KisilerModel;
use Model\SitelerModel;
use Model\FinansalRaporModel;
use Model\BorclandirmaModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf; // Dompdf'i kullanmak için
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$Finansal = new FinansalRaporModel();
$Kisiler   = new KisilerModel();
$Siteler   = new SitelerModel();
$Borcl     = new BorclandirmaModel();

$site_id = $_SESSION['site_id'] ?? 0;
$format = $_GET['format'] ?? 'pdf';
$isPdf = strtolower((string)$format) === 'pdf';
$isXlsx = strtolower((string)$format) === 'xlsx';

$site = $Siteler->find($site_id);
if (!$site) {
    die('Site bilgisi bulunamadı.');
}

// 1) Satırlar: Sitedeki tüm kişiler (aktif/pasif fark etmiyorsa tümü)
$kisiler = $Kisiler->SiteTumKisileriGuncelBakiyesi($site_id);
// Daire koduna göre doğal sıralama; aynı dairede Ev Sahibi -> Kiracı alt alta gelsin
usort($kisiler, function ($a, $b) {
    $ak = strtoupper(trim((string)($a->daire_kodu ?? '')));
    $bk = strtoupper(trim((string)($b->daire_kodu ?? '')));
    // Boş kodlar en sona
    if ($ak === '' && $bk !== '') return 1;
    if ($bk === '' && $ak !== '') return -1;
    $cmp = strnatcasecmp($ak, $bk);
    if ($cmp !== 0) return $cmp;
    $rank = function ($t) {
        $t = trim((string)$t);
        $tl = function_exists('mb_strtolower') ? mb_strtolower($t, 'UTF-8') : strtolower($t);
        if ($tl === 'ev sahibi' || $tl === 'evsahibi') return 0;
        if ($tl === 'kiracı' || $tl === 'kiraci') return 1;
        return 2; // diğerleri
    };
    $ra = $rank($a->uyelik_tipi ?? '');
    $rb = $rank($b->uyelik_tipi ?? '');
    if ($ra !== $rb) return $ra <=> $rb;
    // Son çare: isim
    return strcasecmp((string)($a->adi_soyadi ?? ''), (string)($b->adi_soyadi ?? ''));
});
if (empty($kisiler)) {
    die('Kişi bulunamadı.');
}

// 2) Pivot veri: kisi_id x borclandirma_id -> toplam ödeme
$pivotRows = $Finansal->getOdemelerPivotBySite($site_id);

//Helper::dd($kisiler);


// 3) Başlıklar (YATAY): sitedeki TÜM borçlandırmalar
$borclar = $Borcl->getAll($site_id); // tüm borçlandırmalar (ödemesi olmasa bile)
// Eskiden yeniye sırala (başlangıç_tarihi ASC, sonra id ASC)
usort($borclar, function ($a, $b) {
    $at = isset($a->baslangic_tarihi) ? strtotime($a->baslangic_tarihi) : 0;
    $bt = isset($b->baslangic_tarihi) ? strtotime($b->baslangic_tarihi) : 0;
    if ($at === $bt) {
        return ($a->id <=> $b->id);
    }
    return $at <=> $bt;
});
$borcIds = array_map(fn($b) => (int)$b->id, $borclar);

// Borç başlık isimlerini üret: Öncelik aciklama; yoksa "Ay Yıl [DueAdı]"
// Kısa etiket üreteci (PDF için): "Oca-25 Aidat", "Eki-25 DoGaz" gibi
$abbrMonth = [
    '01' => 'Oca',
    '02' => 'Şub',
    '03' => 'Mar',
    '04' => 'Nis',
    '05' => 'May',
    '06' => 'Haz',
    '07' => 'Tem',
    '08' => 'Ağu',
    '09' => 'Eyl',
    '10' => 'Eki',
    '11' => 'Kas',
    '12' => 'Ara',
];
$shortDueMap = [
    'aidat' => 'Aidat',
    'doğalgaz' => 'DoGaz',
    'dogalgaz' => 'DoGaz',
    'elektrik' => 'Elk',
    'su' => 'Su',
    'güvenlik' => 'Gvn',
    'guvenlik' => 'Gvn',
    'temizlik' => 'Temz',
    'asansör' => 'Asnsr',
    'asansor' => 'Asnsr',
    'bakım' => 'Bakım',
    'bakim' => 'Bakım',
    'fatura' => 'Ftr'
];
// borclandirma_id => tam ve kısa başlık
$borcHeaders = [];
$borcHeadersShort = [];
foreach ($borcIds as $bid) {
    $rec = $Borcl->findWithDueName($bid);
    $label = '';
    $labelShort = '';
    if ($rec) {
        // 1) Açıklama varsa onu kullan
        if (!empty($rec->aciklama)) {
            $label = trim((string)$rec->aciklama);
            // Kısa etiket: açıklama 14 karaktere daralt
            $labelShort = mb_strimwidth($label, 0, 14, '', 'UTF-8');
        } else {
            // 2) Yoksa başlangıç_tarihi'nden Ay Yıl üret ve due adını ekle
            $ts = !empty($rec->baslangic_tarihi) ? strtotime($rec->baslangic_tarihi) : null;
            $dueName  = trim((string)($rec->borc_adi ?? ''));
            if ($ts) {
                $monthNum = date('m', $ts);
                $year     = date('Y', $ts);
                $ayYil    = Date::monthName($monthNum) . ' ' . $year; // Örn: Ocak 2025
                $label    = $ayYil . ($dueName ? (' ' . $dueName) : '');
                // Kısa format: Oca-25 + due kısaltması
                $per      = ($abbrMonth[$monthNum] ?? $monthNum) . '-' . substr($year, -2);
                $dl       = function_exists('mb_strtolower') ? mb_strtolower($dueName, 'UTF-8') : strtolower($dueName);
                $dueShort = $shortDueMap[$dl] ?? mb_strimwidth($dueName, 0, 6, '', 'UTF-8');
                $labelShort = trim($per . ' ' . $dueShort);
            } else {
                $label = $dueName;
                $labelShort = mb_strimwidth($dueName, 0, 10, '', 'UTF-8');
            }
        }
    }
    if ($label === '') {
        $label = 'Borc ' . $bid;
    }
    if ($labelShort === '') {
        $labelShort = $label;
    }
    $borcHeaders[$bid] = $label;
    $borcHeadersShort[$bid] = $labelShort;
}

// 4) Hızlı erişim için kisi_id -> [borclandirma_id => toplam_odeme]
$pivot = [];
foreach ($pivotRows as $r) {
    $kid = (int)$r->kisi_id;
    $bid = (int)$r->borclandirma_id;
    $pivot[$kid][$bid] = (float)$r->toplam_odeme;
}

// 5) Spreadsheet oluştur ve başlıkları dinamik yaz
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$spreadsheet->getDefaultStyle()->getFont()->setName('DejaVu Sans');
$spreadsheet->getDefaultStyle()->getFont()->setSize(7);
$spreadsheet->getActiveSheet()->setTitle('Özet Ödemeler');

// Sabit kolonlar: PDF'te Telefon, Giriş ve Çıkış istenirse kaldırılabilir
if ($isPdf) {
    $staticHeaders = ['Sıra', 'Daire Kodu', 'Adı Soyadı', 'Üyelik Tipi'];
} else {
    $staticHeaders = ['Sıra', 'Daire Kodu', 'Adı Soyadı', 'Telefon', 'Üyelik Tipi', 'Giriş Tarihi', 'Çıkış Tarihi'];
}

$colIndex = 1; // 1=A
foreach ($staticHeaders as $h) {
    $cell = Coordinate::stringFromColumnIndex($colIndex) . '4';
    $sheet->setCellValue($cell, $h);
    $colIndex++;
}
// Dinamik borç kolonları bu indexten başlar
$dynStartCol = $colIndex;

// Dinamik borçlandırma başlıkları
foreach ($borcIds as $bid) {
    $cell = Coordinate::stringFromColumnIndex($colIndex) . '4';
    $sheet->setCellValue($cell, $isPdf ? $borcHeadersShort[$bid] : $borcHeaders[$bid]);
    $colIndex++;
}

// Son toplam kolonu
$cell = Coordinate::stringFromColumnIndex($colIndex) . '4';
$sheet->setCellValue($cell, 'Toplam Ödeme');

if($isXlsx) {
$sheet->getStyle($cell)
       ->getAlignment()
       ->setTextRotation(90);
}

$cell = Coordinate::stringFromColumnIndex($colIndex + 1) . '4';
$sheet->setCellValue($cell, 'Bakiye');

if($isXlsx) {
$sheet->getStyle($cell)
       ->getAlignment()
       ->setTextRotation(90);
}

$lastColIdx = $colIndex + 1; // son kolon

// Üst başlık (site ve tarih)
$lastHeaderColumn = Coordinate::stringFromColumnIndex($lastColIdx);
$sheet->mergeCells('A1:' . $lastHeaderColumn . '1');
$sheet->setCellValue('A1', 'Borç Bazında Ödemeler Özet');
$sheet->mergeCells('A2:B2');
$sheet->setCellValue('A2', 'Site Adı:');
$sheet->mergeCells('C2:' . $lastHeaderColumn . '2');
$sheet->setCellValue('C2', $site->site_adi ?? '');
$sheet->mergeCells('A3:B3');
$sheet->setCellValue('A3', 'Rapor Tarihi:');
$sheet->mergeCells('C3:' . $lastHeaderColumn . '3');
$sheet->setCellValue('C3', date('d.m.Y H:i'));

// Başlık stilleri
$sheet->getStyle('A1:' . $lastHeaderColumn . '3')->applyFromArray([
    'font' => ['bold' => false, 'color' => ['rgb' => '000000'], 'size' => 7],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1, 'wrapText' => true]
]);
$sheet->getStyle('A4:' . $lastHeaderColumn . '4')->applyFromArray([
    'font' => ['bold' => false, 'color' => ['rgb' => '000000'], 'size' => 7],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '9CAFAA']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
]);

// Borç başlıkları: XLSX/HTML'de dikey (90°), PDF'de yatay kısa etiket
if (!empty($borcIds)) {
    // Dinamik borç kolonları: $dynStartCol ... ($lastColIdx - 1)
    for ($i = $dynStartCol; $i <= $lastColIdx - 1; $i++) {
        $hdrCell = Coordinate::stringFromColumnIndex($i) . '4';
        $style = $sheet->getStyle($hdrCell);
        if ($isPdf) {
            // Dompdf döndürülen metinde taşma yapıyor; yatay kısa etiket kullan
            $style->getAlignment()->setTextRotation(0);
            $style->getAlignment()->setWrapText(false);
            $style->getFont()->setSize(6);
        } elseif ($isXlsx) {
            $style->getAlignment()->setTextRotation(90);
            $style->getAlignment()->setWrapText(true);
        }
        $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $style->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    }
    // Toplam başlığı (son kolon) yatay, orta hizalı
    $totalHdr = Coordinate::stringFromColumnIndex($lastColIdx) . '4';
    $sheet->getStyle($totalHdr)->getAlignment()->setTextRotation(0);
    $sheet->getStyle($totalHdr)->getAlignment()->setWrapText(false);
    $sheet->getStyle($totalHdr)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle($totalHdr)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    // Başlık satırı yüksekliği
    $sheet->getRowDimension(4)->setRowHeight($isPdf ? 20 : 90);
}

// 6) Satırları yaz (her kişi için)
$row = 5;
$seq = 1;
foreach ($kisiler as $k) {
    $col = 1;
    $telefon = $k->telefon ? preg_replace('/(\d{3})(\d{3})(\d{2})(\d{2})/', '($1) $2-$3-$4', $k->telefon) : '';
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $seq++);
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $k->daire_kodu ?? '');
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $k->adi_soyadi ?? '');
    if (!$isPdf) {
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $telefon);
    }
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $k->uyelik_tipi ?? '');
    if (!$isPdf) {
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, Date::dmY($k->giris_tarihi ?? ''));
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, Date::dmY($k->cikis_tarihi ?? ''));
    }

    $rowTotal = 0.0;
    foreach ($borcIds as $bid) {
        $amount = (float)($pivot[$k->id][$bid] ?? 0);
        $rowTotal += $amount;
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $amount);

        //tutar 0 ise hücre arka planınıı kırmızı yap
        if ($amount == 0 && $pivot[$k->id][$bid] !== null) {
            $cellAddress = Coordinate::stringFromColumnIndex($col - 1) . $row;
            $sheet->getStyle($cellAddress)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFA4A4');
        } elseif ($amount > 0) {
            $cellAddress = Coordinate::stringFromColumnIndex($col - 1) . $row;
            $sheet->getStyle($cellAddress)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('BADFDB');
        }

    }
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $rowTotal);
    // Bakiye: Kişinin güncel bakiyesi
    $bakiye = (float)($k->bakiye ?? 0);
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $bakiye);

    // Şeritli görünüm
        $sheet->getStyle('A' . $row . ':' . $lastHeaderColumn . $row)->applyFromArray([
            'font' => ['size' => 7]
        ]);

    $row++;
}

// Kolon genişlikleri (statik kısım) - mevcut başlıklara göre uygula
$staticWidths = [
    'Sıra' => 6,
    'Daire Kodu' => 10,
    'Adı Soyadı' => 22,
    'Telefon' => 12,
    'Üyelik Tipi' => 10,
    'Giriş Tarihi' => 10,
    'Çıkış Tarihi' => 10,
];
$wCol = 1;
foreach ($staticHeaders as $label) {
    $letter = Coordinate::stringFromColumnIndex($wCol);
    $sheet->getColumnDimension($letter)->setWidth($staticWidths[$label] ?? 10);
    $wCol++;
}

// Dinamik borç kolonları ve toplam sütunu: daha dar genişlik verelim
for ($i = $dynStartCol; $i <= $lastColIdx; $i++) {
    $colLetter = Coordinate::stringFromColumnIndex($i);
    // Toplam kolonu geniş tut, diğer dinamik kolonları PDF'te biraz daralt
    if ($i === $lastColIdx) {
        $sheet->getColumnDimension($colLetter)->setWidth(12);
    } else {
        $sheet->getColumnDimension($colLetter)->setWidth($isPdf ? 9 : 9);
    }
}

// Yazdırma alanı ve sayfa ayarları
$printLastRow = $row - 1;
$sheet->getPageSetup()->setPrintArea('A1:' . $lastHeaderColumn . $printLastRow);
$pageSetup = $sheet->getPageSetup();
$pageSetup->setPaperSize(PageSetup::PAPERSIZE_A4);
$pageSetup->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
$pageSetup->setFitToWidth(1);
$pageSetup->setFitToHeight(0);
// Kenar boşluklarını küçült (inç cinsinden)
$margins = $sheet->getPageMargins();
$margins->setTop(0.2);
$margins->setBottom(0.2);
$margins->setLeft(0.2);
$margins->setRight(0.2);

// Sayısal format: tüm dinamik ödeme hücreleri + toplam sütunu 2 ondalık
$moneyRange = Coordinate::stringFromColumnIndex($dynStartCol) . '5:' . $lastHeaderColumn . $printLastRow;
$sheet->getStyle($moneyRange)->getNumberFormat()->setFormatCode('#,##0.00');

// Hizalama: dinamik sütunlar ve toplam sağa
$alignStart = Coordinate::stringFromColumnIndex($dynStartCol);
$sheet->getStyle($alignStart . '5:' . $lastHeaderColumn . $printLastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

// Kenarlıklar
$sheet->getStyle('A4:' . $lastHeaderColumn . $printLastRow)->applyFromArray([
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '222222']]]
]);

// Üst başlığı her sayfada tekrar et
$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 4);

// Çıktı dosya adı
$filename = $site->site_adi . ' odeme_pivot_' . date('Y-m-d_H-i-s');

try {
    switch (strtolower($format)) {
        case 'xlsx':
        case 'excel':
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');
            if (ob_get_length()) {
                ob_end_clean();
            }
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            break;

        case 'csv':
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '.csv"');
            header('Cache-Control: max-age=0');
            if (ob_get_length()) {
                ob_end_clean();
            }
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
            header('Cache-Control: max-age=0');
            if (ob_get_length()) {
                ob_end_clean();
            }
            $writer = new Html($spreadsheet);
            $writer->setSheetIndex(0);
            $writer->save('php://output');
            break;

        case 'pdf':
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
            header('Cache-Control: max-age=0');
            IOFactory::registerWriter('Pdf', Dompdf::class);
            // Anti-bold repeat header
            $sheet->getStyle('A1:' . $lastHeaderColumn . '4')->getFont()->setBold(false);
            if (ob_get_length()) {
                ob_end_clean();
            }
            $writer = IOFactory::createWriter($spreadsheet, 'Pdf');
            $writer->save('php://output');
            break;

        default:
            throw new \Exception('Geçersiz format: ' . $format);
    }
    exit();
} catch (\Exception $e) {
    die('Export hatası: ' . $e->getMessage());
}
