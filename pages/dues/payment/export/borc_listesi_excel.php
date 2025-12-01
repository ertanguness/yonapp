<?php

require_once dirname(__DIR__, 4) . '/configs/bootstrap.php';

use App\Helper\Date;
use App\Helper\Helper;
use Model\FinansalRaporModel;
use Model\SitelerModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$siteId = (int)($_SESSION['site_id'] ?? 0);
if ($siteId <= 0) {
    die('Site bilgisi bulunamadı.');
}

// DataTables benzeri istek parametreleri
$request = $_GET;

$model = new FinansalRaporModel();
$records = $model->getGuncelBorclarGruplu($siteId);
$site    = (new SitelerModel())->find($siteId);

// Görünür tablo kolonlarını doldurmak için ham veri oluştur
$rows = [];
foreach ($records as $borc) {
    $kalanAnapara = (float)($borc->kalan_anapara ?? 0);
    $gecikmeZammi = (float)($borc->hesaplanan_gecikme_zammi ?? 0);
    $toplamKalan = (float)($borc->toplam_kalan_borc ?? 0);
    $krediTutari = (float)($borc->kredi_tutari ?? 0);
    $netBorc = $krediTutari - $toplamKalan;

    $rows[] = [
        'daire_kodu' => (string)($borc->daire_kodu ?? ''),
        'adi_soyadi' => (string)($borc->adi_soyadi ?? ''),
        'telefon' => (string)($borc->telefon ?? ''),
        'uyelik_tipi' => (string)($borc->uyelik_tipi ?? ''),
        'durum' => (string)($borc->durum ?? ''),
        'daire_tipi' => (string)($borc->daire_tipi ?? ''),
        'giris_tarihi' => Date::dmY($borc->giris_tarihi ?? ''),
        'cikis_tarihi' => Date::dmY($borc->cikis_tarihi ?? ''),
        '_kalan_anapara' => $kalanAnapara,
        '_gecikme_zammi' => $gecikmeZammi,
        '_toplam_kalan' => $toplamKalan,
        '_kredi_tutari' => $krediTutari,
        '_net_borc' => $netBorc,
    ];
}

// Global arama
if (!empty($request['search']['value'])) {
    $q = mb_strtolower(trim($request['search']['value']));
    $rows = array_values(array_filter($rows, function ($r) use ($q) {
        return (
            mb_strpos(mb_strtolower($r['adi_soyadi']), $q) !== false ||
            mb_strpos(mb_strtolower($r['daire_kodu']), $q) !== false
        );
    }));
}

// Kolon bazlı arama
if (!empty($request['columns']) && is_array($request['columns'])) {
    foreach ($request['columns'] as $idx => $reqCol) {
        $val = trim($reqCol['search']['value'] ?? '');
        if ($val === '') continue;
        $q = mb_strtolower($val);
        if ($idx === 1) { // daire_kodu
            $rows = array_values(array_filter($rows, function ($r) use ($q) {
                return mb_strpos(mb_strtolower($r['daire_kodu']), $q) !== false;
            }));
        } elseif ($idx === 2) { // ad soyad
            $rows = array_values(array_filter($rows, function ($r) use ($q) {
                return mb_strpos(mb_strtolower($r['adi_soyadi']), $q) !== false;
            }));
        } elseif ($idx === 3) { // giris_tarihi (formatted)
            $rows = array_values(array_filter($rows, function ($r) use ($q) {
                return mb_strpos(mb_strtolower($r['giris_tarihi']), $q) !== false;
            }));
        } elseif ($idx === 4) { // cikis_tarihi (formatted)
            $rows = array_values(array_filter($rows, function ($r) use ($q) {
                return mb_strpos(mb_strtolower($r['cikis_tarihi']), $q) !== false;
            }));
        }
    }
}

// Sıralama
if (!empty($request['order'][0]['column'])) {
    $col = (int)$request['order'][0]['column'];
    $dir = ($request['order'][0]['dir'] ?? 'asc') === 'desc' ? -1 : 1;
    if ($col === 1) { // daire_kodu doğal sıralama
        $splitTokens = function (string $s) {
            preg_match_all('/(\d+|[^\d]+)/u', $s, $m);
            return array_map(function ($t) {
                return ctype_digit($t) ? (int)$t : mb_strtolower($t);
            }, $m[0] ?? []);
        };
        usort($rows, function ($a, $b) use ($dir, $splitTokens) {
            $ta = $splitTokens($a['daire_kodu'] ?? '');
            $tb = $splitTokens($b['daire_kodu'] ?? '');
            $len = max(count($ta), count($tb));
            for ($i = 0; $i < $len; $i++) {
                $va = $ta[$i] ?? null; $vb = $tb[$i] ?? null;
                if ($va === $vb) continue;
                if ($va === null) return -1 * $dir;
                if ($vb === null) return 1 * $dir;
                if (is_int($va) && is_int($vb)) return ($va < $vb ? -1 : 1) * $dir;
                $cmp = strcmp((string)$va, (string)$vb);
                if ($cmp !== 0) return ($cmp < 0 ? -1 : 1) * $dir;
            }
            return 0;
        });
    } else {
        $keyMap = [
            2 => function ($r) { return $r['adi_soyadi']; },
            3 => function ($r) { return $r['telefon']; },
            4 => function ($r) { return $r['uyelik_tipi']; },
            5 => function ($r) { return $r['durum']; },
            6 => function ($r) { return $r['daire_tipi']; },
            7 => function ($r) { return $r['giris_tarihi']; },
            8 => function ($r) { return $r['cikis_tarihi']; },
            9 => function ($r) { return $r['_kalan_anapara']; },
            10 => function ($r) { return $r['_gecikme_zammi']; },
            11 => function ($r) { return $r['_toplam_kalan']; },
            12 => function ($r) { return $r['_kredi_tutari']; },    
            13 => function ($r) { return $r['_net_borc']; },
        ];
        if (isset($keyMap[$col])) {
            $getter = $keyMap[$col];
            usort($rows, function ($a, $b) use ($getter, $dir) {
                $va = $getter($a); $vb = $getter($b);
                if ($va == $vb) return 0;
                return ($va < $vb ? -1 : 1) * $dir;
            });
        }
    }
}

// Spreadsheet oluştur
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$spreadsheet->getDefaultStyle()->getFont()->setName('DejaVu Sans');
$spreadsheet->getDefaultStyle()->getFont()->setSize(9);
$sheet->setTitle('Borç Listesi');

// Üst başlıklar
$tableHeaders = [
    'Sıra', 'Daire Kodu', 'Ad Soyad', 'Telefon', 'Üyelik Tipi', 'Durum', 'Daire Tipi',
    'Giriş Tarihi', 'Çıkış Tarihi', 'Borç Tutarı', 'Gecikme Zammı', 'Toplam Borç', 'Kredi Tutarı', 'Net Borç'
];
$colIndex = 1;
foreach ($tableHeaders as $h) {
    $cell = Coordinate::stringFromColumnIndex($colIndex) . '4';
    $sheet->setCellValue($cell, $h);
    $colIndex++;
}
$lastColIdx = count($tableHeaders);
$lastHeaderColumn = Coordinate::stringFromColumnIndex($lastColIdx);

// Üst bilgi satırları
$sheet->mergeCells('A1:' . $lastHeaderColumn . '1');
$sheet->setCellValue('A1', 'Borç Listesi');
$sheet->mergeCells('A2:B2');
$sheet->setCellValue('A2', 'Site Adı:');
$sheet->mergeCells('C2:' . $lastHeaderColumn . '2');
$sheet->setCellValue('C2', $site->site_adi ?? '');
$sheet->mergeCells('A3:B3');
$sheet->setCellValue('A3', 'Rapor Tarihi:');
$sheet->mergeCells('C3:' . $lastHeaderColumn . '3');
$sheet->setCellValue('C3', date('d.m.Y H:i'));

// Üst kısım stilleri
$sheet->getStyle('A1:' . $lastHeaderColumn . '3')->applyFromArray([
    'font' => ['bold' => false, 'color' => ['rgb' => '000000'], 'size' => 9],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1, 'wrapText' => true]
]);

// Başlık satırı stili
$sheet->getStyle('A4:' . $lastHeaderColumn . '4')->applyFromArray([
    'font' => ['bold' => false, 'color' => ['rgb' => '000000'], 'size' => 9],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '9CAFAA']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
]);

// Veriler
$rowIndex = 5;
$seq = 1;
foreach ($rows as $r) {
    $c = 1;
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowIndex, $seq++);
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowIndex, $r['daire_kodu']);
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowIndex, $r['adi_soyadi']);
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowIndex, $r['telefon']);
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowIndex, $r['uyelik_tipi']);
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowIndex, $r['durum']);
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowIndex, $r['daire_tipi']);
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowIndex, $r['giris_tarihi']);
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowIndex, $r['cikis_tarihi']);
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowIndex, (float)$r['_kalan_anapara']);
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowIndex, (float)$r['_gecikme_zammi']);
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowIndex, (float)$r['_toplam_kalan']);
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowIndex, (float)$r['_kredi_tutari']);
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowIndex, (float)$r['_net_borc']);
    $rowIndex++;
}

// Stil: sayı formatları
$lastCol = $lastHeaderColumn;
$lastRow = $rowIndex - 1;

// Kolon genişlikleri
$widths = [6, 12, 24,12, 12, 10, 10, 12, 12, 12, 12, 12, 12, 12];
for ($i = 1; $i <= $lastColIdx; $i++) {
    $letter = Coordinate::stringFromColumnIndex($i);
    $sheet->getColumnDimension($letter)->setWidth($widths[$i - 1]);
}

// Sayısal kolonlar için format ve hizalama
$numericStartColIdx = 9; // Borç Tutarı sütunu
$numericStartCol = Coordinate::stringFromColumnIndex($numericStartColIdx);
$sheet->getStyle($numericStartCol . '5:' . $lastCol . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
$sheet->getStyle($numericStartCol . '5:' . $lastCol . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

// Tüm tabloya ince sınır
$sheet->getStyle('A4:' . $lastCol . $lastRow)->applyFromArray([
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '222222']]]
]);

// Yazdırma ayarları
$sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
$sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
$sheet->getPageSetup()->setFitToWidth(1);
$sheet->getPageSetup()->setFitToHeight(0);
$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 4);

// Çıktı
$filename = ($site->site_adi ?? 'site') . '_borc_listesi_' . date('Y-m-d_H-i-s');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
header('Cache-Control: max-age=0');
if (ob_get_length()) { ob_end_clean(); }
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;