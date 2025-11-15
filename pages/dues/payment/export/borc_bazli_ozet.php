<?php

require_once dirname(__DIR__, 4) . '/configs/bootstrap.php';

use App\Helper\Date;
use Model\SitelerModel;
use Model\FinansalRaporModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

$site_id = $_SESSION['site_id'] ?? 0;
$format = strtolower($_GET['format'] ?? 'pdf');
$start  = $_GET['start'] ?? date('Y-01-01');
$end    = $_GET['end']   ?? date('Y-m-d');

// dd.mm.yyyy geldiyse normalize et
if (strpos($start, '.') !== false) {
	$dt = \DateTime::createFromFormat('d.m.Y', $start);
	if ($dt) { $start = $dt->format('Y-m-d'); }
}
if (strpos($end, '.') !== false) {
	$dt = \DateTime::createFromFormat('d.m.Y', $end);
	if ($dt) { $end = $dt->format('Y-m-d'); }
}

$Siteler = new SitelerModel();
$Fin     = new FinansalRaporModel();

$site = $Siteler->find($site_id);
if (!$site) { die('Site bulunamadı'); }

// Veriyi çek
$rows = $Fin->getBorclandirmaSummaryByDateRange($site_id, $start, $end);


//var_dump($rows); exit;
// Spreadsheet kurulum
$ss = new Spreadsheet();
$sheet = $ss->getActiveSheet();
$ss->getDefaultStyle()->getFont()->setName('DejaVu Sans');
$ss->getDefaultStyle()->getFont()->setSize(9);
$sheet->setTitle('Borç Bazında Özet');
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
    $md->setCoordinates('F1');
    $md->setOffsetX(2);
    $md->setOffsetY(2);
    $md->setWorksheet($sheet);
} else {
    $drawing = new Drawing();
    $drawing->setName('Logo');
    $drawing->setDescription('Site Logo');
    $drawing->setPath($logoFile);
    $drawing->setHeight(36);
    $drawing->setCoordinates('F1');
    $drawing->setOffsetX(2);
    $drawing->setOffsetY(2);
    $drawing->setWorksheet($sheet);
}

// Başlıklar

$sheet->setCellValue('A5', 'SIRA');
$sheet->setCellValue('B5', 'BORÇ ADI');
$sheet->setCellValue('C5', 'AÇIKLAMA');
$sheet->setCellValue('D5', 'TOPLAM TAHAKKUK');
$sheet->setCellValue('E5', 'TOPLAM ÖDEME');
$sheet->setCellValue('F5', 'KALAN');

// Sütun genişlikleri
$sheet->getColumnDimension('A')->setWidth(8);
$sheet->getColumnDimension('B')->setWidth(20);
$sheet->getColumnDimension('C')->setWidth(32);
$sheet->getColumnDimension('D')->setWidth(18);
$sheet->getColumnDimension('E')->setWidth(18);
$sheet->getColumnDimension('F')->setWidth(18);

// Grup üst satırı stilleri
$sheet->getStyle('A5:F5')->applyFromArray([
	'font' => ['bold' => true],
	'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
	'fill' => ['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'E9ECEF']],
	'borders' => ['allBorders'=>['borderStyle'=>Border::BORDER_THIN]]
]);
$sheet->getRowDimension(5)->setRowHeight(26);

// Satırları yaz
$row = 6;
$seq = 1;
$gtTahakkuk = 0.0; $gtOdeme = 0.0; $gtKalan = 0.0;
foreach ($rows as $r) {
	$borcAdi = (string)($r->borc_adi ?? '');
	$tah = (float)($r->toplam_tahakkuk ?? 0);
	$odm = (float)($r->toplam_tahsilat ?? 0);
	$kln = max(0, $tah - $odm);
	// Açıklama örneği: AİDAT (Ocak 2025 Aidat)
	$aciklama = $r->aciklama ?? '';
	

	// PDF çıktısı için başa boşluk ekle
	$indent = ($format === 'pdf') ? '    ' : '';
	$sheet->setCellValue('A'.$row, $seq);
	$sheet->setCellValue('B'.$row, $indent . $borcAdi);
	$sheet->setCellValue('C'.$row, $indent . $aciklama);
	$sheet->setCellValue('D'.$row, $tah);
	$sheet->setCellValue('E'.$row, $odm);
	$sheet->setCellValue('F'.$row, $kln);

	if ($row % 2 === 0) {
		$sheet->getStyle('A'.$row.':F'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8F9FA');
	}
	// Tüm veri hücrelerine girinti uygula
	$sheet->getStyle('A'.$row.':F'.$row)->getAlignment()->setIndent(1);

	$gtTahakkuk += $tah; $gtOdeme += $odm; $gtKalan += $kln;
	$row++;
	$seq++;
}

// Toplam satırı


$indent = ($format === 'pdf') ? '    ' : '';
$sheet->setCellValue('A'.$row, '');
$sheet->setCellValue('B'.$row, $indent . 'TOPLAM');
$sheet->setCellValue('C'.$row, '');
$sheet->setCellValue('D'.$row, $gtTahakkuk);
$sheet->setCellValue('E'.$row, $gtOdeme);
$sheet->setCellValue('F'.$row, $gtKalan);
$sheet->getStyle('A'.$row.':F'.$row)->applyFromArray([
	'font' => ['bold' => true],
	'fill' => ['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'DEE2E6']],
	'borders' => ['allBorders'=>['borderStyle'=>Border::BORDER_THIN]]
]);

// Tüm veri hücrelerine girinti uygula
$sheet->getStyle('A'.$row.':F'.$row)->getAlignment()->setIndent(1);

// Sayısal biçim ve hizalama

$sheet->getStyle('D6:F'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->getStyle('D6:F'.$row)->getNumberFormat()->setFormatCode('#,##0.00');

// Çerçeve
$sheet->getStyle('A5:F'.($row))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

// Üst başlık ve sayfa ayarları

$lastColLetter = 'F';
$sheet->mergeCells('A1:' . $lastColLetter . '1');
$sheet->setCellValue('A1', strtoupper($site->site_adi ?? ''));
$sheet->mergeCells('A2:' . $lastColLetter . '2');
$sheet->setCellValue('A2', '['.Date::dmY($start).']-['.Date::dmY($end).'] BORÇ BAZINDA ÖDEME ÖZETİ');
$sheet->getStyle('A2:' . $lastColLetter . '2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->setCellValue($lastColLetter . '3', date('d.m.Y H:i'));
$sheet->getStyle('A1:' . $lastColLetter . '2')->applyFromArray([
	'font' => ['bold' => true],
	'alignment' => ['horizontal' => Alignment::VERTICAL_CENTER]
]);


//A sütunundaki tüm veri hücrelerini ortala
$sheet->getStyle('A6:A'.($row))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);


$sheet->getRowDimension(1)->setRowHeight(24);
$sheet->getRowDimension(2)->setRowHeight(20);

$sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
$sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
$sheet->getPageSetup()->setFitToWidth(1);
$sheet->getPageSetup()->setFitToHeight(0);
$sheet->getPageSetup()->setHorizontalCentered(true);
$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1,5);
$m = $sheet->getPageMargins(); $m->setTop(0.25); $m->setBottom(0.25); $m->setLeft(0.25); $m->setRight(0.25);
$sheet->getPageSetup()->setPrintArea('A1:' . $lastColLetter . $row);

// Çıktı
$filename = ($site->site_adi ?? 'site') . '_borc_bazinda_odeme_ozet_' . date('Ymd_His');
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

