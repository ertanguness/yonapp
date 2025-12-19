<?php
/**
 * Gelir/Gider Excel Şablonu indir (dinamik dropdown'larla).
 * Kolonlar: Tarih*, Tutar*, Gelir/Gider*, Kategori, Alt Kategori, Açıklama, Referans Kod
 * Kategori/Alt kategori: defines tablosu (site_id + type: gelir=6, gider=7)
 */

require_once dirname(__DIR__, 4) . '/configs/bootstrap.php';

use Model\DefinesModel;
use App\Helper\Site;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$siteHelper = new Site();
$site = $siteHelper->getCurrentSite();
$siteId = (int)($_SESSION['site_id'] ?? 0);

if (!$siteId) {
    http_response_code(400);
    die('site_id bulunamadı');
}

$defines = new DefinesModel();
$gelirCats = $defines->getDefinesTypes($siteId, 6);
$giderCats = $defines->getDefinesTypes($siteId, 7);

/**
 * NamedRange güvenli adı (yalnızca A-Z0-9_). Biz id bazlı gittiğimiz için basit tutuyoruz.
 */
function nrSubByTypeId(int $type, $defineId): string
{
    return 'SUB_' . $type . '_' . (int)$defineId;
}

$ss = new Spreadsheet();
$sheet = $ss->getActiveSheet();
$sheet->setTitle('Sablon');

// Header
$headers = ['Tarih*', 'Tutar*', 'Gelir/Gider*', 'Kategori', 'Alt Kategori', 'Açıklama', 'Referans Kod'];
$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col . '1', $h);
    $col++;
}

$sheet->getStyle('A1:G1')->getFont()->setBold(true);
$sheet->getStyle('A1:G1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1:G1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFEFEF');

$sheet->getColumnDimension('A')->setWidth(14);
$sheet->getColumnDimension('B')->setWidth(12);
$sheet->getColumnDimension('C')->setWidth(16);
$sheet->getColumnDimension('D')->setWidth(22);
$sheet->getColumnDimension('E')->setWidth(22);
$sheet->getColumnDimension('F')->setWidth(40);
$sheet->getColumnDimension('G')->setWidth(18);

$sheet->freezePane('A2');

// Yardım sayfası
$helper = $ss->createSheet();
$helper->setTitle('Yardim');
$helper->setCellValue('A1', 'Gelir Kategoriler');
$helper->setCellValue('B1', 'Gider Kategoriler');
$helper->setCellValue('C1', 'KEY'); // type|id
$helper->getStyle('A1:B1')->getFont()->setBold(true);
$helper->getStyle('C1')->getFont()->setBold(true);

$max = max(count($gelirCats), count($giderCats));
for ($i = 0; $i < $max; $i++) {
    // A: Gelir kategori adı, B: Gider kategori adı
    if (isset($gelirCats[$i])) {
        $helper->setCellValue('A' . (2 + $i), (string)$gelirCats[$i]->define_name);
        $helper->setCellValue('C' . (2 + $i), '6|' . (int)($gelirCats[$i]->id ?? 0));
    }
    if (isset($giderCats[$i])) {
        $helper->setCellValue('B' . (2 + $i), (string)$giderCats[$i]->define_name);
        // Gider KEY aynı C sütununa yazılırsa gelir key'i ezebilir; bu yüzden gider key'i ayrı sütuna alıyoruz.
    }
}

// Gider KEY'leri D sütununda tutalım (B -> D)
$helper->setCellValue('D1', 'GIDER_KEY');
$helper->getStyle('D1')->getFont()->setBold(true);
for ($i = 0; $i < count($giderCats); $i++) {
    $helper->setCellValue('D' . (2 + $i), '7|' . (int)($giderCats[$i]->id ?? 0));
}

// Named ranges: INCOME_CATS, EXPENSE_CATS
if (count($gelirCats) > 0) {
    $ss->addNamedRange(new \PhpOffice\PhpSpreadsheet\NamedRange(
        'INCOME_CATS',
        $helper,
        '=$A$2:$A$' . (1 + count($gelirCats))
    ));
}
if (count($giderCats) > 0) {
    $ss->addNamedRange(new \PhpOffice\PhpSpreadsheet\NamedRange(
        'EXPENSE_CATS',
        $helper,
        '=$B$2:$B$' . (1 + count($giderCats))
    ));
}

// Alt kategoriler: sistemde alt_tur genellikle ayrı satır olarak tutuluyor.
// Bu yüzden define_name (kategori) bazında getGelirGiderKalemleri(...) ile alt_tur'leri çekiyoruz.

$helper->setCellValue('F1', 'Kategori');
$helper->setCellValue('G1', 'Alt Kategori');
$helper->setCellValue('H1', 'KAT_KEY');
$helper->getStyle('F1:H1')->getFont()->setBold(true);

$r = 2;

/**
 * Kategori adına göre alt_tur listesini DB'den getirir.
 * @return string[]
 */
function fetchAltTurler(DefinesModel $defines, int $siteId, int $type, string $kategori): array
{
    $rows = $defines->getGelirGiderKalemleri($siteId, $type, $kategori);
    $alts = [];
    foreach ($rows as $row) {
        $v = trim((string)($row->alt_tur ?? ''));
        if ($v !== '') {
            $alts[] = $v;
        }
    }
    $alts = array_values(array_unique($alts));
    sort($alts);
    return $alts;
}

// Gelir kategorileri
foreach ($gelirCats as $cat) {
    $catName = trim((string)($cat->define_name ?? ''));
    if ($catName === '') continue;
    $alts = fetchAltTurler($defines, $siteId, 6, $catName);
    if (!$alts) continue;

    $key = '6|' . (int)($cat->id ?? 0);
    $nr = nrSubByTypeId(6, $cat->id ?? 0);
    $start = $r;
    foreach ($alts as $alt) {
        $helper->setCellValue('F' . $r, $catName);
        $helper->setCellValue('G' . $r, $alt);
        $helper->setCellValue('H' . $r, $key);
        $r++;
    }
    $end = $r - 1;
    $ss->addNamedRange(new \PhpOffice\PhpSpreadsheet\NamedRange(
        $nr,
        $helper,
        '=$G$' . $start . ':$G$' . $end
    ));
}

// Gider kategorileri
foreach ($giderCats as $cat) {
    $catName = trim((string)($cat->define_name ?? ''));
    if ($catName === '') continue;
    $alts = fetchAltTurler($defines, $siteId, 7, $catName);
    if (!$alts) continue;

    $key = '7|' . (int)($cat->id ?? 0);
    $nr = nrSubByTypeId(7, $cat->id ?? 0);
    $start = $r;
    foreach ($alts as $alt) {
        $helper->setCellValue('F' . $r, $catName);
        $helper->setCellValue('G' . $r, $alt);
        $helper->setCellValue('H' . $r, $key);
        $r++;
    }
    $end = $r - 1;
    $ss->addNamedRange(new \PhpOffice\PhpSpreadsheet\NamedRange(
        $nr,
        $helper,
        '=$G$' . $start . ':$G$' . $end
    ));
}

// Yardım sheet'i gizle
$helper->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

// Veri doğrulamalar
$maxRows = 500; // Kullanıcı artırmak isterse yükseltilebilir
for ($row = 2; $row <= $maxRows; $row++) {
    // Gelir/Gider
    $dvType = $sheet->getCell('C' . $row)->getDataValidation();
    $dvType->setType(DataValidation::TYPE_LIST);
    $dvType->setAllowBlank(false);
    $dvType->setShowDropDown(true);
    $dvType->setFormula1('"Gelir,Gider"');

    // Kategori: Gelir mi Gider mi seçimine göre değişsin
    $dvCat = $sheet->getCell('D' . $row)->getDataValidation();
    $dvCat->setType(DataValidation::TYPE_LIST);
    $dvCat->setAllowBlank(true);
    $dvCat->setShowDropDown(true);
    // C sütunu "Gelir" ise INCOME_CATS, "Gider" ise EXPENSE_CATS
    $dvCat->setFormula1('=IF($C' . $row . '="Gelir",INCOME_CATS,EXPENSE_CATS)');

    // Alt kategori: seçilen kategoriye göre SUB_<kategori>
    $dvSub = $sheet->getCell('E' . $row)->getDataValidation();
    $dvSub->setType(DataValidation::TYPE_LIST);
    $dvSub->setAllowBlank(true);
    $dvSub->setShowDropDown(true);

    // Alt kategori: deterministik KEY (type|id) üzerinden.
    // Gelir KEY: Yardim!A -> Yardim!C
    // Gider KEY: Yardim!B -> Yardim!D
    $keyFromIncome = 'IFERROR(VLOOKUP($D' . $row . ',Yardim!$A$2:$C$200,3,FALSE),"")';
    $keyFromExpense = 'IFERROR(VLOOKUP($D' . $row . ',Yardim!$B$2:$D$200,3,FALSE),"")';
    $keyExpr = 'IF($C' . $row . '="Gider",' . $keyFromExpense . ',' . $keyFromIncome . ')';
    // KEY: 7|123 -> SUB_7_123
    $nrExpr = '"SUB_"&SUBSTITUTE(' . $keyExpr . ',"|","_")';
    $dvSub->setFormula1('=IF($D' . $row . '="","",IF(' . $keyExpr . '="","",INDIRECT(' . $nrExpr . ')))');
}

// Not: INDIRECT formülü özel karakterlerde yine sorun çıkarabilir.
// Şimdilik, define_name standart ise çalışır. Gerekirse kategori"kod" alanı eklenebilir.

$filename = 'gelir_gider_yukleme_sablonu_' . date('Y_m_d_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
if (ob_get_length()) {
    ob_end_clean();
}

(new Xlsx($ss))->save('php://output');
exit;
