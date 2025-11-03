<?php
// Gelir-Gider Raporu - Resimdeki gibi çıktı verecek şekilde düzenlendi
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use Model\SitelerModel;
use App\Helper\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

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
$selected_kasa_id = isset($_GET['kasa_id']) ? intval($_GET['kasa_id']) : ($varsayilan_kasa_id->id ?? 0);

// Gelir ve Gider verilerini ayrı ayrı çek
$gelirler_raw = $KasaHareketModel->getKasaHareketleriByDateRange($selected_kasa_id, $start, $end, 'Gelir');
$giderler_raw = $KasaHareketModel->getKasaHareketleriByDateRange($selected_kasa_id, $start, $end, 'Gider');


// print_r($gelirler_raw);
// print_r($giderler_raw);
// exit;
// --- Verileri İstenen Formata Gruplama ---
// Resimdeki gibi kategorilere göre harcamaları topluyoruz.
$gelirler = [];
$toplam_gelir = 0;
foreach ($gelirler_raw as $veri) {
    $kategori = $veri->kategori ?? 'Diğer Gelirler';
    if (!isset($gelirler[$kategori])) {
        $gelirler[$kategori] = 0;
    }
    $gelirler[$kategori] += floatval($veri->tutar);
    $toplam_gelir += floatval($veri->tutar);
}

$giderler = [];
$toplam_gider = 0;
foreach ($giderler_raw as $veri) {
    $kategori = $veri->kategori ?? 'Diğer Giderler';
    if (!isset($giderler[$kategori])) {
        $giderler[$kategori] = 0;
    }
    $giderler[$kategori] += floatval($veri->tutar);
    $toplam_gider += floatval($veri->tutar);
}


// --- Spreadsheet Oluşturma ---
$ss = new Spreadsheet();
$sheet = $ss->getActiveSheet();
$ss->getDefaultStyle()->getFont()->setName('Arial');
$ss->getDefaultStyle()->getFont()->setSize(11);
$sheet->setTitle('Eylül 2025 Gelir Gider Raporu');

// Kolon Genişlikleri
$sheet->getColumnDimension('A')->setWidth(25);
$sheet->getColumnDimension('B')->setWidth(18);
$sheet->getColumnDimension('C')->setWidth(5); // Ayırıcı sütun
$sheet->getColumnDimension('D')->setWidth(25);
$sheet->getColumnDimension('E')->setWidth(18);

// --- Başlıklar ---
// Ana Başlık
$sheet->setCellValue('A1', strtoupper($site->site_adi ?? 'ÜSKÜP EVLERİ SİTESİ'));
$sheet->mergeCells('A1:E1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Alt Başlık (Rapor Dönemi)
$sheet->setCellValue('A2', 'EYLÜL 2025 GELİR GİDER RAPORU');
$sheet->mergeCells('A2:E2');
$sheet->getStyle('A2')->getFont()->setBold(false)->setSize(12);
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getRowDimension(2)->setRowHeight(20);

// --- Gelir ve Gider Başlıkları ---
$sheet->mergeCells('A4:B4');
$sheet->setCellValue('A4', 'GELİR');
$sheet->getStyle('A4')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A4:B4')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);


$sheet->mergeCells('D4:E4');
$sheet->setCellValue('D4', 'GİDER');
$sheet->getStyle('D4')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('D4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('D4:E4')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);

// --- Veri Satırlarını Yazdırma ---
$row = 5;
$gelir_keys = array_keys($gelirler);
$gider_keys = array_keys($giderler);
$max_rows = max(count($gelirler), count($giderler));

for ($i = 0; $i < $max_rows; $i++) {
    // Gelirleri yaz
    if (isset($gelir_keys[$i])) {
        $kategori = $gelir_keys[$i];
        $tutar = $gelirler[$kategori];
        $sheet->setCellValue('A' . $row, $kategori);
        $sheet->setCellValue('B' . $row, number_format($tutar, 2, ',', '.'));
        $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }
    
    // Giderleri yaz
    if (isset($gider_keys[$i])) {
        $kategori = $gider_keys[$i];
        $tutar = $giderler[$kategori];
        $sheet->setCellValue('D' . $row, $kategori);
        $sheet->setCellValue('E' . $row, number_format($tutar, 2, ',', '.'));
        $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }

    // Hücrelere alt çizgi stili ekle
    $styleArray = [
        'borders' => [
            'bottom' => ['borderStyle' => Border::BORDER_DOTTED],
        ],
    ];
    $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray($styleArray);
    $sheet->getStyle('D'.$row.':E'.$row)->applyFromArray($styleArray);

    $row++;
}


// --- Toplam Satırları ---
$total_row = $row + 1; // Boşluk bırak

// GELİR TOPLAM
$sheet->setCellValue('A' . $total_row, 'GELİR');
$sheet->setCellValue('B' . $total_row, number_format($toplam_gelir, 2, ',', '.'));
$style_gelir_total = [
    'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'ADD8E6']], // Açık mavi
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
];
$sheet->getStyle('A' . $total_row . ':B' . $total_row)->applyFromArray($style_gelir_total);
$sheet->getStyle('A' . $total_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT); // Gelir yazısını sola al

// GİDER TOPLAM
$sheet->setCellValue('D' . $total_row, 'GİDER');
$sheet->setCellValue('E' . $total_row, number_format($toplam_gider, 2, ',', '.'));
$style_gider_total = [
    'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFDAB9']], // Açık turuncu/şeftali
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
];
$sheet->getStyle('D' . $total_row . ':E' . $total_row)->applyFromArray($style_gider_total);
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