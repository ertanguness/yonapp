<?php

require_once dirname(__DIR__, 4) . '/configs/bootstrap.php';

use App\Helper\Helper;
use App\Services\Gate;
use Model\FinansalRaporModel;
use Model\SitelerModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

Gate::authorizeOrDie('yonetici_aidat_odeme');

$siteId = (int)($_SESSION['site_id'] ?? 0);
if ($siteId <= 0) {
    die('Site bilgisi bulunamadı.');
}

// UI state params
$mode = (string)($_GET['mode'] ?? 'view'); // view|default
$filter = (string)($_GET['filter'] ?? 'all'); // all|has_debt|paid
$sort = (string)($_GET['sort'] ?? 'unit_asc'); // unit_asc|unit_desc|name_asc|name_desc|amount_asc|amount_desc
$q = trim((string)($_GET['q'] ?? '')); // search string

$allowedMode = ['view', 'default'];
$allowedFilter = ['all', 'has_debt', 'paid'];
$allowedSort = ['unit_asc', 'unit_desc', 'name_asc', 'name_desc', 'amount_asc', 'amount_desc'];

if (!in_array($mode, $allowedMode, true)) $mode = 'view';
if (!in_array($filter, $allowedFilter, true)) $filter = 'all';
if (!in_array($sort, $allowedSort, true)) $sort = 'unit_asc';

$model = new FinansalRaporModel();
$site = (new SitelerModel())->find($siteId);

$records = $model->getSiteBorclular($siteId);

// If mode=default, keep server default order (already natural unit sort in list-new.php before JS changes).
// If mode=view, apply filter/search/sort same as UI JS.
if ($mode === 'view') {
    $normalize = function ($s) {
        return mb_strtolower((string)$s, 'UTF-8');
    };

    // Filter + Search
    $records = array_values(array_filter($records, function ($r) use ($filter, $q, $normalize) {
        $net = (float)($r->bakiye ?? 0);

        if ($filter === 'has_debt' && !($net < 0)) return false;
        if ($filter === 'paid' && !($net >= 0)) return false;

        if ($q !== '') {
            $qq = $normalize($q);
            $name = $normalize($r->adi_soyadi ?? '');
            $unit = $normalize($r->daire_kodu ?? '');
            $phone = $normalize($r->telefon ?? '');
            if (mb_strpos($name, $qq) === false && mb_strpos($unit, $qq) === false && mb_strpos($phone, $qq) === false) {
                return false;
            }
        }

        return true;
    }));

    $naturalCompare = function ($a, $b) {
        return strnatcasecmp((string)$a, (string)$b);
    };

    usort($records, function ($a, $b) use ($sort, $naturalCompare) {
        $netA = (float)($a->bakiye ?? 0);
        $netB = (float)($b->bakiye ?? 0);

        if ($sort === 'amount_asc') return $netA <=> $netB;
        if ($sort === 'amount_desc') return $netB <=> $netA;

        if ($sort === 'unit_asc') return $naturalCompare($a->daire_kodu ?? '', $b->daire_kodu ?? '');
        if ($sort === 'unit_desc') return $naturalCompare($b->daire_kodu ?? '', $a->daire_kodu ?? '');

        // name sort Turkish-ish: use mb_strtolower as a best-effort.
        $nameA = mb_strtolower((string)($a->adi_soyadi ?? ''), 'UTF-8');
        $nameB = mb_strtolower((string)($b->adi_soyadi ?? ''), 'UTF-8');
        if ($sort === 'name_asc') return $nameA <=> $nameB;
        if ($sort === 'name_desc') return $nameB <=> $nameA;

        return 0;
    });
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$spreadsheet->getDefaultStyle()->getFont()->setName('DejaVu Sans');
$spreadsheet->getDefaultStyle()->getFont()->setSize(9);
$sheet->setTitle('Borçlular');

$sheet->mergeCells('A1:G1');
$sheet->setCellValue('A1', 'Borçlular Listesi');
$sheet->mergeCells('A2:B2');
$sheet->setCellValue('A2', 'Site Adı:');
$sheet->mergeCells('C2:G2');
$sheet->setCellValue('C2', (string)($site->site_adi ?? ''));
$sheet->mergeCells('A3:B3');
$sheet->setCellValue('A3', 'Rapor Tarihi:');
$sheet->mergeCells('C3:G3');
$sheet->setCellValue('C3', date('d.m.Y H:i'));

$sheet->getStyle('A1:G3')->applyFromArray([
    'font' => ['bold' => false, 'color' => ['rgb' => '000000'], 'size' => 9],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1, 'wrapText' => true]
]);

$headers = ['Daire', 'Ad Soyad', 'Telefon', 'Üyelik', 'Durum', 'Daire Tipi', 'Bakiye'];
$row = 5;
foreach ($headers as $idx => $h) {
    $cell = Coordinate::stringFromColumnIndex($idx + 1) . $row;
    $sheet->setCellValue($cell, $h);
}
$sheet->getStyle('A5:G5')->applyFromArray([
    'font' => ['bold' => false, 'color' => ['rgb' => '000000'], 'size' => 9],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '222222']]]
]);

$row = 6;
foreach ($records as $r) {
    $net = (float)($r->bakiye ?? 0);
    $sheet->setCellValue('A' . $row, (string)($r->daire_kodu ?? ''));
    $sheet->setCellValue('B' . $row, (string)($r->adi_soyadi ?? ''));
    $sheet->setCellValue('C' . $row, (string)($r->telefon ?? ''));
    $sheet->setCellValue('D' . $row, (string)($r->uyelik_tipi ?? ''));
    $sheet->setCellValue('E' . $row, (string)($r->durum ?? ''));
    $sheet->setCellValue('F' . $row, (string)($r->daire_tipi ?? ''));
    $sheet->setCellValue('G' . $row, $net);
    $row++;
}

$endRow = max(6, $row - 1);
$sheet->getStyle('A5:G' . $endRow)->applyFromArray([
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]]
]);
$sheet->getStyle('G6:G' . $endRow)->getNumberFormat()->setFormatCode('#,##0.00');
$sheet->getStyle('G6:G' . $endRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

$sheet->getColumnDimension('A')->setWidth(12);
$sheet->getColumnDimension('B')->setWidth(26);
$sheet->getColumnDimension('C')->setWidth(14);
$sheet->getColumnDimension('D')->setWidth(12);
$sheet->getColumnDimension('E')->setWidth(10);
$sheet->getColumnDimension('F')->setWidth(12);
$sheet->getColumnDimension('G')->setWidth(14);

$sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
$sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
$sheet->getPageSetup()->setFitToWidth(1);
$sheet->getPageSetup()->setFitToHeight(0);

$rawSiteName = (string)($site->site_adi ?? 'site');
$slug = trim(preg_replace('/[^A-Za-z0-9\-]+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT', $rawSiteName) ?: $rawSiteName), '-');
$slug = $slug !== '' ? $slug : 'site';
$filename = $slug . '_borclular_' . date('Y-m-d_H-i-s');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
header('Cache-Control: max-age=0');
if (ob_get_length()) {
    ob_end_clean();
}
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
