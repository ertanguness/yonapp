<?php
require_once dirname(__DIR__, 2) . '/configs/bootstrap.php';

use Model\KisilerModel;
use Model\BloklarModel;
use Model\DairelerModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

$site_id = $_SESSION['site_id'] ?? 0;
$format = strtolower($_GET['format'] ?? 'xlsx');

$Kisiler = new KisilerModel();
$Bloklar = new BloklarModel();
$Daireler = new DairelerModel();

$rows = $Kisiler->SiteKisileriJoin($site_id, 'arac');

$ss = new Spreadsheet();
$sheet = $ss->getActiveSheet();
$sheet->setTitle('Site Araçları');

$headers = ['Blok', 'Daire', 'Adı Soyadı', 'Telefon', 'Plaka', 'Marka/Model'];
$sheet->fromArray($headers, null, 'A1');

$r = 2;
foreach ($rows as $row) {
    $blok = $Bloklar->Blok($row->blok_id ?? null);
    $daire = $Daireler->DaireAdi($row->daire_id ?? null);
    $sheet->setCellValue('A' . $r, $blok->blok_adi ?? '');
    $sheet->setCellValue('B' . $r, is_object($daire) ? ($daire->daire_no ?? '') : '');
    $sheet->setCellValue('C' . $r, $row->adi_soyadi ?? '');
    $sheet->setCellValue('D' . $r, $row->telefon ?? '');
    $sheet->setCellValue('E' . $r, $row->plaka ?? '');
    $sheet->setCellValue('F' . $r, $row->marka_model ?? '');
    $r++;
}

foreach (range('A', 'F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$filename = 'site_araclari_' . date('Ymd_His');

try {
    switch ($format) {
        case 'csv':
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '.csv"');
            header('Cache-Control: max-age=0');
            if (ob_get_length()) { ob_end_clean(); }
            $w = new Csv($ss); $w->setDelimiter(';'); $w->save('php://output');
            break;
        case 'xlsx':
        default:
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');
            if (ob_get_length()) { ob_end_clean(); }
            (new Xlsx($ss))->save('php://output');
            break;
    }
    exit;
} catch (\Exception $e) {
    die('Export hatası: ' . $e->getMessage());
}