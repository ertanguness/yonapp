<?php
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use Model\KisilerModel;
use Model\FinansalRaporModel;
use Model\SitelerModel;
use Model\DairelerModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

$format = $_GET['format'] ?? 'xlsx';
$site_id = $_GET['site_id'] ?? ($_SESSION['site_id'] ?? 0);

@ini_set('memory_limit', '1024M');
@set_time_limit(180);

$KisiModel = new KisilerModel();
$FinansalRaporModel = new FinansalRaporModel();
$Siteler = new SitelerModel();
$Daireler = new DairelerModel();
$site = $Siteler->find($site_id);

$idsParam = $_GET['kisi_ids'] ?? [];
if (is_string($idsParam)) {
    $ids = array_filter(array_map('intval', explode(',', $idsParam)));
} elseif (is_array($idsParam)) {
    $ids = array_filter(array_map('intval', $idsParam));
} else {
    $ids = [];
}

$kisiler = [];
if (!empty($ids)) {
    $records = $KisiModel->getKisilerByIds($ids);
    foreach ($records as $row) {
        $row->daire_kodu = $Daireler->DaireKodu($row->daire_id ?? 0);
        $kisiler[] = $row;
    }
}

if (empty($kisiler)) {
    die('Seçili kişi bulunamadı.');
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$spreadsheet->getDefaultStyle()->getFont()->setName('DejaVu Sans');
$spreadsheet->getDefaultStyle()->getFont()->setSize(7);

$sheet->getPageMargins()->setTop(0.4);
$sheet->getPageMargins()->setBottom(0.4);
$sheet->getPageMargins()->setLeft(0.4);
$sheet->getPageMargins()->setRight(0.4);

if (strtolower($format) === 'pdf') {
    $spreadsheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(24);
}

foreach ($kisiler as $index => $kisi) {
    $sheet = $index === 0 ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
    $sheetTitle = mb_substr($kisi->adi_soyadi ?? 'Kisi', 0, 31);
    $sheet->setTitle($sheetTitle);
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
        $md->setCoordinates('I1');
        $md->setOffsetX(2);
        $md->setOffsetY(2);
        $md->setWorksheet($sheet);
    } else {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Site Logo');
        $drawing->setPath($logoFile);
        $drawing->setHeight(36);
        $drawing->setCoordinates('I1');
        $drawing->setOffsetX(2);
        $drawing->setOffsetY(2);
        $drawing->setWorksheet($sheet);
    }

    $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
    $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
    $sheet->getPageSetup()->setFitToWidth(1);
    $sheet->getPageSetup()->setFitToHeight(0);
    $sheet->getDefaultRowDimension()->setRowHeight(18);
    $sheet->getColumnDimension('A')->setWidth(10);
    $sheet->getColumnDimension('B')->setWidth(12);
    $sheet->getColumnDimension('C')->setWidth(14);
    $sheet->getColumnDimension('D')->setWidth(16);
    $sheet->getColumnDimension('E')->setWidth(14);
    $sheet->getColumnDimension('F')->setWidth(12);
    $sheet->getColumnDimension('G')->setWidth(12);
    $sheet->getColumnDimension('H')->setWidth(12);
    $sheet->getColumnDimension('I')->setWidth(40);

    $currentRow = 1;

    $finans = $FinansalRaporModel->kisiFinansalDurum($kisi->id);
    $adiSoyadi = $kisi->adi_soyadi ?? '';
    $daireKodu = $kisi->daire_kodu ?? '';
    $oturum = trim(($kisi->uyelik_tipi ?? ''));
    $telefon = $kisi->telefon ?? '';

    $sheet->mergeCells('A' . $currentRow . ':I' . ($currentRow + 1));
    $sheet->setCellValue('A' . $currentRow, 'Kişi Hesap Özeti');
    $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
    $sheet->getStyle('A' . $currentRow . ':I' . ($currentRow + 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $currentRow += 2;

    $boxStart = $currentRow;
    $sheet->mergeCells('A' . $currentRow . ':B' . $currentRow);
    $sheet->setCellValue('A' . $currentRow, 'Daire No :');
    $sheet->mergeCells('C' . $currentRow . ':D' . $currentRow);
    $sheet->setCellValue('C' . $currentRow, $daireKodu);
    $sheet->mergeCells('E' . $currentRow . ':G' . $currentRow);
    $sheet->setCellValue('E' . $currentRow, 'Daire Kodu :');
    $sheet->mergeCells('H' . $currentRow . ':I' . $currentRow);
    $sheet->setCellValue('H' . $currentRow, $daireKodu);
    $currentRow++;

    $sheet->mergeCells('A' . $currentRow . ':B' . $currentRow);
    $sheet->setCellValue('A' . $currentRow, 'Kişi :');
    $sheet->mergeCells('C' . $currentRow . ':D' . $currentRow);
    $sheet->setCellValue('C' . $currentRow, $adiSoyadi);
    $sheet->mergeCells('E' . $currentRow . ':G' . $currentRow);
    $sheet->setCellValue('E' . $currentRow, 'Oturum Şekli :');
    $sheet->mergeCells('H' . $currentRow . ':I' . $currentRow);
    $sheet->setCellValue('H' . $currentRow, $oturum ?: '-');
    $currentRow++;

    $sheet->mergeCells('A' . $currentRow . ':B' . $currentRow);
    $sheet->setCellValue('A' . $currentRow, 'Cep Tel :');
    $sheet->mergeCells('C' . $currentRow . ':D' . $currentRow);
    $sheet->setCellValue('C' . $currentRow, $telefon ?: '-');
    $sheet->mergeCells('E' . $currentRow . ':G' . $currentRow);
    $sheet->setCellValue('E' . $currentRow, 'Bakiye :');
    $sheet->mergeCells('H' . $currentRow . ':I' . $currentRow);
    $sheet->setCellValue('H' . $currentRow, (float)($finans->bakiye ?? 0));

    $sheet->getStyle('A' . $boxStart . ':I' . $currentRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $currentRow += 2;
    $sheet->setCellValue('A' . $currentRow, ' ');
    $sheet->setCellValue('A' . $currentRow, 'Ö.T.');
    $sheet->setCellValue('B' . $currentRow, 'Tarih');
    $sheet->setCellValue('C' . $currentRow, 'Son Ödeme Tarihi');
    $sheet->setCellValue('D' . $currentRow, 'Ödenmesi Gereken');
    $sheet->setCellValue('E' . $currentRow, 'Ödenen');
    $sheet->setCellValue('F' . $currentRow, 'Gecikme Oran');
    $sheet->setCellValue('G' . $currentRow, 'Gecikme');
    $sheet->setCellValue('H' . $currentRow, 'Bakiye');
    $sheet->setCellValue('I' . $currentRow, 'Açıklama');
    $sheet->getStyle('A' . $currentRow . ':I' . $currentRow)->applyFromArray([
        'font' => ['bold' => true],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DEE2E6']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
    ]);
    $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, $currentRow);
    $currentRow++;

    $hareketler = $FinansalRaporModel->kisiHesapHareketleri($kisi->id);
    $sumAnapara = 0;
    $sumOdenen = 0;
    $sumGecikme = 0;
    $lastBakiye = 0;

    foreach ($hareketler as $h) {
        $aciklama = trim(($h->aciklama ?? '') !== '' ? $h->aciklama : ($h->borc_adi ?? $h->islem_tipi ?? '-'));
        $otKodu = $h->ot_kodu ?? ($h->borc_adi ?? '');
        $tarih = $h->islem_tarihi ? date('d.m.Y', strtotime($h->islem_tarihi)) : '';
        $sonOdeme = $h->bitis_tarihi ?? null;
        $sonOdemeFmt = $sonOdeme ? date('d.m.Y', strtotime($sonOdeme)) : '';
        $anapara = (float)($h->anapara ?? 0);
        $sumAnapara += $anapara;
        $odenen  = (float)($h->odenen ?? 0);
        $sumOdenen += $odenen;
        $gecikme = (float)($h->gecikme_zammi ?? 0);
        $sumGecikme += $gecikme;
        $bakiye  = (float)($h->yuruyen_bakiye ?? 0);
        $lastBakiye = $bakiye;

        $sheet->setCellValue('A' . $currentRow, $otKodu);
        $sheet->setCellValue('B' . $currentRow, $tarih);
        $sheet->setCellValue('C' . $currentRow, $sonOdemeFmt ?: '-');
        $sheet->setCellValue('D' . $currentRow, $anapara);
        $sheet->setCellValue('E' . $currentRow, $odenen);
        $sheet->setCellValue('F' . $currentRow, $h->gecikme_oran ?? '');
        $sheet->setCellValue('G' . $currentRow, $gecikme);
        $sheet->setCellValue('H' . $currentRow, $bakiye);
        $sheet->setCellValue('I' . $currentRow, $aciklama);
        if ($gecikme > 0) {
            $sheet->getStyle('G' . $currentRow)->getFont()->getColor()->setRGB('D90429');
        }
        $currentRow++;
    }

    $sheet->mergeCells('A' . $currentRow . ':C' . $currentRow);
    $sheet->setCellValue('A' . $currentRow, 'Toplam');
    $sheet->setCellValue('D' . $currentRow, $sumAnapara);
    $sheet->setCellValue('E' . $currentRow, $sumOdenen);
    $sheet->setCellValue('G' . $currentRow, $sumGecikme);
    $sheet->setCellValue('H' . $currentRow, $lastBakiye);
    $sheet->getStyle('A' . $currentRow . ':I' . $currentRow)->applyFromArray([
        'font' => ['bold' => true],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E9ECEF']]
    ]);

    $firstDataRow = 1;
    $lastDataRow  = $currentRow;
    $sheet->getStyle('D' . $firstDataRow . ':D' . $lastDataRow)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('E' . $firstDataRow . ':E' . $lastDataRow)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('G' . $firstDataRow . ':G' . $lastDataRow)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('H' . $firstDataRow . ':H' . $lastDataRow)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('D' . $firstDataRow . ':H' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->getStyle('A1:I' . $lastDataRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
}

$filename = 'kisi_bazinda_hesap_dokumu_' . date('Y-m-d_H-i-s');
try {
    switch ($format) {
        case 'xlsx':
        case 'excel':
            if (ob_get_length()) {
                @ob_end_clean();
            }
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');
            $writer = new Xlsx($spreadsheet);
            if (method_exists($writer, 'setPreCalculateFormulas')) {
                $writer->setPreCalculateFormulas(false);
            }
            if (method_exists($writer, 'setUseDiskCaching')) {
                $writer->setUseDiskCaching(true, sys_get_temp_dir());
            }
            $writer->save('php://output');
            break;
        case 'csv':
            if (ob_get_length()) {
                @ob_end_clean();
            }
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '.csv"');
            header('Cache-Control: max-age=0');
            $writer = new Csv($spreadsheet);
            $writer->setDelimiter(';');
            $writer->setEnclosure('"');
            $writer->setLineEnding("\r\n");
            $writer->setSheetIndex(0);
            if (method_exists($writer, 'setUseDiskCaching')) {
                $writer->setUseDiskCaching(true, sys_get_temp_dir());
            }
            $writer->save('php://output');
            break;
        case 'html':
            if (ob_get_length()) {
                @ob_end_clean();
            }
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '.html"');
            header('Cache-Control: max-age=0');
            $html = '<!DOCTYPE html><html lang="tr"><head><meta charset="utf-8"><title>' . htmlspecialchars($filename) . '</title></head><body>';
            $sheetCount = $spreadsheet->getSheetCount();
            for ($i = 0; $i < $sheetCount; $i++) {
                $htmlWriter = new Html($spreadsheet);
                $htmlWriter->setSheetIndex($i);
                $htmlWriter->setGenerateSheetNavigationBlock(false);
                ob_start();
                $htmlWriter->save('php://output');
                $sheetHtml = ob_get_clean();
                $html .= '<section style="page-break-after:always">' . $sheetHtml . '</section>';
            }
            $html .= '</body></html>';
            echo $html;
            break;
        case 'pdf':
            @ini_set('memory_limit', '512M');
            if (ob_get_length()) {
                @ob_end_clean();
            }
            $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
            $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
            $sheet->getPageSetup()->setFitToWidth(1);
            $sheet->getPageSetup()->setFitToHeight(0);
            $sheetCount = $spreadsheet->getSheetCount();
            if ($sheetCount > 1) {
                $mpdf = new \Mpdf\Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4-L',
                    'tempDir' => sys_get_temp_dir(),
                    'margin_header' => 0,
                    'margin_footer' => 0
                ]);
                $css = '<style>@page{margin:10mm;}body{font-family:DejaVu Sans,sans-serif;font-size:5pt;margin:0;padding:0;}table{width:100%;border-collapse:collapse;}td,th{border:1px solid #000;padding:8px;vertical-align:top;}</style>';
                $mpdf->WriteHTML($css);
                for ($i = 0; $i < $sheetCount; $i++) {
                    $htmlWriter = new Html($spreadsheet);
                    $htmlWriter->setSheetIndex($i);
                    $htmlWriter->setGenerateSheetNavigationBlock(false);
                    ob_start();
                    $htmlWriter->save('php://output');
                    $sheetHtml = ob_get_clean();
                    $mpdf->WriteHTML($sheetHtml);
                }
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                header('Cache-Control: max-age=0');
                $mpdf->Output($filename . '.pdf', 'I');
            } else {
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                header('Cache-Control: max-age=0');
                IOFactory::registerWriter('Pdf', \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf::class);
                $writer = IOFactory::createWriter($spreadsheet, 'Pdf');
                $writer->save('php://output');
            }
            break;
        default:
            throw new \Exception('Geçersiz format: ' . $format);
    }
    exit();
} catch (\Exception $e) {
    die('Export hatası: ' . $e->getMessage());
}