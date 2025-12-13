<?php
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use App\Helper\Date;
use App\Helper\Helper;
use Model\FinansalRaporModel;
use Model\SitelerModel;
use Model\KisilerModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$siteId = (int)($_SESSION['site_id'] ?? 0);
if ($siteId <= 0) die('Site bilgisi bulunamadı.');

$end = $_GET['end'] ?? date('Y-m-d');
$format = $_GET['format'] ?? 'xlsx';

$fin = new FinansalRaporModel();
$site = (new SitelerModel())->find($siteId);
$kisiModel = new KisilerModel();

$groups = $fin->getGecikenBorclarGrupluByDate($siteId, $end);

// Excel/HTML hazırlığı
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$spreadsheet->getDefaultStyle()->getFont()->setName('DejaVu Sans');
$spreadsheet->getDefaultStyle()->getFont()->setSize(9);
$sheet->setTitle('Geciken Ödemeler');

$sheet->mergeCells('A1:H1');
$sheet->setCellValue('A1', 'Gecikmiş Ödemeler (Bitiş Tarihinden Önce)');
$sheet->mergeCells('A2:B2');
$sheet->setCellValue('A2', 'Site Adı:');
$sheet->mergeCells('C2:H2');
$sheet->setCellValue('C2', $site->site_adi ?? '');
$sheet->mergeCells('A3:B3');
$sheet->setCellValue('A3', 'Bitiş Tarihi:');
$sheet->mergeCells('C3:H3');
$sheet->setCellValue('C3', Date::dmY($end));
$sheet->mergeCells('A4:B4');
$sheet->setCellValue('A4', 'Rapor Tarihi:');
$sheet->mergeCells('C4:H4');
$sheet->setCellValue('C4', date('d.m.Y H:i'));
$sheet->getStyle('A1:H4')->applyFromArray([
    'font' => ['bold' => false, 'color' => ['rgb' => '000000'], 'size' => 9],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1, 'wrapText' => true]
]);

$headers = ['Borç Adı', 'Açıklama', 'Son Ödeme', 'Anapara', 'Gecikme', 'Toplam Kalan', 'Daire Kodu', 'Ad Soyad'];
$row = 5;
$isPdf = ($format === 'pdf');
$isHtml = ($format === 'html');
$maxRowsPerPage = 45;
$currentPageStartRow = 6;
foreach ($headers as $idx => $h) {
    $cell = Coordinate::stringFromColumnIndex($idx + 1) . $row;
    $sheet->setCellValue($cell, $h);
}
$sheet->getStyle('A5:H5')->applyFromArray([
    'font' => ['bold' => false, 'color' => ['rgb' => '000000'], 'size' => 9],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '9CAFAA']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
]);

/**Başlıkları yinele */



$row = 6;
foreach ($groups as $g) {
    // Kişinin kalem sayısına göre tahmini blok uzunluğu
    $borclarPreview = $fin->getKisiGecikenBorclarByDate((int)$g->kisi_id, $end);
    $estimatedRows = max(5, 2 + count($borclarPreview) + 2); // kişi başlığı + kişi kolon başlığı + kalemler + toplam + boşluk
    $remainingRows = ($currentPageStartRow + $maxRowsPerPage) - $row;
    if (($isPdf || $isHtml) && ($remainingRows <= $estimatedRows)) {
        if ($isPdf) {
            $sheet->setBreak('A' . max(6, $row - 1), Worksheet::BREAK_ROW);
        } else if ($isHtml) {
            $sheet->mergeCells('A' . $row . ':H' . $row);
            $sheet->setCellValue('A' . $row, '__PAGE_BREAK__');
            $row++;
        }
        $currentPageStartRow = $row;
    }
    $sheet->mergeCells('A' . $row . ':H' . $row);
    $sheet->setCellValue('A' . $row, ($g->daire_kodu ?? '') . ' | ' . ($g->adi_soyadi ?? ''));
    $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
        'font' => ['bold' => true],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
    ]);
    $row++;
    // Kişi bazında kolon başlıklarını yineler
    foreach ($headers as $idx => $h) {
        $cell = Coordinate::stringFromColumnIndex($idx + 1) . $row;
        $sheet->setCellValue($cell, $h);
    }
    $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
        'font' => ['bold' => false, 'color' => ['rgb' => '000000'], 'size' => 9],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '9CAFAA']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
    ]);
    $row++;
    $sumAna = 0.0; $sumGec = 0.0; $sumKalan = 0.0;
    $borclar = $fin->getKisiGecikenBorclarByDate((int)$g->kisi_id, $end);
    foreach ($borclar as $b) {
        $sheet->setCellValue('A' . $row, (string)($b->borc_adi ?? ($b->aciklama ?? '')));
        $sheet->setCellValue('B' . $row, (string)($b->aciklama ?? ''));
        $sheet->setCellValue('C' . $row, Date::dmY($b->bitis_tarihi ?? ''));
        $sheet->setCellValue('D' . $row, (float)($b->kalan_anapara ?? 0));
        $sheet->setCellValue('E' . $row, (float)($b->hesaplanan_gecikme_zammi ?? 0));
        $sheet->setCellValue('F' . $row, (float)($b->toplam_kalan_borc ?? 0));
        $sheet->setCellValue('G' . $row, (string)($g->daire_kodu ?? ''));
        $sheet->setCellValue('H' . $row, (string)($g->adi_soyadi ?? ''));
        $sumAna   += (float)($b->kalan_anapara ?? 0);
        $sumGec   += (float)($b->hesaplanan_gecikme_zammi ?? 0);
        $sumKalan += (float)($b->toplam_kalan_borc ?? 0);
        $row++;
    }
    $sheet->setCellValue('A' . $row, 'TOPLAM');
    $sheet->mergeCells('A' . $row . ':C' . $row);
    $sheet->setCellValue('D' . $row, $sumAna);
    $sheet->setCellValue('E' . $row, $sumGec);
    $sheet->setCellValue('F' . $row, $sumKalan);
    $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
        'font' => ['bold' => true],
        'borders' => ['top' => ['borderStyle' => Border::BORDER_THIN]]
    ]);
    $addedSpacer = false;
    $row++;
    $remainingAfterTotal = ($currentPageStartRow + $maxRowsPerPage) - $row;
    if ($remainingAfterTotal > 0) {
        $sheet->mergeCells('A' . $row . ':H' . $row);
        $sheet->setCellValue('A' . $row, '');
        $sheet->getRowDimension($row)->setRowHeight(8);
        $row++;
        $addedSpacer = true;
    }
}

$sheet->getColumnDimension('A')->setWidth(24);
$sheet->getColumnDimension('B')->setWidth(24);
$sheet->getColumnDimension('C')->setWidth(12);
$sheet->getColumnDimension('D')->setWidth(12);
$sheet->getColumnDimension('E')->setWidth(12);
$sheet->getColumnDimension('F')->setWidth(12);
$sheet->getColumnDimension('G')->setWidth(12);
$sheet->getColumnDimension('H')->setWidth(18);

$borderEndRow = $row - 1;
$sheet->getStyle('D6:F' . $borderEndRow)->getNumberFormat()->setFormatCode('#,##0.00');
$sheet->getStyle('D6:F' . $borderEndRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->getStyle('A5:H' . $borderEndRow)->applyFromArray([
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '222222']]]
]);

$sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
$sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
$sheet->getPageSetup()->setFitToWidth(1);
$sheet->getPageSetup()->setFitToHeight(0);
$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 5);

$filename = ($site->site_adi ?? 'site') . '_geciken_odemeler_' . date('Y-m-d_H-i-s');

if ($format === 'html') {
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: inline');
    header('Cache-Control: max-age=0');
    $writer = new Html($spreadsheet);
    $writer->setSheetIndex(0);
    ob_start();
    $writer->save('php://output');
    $html = ob_get_clean();
    $html = preg_replace('/<tr[^>]*>\\s*<td[^>]*>__PAGE_BREAK__<\\/td>\\s*<\\/tr>/i', '<tr style="page-break-before: always;"><td colspan="8"></td></tr>', $html);
    // İlk tablonun ilk 5 satırını thead içine taşıyarak yazdırmada tekrarlanmasını sağla
    $tbStart = strpos($html, '<table');
    if ($tbStart !== false) {
        $tagEnd = strpos($html, '>', $tbStart);
        $tbClose = strpos($html, '</table>', $tbStart);
        if ($tagEnd !== false && $tbClose !== false) {
            $tableOpen = substr($html, $tbStart, $tagEnd - $tbStart + 1);
            $inner = substr($html, $tagEnd + 1, $tbClose - ($tagEnd + 1));
            if (preg_match('/((?:<tr[\s\S]*?<\/tr>){5})/i', $inner, $m)) {
                $headerRows = $m[1];
                $innerWithoutHeaders = preg_replace('/^' . preg_quote($headerRows, '/') . '/i', '', $inner, 1);
                $newTable = $tableOpen . '<thead>' . $headerRows . '</thead>' . $innerWithoutHeaders . '</table>';
                $html = substr($html, 0, $tbStart) . $newTable . substr($html, $tbClose + 8);
            }
        }
    }
    $html = '<style>@media print { thead { display: table-header-group; } }</style>' . $html;
    echo $html;
    exit;
}

if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment;filename="' . $filename . '.csv"');
    header('Cache-Control: max-age=0');
    $writer = new Csv($spreadsheet);
    $writer->setDelimiter(';');
    $writer->setEnclosure('"');
    $writer->setLineEnding("\r\n");
    $writer->setSheetIndex(0);
    $writer->save('php://output');
    exit;
}

if ($format === 'pdf') {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
    header('Cache-Control: max-age=0');
    IOFactory::registerWriter('Pdf', Dompdf::class);
    $writer = IOFactory::createWriter($spreadsheet, 'Pdf');
    $writer->save('php://output');
    exit;
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
header('Cache-Control: max-age=0');
if (ob_get_length()) { ob_end_clean(); }
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
