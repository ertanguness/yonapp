<?php
// Hazirun Listesi Raporu (mülk sahipleri)
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';
use Model\SitelerModel;
use Model\DairelerModel;
use Model\KisilerModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf;

$site_id = $_SESSION['site_id'] ?? 0;
$format = strtolower($_GET['format'] ?? 'pdf');

$Siteler = new SitelerModel();
$Daireler = new DairelerModel();
$Kisiler = new KisilerModel();

$site = $Siteler->find($site_id);
if (!$site) { die('Site bulunamadı'); }

// Daire, blok ve mülk sahipleri
$daireler = $Daireler->getDairelerWithOwner($site_id); // blok_adi, daire_no, ev_sahibi

// Spreadsheet
$ss = new Spreadsheet();
$sheet = $ss->getActiveSheet();
$ss->getDefaultStyle()->getFont()->setName('DejaVu Sans');
$ss->getDefaultStyle()->getFont()->setSize(9);
$sheet->setTitle('Hazirun Listesi');

// Başlıklar
$sheet->setCellValue('A1', strtoupper($site->site_adi ?? ''));
$sheet->mergeCells('A1:F1');
$sheet->setCellValue('A2', date('d.m.Y') . ' TARİHLİ OLAĞANÜSTÜ GENEL KURUL');
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
$sheet->getStyle('A6:A'.($row-1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
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
