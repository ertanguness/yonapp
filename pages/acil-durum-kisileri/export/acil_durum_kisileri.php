<?php

require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use App\Helper\Helper;
use Model\AcilDurumKisileriModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$siteId = (int)($_SESSION['site_id'] ?? 0);
if ($siteId <= 0) { die('Site bilgisi bulunamadı.'); }

$req = $_GET;
$name = trim($req['name'] ?? '');
$phone = trim($req['phone'] ?? '');
$rel = trim($req['relation'] ?? '');

$pdo = getDbConnection();
$sql = "SELECT * FROM acil_durum_kisileri WHERE silinme_tarihi IS NULL";
$bind = [];
if ($name !== '') { $sql .= " AND adi_soyadi LIKE ?"; $bind[] = "%$name%"; }
if ($phone !== '') { $sql .= " AND telefon LIKE ?"; $bind[] = "%$phone%"; }
if ($rel !== '') { $sql .= " AND yakinlik = ?"; $bind[] = $rel; }
$sql .= " ORDER BY kayit_tarihi DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($bind);
$rows = $stmt->fetchAll(PDO::FETCH_OBJ);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$spreadsheet->getDefaultStyle()->getFont()->setName('DejaVu Sans');
$spreadsheet->getDefaultStyle()->getFont()->setSize(9);
$sheet->setTitle('Acil Durum Kişileri');

$headers = ['Sıra','Ad Soyad','Telefon','Yakınlık','Kayıt Tarihi'];
$idx = 1;
foreach ($headers as $h) { $sheet->setCellValue(Coordinate::stringFromColumnIndex($idx) . '4', $h); $idx++; }
$lastColIdx = count($headers);
$lastHeaderColumn = Coordinate::stringFromColumnIndex($lastColIdx);

$sheet->mergeCells('A1:' . $lastHeaderColumn . '1');
$sheet->setCellValue('A1', 'Acil Durum Kişileri');
$sheet->mergeCells('A2:' . $lastHeaderColumn . '2');
$sheet->setCellValue('A2', date('d.m.Y H:i'));

$sheet->getStyle('A4:' . $lastHeaderColumn . '4')->applyFromArray([
    'font' => ['bold' => false, 'color' => ['rgb' => '000000'], 'size' => 9],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '9CAFAA']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
]);

$rowIndex = 5;
$seq = 1;
foreach ($rows as $r) {
    $c = 1;
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowIndex, $seq++);
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowIndex, (string)($r->adi_soyadi ?? ''));
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowIndex, (string)($r->telefon ?? ''));
    $yv = $r->yakinlik ?? '';
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowIndex, (string)(Helper::RELATIONSHIP[$yv] ?? $yv));
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowIndex, (string)($r->kayit_tarihi ?? ''));
    $rowIndex++;
}

$widths = [6,24,16,16,18];
for ($i = 1; $i <= $lastColIdx; $i++) { $letter = Coordinate::stringFromColumnIndex($i); $sheet->getColumnDimension($letter)->setWidth($widths[$i - 1]); }

$lastCol = $lastHeaderColumn;
$lastRow = $rowIndex - 1;
$sheet->getStyle('A4:' . $lastCol . $lastRow)->applyFromArray([
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '222222']]]
]);

$sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
$sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
$sheet->getPageSetup()->setFitToWidth(1);
$sheet->getPageSetup()->setFitToHeight(0);
$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 4);

$filename = 'acil_durum_kisileri_' . date('Y-m-d_H-i-s');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
header('Cache-Control: max-age=0');
if (ob_get_length()) { ob_end_clean(); }
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;