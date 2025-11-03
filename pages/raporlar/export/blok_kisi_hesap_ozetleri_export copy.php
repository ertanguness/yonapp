<?php

require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use App\Helper\Helper;
use App\Services\Gate;
use Model\KisilerModel;
use Model\FinansalRaporModel;
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

// Parametreler: blok_id veya tumu

$format = $_GET['format'] ?? 'xlsx';
$blok_id = $_GET['blok_id'] ?? 'tumu';
$site_id = $_GET['site_id'] ?? ($_SESSION['site_id'] ?? 0);

$KisiModel = new KisilerModel();
$FinansalRaporModel = new FinansalRaporModel();



// Kişileri getir
if ($blok_id == 0) {
    $kisiler = $KisiModel->getAktifKisilerBySite($site_id); // Tüm site
} else {
    $kisiler = $KisiModel->getAktifKisilerByBlok($blok_id); // Belirli blok
}



//Helper::dd($kisiler);
if (empty($kisiler)) {
    die('Dışarı aktarılacak kişi bulunamadı.');
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$spreadsheet->getDefaultStyle()->getFont()->setName('DejaVu Sans');
$spreadsheet->getDefaultStyle()->getFont()->setSize(sizeInPoints: 8);

$isPdf = strtolower($format) === 'pdf';


    // Liste formatı (Excel/CSV/HTML)
    $headers = [
        'A1' => 'Blok',
        'B1' => 'Daire Kodu',
        'C1' => 'Adı Soyadı',
        'D1' => 'Toplam Borç',
        'E1' => 'Toplam Tahsilat',
        'F1' => 'Bakiye',
        'G1' => 'Oturum Şekli',
    ];
    foreach ($headers as $cell => $value) {
        $sheet->setCellValue($cell, $value);
    }

    $detailHeaders = [
        'A' => 'İşlem Tarihi',
        'B' => 'İşlem Tipi',
        'C' => 'Borç',
        'D' => 'Gecikme Zammı',
        'E' => 'Ödenen',
        'F' => 'Bakiye',
        'G' => 'Açıklama',
    ];

    $row = 2;
    foreach ($kisiler as $kisi) {
        $finans = $FinansalRaporModel->kisiFinansalDurum($kisi->id);
        // Kişi özet satırı
        $sheet->setCellValue('A' . $row, $kisi->blok_adi ?? '');
        $sheet->setCellValue('B' . $row, $kisi->daire_kodu ?? '');
        $sheet->setCellValue('C' . $row, $kisi->adi_soyadi ?? '');
        $sheet->setCellValue('D' . $row, Helper::formattedMoney((float)($finans->toplam_borc ?? 0)));
        $sheet->setCellValue('E' . $row, Helper::formattedMoney((float)($finans->toplam_tahsilat ?? 0)));
        $sheet->setCellValue('F' . $row, Helper::formattedMoney((float)($finans->bakiye ?? 0)));
        $sheet->setCellValue('G' . $row, $kisi->uyelik_tipi ?? '');
        $sheet->getStyle('A' . $row . ':G' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E9ECEF']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        $row++;

        foreach ($detailHeaders as $col => $val) {
            $sheet->setCellValue($col . $row, $val);
        }
        $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'DEE2E6']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        $row++;

        $hareketler = $FinansalRaporModel->kisiHesapHareketleri($kisi->id);
        foreach ($hareketler as $h) {
            $sheet->setCellValue('A' . $row, ($h->islem_tarihi));
            $sheet->setCellValue('B' . $row, ucfirst($h->borc_adi ?? $h->islem_tipi));
            $anapara = (float)($h->anapara ?? 0);
            $gecikme = (float)($h->gecikme_zammi ?? 0);
            $odenen  = (float)($h->odenen ?? 0);
            $bakiye  = (float)($h->yuruyen_bakiye ?? 0);
            $sheet->setCellValue('C' . $row, Helper::formattedMoney($anapara));
            $sheet->setCellValue('D' . $row, Helper::formattedMoney($gecikme));
            $sheet->setCellValue('E' . $row, Helper::formattedMoney($odenen));
            $sheet->setCellValue('F' . $row, Helper::formattedMoney($bakiye));
            $sheet->setCellValue('G' . $row, $h->aciklama ?? '-');
            $sheet->getStyle('C' . $row . ':F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ]);
            if ($gecikme > 0) {
                $sheet->getStyle('D' . $row)->getFont()->getColor()->setRGB('D90429');
            }
            $row++;
        }

        $row++;
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
            $writer->save('php://output');
            break;
        case 'pdf':
            // Kaynak sınırlarını artır ve buffer temizle
            @ini_set('memory_limit', '512M');
            @set_time_limit(120);
            if (ob_get_length()) {
                @ob_end_clean();
            }

            // Sayfa ayarları
            $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
            $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
            $sheet->getPageSetup()->setFitToWidth(1);
            $sheet->getPageSetup()->setFitToHeight(0);

                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                header('Cache-Control: max-age=0');
                // Tek sheet için mPDF backend kullan
                IOFactory::registerWriter('Pdf', \PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf::class);
                $writer = IOFactory::createWriter($spreadsheet, 'Pdf');
                $writer->save('php://output');
          //  }
            break;
        default:
            throw new Exception('Geçersiz format: ' . $format);
    }
    exit();
} catch (Exception $e) {
    die('Export hatası: ' . $e->getMessage());
}
