<?php

require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use App\Helper\Helper;
use App\Services\Gate;
use Model\KisilerModel;
use Model\FinansalRaporModel;
use Model\SitelerModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

// Parametreler: blok_id veya tumu

$format = $_GET['format'] ?? 'xlsx';
$blok_id = $_GET['blok_id'] ?? 'tumu';
$site_id = $_GET['site_id'] ?? ($_SESSION['site_id'] ?? 0);

// Performans: Kaynak sınırlarını artır
@ini_set('memory_limit', '1024M');
@set_time_limit(180);

$KisiModel = new KisilerModel();
$FinansalRaporModel = new FinansalRaporModel();
$Siteler = new SitelerModel();
$site = $Siteler->find($site_id);



// Kişileri getir
if ($blok_id == 0) {
    $kisiler = $KisiModel->getAktifKisilerBySite($site_id); // Tüm site
} else {
    $kisiler = $KisiModel->getAktifKisilerByBlok($blok_id); // Belirli blok
}




if (empty($kisiler)) {
    die('Dışarı aktarılacak kişi bulunamadı.');
}

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


$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$spreadsheet->getDefaultStyle()->getFont()->setName('DejaVu Sans');
$spreadsheet->getDefaultStyle()->getFont()->setSize(sizeInPoints: 7);

//sayfa kenar boşluklarını ayarla
$sheet->getPageMargins()->setTop(0.4);
$sheet->getPageMargins()->setBottom(0.4);
$sheet->getPageMargins()->setLeft(0.4);
$sheet->getPageMargins()->setRight(0.4);

// Varsayılan satır yüksekliğini artır
if (strtolower($_GET['format'] ?? 'xlsx') === 'pdf') {
    $spreadsheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(height: 24);
}

// Tüm formatlar için PDF ile aynı yerleşim: kişi başına bir sayfa (worksheet)
{
    // PDF için kişi başına worksheet oluştur
    foreach ($kisiler as $index => $kisi) {
        // Kişi başına yeni sheet
        $sheet = $index === 0 ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
        $sheetTitle = mb_substr($kisi->adi_soyadi, 0, 31); // Excel sheet isim limiti 31 karakter
        $sheet->setTitle($sheetTitle);
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
            $md->setCoordinates('I1');
            $md->setOffsetX(2);
            $md->setOffsetY(2);
            $md->setWorksheet($sheet);
        } else {
            $drawing = new Drawing();
            $drawing->setName('Logo');
            $drawing->setDescription('Site Logo');
            $drawing->setPath($logoFile);
            $drawing->setHeight(36);
            $drawing->setCoordinates('I1');
            $drawing->setOffsetX(2);
            $drawing->setOffsetY(2);
            $drawing->setWorksheet($sheet);
        }
             
        // Sayfa ayarları (her sheet için)
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        
        // Satır yüksekliğini artır
        $sheet->getDefaultRowDimension()->setRowHeight(18);
        
        // Sütun genişlikleri
        $sheet->getColumnDimension('A')->setWidth(10);  // Ö.T.
        $sheet->getColumnDimension('B')->setWidth(12);  // Tarih
        $sheet->getColumnDimension('C')->setWidth(14);  // Son Ödeme
        $sheet->getColumnDimension('D')->setWidth(16);  // Ödenmesi Gereken
        $sheet->getColumnDimension('E')->setWidth(14);  // Ödenen
        $sheet->getColumnDimension('F')->setWidth(12);  // Gecikme Oran
        $sheet->getColumnDimension('G')->setWidth(12);  // Gecikme
        $sheet->getColumnDimension('H')->setWidth(12);  // Bakiye
        $sheet->getColumnDimension('I')->setWidth(40);  // Açıklama
        
        $currentRow = 1;
        
        $finans = $FinansalRaporModel->kisiFinansalDurum($kisi->id);
        $adiSoyadi = $kisi->adi_soyadi ?? '';
        $daireKodu = $kisi->daire_kodu ?? '';
        $oturum = trim(($kisi->uyelik_tipi ?? ''));
        $telefon = $kisi->telefon ?? '';

        // Başlık
        $sheet->mergeCells('A' . $currentRow . ':I' . ($currentRow + 1));
        $sheet->setCellValue('A' . $currentRow, 'Kişi Hesap Özeti');
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
    $sheet->getStyle('A' . $currentRow . ':I' . ($currentRow + 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $currentRow += 2;

        // Bilgi kutusu başlangıcı
        $boxStart = $currentRow;
        
        $sheet->mergeCells('A' . $currentRow . ':B' . $currentRow);
        $sheet->setCellValue('A' . $currentRow, 'Daire No :');
        $sheet->mergeCells('C' . $currentRow . ':D' . $currentRow);
        $sheet->setCellValue('C' . $currentRow, $daireKodu);
        $sheet->mergeCells('E' . $currentRow . ':G' . $currentRow);
        $sheet->setCellValue('E' . $currentRow, 'Daire Kodu :');
        $sheet->mergeCells('H' . $currentRow . ':I' . $currentRow);
        $sheet->setCellValue('H' . $currentRow, $daireKodu);
        $currentRow++;

        $sheet->mergeCells('A' . $currentRow . ':B' . $currentRow);
        $sheet->setCellValue('A' . $currentRow, 'Kişi :');
        $sheet->mergeCells('C' . $currentRow . ':D' . $currentRow);
        $sheet->setCellValue('C' . $currentRow, $adiSoyadi);
        $sheet->mergeCells('E' . $currentRow . ':G' . $currentRow);
        $sheet->setCellValue('E' . $currentRow, 'Oturum Şekli :');
        $sheet->mergeCells('H' . $currentRow . ':I' . $currentRow);
        $sheet->setCellValue('H' . $currentRow, $oturum ?: '-');
        $currentRow++;

        $sheet->mergeCells('A' . $currentRow . ':B' . $currentRow);
        $sheet->setCellValue('A' . $currentRow, 'Cep Tel :');
        $sheet->mergeCells('C' . $currentRow . ':D' . $currentRow);
        $sheet->setCellValue('C' . $currentRow, $telefon ?: '-');
        $sheet->mergeCells('E' . $currentRow . ':G' . $currentRow);
        $sheet->setCellValue('E' . $currentRow, 'Bakiye :');
        $sheet->mergeCells('H' . $currentRow . ':I' . $currentRow);
    $sheet->setCellValue('H' . $currentRow, (float)($finans->bakiye ?? 0));

        // Kutu kenarlık
        $sheet->getStyle('A' . $boxStart . ':I' . $currentRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        // Alt çizgiyi kaldır
       // $sheet->getStyle('A' . $currentRow . ':I' . $currentRow)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_NONE);
        $currentRow++;
        $currentRow++;
        //satıra görünmeyen içerik ekle
        $sheet->setCellValue('A' . $currentRow, ' ');
        // Detay başlıkları
        $sheet->setCellValue('A' . $currentRow, 'Ö.T.');
        $sheet->setCellValue('B' . $currentRow, 'Tarih');
        $sheet->setCellValue('C' . $currentRow, 'Son Ödeme Tarihi');
        $sheet->setCellValue('D' . $currentRow, 'Ödenmesi Gereken');
        $sheet->setCellValue('E' . $currentRow, 'Ödenen');
        $sheet->setCellValue('F' . $currentRow, 'Gecikme Oran');
        $sheet->setCellValue('G' . $currentRow, 'Gecikme');
        $sheet->setCellValue('H' . $currentRow, 'Bakiye');
        $sheet->setCellValue('I' . $currentRow, 'Açıklama');
        $sheet->getStyle('A' . $currentRow . ':I' . $currentRow)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DEE2E6']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        
        // Başlık satırlarını her sayfada tekrarla (1'den currentRow'a kadar)
        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, $currentRow);
        $currentRow++;
        
       
        

        // Hareketler
        $hareketler = $FinansalRaporModel->kisiHesapHareketleri($kisi->id);
        $sumAnapara = 0;
        $sumOdenen = 0;
        $sumGecikme = 0;
        $lastBakiye = 0;
        
        foreach ($hareketler as $h) {
            $aciklama = trim(($h->aciklama ?? '') !== '' ? $h->aciklama : ($h->borc_adi ?? $h->islem_tipi ?? '-'));
            $otKodu = $h->ot_kodu ?? ($h->borc_adi ?? '');
            $tarih = $h->islem_tarihi ? date('d.m.Y', strtotime($h->islem_tarihi)) : '';
            $sonOdeme = $h->bitis_tarihi ?? null;
            $sonOdemeFmt = $sonOdeme ? date('d.m.Y', strtotime($sonOdeme)) : '';
            $anapara = (float)($h->anapara ?? 0);
            $sumAnapara += $anapara;
            $odenen  = (float)($h->odenen ?? 0);
            $sumOdenen += $odenen;
            $gecikme = (float)($h->gecikme_zammi ?? 0);
            $sumGecikme += $gecikme;
            $bakiye  = (float)($h->yuruyen_bakiye ?? 0);
            $lastBakiye = $bakiye;

            $sheet->setCellValue('A' . $currentRow, $otKodu);
            $sheet->setCellValue('B' . $currentRow, $tarih);
            $sheet->setCellValue('C' . $currentRow, $sonOdemeFmt ?: '-');
            // Numeric yazım
            $sheet->setCellValue('D' . $currentRow, $anapara);
            $sheet->setCellValue('E' . $currentRow, $odenen);
            $sheet->setCellValue('F' . $currentRow, $h->gecikme_oran ?? '');
            $sheet->setCellValue('G' . $currentRow, $gecikme);
            $sheet->setCellValue('H' . $currentRow, $bakiye);
            $sheet->setCellValue('I' . $currentRow, $aciklama);
            
            // Gecikme kırmızı
            if ($gecikme > 0) {
                $sheet->getStyle('G' . $currentRow)->getFont()->getColor()->setRGB('D90429');
            }
            $currentRow++;
        }

        // Toplam satırı
        $sheet->mergeCells('A' . $currentRow . ':C' . $currentRow);
        $sheet->setCellValue('A' . $currentRow, 'Toplam');
        $sheet->setCellValue('D' . $currentRow, $sumAnapara);
        $sheet->setCellValue('E' . $currentRow, $sumOdenen);
        $sheet->setCellValue('G' . $currentRow, $sumGecikme);
        $sheet->setCellValue('H' . $currentRow, $lastBakiye);
        $sheet->getStyle('A' . $currentRow . ':I' . $currentRow)->applyFromArray([
            'font' => ['bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E9ECEF']]
        ]);

        // Bu sayfa (worksheet) için toplu sayı formatı ve hizalama
        $detailStartRow = $sheet->getPageSetup()->getRowsToRepeatAtTop() ? $sheet->getPageSetup()->getRowsToRepeatAtTop()[1] : 1; // not used further, manual compute
        // Detay başlık satırı, yukarıda set edildiği anda currentRow-? idi. Basitçe: Detay başlığı sonrası ilk veri satırı = (başlık satırı indexi + 1)
        // Başlık satırını bulmak için geriye bakmak yerine, bu blokta sabit: bilgi kutusundan sonra 2 satır boşluk ve bir başlık var.
        // Güvenli yol: Detay başlık satırını, bu sayfa içinde 'Ö.T.' yazan hücreyi aramadan tahmin etmek yerine; aralığı geniş tutup tüm tabloyu biçimlendirelim.
        $firstDataRow = 1; // geniş format uygulayacağız
        $lastDataRow  = $currentRow; // toplam dahil
        // Sayı formatları (D,E,G,H)
        $sheet->getStyle('D' . $firstDataRow . ':D' . $lastDataRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('E' . $firstDataRow . ':E' . $lastDataRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('G' . $firstDataRow . ':G' . $lastDataRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('H' . $firstDataRow . ':H' . $lastDataRow)->getNumberFormat()->setFormatCode('#,##0.00');
        // Hizalama sağ
        $sheet->getStyle('D' . $firstDataRow . ':H' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        // İnce ızgara kenarlıkları (tüm tablo alanı)
        $sheet->getStyle('A1:I' . $lastDataRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }
}
// Export
$filename = 'blok_kisi_hesap_ozetleri_' . date('Y-m-d_H-i-s');
try {
    switch ($format) {
        case 'xlsx':
        case 'excel':
            if (ob_get_length()) {
                @ob_end_clean();
            }
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');
            $writer = new Xlsx($spreadsheet);
            if (method_exists($writer, 'setPreCalculateFormulas')) {
                $writer->setPreCalculateFormulas(false);
            }
            if (method_exists($writer, 'setUseDiskCaching')) {
                $writer->setUseDiskCaching(true, sys_get_temp_dir());
            }
            $writer->save('php://output');
            break;
        case 'csv':
            if (ob_get_length()) {
                @ob_end_clean();
            }
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '.csv"');
            header('Cache-Control: max-age=0');
            $writer = new Csv($spreadsheet);
            $writer->setDelimiter(';');
            $writer->setEnclosure('"');
            $writer->setLineEnding("\r\n");
            $writer->setSheetIndex(0);
            if (method_exists($writer, 'setUseDiskCaching')) {
                $writer->setUseDiskCaching(true, sys_get_temp_dir());
            }
            $writer->save('php://output');
            break;
        case 'html':
            if (ob_get_length()) {
                @ob_end_clean();
            }

            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '.html"');
            header('Cache-Control: max-age=0');

            // Çoklu sheet'i tek HTML olarak birleştir
            $html = '<!DOCTYPE html><html lang="tr"><head><meta charset="utf-8"><title>' . htmlspecialchars($filename) . '</title></head><body>';
            $sheetCount = $spreadsheet->getSheetCount();
            for ($i = 0; $i < $sheetCount; $i++) {
                $htmlWriter = new Html($spreadsheet);
                $htmlWriter->setSheetIndex($i);
                $htmlWriter->setGenerateSheetNavigationBlock(false);
                ob_start();
                $htmlWriter->save('php://output');
                $sheetHtml = ob_get_clean();
                // Her sayfayı ayrı bölüm olarak ekle
                $html .= '<section style="page-break-after:always">' . $sheetHtml . '</section>';
            }
            $html .= '</body></html>';
            echo $html;
            break;


        case 'pdf':
            // Kaynak sınırlarını artır ve buffer temizle
            @ini_set('memory_limit', '512M');
            //@set_time_limit(120);
            if (ob_get_length()) {
                @ob_end_clean();
            }

            // Sayfa ayarları
            $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
            $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
            $sheet->getPageSetup()->setFitToWidth(1);
            $sheet->getPageSetup()->setFitToHeight(0);

            $sheetCount = $spreadsheet->getSheetCount();

            if ($sheetCount > 1) {
                // Çoklu worksheet'i tek PDF'te birleştir
                $mpdf = new \Mpdf\Mpdf([
                    'mode' => 'utf-8', 
                    'format' => 'A4-L', 
                    'tempDir' => sys_get_temp_dir(),
                    'margin_header' => 0,
                    'margin_footer' => 0
                ]);
                
                // CSS'i sadece bir kere yaz
                $css = '<style>
                    @page { 
                        margin: 10mm;
                    }
                    body { 
                        font-family: DejaVu Sans, sans-serif; 
                        font-size: 5pt;
                        margin: 0;
                        padding: 0;
                    }
                    table { 
                        width: 100%; 
                        border-collapse: collapse;
                    }
                    td, th { 
                        border: 1px solid #000;
                        padding: 8px;
                        vertical-align: top;
                    }
                </style>';
                $mpdf->WriteHTML($css);
                
                for ($i = 0; $i < $sheetCount; $i++) {
                                        
                    $currentSheet = $spreadsheet->getSheet($i);
                    $htmlWriter = new Html($spreadsheet);
                    $htmlWriter->setSheetIndex($i);
                    $htmlWriter->setGenerateSheetNavigationBlock(false);
                    
                    ob_start();
                    $htmlWriter->save('php://output');
                    $sheetHtml = ob_get_clean();
                    
                    // Sadece HTML icerigini yaz
                    $mpdf->WriteHTML($sheetHtml);
                }
                

                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                header('Cache-Control: max-age=0');
                $mpdf->Output($filename . '.pdf', 'I');
            } else {
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                header('Cache-Control: max-age=0');
                // Tek sheet için mPDF backend kullan
                IOFactory::registerWriter('Pdf', \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf::class);
                $writer = IOFactory::createWriter($spreadsheet, 'Pdf');
                $writer->save('php://output');
            }
            break;
        default:
            throw new \Exception('Geçersiz format: ' . $format);
    }
    exit();
} catch (\Exception $e) {
    die('Export hatası: ' . $e->getMessage());
}
