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
// --- Verileri liste görünümünde hazırlama (resimdeki gibi satır satır) ---
$gelirler_list = $gelirler_raw;  // Objeler doğrudan kullanılacak
$giderler_list = $giderler_raw;
$toplam_gelir = 0;
foreach ($gelirler_list as $v) { $toplam_gelir += floatval($v->tutar ?? 0); }
$toplam_gider = 0;
foreach ($giderler_list as $v) { $toplam_gider += floatval($v->tutar ?? 0); }


// --- Spreadsheet Oluşturma ---
$ss = new Spreadsheet();
$sheet = $ss->getActiveSheet();
$ss->getDefaultStyle()->getFont()->setName('Arial');
$ss->getDefaultStyle()->getFont()->setSize(10);
$sheet->setTitle('Gelir Gider Raporu');

// Kolon Genişlikleri (Sol: Giderler | Sağ: Gelirler)
$sheet->getColumnDimension('A')->setWidth(6);   // Sıra
$sheet->getColumnDimension('B')->setWidth(12);  // Tarih
$sheet->getColumnDimension('C')->setWidth(12);  // Evrak No
$sheet->getColumnDimension('D')->setWidth(28);  // Cari Hesap Adı
$sheet->getColumnDimension('E')->setWidth(14);  // Tutar
$sheet->getColumnDimension('F')->setWidth(2);   // Ayırıcı
$sheet->getColumnDimension('G')->setWidth(6);   // Sıra
$sheet->getColumnDimension('H')->setWidth(12);  // Tarih
$sheet->getColumnDimension('I')->setWidth(12);  // Fiş No
$sheet->getColumnDimension('J')->setWidth(12);  // No
$sheet->getColumnDimension('K')->setWidth(36);  // Açıklama
$sheet->getColumnDimension('L')->setWidth(14);  // Tutar

// Kenarlık stili yardımcı dizi
$thinBorder = [
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
    ]
];

// --- Başlıklar ---
// Sol üstte site adı
$sheet->mergeCells('A1:I1');
$sheet->setCellValue('A1', mb_strtoupper($site->site_adi ?? 'SİTE ADI', 'UTF-8'));
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Sağ üstte tarih-saat kutusu
$sheet->mergeCells('J1:L1');
$sheet->mergeCells('J2:L2');
$sheet->setCellValue('J1', 'Tarih-Saat');
$sheet->setCellValue('J2', date('d F Y H:i'));
$sheet->getStyle('J1:L2')->applyFromArray($thinBorder);
$sheet->getStyle('J1:L1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('J2:L2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Alt başlık (rapor dönemi)
$sheet->mergeCells('A2:I2');
$sheet->setCellValue('A2', 'Site Gelir-Gider Raporu [' . date('d.m.Y', strtotime($start)) . ']-[' . date('d.m.Y', strtotime($end)) . ']');
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Bölüm başlıkları
$sheet->mergeCells('A4:E4');
$sheet->setCellValue('A4', 'GİDERLER');
$sheet->getStyle('A4')->getFont()->setBold(true);
$sheet->mergeCells('G4:L4');
$sheet->setCellValue('G4', 'GELİRLER');
$sheet->getStyle('G4')->getFont()->setBold(true);

// Sütun başlıkları
$sheet->fromArray(['Sıra No', 'Tarih', 'Evrak No', 'Cari Hesap Adı', 'Tutar'], null, 'A5');
$sheet->fromArray(['Sıra No', 'Tarih', 'Fiş No', 'No', 'Açıklama', 'Tutar'], null, 'G5');
$sheet->getStyle('A5:E5')->getFont()->setBold(true);
$sheet->getStyle('G5:L5')->getFont()->setBold(true);
$sheet->getStyle('A5:E5')->applyFromArray($thinBorder);
$sheet->getStyle('G5:L5')->applyFromArray($thinBorder);

// --- Veri Satırları ---
$rowStart = 6;
$max = max(count($giderler_list), count($gelirler_list));
$gi = 0; $ge = 0; // sıra numaraları
$r = $rowStart;
for ($i = 0; $i < $max; $i++) {
    // Giderler sol tarafta
    if (isset($giderler_list[$i])) {
        $g = $giderler_list[$i];
        $sheet->setCellValueExplicit('A'.$r, ++$gi, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $sheet->setCellValue('B'.$r, date('d.m.y', strtotime($g->islem_tarihi ?? 'now')));
        $sheet->setCellValue('C'.$r, (string)($g->makbuz_no ?? ''));
        $sheet->setCellValue('D'.$r, (string)($g->adi_soyadi ?? ''));
        $sheet->setCellValue('E'.$r, number_format(floatval($g->tutar ?? 0), 2, ',', '.'));
        $sheet->getStyle('E'.$r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        // Açıklama satırı (küçük puntolu)
        if (!empty($g->aciklama)) {
            $r++; // alt satıra in
            $sheet->mergeCells('A'.$r.':E'.$r);
            $sheet->setCellValue('A'.$r, (string)$g->aciklama);
            $sheet->getStyle('A'.$r)->getFont()->setSize(9)->setItalic(true);
        }
        // Kenarlıklar
        $sheet->getStyle('A'.($r - (empty($g->aciklama)?0:1)).':E'.$r)->applyFromArray($thinBorder);
    }

    // Gelirler sağ tarafta
    if (isset($gelirler_list[$i])) {
        $elRow = $rowStart + ($r - $rowStart); // eş satır
        $g2 = $gelirler_list[$i];
        $sheet->setCellValueExplicit('G'.$elRow, ++$ge, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $sheet->setCellValue('H'.$elRow, date('d.m.y', strtotime($g2->islem_tarihi ?? 'now')));
        $sheet->setCellValue('I'.$elRow, (string)($g2->makbuz_no ?? ''));
        $sheet->setCellValue('J'.$elRow, (string)($g2->daire_kodu ?? ''));
        // Açıklama olarak öncelik: adi_soyadi + (aciklama varsa ekle)
        $acik = trim(((string)($g2->adi_soyadi ?? '')) . (isset($g2->aciklama) && $g2->aciklama ? (' / '.$g2->aciklama) : ''));
        $sheet->setCellValue('K'.$elRow, $acik);
        $sheet->setCellValue('L'.$elRow, number_format(floatval($g2->tutar ?? 0), 2, ',', '.'));
        $sheet->getStyle('L'.$elRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        // Kenarlıklar
        $sheet->getStyle('G'.$elRow.':L'.$elRow)->applyFromArray($thinBorder);
    }

    // Sonraki satıra geç (en azından 1 art)
    $r++;
}

// --- Toplam Satırları ---
$totalRow = $r + 1;
// Gider toplamı sol
$sheet->mergeCells('A'.$totalRow.':D'.$totalRow);
$sheet->setCellValue('A'.$totalRow, 'Giderler Toplamı');
$sheet->setCellValue('E'.$totalRow, number_format($toplam_gider, 2, ',', '.'));
$sheet->getStyle('A'.$totalRow.':E'.$totalRow)->getFont()->setBold(true);
$sheet->getStyle('A'.$totalRow.':E'.$totalRow)->applyFromArray($thinBorder);
$sheet->getStyle('E'.$totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

// Gelir toplamı sağ
$sheet->mergeCells('G'.$totalRow.':K'.$totalRow);
$sheet->setCellValue('G'.$totalRow, 'Gelirler Toplamı');
$sheet->setCellValue('L'.$totalRow, number_format($toplam_gelir, 2, ',', '.'));
$sheet->getStyle('G'.$totalRow.':L'.$totalRow)->getFont()->setBold(true);
$sheet->getStyle('G'.$totalRow.':L'.$totalRow)->applyFromArray($thinBorder);
$sheet->getStyle('L'.$totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);


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