<?php
// Aidat Raporu - Aylık aidat ödemeleri ve borç durumu
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use Model\SitelerModel;
use Model\DairelerModel;
use Model\BloklarModel;
use Model\KisilerModel;
use Model\TahsilatDetayModel;
use Model\BorclandirmaDetayModel;
use App\Helper\Helper;
use App\Helper\Date;
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
$ay = $_GET['ay'] ?? date('n');
$yil = $_GET['yil'] ?? date('Y');
$sadece_borclu = isset($_GET['sadece_borclu']) && $_GET['sadece_borclu'] == '1';
$iletisim_bilgileri = isset($_GET['iletisim_bilgileri']) && $_GET['iletisim_bilgileri'] == '1';

// Model'leri başlat
$Siteler = new SitelerModel();
$Daireler = new DairelerModel();
$Bloklar = new BloklarModel();
$Kisiler = new KisilerModel();
$TahsilatDetay = new TahsilatDetayModel();
$BorclandirmaDetay = new BorclandirmaDetayModel();

// Site bilgisi
$site = $Siteler->find($site_id);
if (!$site) {
    die('Site bulunamadı');
}

// Ay adı
$ay_adlari = [
    1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan',
    5 => 'Mayıs', 6 => 'Haziran', 7 => 'Temmuz', 8 => 'Ağustos',
    9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık'
];
$ay_adi = $ay_adlari[$ay] ?? 'Bilinmeyen';

// Seçilen ay için tarih aralığı
$baslangic_tarihi = date('Y-m-01', strtotime("$yil-$ay-01"));
$bitis_tarihi = date('Y-m-t', strtotime("$yil-$ay-01"));

// Tüm daireleri getir
$daireler = $Daireler->SitedekiDaireler($site_id);
$raporData = [];

foreach ($daireler as $daire) {
    // Daire bilgileri
    $blok = $Bloklar->find($daire->blok_id);
    $blok_adi = $blok->blok_adi ?? '';
    
    // Dairenin tüm aktif sakinlerini getir
    $ev_sahibi      = $Kisiler->AktifKisiByDaireId($daire->id, 'Kat Maliki');
    $kiraci         = $Kisiler->AktifKisiByDaireId($daire->id, 'Kiracı');
    
    // Sakin yoksa atla (boş daire bilgisi eklenmeyecek)
    if (!$ev_sahibi && !$kiraci) {
        continue;
    }
    
    // Tüm sakinleri listeye ekle (ev sahibi ve/veya kiracı)
    $sakinler = [];
    if ($ev_sahibi) {
        $sakinler[] = $ev_sahibi;
    }
    if ($kiraci) {
        $sakinler[] = $kiraci;
    }
    
    // Her sakin için borçlandırma/tahsilat bilgilerini al
    foreach ($sakinler as $kisi) {
        $toplam_borc = 0;
        $toplam_odenen = 0;
        $toplam_kalan = 0;
        
        // SEÇİLEN AYDAKI BORÇLANDIRMALARI AL (MODEL İLE)
        $borclar = $BorclandirmaDetay->getAylikBorclandirma($kisi->id, $baslangic_tarihi, $bitis_tarihi);
        
        // Borç ID'lerini topla
        $borc_ids = [];
        foreach ($borclar as $borc) {
            $toplam_borc += floatval($borc->tutar);
            $borc_ids[] = $borc->id;
        }
        
        // TAHSİLATLARI AL (MODEL İLE)
        if (!empty($borc_ids)) {
            $toplam_odenen = $TahsilatDetay->getTahsilatByBorcIds($borc_ids);
        }
        
        // KALAN BORÇ HESAPLA
        $toplam_kalan = $toplam_borc - $toplam_odenen;
        
        // "Sadece Borçlu" seçeneği kontrolü
        if ($sadece_borclu && $toplam_kalan <= 0) {
            continue;
        }
        
        // Durum belirle
        $durum = $toplam_kalan <= 0 ? 'Ödendi' : 'Borçlu';
        
        // Rapor verisine ekle
        $raporData[] = [
            'blok_adi' => $blok_adi,
            'daire_no' => $daire->daire_no,
            'daire_kodu' => $daire->daire_kodu,
            'kisi_adi' => $kisi->adi_soyadi ?? '',
            'uyelik_tipi' => $kisi->uyelik_tipi ?? '',
            'telefon' => $kisi->telefon ?? '',
            'toplam_borc' => $toplam_borc,
            'odenen' => $toplam_odenen,
            'kalan' => $toplam_kalan,
            'durum' => $durum
        ];
    }
}

// Boş kontrol
if (empty($raporData)) {
    die('Rapor için veri bulunamadı.');
}

// Sıralama: Blok ve daire numarasına göre
usort($raporData, function ($a, $b) {
    if ($a['blok_adi'] != $b['blok_adi']) {
        return strcmp($a['blok_adi'], $b['blok_adi']);
    }
    return intval($a['daire_no']) - intval($b['daire_no']);
});

// Toplamları hesapla
$genel_toplam_borc = array_sum(array_column($raporData, 'toplam_borc'));
$genel_toplam_odenen = array_sum(array_column($raporData, 'odenen'));
$genel_toplam_kalan = array_sum(array_column($raporData, 'kalan'));

// Spreadsheet oluştur
$ss = new Spreadsheet();
$sheet = $ss->getActiveSheet();
$ss->getDefaultStyle()->getFont()->setName('DejaVu Sans');
$ss->getDefaultStyle()->getFont()->setSize(9);
$sheet->setTitle('Aidat Raporu');
//Satır Yüksekliği
$sheet->getDefaultRowDimension()->setRowHeight(20);
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
    $md->setCoordinates(($iletisim_bilgileri ? 'K' : 'J') . '1');
    $md->setOffsetX(2);
    $md->setOffsetY(2);
    $md->setWorksheet($sheet);
} else {
    $drawing = new Drawing();
    $drawing->setName('Logo');
    $drawing->setDescription('Site Logo');
    $drawing->setPath($logoFile);
    $drawing->setHeight(40);
    $drawing->setCoordinates(($iletisim_bilgileri ? 'K' : 'J') . '1');
    $drawing->setOffsetX(2);
    $drawing->setOffsetY(2);
    $drawing->setWorksheet($sheet);
}

// Başlık
$lastCol = $iletisim_bilgileri ? 'K' : 'J';
$sheet->setCellValue('A1', strtoupper($site->site_adi ?? 'SİTE'));
$sheet->mergeCells('A1:' . $lastCol . '1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);




// Alt başlık
$rapor_basligi = "$ay_adi $yil AİDAT RAPORU";
if ($sadece_borclu) {
    $rapor_basligi .= " (SADECE BORÇLU DAİRELER)";
}
$sheet->setCellValue('A2', $rapor_basligi);
$sheet->mergeCells('A2:' . $lastCol . '2');
$sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Rapor tarihi
$sheet->setCellValue('A3', 'Rapor Tarihi: ' . date('d.m.Y H:i'));
$sheet->mergeCells('A3:' . $lastCol . '3');
$sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Kolon başlıkları - İletişim bilgilerine göre dinamik
$row = 5;
if ($iletisim_bilgileri) {
    $headers = ['#', 'Blok', 'Daire', 'Daire Kodu', 'Sakin Adı', 'Üyelik', 'Telefon', 'Toplam Borç', 'Ödenen', 'Kalan', 'Durum'];
} else {
    $headers = ['#', 'Blok', 'Daire', 'Daire Kodu', 'Sakin Adı', 'Üyelik', 'Toplam Borç', 'Ödenen', 'Kalan', 'Durum'];
}
$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . $row, $header);
    $col++;
}

// Başlık stili
$headerRange = 'A' . $row . ':' . $lastCol . $row;
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
    $sheet->setCellValue('A' . $row, $sira);
    $sheet->setCellValue('B' . $row, $data['blok_adi']);
    $sheet->setCellValue('C' . $row, $data['daire_no']);
    $sheet->setCellValue('D' . $row, $data['daire_kodu']);
    $sheet->setCellValue('E' . $row, $data['kisi_adi']);
    $sheet->setCellValue('F' . $row, $data['uyelik_tipi']);
    
    if ($iletisim_bilgileri) {
        $sheet->setCellValue('G' . $row, $data['telefon']);
        $sheet->setCellValue('H' . $row, number_format($data['toplam_borc'], 2, ',', '.') . ' ₺');
        $sheet->setCellValue('I' . $row, number_format($data['odenen'], 2, ',', '.') . ' ₺');
        $sheet->setCellValue('J' . $row, number_format($data['kalan'], 2, ',', '.') . ' ₺');
        $sheet->setCellValue('K' . $row, $data['durum']);
        
        // Durum renklendir
        if ($data['durum'] == 'Ödendi') {
            $sheet->getStyle('K' . $row)->getFont()->getColor()->setRGB('008000');
        } elseif ($data['durum'] == 'Borçlu') {
            $sheet->getStyle('K' . $row)->getFont()->getColor()->setRGB('FF0000');
        }
        
        // Sağa hizalama (tutarlar)
        $sheet->getStyle('H' . $row . ':J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    } else {
        $sheet->setCellValue('G' . $row, number_format($data['toplam_borc'], 2, ',', '.') . ' ₺');
        $sheet->setCellValue('H' . $row, number_format($data['odenen'], 2, ',', '.') . ' ₺');
        $sheet->setCellValue('I' . $row, number_format($data['kalan'], 2, ',', '.') . ' ₺');
        $sheet->setCellValue('J' . $row, $data['durum']);
        
        // Durum renklendir
        if ($data['durum'] == 'Ödendi') {
            $sheet->getStyle('J' . $row)->getFont()->getColor()->setRGB('008000');
        } elseif ($data['durum'] == 'Borçlu') {
            $sheet->getStyle('J' . $row)->getFont()->getColor()->setRGB('FF0000');
        }
        
        
        // Sağa hizalama (tutarlar)
        $sheet->getStyle('G' . $row . ':I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }
    
    //A'dan D'ye kadar sütundaki verileri ortala
    $sheet->getStyle('A' . $row . ':D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    
  
    
    // Kenarlıklar
    $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]]
    ]);
    
    $row++;
    $sira++;
}

// Toplam satırı
if ($iletisim_bilgileri) {
    $sheet->setCellValue('A' . $row, 'GENEL TOPLAM');
    $sheet->mergeCells('A' . $row . ':G' . $row);
    $sheet->setCellValue('H' . $row, number_format($genel_toplam_borc, 2, ',', '.') . ' ₺');
    $sheet->setCellValue('I' . $row, number_format($genel_toplam_odenen, 2, ',', '.') . ' ₺');
    $sheet->setCellValue('J' . $row, number_format($genel_toplam_kalan, 2, ',', '.') . ' ₺');
    $sheet->setCellValue('K' . $row, '');
    $sheet->getStyle('H' . $row . ':J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
} else {
    $sheet->setCellValue('A' . $row, 'GENEL TOPLAM');
    $sheet->mergeCells('A' . $row . ':F' . $row);
    $sheet->setCellValue('G' . $row, number_format($genel_toplam_borc, 2, ',', '.') . ' ₺');
    $sheet->setCellValue('H' . $row, number_format($genel_toplam_odenen, 2, ',', '.') . ' ₺');
    $sheet->setCellValue('I' . $row, number_format($genel_toplam_kalan, 2, ',', '.') . ' ₺');
    $sheet->setCellValue('J' . $row, '');
    $sheet->getStyle('G' . $row . ':I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
}

$sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray([
    'font' => ['bold' => true, 'size' => 11],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E6E6']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '000000']]]
]);

// Kolon genişlikleri - optimize edilmiş
$sheet->getColumnDimension('A')->setWidth(6);   // #
$sheet->getColumnDimension('B')->setWidth(8);   // Blok
$sheet->getColumnDimension('C')->setWidth(7);   // Daire
$sheet->getColumnDimension('D')->setWidth(11);  // Daire Kodu
$sheet->getColumnDimension('E')->setWidth(width: 35);  // Sakin Adı
$sheet->getColumnDimension('F')->setWidth(15);  // Üyelik

if ($iletisim_bilgileri) {
    $sheet->getColumnDimension('G')->setWidth(20);  // Telefon
    $sheet->getColumnDimension('H')->setWidth(18);  // Toplam Borç
    $sheet->getColumnDimension('I')->setWidth(18);  // Ödenen
    $sheet->getColumnDimension('J')->setWidth(18);  // Kalan
    $sheet->getColumnDimension('K')->setWidth(15);  // Durum
    
    $sheet->getStyle('K' . $row . ':K' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
} else {
    $sheet->getColumnDimension('E')->setWidth(width: 22);  // Sakin Adı
    $sheet->getColumnDimension('F')->setWidth(12);  // Üyelik
    $sheet->getColumnDimension('G')->setWidth(14);  // Toplam Borç
    $sheet->getColumnDimension('H')->setWidth(14);  // Ödenen
    $sheet->getColumnDimension('I')->setWidth(15);  // Kalan
    $sheet->getColumnDimension('J')->setWidth(13);  // Durum


    $sheet->getStyle('J' . $row . ':J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
}

// Tüm hücrelere otomatik wrap text
$sheet->getStyle('A1:' . $lastCol . ($row + 10))->getAlignment()->setWrapText(true);

// Sayfa ayarları - İletişim bilgilerine göre dinamik
if ($iletisim_bilgileri) {
    // İletişim bilgileri varsa YATAY/LANDSCAPE
    $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
} else {
    // İletişim bilgileri yoksa DİKEY/PORTRAIT
    $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
}

$sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
$sheet->getPageSetup()->setFitToWidth(1);
$sheet->getPageSetup()->setFitToHeight(0);
$sheet->getPageMargins()->setTop(0.5);
$sheet->getPageMargins()->setRight(0.3);
$sheet->getPageMargins()->setLeft(0.3);
$sheet->getPageMargins()->setBottom(0.5);

// Başlıkları her sayfada tekrarla (satır 1-5: site adı, rapor başlığı, tarih, boş satır, kolon başlıkları)
$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 5);

// İstatistikler
$row += 2;
$toplam_kayit = count($raporData);
$borclu_kayit = count(array_filter($raporData, fn($d) => $d['durum'] == 'Borçlu'));
$odenen_kayit = count(array_filter($raporData, fn($d) => $d['durum'] == 'Ödendi'));
$ev_sahibi_kayit = count(array_filter($raporData, fn($d) => $d['uyelik_tipi'] == 'Ev Sahibi'));
$kiraci_kayit = count(array_filter($raporData, fn($d) => $d['uyelik_tipi'] == 'Kiracı'));

if ($iletisim_bilgileri) {
    $sheet->setCellValue('A' . $row, 'Toplam Kayıt: ' . $toplam_kayit);
    $sheet->mergeCells('A' . $row . ':B' . $row);
    $sheet->setCellValue('C' . $row, 'Ev Sahibi: ' . $ev_sahibi_kayit);
    $sheet->mergeCells('C' . $row . ':D' . $row);
    $sheet->setCellValue('E' . $row, 'Kiracı: ' . $kiraci_kayit);
    $sheet->mergeCells('E' . $row . ':F' . $row);
    $sheet->setCellValue('G' . $row, 'Borçlu: ' . $borclu_kayit);
    $sheet->mergeCells('G' . $row . ':H' . $row);
    $sheet->setCellValue('I' . $row, 'Ödenen: ' . $odenen_kayit);
    $sheet->mergeCells('I' . $row . ':K' . $row);
} else {
    $sheet->setCellValue('A' . $row, 'Toplam Kayıt: ' . $toplam_kayit);
    $sheet->mergeCells('A' . $row . ':B' . $row);
    $sheet->setCellValue('C' . $row, 'Ev Sahibi: ' . $ev_sahibi_kayit);
    $sheet->mergeCells('C' . $row . ':D' . $row);
    $sheet->setCellValue('E' . $row, 'Kiracı: ' . $kiraci_kayit);
    $sheet->setCellValue('F' . $row, 'Borçlu: ' . $borclu_kayit);
    $sheet->mergeCells('F' . $row . ':G' . $row);
    $sheet->setCellValue('H' . $row, 'Ödenen: ' . $odenen_kayit);
    $sheet->mergeCells('H' . $row . ':J' . $row);
}

$sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray([
    'font' => ['bold' => true, 'size' => 9],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
]);

// Dosya adı
$filename = ($site->site_adi ?? 'site') . '_aidat_raporu_' . $ay_adi . '_' . $yil . '_' . date('Ymd_His');

// Çıktı formatına göre işlem
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
        body { 
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; 
            margin: 10px; 
            font-size: 12px;
        }
        table { 
            border-collapse: collapse; 
            width: 100%; 
            margin-top: 15px;
            font-size: 11px;
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 6px 8px; 
            text-align: left; 
        }
        th { 
            background-color: #4472C4; 
            color: white; 
            font-weight: bold;
            font-size: 11px;
        }
        .toplam { 
            background-color: #E7E6E6; 
            font-weight: bold; 
            font-size: 11px;
        }
        .stats { 
            background-color: #FFF2CC; 
            font-weight: bold; 
            margin-top: 15px; 
            padding: 8px; 
            border: 1px solid #000;
            font-size: 11px;
        }
        .odendi { color: green; font-weight: bold; }
        .borclu { color: red; font-weight: bold; }
        .bos { color: gray; font-style: italic; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        h1 { 
            text-align: center; 
            font-size: 16px;
            margin: 10px 0;
        }
        h2 { 
            text-align: center;
            font-size: 14px;
            margin: 8px 0;
        }
        p.tarih { 
            text-align: center; 
            font-size: 11px;
            margin: 5px 0;
        }
        /* Sütun genişlikleri */
        td:nth-child(1), th:nth-child(1) { width: 4%; text-align: center; } /* # */
        td:nth-child(2), th:nth-child(2) { width: 6%; text-align: center; } /* Blok */
        td:nth-child(3), th:nth-child(3) { width: 6%; text-align: center; } /* Daire */
        td:nth-child(4), th:nth-child(4) { width: 9%; } /* Daire Kodu */
        td:nth-child(5), th:nth-child(5) { width: 22%; } /* Sakin Adı */
        td:nth-child(6), th:nth-child(6) { width: 9%; text-align: center; } /* Üyelik */
        ' . ($iletisim_bilgileri ? '
        td:nth-child(7), th:nth-child(7) { width: 11%; } /* Telefon */
        td:nth-child(8), th:nth-child(8) { width: 11%; } /* Toplam Borç */
        td:nth-child(9), th:nth-child(9) { width: 11%; } /* Ödenen */
        td:nth-child(10), th:nth-child(10) { width: 11%; } /* Kalan */
        td:nth-child(11), th:nth-child(11) { width: 8%; text-align: center; } /* Durum */
        ' : '
        td:nth-child(7), th:nth-child(7) { width: 13%; } /* Toplam Borç */
        td:nth-child(8), th:nth-child(8) { width: 13%; } /* Ödenen */
        td:nth-child(9), th:nth-child(9) { width: 13%; } /* Kalan */
        td:nth-child(10), th:nth-child(10) { width: 10%; text-align: center; } /* Durum */
        ') . '
        
        @media print {
            body { margin: 5px; font-size: 10px; }
            table { font-size: 9px; }
            th, td { padding: 4px 6px; }
        }
    </style>
</head>
<body>
    <div style="display:flex;align-items:center;justify-content:flex-end;gap:10px;margin-top:5px;">
        <img src="' . htmlspecialchars($logo_web) . '" alt="Logo" style="height:40px;object-fit:contain;" />
    </div>
    <h1>' . strtoupper($site->site_adi ?? 'SİTE') . '</h1>
    <h2>' . htmlspecialchars($rapor_basligi) . '</h2>
    <p class="tarih">Rapor Tarihi: ' . date('d.m.Y H:i') . '</p>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Blok</th>
                <th>Daire</th>
                <th>Daire Kodu</th>
                <th>Sakin Adı</th>
                <th>Üyelik</th>';
            
            if ($iletisim_bilgileri) {
                echo '<th>Telefon</th>';
            }
            
            echo '<th class="text-right">Toplam Borç</th>
                <th class="text-right">Ödenen</th>
                <th class="text-right">Kalan</th>
                <th>Durum</th>
            </tr>
        </thead>
        <tbody>';
            
            $sira = 1;
            foreach ($raporData as $data) {
                $durum_class = $data['durum'] == 'Ödendi' ? 'odendi' : ($data['durum'] == 'Borçlu' ? 'borclu' : 'bos');
                echo '<tr>
                    <td class="text-center">' . $sira . '</td>
                    <td class="text-center">' . htmlspecialchars($data['blok_adi']) . '</td>
                    <td class="text-center">' . htmlspecialchars($data['daire_no']) . '</td>
                    <td>' . htmlspecialchars($data['daire_kodu']) . '</td>
                    <td>' . htmlspecialchars($data['kisi_adi']) . '</td>
                    <td class="text-center">' . htmlspecialchars($data['uyelik_tipi']) . '</td>';
                
                if ($iletisim_bilgileri) {
                    echo '<td>' . htmlspecialchars($data['telefon']) . '</td>';
                }
                
                echo '<td class="text-right">' . number_format($data['toplam_borc'], 2, ',', '.') . ' ₺</td>
                    <td class="text-right">' . number_format($data['odenen'], 2, ',', '.') . ' ₺</td>
                    <td class="text-right">' . number_format($data['kalan'], 2, ',', '.') . ' ₺</td>
                    <td class="text-center ' . $durum_class . '">' . htmlspecialchars($data['durum']) . '</td>
                </tr>';
                $sira++;
            }
            
            $colspan = $iletisim_bilgileri ? '7' : '6';
            echo '<tr class="toplam">
                <td colspan="' . $colspan . '">GENEL TOPLAM</td>
                <td class="text-right">' . number_format($genel_toplam_borc, 2, ',', '.') . ' ₺</td>
                <td class="text-right">' . number_format($genel_toplam_odenen, 2, ',', '.') . ' ₺</td>
                <td class="text-right">' . number_format($genel_toplam_kalan, 2, ',', '.') . ' ₺</td>
                <td></td>
            </tr>
        </tbody>
    </table>
    <div class="stats">
        <p>Toplam Kayıt: ' . $toplam_kayit . ' | Ev Sahibi: ' . $ev_sahibi_kayit . ' | Kiracı: ' . $kiraci_kayit . ' | Borçlu: ' . $borclu_kayit . ' | Ödenen: ' . $odenen_kayit . '</p>
    </div>
</body>
</html>';
            break;

        case 'pdf':

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
