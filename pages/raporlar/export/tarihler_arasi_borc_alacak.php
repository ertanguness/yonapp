<?php

require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use App\Helper\Date;
use App\Helper\Helper;
use Model\KisilerModel;
use Model\SitelerModel;
use Model\FinansalRaporModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

$site_id = $_SESSION['site_id'] ?? 0;
$format = strtolower($_GET['format'] ?? 'pdf');
$start  = $_GET['start'] ?? date('Y-01-01');
$end    = $_GET['end']   ?? date('Y-m-d');
// Tarihleri normalize et: dd.mm.yyyy geldiyse Y-m-d'e çevir
if (strpos($start, '.') !== false) {
    $dt = \DateTime::createFromFormat('d.m.Y', $start);
    if ($dt) {
        $start = $dt->format('Y-m-d');
    }
}
if (strpos($end, '.') !== false) {
    $dt = \DateTime::createFromFormat('d.m.Y', $end);
    if ($dt) {
        $end = $dt->format('Y-m-d');
    }
}

$Siteler = new SitelerModel();
$Kisiler = new KisilerModel();
$Fin     = new FinansalRaporModel();

$site = $Siteler->find($site_id);
if (!$site) {
    die('Site bulunamadı');
}

// Kişiler (tüm veya aktif)
$kisiler = $Kisiler->SiteTumKisileri($site_id);
// Daire kodu ve üyelik tipine göre sıralama
usort($kisiler, function ($a, $b) {
    $ak = strtoupper($a->daire_kodu ?? '');
    $bk = strtoupper($b->daire_kodu ?? '');
    if ($ak === '' && $bk !== '') return 1;
    if ($bk === '' && $ak !== '') return -1;
    $c = strnatcasecmp($ak, $bk);
    if ($c !== 0) return $c;
    $rank = function ($t) {
        $t = mb_strtolower($t ?? '', 'UTF-8');
        if ($t === 'ev sahibi' || $t === 'evsahibi') return 0;
        if ($t === 'kiracı' || $t === 'kiraci') return 1;
        return 2;
    };
    $ra = $rank($a->uyelik_tipi ?? '');
    $rb = $rank($b->uyelik_tipi ?? '');
    if ($ra !== $rb) return $ra <=> $rb;
    return strcasecmp($a->adi_soyadi ?? '', $b->adi_soyadi ?? '');
});

// Veriler
$opening = $Fin->getOpeningBreakdownByDate($start); // kisi_id -> open_* map
$paymentsByDate = $Fin->getPaymentBreakdownByDate($start); // kisi_id -> open_* map
$payments = $Fin->getPaymentsByDateRange($start, $end); // kisi_id -> donem_odenen
$accruals = $Fin->getAccrualsBySiteBetween($site_id, $start, $end); // kisi_id + kategori


//Helper::dd([$opening]);
$openMap = [];
foreach ($opening as $r) {
    $openMap[(int)$r->kisi_id] = $r;
}
$payMap  = [];
foreach ($paymentsByDate as $r) {
    $payMap[(int)$r->kisi_id] = (float)$r->payed_odenen;
}
$payMapAll  = [];
foreach ($payments as $r) {
    $payMapAll[(int)$r->kisi_id] = (float)$r->donem_odenen;
}

// Kategori listesini çıkar (kolonlar)
$kategoriSet = [];
foreach ($accruals as $r) {
    $kategoriSet[trim((string)$r->kategori)] = true;
}
$kategoriler = array_keys($kategoriSet);
sort($kategoriler, SORT_NATURAL | SORT_FLAG_CASE);

// kisi_id x kategori -> tutar
$accMap = [];
foreach ($accruals as $r) {
    $kid = (int)$r->kisi_id;
    $kat = trim((string)$r->kategori);
    $accMap[$kid][$kat] = ($accMap[$kid][$kat] ?? 0) + (float)$r->toplam_tahakkuk;
}

$ss = new Spreadsheet();
$sheet = $ss->getActiveSheet();
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
    $md->setHeight(40);
    $md->setCoordinates('L1');
    $md->setOffsetX(2);
    $md->setOffsetY(2);
    $md->setWorksheet($sheet);
} else {
    $drawing = new Drawing();
    $drawing->setName('Logo');
    $drawing->setDescription('Site Logo');
    $drawing->setPath($logoFile);
    $drawing->setHeight(40);
    $drawing->setCoordinates('L1');
    $drawing->setOffsetX(2);
    $drawing->setOffsetY(2);
    $drawing->setWorksheet($sheet);
}
$ss->getDefaultStyle()->getFont()->setName('DejaVu Sans');
$ss->getDefaultStyle()->getFont()->setSize(8);
$sheet->setTitle('Tarihler Arası Durum');

// Üst başlık (kolonlar oluştuğunda genişliği dinamik ayarlanacak)

// Header satırı: 3-4-5 grupları
// 1) Daire-Kişi Bilgileri
$headersLeft = ['Daire', 'Adı Soyadı', 'Oturum Şekli'];
$col = 1; // A
foreach ($headersLeft as $h) {
    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '5';
    $sheet->setCellValue($cell, $h);
    $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
    $sheet->getColumnDimension($letter)->setWidth($h === 'Adı Soyadı' ? 24 : 12);
    $col++;
}

// 2) Devir Bilgileri
$devBas = $col; // start col
$devHeaders = ['ALACAK', 'ANA BORÇ', 'GECİKME'];
foreach ($devHeaders as $h) {
    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '5';
    $sheet->setCellValue($cell, $h);
    $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
    $sheet->getColumnDimension($letter)->setWidth(14);
    $col++;
}
$devBit = $col - 1;
$devStartLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($devBas);
$devEndLetter   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($devBit);
$sheet->mergeCells($devStartLetter . '4:' . $devEndLetter . '4');
$sheet->setCellValue($devStartLetter . '4', 'DEVİR BİLGİLERİ');

// 3) Dönem içi tahakkuklar (kategori kolonları)
$tahBas = $col;
foreach ($kategoriler as $k) {
    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '5';
    $sheet->setCellValue($cell, mb_strtoupper($k, 'UTF-8'));
    $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
    $sheet->getColumnDimension($letter)->setWidth(14);
    $col++;
}
$tahBit = $col - 1;
$tahStartLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($tahBas);
$tahEndLetter   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($tahBit);
$sheet->mergeCells($tahStartLetter . '4:' . $tahEndLetter . '4');
$sheet->setCellValue($tahStartLetter . '4', 'DÖNEM İÇİ TAHAKKUKLAR');

// 4) Dönem sonu: Ödenen, Borcu, Alacağı (tek grup)
$donemBas = $col;
// ÖDENEN
$cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '5';
$sheet->setCellValue($cell, 'ÖDENEN');
$letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
$sheet->getColumnDimension($letter)->setWidth(14);
$col++;
// BORCU
$cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '5';
$sheet->setCellValue($cell, 'BORCU');
$borcLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
$sheet->getColumnDimension($borcLetter)->setWidth(14);
$col++;
// ALACAĞI
$cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '5';
$sheet->setCellValue($cell, 'ALACAĞI');
$alacakLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
$sheet->getColumnDimension($alacakLetter)->setWidth(14);
$col++;
$donemBit = $col - 1;
// Grup başlığı
$donStartLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($donemBas);
$donEndLetter   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($donemBit);
$sheet->mergeCells($donStartLetter . '4:' . $donEndLetter . '4');
$sheet->setCellValue($donStartLetter . '4', 'DÖNEM SONU');

// Gruplama üst satırı stilleri
$lastCol = $col - 1;
$sheet->getStyle('A4:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastCol) . '5')->applyFromArray([
    'font' => ['bold' => true, 'size' => 8],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E9ECEF']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
]);
$sheet->getRowDimension(4)->setRowHeight(18);
$sheet->getRowDimension(5)->setRowHeight(32);

// Satırlar
$row = 6;
$seq = 1;
foreach ($kisiler as $k) {
    $col = 1;
    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row;
    $sheet->setCellValue($cell, $k->daire_kodu ?? '');
    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row;
    $sheet->setCellValue($cell, $k->adi_soyadi ?? '');
    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row;
    $sheet->setCellValue($cell, $k->uyelik_tipi ?? '');

    $op = $openMap[$k->id] ?? null;
    $openAlacak  = 0.0; // Pozitif bakiye varsayımı: ödenmiş fazla
    $openAna     = (float)($op->open_anapara ?? 0);
    $openGecikme = (float)($op->open_gecikme ?? 0);
    $openOdenen  = (float)($op->open_odenen ?? 0);
    // Açılış borçlarını hesapla: ana+gecikme - ödenen (negatifse alacak olsun)
    $openBorclu = max(0, ($openAna + $openGecikme) - $openOdenen);
    if (($openAna + $openGecikme) - $openOdenen < 0) {
        $openAlacak = abs(($openAna + $openGecikme) - $openOdenen);
        $openBorclu = 0;
    }

    //$openAlacak = $paymentsByDate[$k->id]->payed_odenen ?? 0.0;

    // Devir Bilgileri: Alacak, Ana Borç, Gecikme
    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row;
    $sheet->setCellValue($cell, $openAlacak);
    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row;
    $sheet->setCellValue($cell, $openAna);
    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row;
    $sheet->setCellValue($cell, $openGecikme);

    // Dönem içi tahakkuklar (kategorilere göre)
    $donemToplamTahakkuk = 0.0;
    foreach ($kategoriler as $kat) {
        $tutar = (float)($accMap[$k->id][$kat] ?? 0);
        $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row;
        $sheet->setCellValue($cell, $tutar);
        $donemToplamTahakkuk += $tutar;
    }

    // Tarih öncesi ödenen
    $odenDev = (float)($payMap[$k->id] ?? 0);

    // Dönem sonu: Ödenen + net (tahakkuk - ödenen)
    $odenAll = (float)($payMapAll[$k->id] ?? 0);


    // YENİ HESAP: Açılış ana borç + dönem içi tahakkuklar - ödenen
    $toplamBorc = $openAna + $donemToplamTahakkuk - $odenAll;

    if ($toplamBorc >= 0) {
        $borcSon = $toplamBorc;
        $alacakSon = 0.0;
    } else {
        $borcSon = 0.0;
        $alacakSon = abs($toplamBorc);
    }

    // ÖDENEN yaz
    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row;
    $sheet->setCellValue($cell, $odenAll + $odenDev);
    // Net hesap:
    // 1) Dönem neti: tahakkuk - ödenen
    $donemNet = $donemToplamTahakkuk - $odenAll;
    // 2) Devir etkisi: açılış borcu ekle, açılış alacağı düş
    $finalNet = $openBorclu + $donemNet - $openAlacak;
    if ($finalNet >= 0) {
        $borcSon = $finalNet;
        $alacakSon = 0.0;
    } else {
        $borcSon = 0.0;
        $alacakSon = abs($finalNet);
    }


    // YENİ HESAP: Açılış ana borç + dönem içi tahakkuklar - ödenen
    $toplamBorc = $openAna + $donemToplamTahakkuk - $odenAll-$odenDev;

    if ($toplamBorc >= 0) {
        $borcSon = $toplamBorc;
        $alacakSon = 0.0;
    } else {
        $borcSon = 0.0;
        $alacakSon = abs($toplamBorc);
    }



    // BORCU ve ALACAĞI yaz
    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row;
    $sheet->setCellValue($cell, $borcSon);
    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row;
    $sheet->setCellValue($cell, $alacakSon);

    if ($row % 2 == 0) {
        $sheet->getStyle('A' . $row . ':' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastCol) . $row)
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8F9FA');
    }
    $row++;
}

// Stil: sayısal kolonları sağa ve 2 ondalık
$numStartCol = 4; // Alacak
$range = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($numStartCol) . '6:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastCol) . ($row - 1);
$sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->getStyle($range)->getNumberFormat()->setFormatCode('#,##0.00');
// Özellikle ALACAĞI sütununu BORCU gibi biçimlendir (sağa hizalı, 2 ondalık)
$alacakRange = $alacakLetter . '6:' . $alacakLetter . ($row - 1);
$sheet->getStyle($alacakRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->getStyle($alacakRange)->getNumberFormat()->setFormatCode('#,##0.00');

// Kenarlıklar
$sheet->getStyle('A5:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastCol) . ($row - 1))
    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

// Yazdırma ve sayfa
$sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
$sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
$sheet->getPageSetup()->setFitToWidth(1);
$sheet->getPageSetup()->setFitToHeight(0);
$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 5);
$sheet->getPageSetup()->setHorizontalCentered(true);
$m = $sheet->getPageMargins();
$m->setTop(0.25);
$m->setBottom(0.25);
$m->setLeft(0.25);
$m->setRight(0.25);
// Dinamik başlık artık biliniyor: A1..last
$lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastCol);
$sheet->mergeCells('A1:' . $lastColLetter . '1');
$sheet->setCellValue('A1', strtoupper($site->site_adi ?? ''));
$sheet->mergeCells('A2:' . $lastColLetter . '2');
$sheet->setCellValue('A2', '[' . Date::dmY($start) . ']-[' . Date::dmY($end) . '] TARİHLER ARASI BORÇ ALACAK DURUMU');
$sheet->setCellValue($lastColLetter . '3', date('d.m.Y H:i'));
$sheet->getStyle('A1:' . $lastColLetter . '2')->applyFromArray([
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
]);
$sheet->getRowDimension(1)->setRowHeight(24);
$sheet->getRowDimension(2)->setRowHeight(20);

$sheet->getPageSetup()->setPrintArea('A1:' . $lastColLetter . ($row - 1));

// Çıktı
$filename = ($site->site_adi ?? 'site') . '_tarihler_arasi_borc_alacak_' . date('Ymd_His');
try {
    switch ($format) {
        case 'xlsx':
        case 'excel':
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');
            if (ob_get_length()) {
                ob_end_clean();
            }
            (new Xlsx($ss))->save('php://output');
            break;
        case 'csv':
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '.csv"');
            header('Cache-Control: max-age=0');
            if (ob_get_length()) {
                ob_end_clean();
            }
            $w = new Csv($ss);
            $w->setDelimiter(';');
            $w->setEnclosure('"');
            $w->setLineEnding("\r\n");
            $w->save('php://output');
            break;
        case 'html':
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '.html"');
            header('Cache-Control: max-age=0');
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
            IOFactory::registerWriter('Pdf', Dompdf::class);
            if (ob_get_length()) {
                ob_end_clean();
            }
            $writer = IOFactory::createWriter($ss, 'Pdf');
            $writer->save('php://output');
            break;
    }
    exit;
} catch (\Exception $e) {
    die('Export hatası: ' . $e->getMessage());
}
