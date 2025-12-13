<?php
require_once dirname(__DIR__, 4) . '/configs/bootstrap.php';

use App\Helper\Date;
use App\Helper\Helper;
use Model\FinansalRaporModel;
use Model\KisilerModel;
use Model\SitelerModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$siteId = (int)($_SESSION['site_id'] ?? 0);
if ($siteId <= 0) die('Site bilgisi bulunamadı.');

$kisiId = isset($_GET['kisi_id']) ? (int)$_GET['kisi_id'] : 0;
$finModel = new FinansalRaporModel();
$kisiModel = new KisilerModel();
$site     = (new SitelerModel())->find($siteId);

if ($kisiId > 0) {
    $people = [$kisiModel->find($kisiId)];
} else {
    // Tüm gecikmiş gruplu listeden kişi kimliklerini topla
    $records = $finModel->getGecikenBorclarGruplu($siteId);
    $ids = array_values(array_unique(array_map(function($r){ return (int)$r->kisi_id; }, $records)));
    $people = [];
    foreach ($ids as $id) {
        $p = $kisiModel->find($id);
        if ($p) $people[] = $p;
    }
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$spreadsheet->getDefaultStyle()->getFont()->setName('DejaVu Sans');
$spreadsheet->getDefaultStyle()->getFont()->setSize(9);
$sheet->setTitle('Gecikmiş Borçlar');

$sheet->mergeCells('A1:H1');
$sheet->setCellValue('A1', 'Gecikmiş Borçlar Detay');
$sheet->mergeCells('A2:B2');
$sheet->setCellValue('A2', 'Site Adı:');
$sheet->mergeCells('C2:H2');
$sheet->setCellValue('C2', $site->site_adi ?? '');
$sheet->mergeCells('A3:B3');
$sheet->setCellValue('A3', 'Rapor Tarihi:');
$sheet->mergeCells('C3:H3');
$sheet->setCellValue('C3', date('d.m.Y H:i'));
$sheet->getStyle('A1:H3')->applyFromArray([
    'font' => ['bold' => false, 'color' => ['rgb' => '000000'], 'size' => 9],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1, 'wrapText' => true]
]);

$headers = ['Borç Adı', 'Açıklama', 'Son Ödeme', 'Anapara', 'Gecikme', 'Toplam Kalan', 'Daire Kodu', 'Ad Soyad'];
$row = 5;
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

$row = 6;
foreach ($people as $person) {
    if (!$person) continue;
    $borclar = $finModel->getKisiGecikenBorclar((int)$person->id);
    if (empty($borclar)) continue;
    $sheet->mergeCells('A' . $row . ':H' . $row);
    $sheet->setCellValue('A' . $row, ($person->daire_kodu ?? '') . ' | ' . ($person->adi_soyadi ?? ''));
    $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
        'font' => ['bold' => true],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
    ]);
    $row++;
    $sumAna = 0.0; $sumGec = 0.0; $sumKalan = 0.0;
    foreach ($borclar as $b) {
        $sheet->setCellValue('A' . $row, (string)($b->borc_adi ?? ($b->aciklama ?? '')));
        $sheet->setCellValue('B' . $row, (string)($b->aciklama ?? ''));
        $sheet->setCellValue('C' . $row, Date::dmY($b->bitis_tarihi ?? ''));
        $sheet->setCellValue('D' . $row, (float)($b->kalan_anapara ?? 0));
        $sheet->setCellValue('E' . $row, (float)($b->hesaplanan_gecikme_zammi ?? 0));
        $sheet->setCellValue('F' . $row, (float)($b->toplam_kalan_borc ?? 0));
        $sheet->setCellValue('G' . $row, (string)($person->daire_kodu ?? ''));
        $sheet->setCellValue('H' . $row, (string)($person->adi_soyadi ?? ''));
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
    $row += 2;
}

$sheet->getColumnDimension('A')->setWidth(24);
$sheet->getColumnDimension('B')->setWidth(24);
$sheet->getColumnDimension('C')->setWidth(12);
$sheet->getColumnDimension('D')->setWidth(12);
$sheet->getColumnDimension('E')->setWidth(12);
$sheet->getColumnDimension('F')->setWidth(12);
$sheet->getColumnDimension('G')->setWidth(12);
$sheet->getColumnDimension('H')->setWidth(18);

$sheet->getStyle('D6:F' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0.00');
$sheet->getStyle('D6:F' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->getStyle('A5:H' . ($row - 1))->applyFromArray([
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '222222']]]
]);

$sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
$sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
$sheet->getPageSetup()->setFitToWidth(1);
$sheet->getPageSetup()->setFitToHeight(0);
$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 5);

$filename = ($site->site_adi ?? 'site') . '_gecikmis_borclar_' . date('Y-m-d_H-i-s');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
header('Cache-Control: max-age=0');
if (ob_get_length()) { ob_end_clean(); }
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
