<?php
// Gelir-Gider Raporu - Resimdeki gibi çıktı verecek şekilde düzenlendi
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use Model\SitelerModel;
use App\Helper\Date;

use App\Services\Gate;
use App\Helper\Helper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;


Gate::authorizeOrDie('rapor_gosterim', 'Yetkisiz erişim',false);


// --- Veri Çekme ve Hazırlık (Bu kısım projenize göre aynı kalabilir) ---
$site_id = $_SESSION['site_id'] ?? 0;
$format = strtolower($_GET['format'] ?? 'pdf');
// Raporun Eylül 2025'e ait olduğunu varsayarak tarihleri ayarlıyoruz.
// Gerçek kullanımda bu tarihleri dinamik olarak almanız gerekebilir.
$start = $_GET['start'] ?? date('Y-m-01', strtotime('first day of last month'));
$end = $_GET['end'] ?? date('Y-m-t', strtotime('last day of last month'));

// Site bilgilerini al


$Siteler = new SitelerModel();
$site = $Siteler->find($site_id);
if (!$site) {
    die('Site bulunamadı');
}

// Model'den verileri çekme
use Model\KasaModel;
use Model\KasaHareketModel;

$KasaModel = new KasaModel();

$varsayilan_kasa_id = $KasaModel->varsayilanKasa();

$KasaHareketModel = new KasaHareketModel();
$selected_kasa_id = isset($_GET['kasa_id']) ? intval($_GET['kasa_id']) : ($_SESSION['kasa_id'] ?? ($varsayilan_kasa_id->id ?? 0));
if (!$selected_kasa_id) {
    $kasalar = $KasaModel->SiteKasalari();
    if (!empty($kasalar)) {
        $selected_kasa_id = (int)$kasalar[0]->id;
    }
}

// Gelir ve Gider verilerini ayrı ayrı çek
$gelirler_raw = $KasaHareketModel->getKasaHareketleriByDateRange($selected_kasa_id, $start, $end, 'gelir');
$giderler_raw = $KasaHareketModel->getKasaHareketleriByDateRange($selected_kasa_id, $start, $end, 'gider');

//Helper::dd($gelirler_raw);

// print_r($gelirler_raw);
// print_r($giderler_raw);
// exit;
// --- Verileri İstenen Formata Hazırla ---
// Alt türlere göre gruplama: 1. sütun kategori, 2. sütun alt_tur, 3. sütun toplam tutar
$toplam_gelir = 0;
$gelir_grouped = [];
foreach ($gelirler_raw as $veri) {
    $kategori = trim((string)($veri->kategori ?? ''));
    $alt_tur = trim((string)($veri->alt_tur ?? ''));
    $tutar = floatval($veri->tutar ?? 0);
    $toplam_gelir += $tutar;
    if (!isset($gelir_grouped[$kategori])) {
        $gelir_grouped[$kategori] = [];
    }
    if (!isset($gelir_grouped[$kategori][$alt_tur])) {
        $gelir_grouped[$kategori][$alt_tur] = 0.0;
    }
    $gelir_grouped[$kategori][$alt_tur] += $tutar;
}

$toplam_gider = 0;
$gider_grouped = [];
foreach ($giderler_raw as $veri) {
    $kategori = trim((string)($veri->kategori ?? ''));
    $alt_tur = trim((string)($veri->alt_tur ?? ''));
    $tutar = floatval($veri->tutar ?? 0);
    $toplam_gider += $tutar;
    if (!isset($gider_grouped[$kategori])) {
        $gider_grouped[$kategori] = [];
    }
    if (!isset($gider_grouped[$kategori][$alt_tur])) {
        $gider_grouped[$kategori][$alt_tur] = 0.0;
    }
    $gider_grouped[$kategori][$alt_tur] += $tutar;
}

// Düz listeye çevir
$gelir_rows = [];
foreach ($gelir_grouped as $kategori => $alts) {
    ksort($alts);
    foreach ($alts as $alt_tur => $sum) {
        $gelir_rows[] = [$kategori, $alt_tur, $sum];
    }
}
$gider_rows = [];
foreach ($gider_grouped as $kategori => $alts) {
    ksort($alts);
    foreach ($alts as $alt_tur => $sum) {
        $gider_rows[] = [$kategori, $alt_tur, $sum];
    }
}


// --- Spreadsheet Oluşturma ---
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
    $md->setCoordinates('E1');
    $md->setOffsetX(2);
    $md->setOffsetY(2);
    $md->setWorksheet($sheet);
} else {
    $drawing = new Drawing();
    $drawing->setName('Logo');
    $drawing->setDescription('Site Logo');
    $drawing->setPath($logoFile);
    $drawing->setHeight(40);
    $drawing->setCoordinates('E1');
    $drawing->setOffsetX(2);
    $drawing->setOffsetY(2);
    $drawing->setWorksheet($sheet);
}
$ss->getDefaultStyle()->getFont()->setName('Arial');
$ss->getDefaultStyle()->getFont()->setSize(11);
$sheet->setTitle('Eylül 2025 Gelir Gider Raporu');

// Kolon Genişlikleri
$sheet->getColumnDimension('A')->setWidth(25); // Kategori (Gelir)
$sheet->getColumnDimension('B')->setWidth(25); // Alt Tür (Gelir)
$sheet->getColumnDimension('C')->setWidth(18); // Tutar (Gelir)
$sheet->getColumnDimension('D')->setWidth(25); // Kategori (Gider)
$sheet->getColumnDimension('E')->setWidth(25); // Alt Tür (Gider)
$sheet->getColumnDimension('F')->setWidth(18); // Tutar (Gider)

// --- Başlıklar ---
// Ana Başlık
$sheet->setCellValue('A1', strtoupper($site->site_adi ?? 'ÜSKÜP EVLERİ SİTESİ'));
$sheet->mergeCells('A1:E1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Alt Başlık (Rapor Dönemi)
$sheet->setCellValue('A2', Date::dmY($start) . ' - ' . Date::dmY($end) . ' ARASI 2025 GELİR GİDER RAPORU');
$sheet->mergeCells('A2:E2');
$sheet->getStyle('A2')->getFont()->setBold(false)->setSize(12);
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getRowDimension(2)->setRowHeight(20);

// --- Gelir ve Gider Başlıkları ---
$sheet->mergeCells('A4:C4');
$sheet->setCellValue('A4', 'GELİR');
$sheet->getStyle('A4')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A4:B4')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);


$sheet->mergeCells('D4:F4');
$sheet->setCellValue('D4', 'GİDER');
$sheet->getStyle('D4')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('D4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('D4:E4')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);

// --- Veri Satırlarını Yazdırma ---
$row = 5;
$max_rows = max(count($gelir_rows), count($gider_rows));

for ($i = 0; $i < $max_rows; $i++) {
    // Gelirleri yaz
    if (isset($gelir_rows[$i])) {
        [$kategori, $alt_tur, $tutar] = $gelir_rows[$i];
        $sheet->setCellValue('A' . $row, (string)$kategori);
        $sheet->setCellValue('B' . $row, (string)$alt_tur);
        $sheet->setCellValue('C' . $row, number_format((float)$tutar, 2, ',', '.'));
        $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }
    // Giderleri yaz
    if (isset($gider_rows[$i])) {
        [$kategori, $alt_tur, $tutar] = $gider_rows[$i];
        $sheet->setCellValue('D' . $row, (string)$kategori);
        $sheet->setCellValue('E' . $row, (string)$alt_tur);
        $sheet->setCellValue('F' . $row, number_format((float)$tutar, 2, ',', '.'));
        $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }

    // Hücrelere alt çizgi stili ekle
    $styleArray = [
        'borders' => [
            'bottom' => ['borderStyle' => Border::BORDER_DOTTED],
        ],
    ];
    $sheet->getStyle('A'.$row.':C'.$row)->applyFromArray($styleArray);
    $sheet->getStyle('D'.$row.':F'.$row)->applyFromArray($styleArray);

    $row++;
}


// --- Toplam Satırları ---
$total_row = $row + 1; // Boşluk bırak

// GELİR TOPLAM
$sheet->setCellValue('A' . $total_row, 'GELİR');
$sheet->setCellValue('C' . $total_row, number_format($toplam_gelir, 2, ',', '.'));
$style_gelir_total = [
    'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'ADD8E6']], // Açık mavi
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
];
$sheet->getStyle('A' . $total_row . ':C' . $total_row)->applyFromArray($style_gelir_total);
$sheet->getStyle('A' . $total_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT); // Gelir yazısını sola al

// GİDER TOPLAM
$sheet->setCellValue('D' . $total_row, 'GİDER');
$sheet->setCellValue('F' . $total_row, number_format($toplam_gider, 2, ',', '.'));
$style_gider_total = [
    'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFDAB9']], // Açık turuncu/şeftali
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
];
$sheet->getStyle('D' . $total_row . ':F' . $total_row)->applyFromArray($style_gider_total);
$sheet->getStyle('D' . $total_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT); // Gider yazısını sola al


// --- Çıktı Oluşturma (Bu kısım projenize göre aynı kalabilir) ---
$filename = ($site->site_adi ?? 'site') . '_gelir_gider_raporu_' . date('Y_m');

try {
    switch ($format) {
        case 'xlsx':
        case 'excel':
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');
            if (ob_get_length()) ob_end_clean();
            (new Xlsx($ss))->save('php://output');
            break;

             case 'html':
            header('Content-Type: text/html; charset=utf-8');
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
            IOFactory::registerWriter('Pdf', \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf::class); // VEYA Dompdf
            if (ob_get_length()) ob_end_clean();
            $writer = IOFactory::createWriter($ss, 'Pdf');
            $writer->save('php://output');
            break;
    }
    exit;
} catch (\Exception $e) {
    die('Export hatası: ' . $e->getMessage());
}
