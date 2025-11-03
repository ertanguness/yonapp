<?php 


require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';

use App\Helper\Helper;
use App\Helper\Security;
use App\Services\Gate;
use Model\KasaHareketModel;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Yetki kontrolü
//Gate::can('gelir_gider_export');


// Token ile şifreli parametre geldiyse çöz ve $_GET'e uygula
if (isset($_GET['token']) && $_GET['token'] !== '') {
    try {
        $decoded = Security::decrypt($_GET['token']);
        $arr = json_decode($decoded, true);
        if (is_array($arr)) {
            foreach ($arr as $k => $v) { $_GET[$k] = $v; }
        }
    } catch (\Throwable $e) {
        // Hatalı token yok sayılır
    }
}

$format = $_GET['format'] ?? 'xlsx';
$kasa_id = $_SESSION['kasa_id'];

// Filtre parametreleri
$start = isset($_GET['start']) && $_GET['start'] !== '' ? $_GET['start'] : null; // YYYY-MM-DD veya YYYY-MM-DD HH:ii:ss
$end   = isset($_GET['end']) && $_GET['end'] !== '' ? $_GET['end'] : null;
$type  = isset($_GET['type']) ? strtolower($_GET['type']) : 'all'; // all|income|expense

// Kolon bazlı arama parametreleri (liste sayfası uyumlu)
$q_date      = $_GET['q_date'] ?? '';
$q_islem     = $_GET['q_islem'] ?? '';
$q_daire     = $_GET['q_daire'] ?? '';
$q_hesap     = $_GET['q_hesap'] ?? '';
$q_tutar     = $_GET['q_tutar'] ?? '';
$q_bakiye    = $_GET['q_bakiye'] ?? '';
$q_kategori  = $_GET['q_kategori'] ?? '';
$q_makbuz    = $_GET['q_makbuz'] ?? '';
$q_aciklama  = $_GET['q_aciklama'] ?? '';

$KasaHareketModel = new KasaHareketModel();


// Eğer token'dan geldi ise d.m.Y tarihlerini Y-m-d formatına çevir ve start/end türet
if (!$start && isset($_GET['startDate'])) {
    $dt = \DateTime::createFromFormat('d.m.Y', (string)$_GET['startDate']);
    if ($dt) { $start = $dt->format('Y-m-d'); }
}
if (!$end && isset($_GET['endDate'])) {
    $dt = \DateTime::createFromFormat('d.m.Y', (string)$_GET['endDate']);
    if ($dt) { $end = $dt->format('Y-m-d'); }
}
if (isset($_GET['incExpType']) && (!isset($_GET['type']) || $_GET['type']==='')) {
    $type = strtolower((string)$_GET['incExpType']);
}
// Veriyi getir (tarih/tür filtresi varsa ona göre)
if ($start || $end || ($type && $type !== 'all')) {
    // Tarihler set edilmemişse mantıklı varsayılan atayalım
    $start = $start ?: date('Y-m-01');
    $end   = $end   ?: date('Y-m-t');
    $yon = '';
    if ($type === 'income') { $yon = 'Gelir'; }
    elseif ($type === 'expense') { $yon = 'Gider'; }
    $kasaHareketler = $KasaHareketModel->getKasaHareketleriByDateRange($kasa_id, $start, $end, $yon);
} else {
    $kasaHareketler = $KasaHareketModel->getKasaHareketleri($kasa_id);
}

// Kolon bazlı basit filtreleme (istemci tarafındaki arama kutuları ile uyumlu)
$kasaHareketler = array_values(array_filter($kasaHareketler, function($h) use (
    $q_date,$q_islem,$q_daire,$q_hesap,$q_tutar,$q_bakiye,$q_kategori,$q_makbuz,$q_aciklama
) {
    $ok = true;
    if ($q_date !== '')    { $ok = $ok && stripos(date('d.m.Y H:i', strtotime($h->islem_tarihi ?? '')), $q_date) !== false; }
    if ($q_islem !== '')   { $ok = $ok && stripos((string)($h->islem_tipi ?? ''), $q_islem) !== false; }
    if ($q_daire !== '')   { $ok = $ok && stripos((string)($h->daire_kodu ?? ''), $q_daire) !== false; }
    if ($q_hesap !== '')   { $ok = $ok && stripos((string)($h->adi_soyadi ?? ''), $q_hesap) !== false; }
    if ($q_tutar !== '')   { $ok = $ok && stripos((string)($h->tutar ?? ''), str_replace([',','.'], '', $q_tutar)) !== false; }
    if ($q_bakiye !== '')  { $ok = $ok && stripos((string)($h->yuruyen_bakiye ?? ''), str_replace([',','.'], '', $q_bakiye)) !== false; }
    if ($q_kategori !== ''){ $ok = $ok && stripos((string)($h->kategori ?? ''), $q_kategori) !== false; }
    if ($q_makbuz !== '')  { $ok = $ok && stripos((string)($h->makbuz_no ?? ''), $q_makbuz) !== false; }
    if ($q_aciklama !== ''){ $ok = $ok && stripos((string)($h->aciklama ?? ''), $q_aciklama) !== false; }
    return $ok;
}));

if (empty($kasaHareketler)) {
    die('Dışarı aktarılacak veri bulunamadı.');
}

// Spreadsheet oluştur
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Başlık satırı (liste sayfası ile aynı alanlar)
$headers = [
    'A1' => 'Tarih',
    'B1' => 'İşlem Türü',
    'C1' => 'Daire Kodu',
    'D1' => 'Hesap Adı',
    'E1' => 'Tutar',
    'F1' => 'Bakiye',
    'G1' => 'Kategori',
    'H1' => 'Makbuz No',
    'I1' => 'Açıklama'
];

// Başlıkları set et
foreach ($headers as $cell => $value) {
    $sheet->setCellValue($cell, $value);
}

// Başlık satırını formatla
$sheet->getStyle('A1:I1')->applyFromArray([
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF']
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '366092']
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ]
    ]
]);

// Veri satırlarını doldur
$row = 2;
foreach ($kasaHareketler as $hareket) {
    $sheet->setCellValue('A' . $row, date('d.m.Y H:i', strtotime($hareket->islem_tarihi)));
    $sheet->setCellValue('B' . $row, ucfirst($hareket->islem_tipi));
    $sheet->setCellValue('C' . $row, $hareket->daire_kodu);
    $sheet->setCellValue('D' . $row, $hareket->adi_soyadi);
    $sheet->setCellValue('E' . $row, number_format($hareket->tutar, 2, ',', '.'));
    $sheet->setCellValue('F' . $row, isset($hareket->yuruyen_bakiye) ? number_format($hareket->yuruyen_bakiye, 2, ',', '.') : '');
    $sheet->setCellValue('G' . $row, $hareket->kategori ?? '');
    $sheet->setCellValue('H' . $row, $hareket->makbuz_no ?? '');
    $sheet->setCellValue('I' . $row, $hareket->aciklama ?: '');
    
    // Satır rengini değiştir (çift satırlar açık gri)
    if ($row % 2 == 0) {
    $sheet->getStyle('A' . $row . ':I' . $row)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F8F9FA']
            ]
        ]);
    }
    
    $row++;
}

// Sütun genişliklerini ayarla
// Sütun genişlikleri
$sheet->getColumnDimension('A')->setWidth(18); // Tarih
$sheet->getColumnDimension('B')->setWidth(12); // İşlem Türü
$sheet->getColumnDimension('C')->setWidth(12); // Daire Kodu
$sheet->getColumnDimension('D')->setWidth(24); // Hesap Adı
$sheet->getColumnDimension('E')->setWidth(14); // Tutar
$sheet->getColumnDimension('F')->setWidth(14); // Bakiye
$sheet->getColumnDimension('G')->setWidth(18); // Kategori
$sheet->getColumnDimension('H')->setWidth(14); // Makbuz No
$sheet->getColumnDimension('I')->setWidth(60); // Açıklama

// Tüm verilere border ekle
$sheet->getStyle('A1:I' . ($row - 1))->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ]
    ]
]);


// Print ayarları - Başlık satırını her sayfada tekrarla
$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);

// Header ve Footer ekle
$sheet->getHeaderFooter()->setOddHeader('&C&B&14Kasa Hareketleri (Filtreli)');
$sheet->getHeaderFooter()->setOddFooter('&L&D &T&R Sayfa &P / &N');

// Dosya adı
$filename = 'kasa_hareketleri_' . date('Y-m-d_H-i-s');

// Format'a göre export yap
try {
    switch ($format) {
        case 'xlsx':
        case 'excel':
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');
            header('Pragma: public');
            header('Expires: 0');
            
            if (ob_get_length()) { ob_end_clean(); }
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            break;
            
        case 'csv':
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '.csv"');
            header('Cache-Control: max-age=0');
            header('Pragma: public');
            header('Expires: 0');
            
            if (ob_get_length()) { ob_end_clean(); }
            $writer = new Csv($spreadsheet);
            $writer->setDelimiter(';');
            $writer->setEnclosure('"');
            $writer->setLineEnding("\r\n");
            $writer->setSheetIndex(0);
            if (method_exists($writer, 'setUseBOM')) { $writer->setUseBOM(true); }
            $writer->save('php://output');
            break;
            
        case 'html':
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '.html"');
            header('Pragma: public');
            header('Expires: 0');
            
            if (ob_get_length()) { ob_end_clean(); }
            $writer = new Html($spreadsheet);
            $writer->setSheetIndex(0);
            $writer->save('php://output');
            break;
            
        case 'pdf':
            // PDF için Dompdf kullanıyoruz
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
            header('Cache-Control: max-age=0');
            header('Pragma: public');
            header('Expires: 0');
            
            // PDF writer'ı ayarla
            IOFactory::registerWriter('Pdf', Dompdf::class);
            if (ob_get_length()) { ob_end_clean(); }
            $writer = IOFactory::createWriter($spreadsheet, 'Pdf');
            
            // Kağıt boyutu ve oryantasyon
            $spreadsheet->getActiveSheet()->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
            $spreadsheet->getActiveSheet()->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
            
            $writer->save('php://output');
            break;

        case "xml":
           header('Content-Type: application/xml; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '.xml"');
            header('Cache-Control: max-age=0');
            header('Pragma: public');
            header('Expires: 0');

            // Manuel XML oluştur
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<KasaHareketleri>' . "\n";
            $xml .= '  <Rapor>' . "\n";
            $xml .= '    <Tarih>' . date('Y-m-d H:i:s') . '</Tarih>' . "\n";
            $xml .= '    <KasaId>' . $kasa_id . '</KasaId>' . "\n";
            $xml .= '  </Rapor>' . "\n";
            $xml .= '  <Hareketler>' . "\n";
            
            foreach ($kasaHareketler as $hareket) {
                $xml .= '    <Hareket>' . "\n";
                $xml .= '      <Id>' . htmlspecialchars($hareket->id, ENT_XML1, 'UTF-8') . '</Id>' . "\n";
                $xml .= '      <IslemTarihi>' . htmlspecialchars(date('Y-m-d H:i:s', strtotime($hareket->islem_tarihi)), ENT_XML1, 'UTF-8') . '</IslemTarihi>' . "\n";
                $xml .= '      <IslemTipi>' . htmlspecialchars(ucfirst($hareket->islem_tipi), ENT_XML1, 'UTF-8') . '</IslemTipi>' . "\n";
                $xml .= '      <Tutar>' . htmlspecialchars($hareket->tutar, ENT_XML1, 'UTF-8') . '</Tutar>' . "\n";
                $xml .= '      <ParaBirimi>' . htmlspecialchars($hareket->para_birimi, ENT_XML1, 'UTF-8') . '</ParaBirimi>' . "\n";
                $xml .= '      <Kategori>' . htmlspecialchars($hareket->kategori, ENT_XML1, 'UTF-8') . '</Kategori>' . "\n";
                $xml .= '      <Aciklama>' . htmlspecialchars($hareket->aciklama ?: '', ENT_XML1, 'UTF-8') . '</Aciklama>' . "\n";
                $xml .= '      <KaynakTablo>' . htmlspecialchars($hareket->kaynak_tablo ?: '', ENT_XML1, 'UTF-8') . '</KaynakTablo>' . "\n";
                $xml .= '      <KaynakId>' . htmlspecialchars($hareket->kaynak_id ?: '', ENT_XML1, 'UTF-8') . '</KaynakId>' . "\n";
                $xml .= '      <KayitYapan>' . htmlspecialchars($hareket->kayit_yapan ?: '', ENT_XML1, 'UTF-8') . '</KayitYapan>' . "\n";
                $xml .= '      <OlusturmaTarihi>' . htmlspecialchars(date('Y-m-d H:i:s', strtotime($hareket->created_at)), ENT_XML1, 'UTF-8') . '</OlusturmaTarihi>' . "\n";
                $xml .= '    </Hareket>' . "\n";
            }
            
            $xml .= '  </Hareketler>' . "\n";
            $xml .= '</KasaHareketleri>';
            
            if (ob_get_length()) { ob_end_clean(); }
            echo $xml;
            break;
            
        default:
            throw new Exception('Geçersiz format: ' . $format);
    }
    
    exit();
    
} catch (Exception $e) {
    die('Export hatası: ' . $e->getMessage());
}