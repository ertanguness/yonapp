<?php
// Daire Bazlı Rapor - Tüm daireler ve sakinleri
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use Model\SitelerModel;
use Model\DairelerModel;
use Model\BloklarModel;
use Model\KisilerModel;
use App\Helper\Helper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

$site_id = $_SESSION['site_id'] ?? 0;
$format = strtolower($_GET['format'] ?? 'pdf');
$iletisim = isset($_GET['iletisim']) && $_GET['iletisim'] == '1';

// Model'leri başlat
$Siteler = new SitelerModel();
$Daireler = new DairelerModel();
$Bloklar = new BloklarModel();
$Kisiler = new KisilerModel();

$site = $Siteler->find($site_id);
if (!$site) {
    die('Site bulunamadı');
}

// Tüm daireleri getir
$daireler = $Daireler->SitedekiDaireler($site_id);

$raporData = [];

foreach ($daireler as $daire) {
    // Blok bilgisi
    $blok = $Bloklar->find($daire->blok_id);
    $blok_adi = $blok->blok_adi ?? '';
    
    // Tüm aktif sakinleri getir
    $ev_sahibi = $Kisiler->AktifKisiByDaireId($daire->id, 'Kat Maliki');
    $kiraci = $Kisiler->AktifKisiByDaireId($daire->id, 'Kiracı');
    
    // Ev sahibi varsa ekle
    if ($ev_sahibi) {
        $raporData[] = [
            'blok_adi' => $blok_adi,
            'daire_no' => $daire->daire_no,
            'daire_kodu' => $daire->daire_kodu,
            'kat' => $daire->kat ?? '-',
            'daire_metrekare' => $daire->daire_metrekare ?? '-',
            'kisi_adi' => $ev_sahibi->adi_soyadi ?? '',
            'uyelik_tipi' => 'Kat Maliki',
            'telefon' => $ev_sahibi->telefon ?? '',
            'email' => $ev_sahibi->email ?? '',
            'tc_no' => $ev_sahibi->tc_no ?? ''
        ];
    }
    
    // Kiracı varsa ekle
    if ($kiraci) {
        $raporData[] = [
            'blok_adi' => $blok_adi,
            'daire_no' => $daire->daire_no,
            'daire_kodu' => $daire->daire_kodu,
            'kat' => $daire->kat ?? '-',
            'daire_metrekare' => $daire->daire_metrekare ?? '-',
            'kisi_adi' => $kiraci->adi_soyadi ?? '',
            'uyelik_tipi' => 'Kiracı',
            'telefon' => $kiraci->telefon ?? '',
            'email' => $kiraci->email ?? '',
            'tc_no' => $kiraci->tc_no ?? ''
        ];
    }
    
    // Sakin yoksa boş olarak ekle
    if (!$ev_sahibi && !$kiraci) {
        $raporData[] = [
            'blok_adi' => $blok_adi,
            'daire_no' => $daire->daire_no,
            'daire_kodu' => $daire->daire_kodu,
            'kat' => $daire->kat ?? '-',
            'daire_metrekare' => $daire->daire_metrekare ?? '-',
            'kisi_adi' => 'Boş Daire',
            'uyelik_tipi' => '-',
            'telefon' => '-',
            'email' => '-',
            'tc_no' => '-'
        ];
    }
}

// Sıralama: Blok, daire ve üyelik tipine göre
usort($raporData, function ($a, $b) {
    if ($a['blok_adi'] != $b['blok_adi']) {
        return strcmp($a['blok_adi'], $b['blok_adi']);
    }
    if (intval($a['daire_no']) != intval($b['daire_no'])) {
        return intval($a['daire_no']) - intval($b['daire_no']);
    }
    // Kat Maliki önce, sonra Kiracı
    if ($a['uyelik_tipi'] == 'Kat Maliki' && $b['uyelik_tipi'] != 'Kat Maliki') {
        return -1;
    }
    if ($a['uyelik_tipi'] != 'Kat Maliki' && $b['uyelik_tipi'] == 'Kat Maliki') {
        return 1;
    }
    return 0;
});

if (empty($raporData)) {
    die('Rapor için veri bulunamadı.');
}

// Spreadsheet oluştur
$ss = new Spreadsheet();
$sheet = $ss->getActiveSheet();
$ss->getDefaultStyle()->getFont()->setName('DejaVu Sans');
$ss->getDefaultStyle()->getFont()->setSize(9);
$sheet->setTitle('Daire Listesi');
$sheet->getDefaultRowDimension()->setRowHeight(20);
$lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($iletisim ? 10 : 8);
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
    $md->setCoordinates($lastCol . '1');
    $md->setOffsetX(2);
    $md->setOffsetY(2);
    $md->setWorksheet($sheet);
} else {
    $drawing = new Drawing();
    $drawing->setName('Logo');
    $drawing->setDescription('Site Logo');
    $drawing->setPath($logoFile);
    $drawing->setHeight(40);
    $drawing->setCoordinates($lastCol . '1');
    $drawing->setOffsetX(2);
    $drawing->setOffsetY(2);
    $drawing->setWorksheet($sheet);
}

// Başlık
$sheet->setCellValue('A1', mb_strtoupper($site->site_adi ?? 'SİTE', 'UTF-8'));
$sheet->mergeCells('A1:' . $lastCol . '1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Alt başlık
$sheet->setCellValue('A2', 'DAİRE BAZLI RAPOR - TÜM DAİRELER VE SAKİNLER');
$sheet->mergeCells('A2:' . $lastCol . '2');
$sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Rapor tarihi
$sheet->setCellValue('A3', 'Rapor Tarihi: ' . date('d.m.Y H:i'));
$sheet->mergeCells('A3:' . $lastCol . '3');
$sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Kolon başlıkları
$row = 5;
$headers = ['#', 'Blok', 'Daire', 'Daire Kodu', 'Kat', 'M²', 'Sakin Adı', 'Üyelik'];
if ($iletisim) {
    $headers[] = 'Telefon';
    $headers[] = 'E-posta';
}
$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . $row, $header);
    $col++;
}

// Başlık stili
$headerRange = 'A5:' . $lastCol . '5';
$sheet->getStyle($headerRange)->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
]);

// Veri satırları
$row++;
$sira = 1;
foreach ($raporData as $data) {
    $col = 0;
    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(++$col) . $row, $sira);
    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(++$col) . $row, $data['blok_adi']);
    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(++$col) . $row, $data['daire_no']);
    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(++$col) . $row, $data['daire_kodu']);
    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(++$col) . $row, $data['kat']);
    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(++$col) . $row, $data['daire_metrekare']);
    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(++$col) . $row, $data['kisi_adi']);
    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(++$col) . $row, $data['uyelik_tipi']);
    
    if ($iletisim) {
        $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(++$col) . $row, $data['telefon']);
        $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(++$col) . $row, $data['email']);
    }
    
    // Üyelik tipine göre renklendirme
    $colUyelik = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($iletisim ? 8 : 8);
    if ($data['uyelik_tipi'] == 'Kat Maliki') {
        $sheet->getStyle($colUyelik . $row)->getFont()->getColor()->setRGB('0000FF');
    } elseif ($data['uyelik_tipi'] == 'Kiracı') {
        $sheet->getStyle($colUyelik . $row)->getFont()->getColor()->setRGB('FF8C00');
    } else {
        $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->getFont()->getColor()->setRGB('999999');
        $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->getFont()->setItalic(true);
    }
    
    // Hizalamalar
    $sheet->getStyle('A' . $row . ':D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

    // Zebra striping
    if ($sira % 2 == 0) {
        $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F8F9FA');
    }
    
    // Kenarlıklar
    $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]]
    ]);
    
    $row++;
    $sira++;
}

// İstatistikler
$row++;
$toplam_daire = count(array_unique(array_column($raporData, 'daire_kodu')));
$ev_sahibi_sayisi = count(array_filter($raporData, fn($d) => $d['uyelik_tipi'] == 'Kat Maliki'));
$kiraci_sayisi = count(array_filter($raporData, fn($d) => $d['uyelik_tipi'] == 'Kiracı'));
$bos_daire = count(array_filter($raporData, fn($d) => $d['kisi_adi'] == 'Boş Daire'));

$sheet->setCellValue('A' . $row, 'Toplam Daire: ' . $toplam_daire);
$sheet->mergeCells('A' . $row . ':B' . $row);
$sheet->setCellValue('C' . $row, 'Kat Maliki: ' . $ev_sahibi_sayisi);
$sheet->mergeCells('C' . $row . ':D' . $row);
$sheet->setCellValue('E' . $row, 'Kiracı: ' . $kiraci_sayisi);
$sheet->mergeCells('E' . $row . ':F' . $row);
$sheet->setCellValue('G' . $row, 'Boş: ' . $bos_daire);
if ($iletisim) {
    $sheet->mergeCells('G' . $row . ':H' . $row);
}

$sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray([
    'font' => ['bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
]);

// Kolon genişlikleri
$sheet->getColumnDimension('A')->setWidth(6);
$sheet->getColumnDimension('B')->setWidth(8);
$sheet->getColumnDimension('C')->setWidth(7);
$sheet->getColumnDimension('D')->setWidth(11);
$sheet->getColumnDimension('E')->setWidth(6);
$sheet->getColumnDimension('F')->setWidth(7);
$sheet->getColumnDimension('G')->setWidth(22);
$sheet->getColumnDimension('H')->setWidth(10);
if ($iletisim) {
    $sheet->getColumnDimension('I')->setWidth(14);
    $sheet->getColumnDimension('J')->setWidth(22);
}

// Sayfa ayarları
$sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
$sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
$sheet->getPageSetup()->setFitToWidth(1);
$sheet->getPageSetup()->setFitToHeight(0);
$sheet->getPageMargins()->setTop(0.5);
$sheet->getPageMargins()->setRight(0.3);
$sheet->getPageMargins()->setLeft(0.3);
$sheet->getPageMargins()->setBottom(0.5);
$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 5);
$sheet->getStyle('A1:' . $lastCol . ($row + 10))->getAlignment()->setWrapText(true);

// Dosya adı
$filename = ($site->site_adi ?? 'site') . '_daire_raporu_' . date('Ymd_His');

// Çıktı
try {
    switch ($format) {
        case 'xlsx':
        case 'excel':
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');
            if (ob_get_length()) {
                ob_end_clean();
            }
            (new Xlsx($ss))->save('php://output');
            break;

        case 'csv':
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '.csv"');
            header('Cache-Control: max-age=0');
            if (ob_get_length()) {
                ob_end_clean();
            }
            $w = new Csv($ss);
            $w->setDelimiter(';');
            $w->setEnclosure('"');
            $w->setLineEnding("\r\n");
            $w->save('php://output');
            break;

        case 'html':
            header('Content-Type: text/html; charset=utf-8');
            if (ob_get_length()) {
                ob_end_clean();
            }
            $logo_web = '/assets/images/logo/' . ($site->logo_path ?? 'default-logo.png');
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $logo_web)) {
                $logo_web = '/assets/images/logo/default-logo.png';
            }
            echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($filename) . '</title>
    <style>
        body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; margin: 10px; font-size: 12px; }
        h1 { text-align: center; font-size: 16px; margin: 10px 0; }
        h2 { text-align: center; font-size: 14px; margin: 8px 0; }
        p { text-align: center; font-size: 11px; margin: 5px 0; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; font-size: 11px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background-color: #4472C4; color: white; font-weight: bold; text-align: center; font-size: 11px; }
        .ev-sahibi { color: #0000FF; font-weight: bold; }
        .kiraci { color: #FF8C00; font-weight: bold; }
        .bos { color: #999999; font-style: italic; }
        .stats { background-color: #FFF2CC; font-weight: bold; margin-top: 15px; padding: 8px; border: 1px solid #000; font-size: 11px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        tr:nth-child(even) { background-color: #F8F9FA; }
        /* Sütun genişlikleri */
        td:nth-child(1), th:nth-child(1) { width: 4%; text-align: center; }
        td:nth-child(2), th:nth-child(2) { width: 6%; text-align: center; }
        td:nth-child(3), th:nth-child(3) { width: 6%; text-align: center; }
        td:nth-child(4), th:nth-child(4) { width: 9%; }
        td:nth-child(5), th:nth-child(5) { width: 6%; text-align: center; }
        td:nth-child(6), th:nth-child(6) { width: 7%; text-align: right; }
        td:nth-child(7), th:nth-child(7) { width: 22%; }
        td:nth-child(8), th:nth-child(8) { width: 10%; text-align: center; }
        ' . ($iletisim ? '
        td:nth-child(9), th:nth-child(9) { width: 14%; }
        td:nth-child(10), th:nth-child(10) { width: 22%; }
        ' : '') . '
        @media print { body { margin: 5px; font-size: 10px; } table { font-size: 9px; } th, td { padding: 4px 6px; } }
    </style>
</head>
<body>
    <div style="display:flex;align-items:center;justify-content:flex-end;gap:10px;margin-top:5px;">
        <img src="' . htmlspecialchars($logo_web) . '" alt="Logo" style="height:40px;object-fit:contain;" />
    </div>
    <h1>' . strtoupper($site->site_adi ?? 'SİTE') . '</h1>
    <h2>DAİRE BAZLI RAPOR - TÜM DAİRELER VE SAKİNLER</h2>
    <p>Rapor Tarihi: ' . date('d.m.Y H:i') . '</p>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Blok</th>
                <th>Daire</th>
                <th>Daire Kodu</th>
                <th>Kat</th>
                <th class="text-right">M²</th>
                <th>Sakin Adı</th>
                <th class="text-center">Üyelik</th>';
            if ($iletisim) {
                echo '<th>Telefon</th><th>E-posta</th>';
            }
            echo '</tr>
        </thead>
        <tbody>';

            $sira = 1;
            foreach ($raporData as $data) {
                $class = $data['uyelik_tipi'] == 'Kat Maliki' ? 'ev-sahibi' : ($data['uyelik_tipi'] == 'Kiracı' ? 'kiraci' : 'bos');
                echo '<tr>
                    <td class="text-center">' . $sira . '</td>
                    <td class="text-center">' . htmlspecialchars($data['blok_adi']) . '</td>
                    <td class="text-center">' . htmlspecialchars($data['daire_no']) . '</td>
                    <td>' . htmlspecialchars($data['daire_kodu']) . '</td>
                    <td class="text-center">' . htmlspecialchars($data['kat']) . '</td>
                    <td class="text-right">' . htmlspecialchars($data['daire_metrekare']) . '</td>
                    <td class="' . $class . '">' . htmlspecialchars($data['kisi_adi']) . '</td>
                    <td class="text-center ' . $class . '">' . htmlspecialchars($data['uyelik_tipi']) . '</td>';
                if ($iletisim) {
                    echo '<td>' . htmlspecialchars($data['telefon']) . '</td>
                    <td>' . htmlspecialchars($data['email']) . '</td>';
                }
                echo '</tr>';
                $sira++;
            }

            echo '</tbody>
    </table>
    <div class="stats">
        <p>Toplam Daire: ' . $toplam_daire . ' | Kat Maliki: ' . $ev_sahibi_sayisi . ' | Kiracı: ' . $kiraci_sayisi . ' | Boş: ' . $bos_daire . '</p>
    </div>
</body>
</html>';
            break;

        case 'pdf':
        default:
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
            header('Cache-Control: max-age=0');
            IOFactory::registerWriter('Pdf', Dompdf::class);
            if (ob_get_length()) {
                ob_end_clean();
            }
            $writer = IOFactory::createWriter($ss, 'Pdf');
            $writer->save('php://output');
            break;
    }
    exit;
} catch (\Exception $e) {
    die('Export hatası: ' . $e->getMessage());
}
