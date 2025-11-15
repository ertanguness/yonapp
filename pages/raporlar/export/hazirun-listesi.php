<?php
// Hazirun Listesi Raporu (mülk sahipleri)
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use App\Helper\Date;
use App\Helper\Helper;
use Model\KisilerModel;
use Model\SitelerModel;
use Model\DairelerModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

$site_id = $_SESSION['site_id'] ?? 0;
$format = strtolower($_GET['format'] ?? 'pdf');
$baslik = $_GET['baslik'] ?? '';
$tarih = $_GET['tarih'] ?? '';


$Siteler = new SitelerModel();
$Daireler = new DairelerModel();
$Kisiler = new KisilerModel();

$site = $Siteler->find($site_id);
if (!$site) { die('Site bulunamadı'); }

// Daire, blok ve mülk sahipleri
$daireler = $Daireler->getDairelerWithOwner($site_id); // blok_adi, daire_no, ev_sahibi


//Helper::dd($daireler);
// Spreadsheet
$ss = new Spreadsheet();
$sheet = $ss->getActiveSheet();
$ss->getDefaultStyle()->getFont()->setName('DejaVu Sans');
$ss->getDefaultStyle()->getFont()->setSize(9);
$sheet->setTitle('Hazirun Listesi');
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
    $md->setCoordinates('F1');
    $md->setOffsetX(2);
    $md->setOffsetY(2);
    $md->setWorksheet($sheet);
} else {
    $drawing = new Drawing();
    $drawing->setName('Logo');
    $drawing->setDescription('Site Logo');
    $drawing->setPath($logoFile);
    $drawing->setHeight(40);
    $drawing->setCoordinates('F1');
    $drawing->setOffsetX(2);
    $drawing->setOffsetY(2);
    $drawing->setWorksheet($sheet);
}

// Başlıklar
$sheet->setCellValue('A1', strtoupper($site->site_adi ?? ''));
$sheet->mergeCells('A1:F1');
$sheet->setCellValue('A2', Date::dmy($tarih) .  ' ' . strtoupper($baslik));
$sheet->mergeCells('A2:F2');
$sheet->setCellValue('A3', 'HAZİRUN LİSTESİ');
$sheet->mergeCells('A3:F3');
$sheet->setCellValue('A5', 'Blok No');
$sheet->setCellValue('B5', 'Daire');
$sheet->setCellValue('C5', 'Vekil Adı Soyadı');
$sheet->setCellValue('D5', 'İmza (Vekil)');
$sheet->setCellValue('E5', 'Ev Sahibi Adı Soyadı');
$sheet->setCellValue('F5', 'İmza (Ev Sahibi)');
$sheet->getStyle('A5:F5')->applyFromArray([
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
    'fill' => ['fillType'=>\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'startColor'=>['rgb'=>'E9ECEF']],
    'borders' => ['allBorders'=>['borderStyle'=>\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
]);
$sheet->getColumnDimension('A')->setWidth(14);
$sheet->getColumnDimension('B')->setWidth(10);
$sheet->getColumnDimension('C')->setWidth(28);
$sheet->getColumnDimension('D')->setWidth(22);
$sheet->getColumnDimension('E')->setWidth(28);
$sheet->getColumnDimension('F')->setWidth(22);

//Başlıkları yinele
$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 5);

// Satırlar
$row = 6;
foreach ($daireler as $d) {
    $sheet->setCellValue('A'.$row, $d['blok_adi'] ?? '');
    $sheet->setCellValue('B'.$row, $d['daire_no'] ?? '');
    $sheet->setCellValue('C'.$row, ''); // Vekil Adı Soyadı
    $sheet->setCellValue('D'.$row, ''); // İmza (Vekil)
    $sheet->setCellValue('E'.$row, $d['ev_sahibi'] ?? '');
    $sheet->setCellValue('F'.$row, ''); // İmza (Ev Sahibi)
    $sheet->getRowDimension($row)->setRowHeight(32); // İmza için satır yüksekliği
    
    $row++;
    
}
$sheet->getStyle('A6:B'.($row-1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

//Dikeyde ortala
$sheet->getStyle('A6:B'.($row-1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
$sheet->getStyle('D6:F'.($row-1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

$sheet->getPageSetup()->setFitToWidth(1);
$sheet->getPageSetup()->setFitToHeight(0);

// Çıktı
$filename = ($site->site_adi ?? 'site') . '_hazirun_listesi_' . date('Ymd_His');
try {
    switch ($format) {
        case 'xlsx':
        case 'excel':
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');
            if (ob_get_length()) { ob_end_clean(); }
            (new Xlsx($ss))->save('php://output');
            break;
        case 'csv':
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '.csv"');
            header('Cache-Control: max-age=0');
            if (ob_get_length()) { ob_end_clean(); }
            $w = new Csv($ss); $w->setDelimiter(';'); $w->setEnclosure('"'); $w->setLineEnding("\r\n"); $w->save('php://output');
            break;
        case 'html':
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '.html"');
            header('Cache-Control: max-age=0');
            if (ob_get_length()) { ob_end_clean(); }
            (new Html($ss))->save('php://output');
            break;
        case 'pdf':
        default:
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
            header('Cache-Control: max-age=0');
            IOFactory::registerWriter('Pdf', Dompdf::class);
            if (ob_get_length()) { ob_end_clean(); }
            $writer = IOFactory::createWriter($ss, 'Pdf');
            $writer->save('php://output');
            break;
    }
    exit;
} catch (\Exception $e) {
    die('Export hatası: ' . $e->getMessage());
}
