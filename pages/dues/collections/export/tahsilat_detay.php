<?php
require_once dirname(__DIR__, 4) . '/configs/bootstrap.php';

use App\Helper\Security;
use App\Helper\Date;
use Model\BorclandirmaModel;
use Model\TahsilatDetayModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$site_id = $_SESSION['site_id'] ?? 0;
$format = strtolower($_GET['format'] ?? 'excel');
$enc_id = $id ?? '';
$id = Security::decrypt($enc_id ?? 0) ?? 0;

$BorclandirmaModel = new BorclandirmaModel();
$TahsilatDetayModel = new TahsilatDetayModel();

$borc = $BorclandirmaModel->findByID($site_id, $id);
$tahsilat_detay = $TahsilatDetayModel->getTahsilatlarByBorclandirmaId($id);

$ss = new Spreadsheet();
$sheet = $ss->getActiveSheet();
$ss->getDefaultStyle()->getFont()->setName('DejaVu Sans');
$ss->getDefaultStyle()->getFont()->setSize(10);
$sheet->setTitle('Tahsilat Detayları');
$sheet->getDefaultRowDimension()->setRowHeight(20);

$row = 1;
$headers = ['#', 'Borç Adı', 'Borç Açıklama', 'Tutar', 'Ödeme Tarihi', 'Kime'];
$col = 'A';
foreach ($headers as $h) { $sheet->setCellValue($col . $row, $h); $col++; }
$lastCol = 'F';
$sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
]);

$row++;
$i = 1;
$total = 0;
foreach ($tahsilat_detay as $detay) {
    $tutar = (float)($detay->odeme_tutari ?? 0);
    $total += $tutar;
    $sheet->setCellValue('A' . $row, $i);
    $sheet->setCellValue('B' . $row, $detay->borc_adi);
    $sheet->setCellValue('C' . $row, $detay->borc_aciklama);
    $sheet->setCellValue('D' . $row, number_format($tutar, 2, ',', '.') . ' ₺');
    $sheet->setCellValue('E' . $row, Date::dmYHIS($detay->odeme_tarihi));
    $sheet->setCellValue('F' . $row, $detay->adi_soyadi);
    $sheet->getStyle('A' . $row . ':A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]]
    ]);
    $row++; $i++;
}

$sheet->setCellValue('A' . $row, 'TOPLAM');
$sheet->mergeCells('A' . $row . ':C' . $row);
$sheet->setCellValue('D' . $row, number_format($total, 2, ',', '.') . ' ₺');
$sheet->setCellValue('E' . $row, '');
$sheet->setCellValue('F' . $row, '');
$sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray([
    'font' => ['bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E6E6']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '000000']]]
]);
$sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

$sheet->getColumnDimension('A')->setWidth(6);
$sheet->getColumnDimension('B')->setWidth(24);
$sheet->getColumnDimension('C')->setWidth(40);
$sheet->getColumnDimension('D')->setWidth(16);
$sheet->getColumnDimension('E')->setWidth(20);
$sheet->getColumnDimension('F')->setWidth(24);
$sheet->getStyle('A1:' . $lastCol . ($row + 5))->getAlignment()->setWrapText(true);

$filenameBase = ($borc->aciklama ?? 'tahsilat_detay') . '_' . date('Ymd_His');
try {
    switch ($format) {
        case 'xlsx':
        case 'excel':
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $filenameBase) . '.xlsx"');
            header('Cache-Control: max-age=0');
            if (ob_get_length()) { ob_end_clean(); }
            (new Xlsx($ss))->save('php://output');
            break;
        case 'csv':
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $filenameBase) . '.csv"');
            header('Cache-Control: max-age=0');
            if (ob_get_length()) { ob_end_clean(); }
            $w = new Csv($ss);
            $w->setDelimiter(';');
            $w->setEnclosure('"');
            $w->setLineEnding("\r\n");
            $w->save('php://output');
            break;
        default:
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $filenameBase) . '.xlsx"');
            header('Cache-Control: max-age=0');
            if (ob_get_length()) { ob_end_clean(); }
            (new Xlsx($ss))->save('php://output');
            break;
    }
    exit;
} catch (\Exception $e) {
    die('Export hatası: ' . $e->getMessage());
}

