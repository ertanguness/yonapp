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
    $ev_sahibi = $Kisiler->AktifKisiByDaireId($daire->id, 'Ev Sahibi');
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
            'uyelik_tipi' => 'Ev Sahibi',
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
    // Ev sahibi önce, sonra kiracı
    if ($a['uyelik_tipi'] == 'Ev Sahibi' && $b['uyelik_tipi'] != 'Ev Sahibi') {
        return -1;
    }
    if ($a['uyelik_tipi'] != 'Ev Sahibi' && $b['uyelik_tipi'] == 'Ev Sahibi') {
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

// Başlık
$sheet->setCellValue('A1', strtoupper($site->site_adi ?? 'SİTE'));
$lastCol = $iletisim ? 'J' : 'G';
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
$headerRange = 'A5:' . chr(ord('A') + count($headers) - 1) . '5';
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
    if ($data['uyelik_tipi'] == 'Ev Sahibi') {
        $sheet->getStyle($colUyelik . $row)->getFont()->getColor()->setRGB('0000FF');
    } elseif ($data['uyelik_tipi'] == 'Kiracı') {
        $sheet->getStyle($colUyelik . $row)->getFont()->getColor()->setRGB('FF8C00');
    } else {
        $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->getFont()->getColor()->setRGB('999999');
        $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->getFont()->setItalic(true);
    }
    
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
$ev_sahibi_sayisi = count(array_filter($raporData, fn($d) => $d['uyelik_tipi'] == 'Ev Sahibi'));
$kiraci_sayisi = count(array_filter($raporData, fn($d) => $d['uyelik_tipi'] == 'Kiracı'));
$bos_daire = count(array_filter($raporData, fn($d) => $d['kisi_adi'] == 'Boş Daire'));

$sheet->setCellValue('A' . $row, 'Toplam Daire: ' . $toplam_daire);
$sheet->mergeCells('A' . $row . ':B' . $row);
$sheet->setCellValue('C' . $row, 'Ev Sahibi: ' . $ev_sahibi_sayisi);
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
$sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
$sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
$sheet->getPageSetup()->setFitToWidth(1);
$sheet->getPageSetup()->setFitToHeight(0);

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
            echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($filename) . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; font-size: 12px; }
        h1 { text-align: center; font-size: 16px; margin: 10px 0; }
        h2 { text-align: center; font-size: 14px; margin: 8px 0; }
        p { text-align: center; font-size: 11px; margin: 5px 0; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; font-size: 11px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4472C4; color: white; font-weight: bold; text-align: center; }
        .ev-sahibi { color: #0000FF; font-weight: bold; }
        .kiraci { color: #FF8C00; font-weight: bold; }
        .bos { color: #999999; font-style: italic; }
        .stats { background-color: #FFF2CC; font-weight: bold; margin-top: 20px; padding: 10px; border: 1px solid #000; }
        .text-center { text-align: center; }
        tr:nth-child(even) { background-color: #F8F9FA; }
    </style>
</head>
<body>
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
                <th>M²</th>
                <th>Sakin Adı</th>
                <th>Üyelik</th>';
            if ($iletisim) {
                echo '<th>Telefon</th><th>E-posta</th>';
            }
            echo '</tr>
        </thead>
        <tbody>';

            $sira = 1;
            foreach ($raporData as $data) {
                $class = $data['uyelik_tipi'] == 'Ev Sahibi' ? 'ev-sahibi' : ($data['uyelik_tipi'] == 'Kiracı' ? 'kiraci' : 'bos');
                echo '<tr>
                    <td class="text-center">' . $sira . '</td>
                    <td class="text-center">' . htmlspecialchars($data['blok_adi']) . '</td>
                    <td class="text-center">' . htmlspecialchars($data['daire_no']) . '</td>
                    <td>' . htmlspecialchars($data['daire_kodu']) . '</td>
                    <td class="text-center">' . htmlspecialchars($data['kat']) . '</td>
                    <td class="text-center">' . htmlspecialchars($data['daire_metrekare']) . '</td>
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
        <p>Toplam Daire: ' . $toplam_daire . ' | Ev Sahibi: ' . $ev_sahibi_sayisi . ' | Kiracı: ' . $kiraci_sayisi . ' | Boş: ' . $bos_daire . '</p>
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
