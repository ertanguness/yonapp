<?php
// Toplu Hesap Özeti Raporu - Blok bazlı veya tüm site için kişi hesap hareketleri
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use Model\SitelerModel;
use Model\BloklarModel;
use Model\KisilerModel;
use Model\FinansalRaporModel;
use App\Helper\Helper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

$site_id = $_SESSION['site_id'] ?? 0;
$format = strtolower($_GET['format'] ?? 'pdf');
$blok_id = intval($_GET['blok_id'] ?? 0);
$sadece_borclu = isset($_GET['sadece_borclu']) && $_GET['sadece_borclu'] == '1';

// Model'leri başlat
$Siteler = new SitelerModel();
$Bloklar = new BloklarModel();
$Kisiler = new KisilerModel();
$FinansalRapor = new FinansalRaporModel();

// Site bilgisi
$site = $Siteler->find($site_id);
if (!$site) {
    die('Site bulunamadı');
}

// Blok bilgisi
$blok_adi = 'TÜM SİTE';
if ($blok_id > 0) {
    $blok = $Bloklar->find($blok_id);
    $blok_adi = $blok->blok_adi ?? 'Bilinmeyen Blok';
}

// Kişileri modelden getir
if ($blok_id > 0) {
    $kisiler = $Kisiler->getAktifKisilerByBlok($blok_id);
} else {
    $kisiler = $Kisiler->getAktifKisilerBySite($site_id);
}

if (empty($kisiler)) {
    die('Rapor için kişi bulunamadı.');
}


// Daire kodu ve üyelik tipine göre sıralama
usort($kisiler, function ($a, $b) {
    $ak = strtoupper($a->daire_kodu ?? '');
    $bk = strtoupper($b->daire_kodu ?? '');
    if ($ak === '' && $bk !== '') return 1;
    if ($bk === '' && $ak !== '') return -1;
    $c = strnatcasecmp($ak, $bk);
    if ($c !== 0) return $c;
    $rank = function ($t) {
        $t = mb_strtolower($t ?? '', 'UTF-8');
        if ($t === 'ev sahibi' || $t === 'evsahibi') return 0;
        if ($t === 'kiracı' || $t === 'kiraci') return 1;
        return 2;
    };
    $ra = $rank($a->uyelik_tipi ?? '');
    $rb = $rank($b->uyelik_tipi ?? '');
    if ($ra !== $rb) return $ra <=> $rb;
    return strcasecmp($a->adi_soyadi ?? '', $b->adi_soyadi ?? '');
});





// Spreadsheet oluştur
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
    $md->setCoordinates('K1');
    $md->setOffsetX(2);
    $md->setOffsetY(2);
    $md->setWorksheet($sheet);
} else {
    $drawing = new Drawing();
    $drawing->setName('Logo');
    $drawing->setDescription('Site Logo');
    $drawing->setPath($logoFile);
    $drawing->setHeight(40);
    $drawing->setCoordinates('K1');
    $drawing->setOffsetX(2);
    $drawing->setOffsetY(2);
    $drawing->setWorksheet($sheet);
}
$ss->getDefaultStyle()->getFont()->setName('DejaVu Sans');
$ss->getDefaultStyle()->getFont()->setSize(9);
$sheet->setTitle('Hesap Özeti');

$row = 1;
$topluVeriVar = false;

// Her kişi için hesap özeti oluştur
foreach ($kisiler as $kisi) {
    // Kişi finansal durumunu getir
    $kisiFinansalDurum = $FinansalRapor->kisiFinansalDurum($kisi->id);
    
    // Sadece borçlu filtresi (bakiye negatif olanlar = borçlu)
    if ($sadece_borclu && ($kisiFinansalDurum->bakiye ?? 0) >= 0) {
        continue;
    }
    
    // Kişi hareketlerini getir
    $kisiHareketler = $FinansalRapor->kisiHesapHareketleri($kisi->id);
    
    $topluVeriVar = true;
    
    // === KİŞİ BAŞLIK BÖLÜMÜ ===
    //Html çıktı için her kişi için yeni sayfa
    if ($row > 1) {
        $sheet->setBreak('A' . ($row - 1), \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);
    }
    
    // Ana başlık
    $sheet->mergeCells('A' . $row . ':K' . ($row + 2));
    $sheet->setCellValue('A' . $row, 'KİŞİ HESAP HAREKETLERİ');
    $sheet->getStyle('A' . $row)->applyFromArray([
        'font' => ['size' => 14, 'bold' => true],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ]);
    $row += 3;
    
    // Kişi bilgileri - Satır 1
    $sheet->mergeCells('A' . $row . ':B' . $row);
    $sheet->setCellValue('A' . $row, 'Adı Soyadı:');
    $sheet->mergeCells('C' . $row . ':F' . $row);
    $sheet->setCellValue('C' . $row, $kisi->adi_soyadi ?? '');
    $sheet->mergeCells('G' . $row . ':J' . $row);
    $sheet->setCellValue('G' . $row, 'Toplam Borç:');
    $sheet->setCellValue('K' . $row, Helper::formattedMoney($kisiFinansalDurum->toplam_borc ?? 0));
    $sheet->getStyle('G' . $row . ':J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $row++;
    
    // Kişi bilgileri - Satır 2
    $sheet->mergeCells('A' . $row . ':B' . $row);
    $sheet->setCellValue('A' . $row, 'Daire Kodu:');
    $sheet->mergeCells('C' . $row . ':F' . $row);
    $sheet->setCellValue('C' . $row, $kisi->daire_kodu ?? '');
    $sheet->mergeCells('G' . $row . ':J' . $row);
    $sheet->setCellValue('G' . $row, 'Toplam Ödenen:');
    $sheet->setCellValue('K' . $row, Helper::formattedMoney($kisiFinansalDurum->toplam_tahsilat ?? 0));
    $sheet->getStyle('G' . $row . ':J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $row++;
    
    // Kişi bilgileri - Satır 3
    $sheet->mergeCells('A' . $row . ':B' . $row);
    $sheet->setCellValue('A' . $row, 'Oturum Şekli:');
    $sheet->mergeCells('C' . $row . ':F' . $row);
    $sheet->setCellValue('C' . $row, $kisi->uyelik_tipi ?? '');
    $sheet->mergeCells('G' . $row . ':J' . $row);
    $sheet->setCellValue('G' . $row, 'Bakiye:');
    $sheet->setCellValue('K' . $row, Helper::formattedMoney($kisiFinansalDurum->bakiye ?? 0));
    $sheet->getStyle('G' . $row . ':J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    
    // Bakiye rengini ayarla
    $bakiyeStyle = $sheet->getStyle('K' . $row);
    if (($kisiFinansalDurum->bakiye ?? 0) < 0) {
        $bakiyeStyle->getFont()->getColor()->setARGB('FFFF0000');
        $bakiyeStyle->getFont()->setBold(true);
    } else {
        $bakiyeStyle->getFont()->getColor()->setARGB('FF008000');
    }
    $row++;
    
    // Rapor tarihi
    $sheet->mergeCells('A' . $row . ':B' . $row);
    $sheet->setCellValue('A' . $row, 'Rapor Tarihi:');
    $sheet->mergeCells('C' . $row . ':K' . $row);
    $sheet->setCellValue('C' . $row, date('d.m.Y H:i'));
    $row++;
    
    // Boş satır
    $sheet->mergeCells('A' . $row . ':K' . $row);
    $row++;
    
    // === HESAP HAREKETLERİ TABLOSU ===
    
    if (!empty($kisiHareketler)) {
        // Tablo başlığı
        $headerRow = $row;
        $headers = ['#', 'Tarih', 'Kategori', 'Borç', 'Alacak', 'Ödenen', 'Bakiye', 'Açıklama'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        
        foreach ($headers as $index => $header) {
            $sheet->setCellValue($cols[$index] . $row, $header);
        }
        
        $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);
        $sheet->mergeCells('H' . $row . ':K' . $row);
        $row++;
        
      
        
        // Veri satırları
        $dataStartRow = $row;
        $sira = 1;
        
        foreach ($kisiHareketler as $hareket) {


            $sheet->setCellValue('A' . $row, $sira++);
            $sheet->setCellValue('B' . $row, date('d.m.Y', strtotime($hareket->tarih ?? '')));
            $sheet->setCellValue('C' . $row, $hareket->kategori ?? '');
            $sheet->setCellValue('D' . $row, Helper::formattedMoney($hareket->anapara ?? 0));
            $sheet->setCellValue('E' . $row, Helper::formattedMoney($hareket->alacak ?? 0));
            $sheet->setCellValue('F' . $row, Helper::formattedMoney($hareket->odenen ?? 0));
            $sheet->setCellValue('G' . $row, Helper::formattedMoney($hareket->bakiye ?? 0));
            $sheet->setCellValue('H' . $row, $hareket->aciklama ?? '');
            
            // Bakiye rengi
            $bakiyeCell = $sheet->getStyle('G' . $row);
            if (($hareket->bakiye ?? 0) < 0) {
                $bakiyeCell->getFont()->getColor()->setARGB('FFFF0000');
            } else {
                $bakiyeCell->getFont()->getColor()->setARGB('FF008000');
            }
            /**h'DEN k Sütuna kadar birleştir */
            
            $sheet->mergeCells('H' . $row . ':K' . $row);
            $row++;
        }
        
        $dataEndRow = $row - 1;
        
        // Veri satırları stil
        $sheet->getStyle('A' . $dataStartRow . ':H' . $dataEndRow)->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD0D0D0']]
            ]
        ]);
        
        $sheet->getStyle('A' . $dataStartRow . ':A' . $dataEndRow)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->getStyle('B' . $dataStartRow . ':B' . $dataEndRow)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->getStyle('D' . $dataStartRow . ':G' . $dataEndRow)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        
        $sheet->getStyle('H' . $dataStartRow . ':H' . $dataEndRow)
            ->getAlignment()->setWrapText(true)->setIndent(1);
            
    } else {
        // Hareket yoksa mesaj göster
        $sheet->mergeCells('A' . $row . ':H' . $row);
        $sheet->setCellValue('A' . $row, 'Henüz hesap hareketi bulunmamaktadır.');
        $sheet->getStyle('A' . $row)->applyFromArray([
            'font' => ['italic' => true, 'color' => ['argb' => 'FF808080']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);
        $row++;
    }
    
    // Kişiler arası ayırıcı (2 boş satır)
    $row += 2;
}

// Hiç veri yoksa hata ver
if (!$topluVeriVar) {
    die('Seçilen kriterlere uygun veri bulunamadı. Lütfen filtreleri kontrol edin.');
}

// Kolon genişlikleri
$sheet->getColumnDimension('A')->setWidth(8);
$sheet->getColumnDimension('B')->setWidth(15);
$sheet->getColumnDimension('C')->setWidth(13);
$sheet->getColumnDimension('D')->setWidth(10);
$sheet->getColumnDimension('E')->setWidth(10);
$sheet->getColumnDimension('F')->setWidth(10);
$sheet->getColumnDimension('G')->setWidth(10);
$sheet->getColumnDimension('H')->setWidth(40);

// Sayfa ayarları
$sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
$sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
$sheet->getPageSetup()->setFitToWidth(1);
$sheet->getPageSetup()->setFitToHeight(0);
$sheet->getPageMargins()->setTop(0.5);
$sheet->getPageMargins()->setRight(0.3);
$sheet->getPageMargins()->setLeft(0.3);
$sheet->getPageMargins()->setBottom(0.5);

// Dosya adı
$filename = ($site->site_adi ?? 'site') . '_hesap_ozeti_' . ($blok_adi) . '_' . date('Ymd_His');

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
            $writer = new Csv($ss);
            $writer->setDelimiter(';');
            $writer->setEnclosure('"');
            $writer->setUseBOM(true);
            $writer->save('php://output');
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
            header('Content-Disposition: inline;filename="' . $filename . '.pdf"');
            header('Cache-Control: max-age=0');
            if (ob_get_length()) {
                ob_end_clean();
            }
            (new Dompdf($ss))->save('php://output');
            break;
    }
    exit;
} catch (Exception $e) {
    die('Rapor oluşturulurken hata: ' . $e->getMessage());
}
