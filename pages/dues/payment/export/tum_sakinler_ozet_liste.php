<?php


require_once dirname(__DIR__, 4) . '/configs/bootstrap.php';

use App\Helper\Helper;
use App\Services\Gate;
use Model\KasaHareketModel;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf; // Dompdf'i kullanmak için
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Model\FinansalRaporModel;
use Model\SitelerModel;
use Dompdf\Options;
use Dompdf\Dompdf as PDF;
use Model\KisilerModel;



//Gate::can('kisi_hesap_ozet_export');


$FinansalRaporModel = new FinansalRaporModel();
$SiteModel = new SitelerModel();

$site_id = $_SESSION['site_id'] ?? 0;
$ozet_liste = $FinansalRaporModel->getGuncelBorclarGruplu($site_id);


$site = $SiteModel->find($site_id);

//Site yoksa hata fırlat
if(!$site){
    die('Site bilgisi bulunamadı.');
}


$format = $_GET['format'] ?? 'pdf'; // Varsayılan format pdf

//echo '<pre>'; print_r($ozet_liste); echo '</pre>'; exit;

if (empty($ozet_liste)) {
    die('Dışarı aktarılacak veri bulunamadı.');
}
//echo '<pre>'; print_r($kisiHareketler); echo '</pre>'; exit;

// Spreadsheet oluştur
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Tüm spreadsheet için varsayılan fontu ayarla
$spreadsheet->getDefaultStyle()->getFont()->setName('DejaVu Sans');
$spreadsheet->getDefaultStyle()->getFont()->setSize(9);



// Başlık satırı
$headers = [
    'A4' => 'Sıra',
    'B4' => 'Daire Kodu',
    'C4' => 'Adı Soyadı',
    'D4' => 'Üyelik Tipi',
    'E4' => 'Durum',
    'F4' => 'Borç Tutarı',
    'G4' => 'Gecikme Zammı',
    'H4' => 'Toplam Borç',
    'I4' => 'Kredi Tutarı',
    'J4' => 'Kalan Borç',
];

// Başlıkları set et
foreach ($headers as $cell => $value) {
    $sheet->setCellValue($cell, $value);
}

/* BAŞLIK AYARLARI */
//a1'den G3'e kadar birleştir
$spreadsheet->getActiveSheet()->setTitle('Site Hesap Hareketleri');
$spreadsheet->setActiveSheetIndex(0);
$sheet->mergeCells('A1:J1');
$sheet->setCellValue('A1', 'Site Hesap Hareketleri');

//Font Size ve Bold yap
//$sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);

//Ortala
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

//Kenarlık ekle
$sheet->getStyle('A1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

/**A4 Hücresine Daire No: ekle, Sağa yasla */
$sheet->mergeCells('A2:B2');
$sheet->setCellValue('A2', 'Site Adı:');
// $sheet->getStyle('A4')->getFont()->setBold(true);
$sheet->getStyle('A2:B2')->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
    ->setVertical(Alignment::VERTICAL_CENTER);

//C4
$sheet->mergeCells('C2:J2');
$sheet->setCellValue('C2', $site->site_adi ?? '');


// $sheet->mergeCells('G4:J4');
// $sheet->setCellValue('G4', 'Toplam Borç:  ');
// $sheet->setCellValue('K4', Helper::formattedMoney($kisiFinansalDurum->toplam_borc ?? '0,00'));
// //Bold yap

// $sheet->getStyle('I4:J4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
// $sheet->getStyle('K4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

// //Daire kodu
// $sheet->getStyle('C5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
// $sheet->mergeCells('A5:B5');
// $sheet->setCellValue('A5', 'Daire Kodu:');
// $sheet->mergeCells('C5:F5');
// $sheet->setCellValue('C5', $kisi->daire_kodu ?? '');


// $sheet->mergeCells('G5:J5');
// $sheet->setCellValue('G5', 'Toplam Ödenen:  ');
// $sheet->setCellValue('K5', Helper::formattedMoney($kisiFinansalDurum->toplam_tahsilat ?? '0,00'));
// $sheet->getStyle('G5:J5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
// $sheet->getStyle('K5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);




// $sheet->mergeCells('A6:B6');
// $sheet->mergeCells('C6:F6');

// $sheet->setCellValue('A6', 'Oturum Şekli:');
// $sheet->setCellValue('C6', $kisi->uyelik_tipi ?? '');

// $sheet->mergeCells('G6:J6');
// $sheet->setCellValue('G6', 'Bakiye:  ');
// $sheet->setCellValue('K6', Helper::formattedMoney($kisiFinansalDurum->bakiye ?? '0,00'));

// $sheet->getStyle('I6:J6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
// $sheet->getStyle('K6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
// //$sheet->getStyle('K6')->getFont()->setBold(true);

 $sheet->mergeCells('A3:B3');
 $sheet->mergeCells('C3:J3');

$sheet->setCellValue('A3', 'Rapor Tarihi:');
$sheet->setCellValue('C3', date('d.m.Y H:i'));



// Son başlık hücresini bul
$lastHeaderColumn = 'J'; // 'A1'den 'G1'e kadar başlıklarınız var

// Başlık satırını formatla
$sheet->getStyle('A1:J3')->applyFromArray([
    'font' => [
        'bold' => false,
        'color' => ['rgb' => '000000'],
        'size' => 8,
    ],
      'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'indent' => 1,
                    'wrapText' => true
                ]
]);


// Başlık satırını formatla
$sheet->getStyle('A4:' . $lastHeaderColumn . '4')->applyFromArray([
    'font' => [
        'bold' => false,
        'color' => ['rgb' => '000000'],
        'size' => 9,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '9CAFAA']
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

//site_id;kisi_id;adi_soyadi;daire_kodu;islem_id;islem_tarihi;islem_tipi;aciklama;anapara;gecikme_zammi;odenen
//  [kisi_id] => 1
//             [daire_kodu] => A1D1
//             [adi_soyadi] => MUHTEREM BOZKURT
//             [uyelik_tipi] => Ev Sahibi
//             [kredi_tutari] => 206.89
//             [kalan_anapara] => 2860.00
//             [hesaplanan_gecikme_zammi] => 0.00
//             [toplam_kalan_borc] => 2860.00
//             [cikis_tarihi] => 0000-00-00
//             [durum] => Aktif

// Veri satırlarını doldur
$row =5;
foreach ($ozet_liste as $kisi) {
    $kalan_anapara = (float)$kisi->toplam_kalan_borc - (float)$kisi->kredi_tutari;

    $sheet->setCellValue('A' . $row, $row-4); // 'islem_id' daha mantıklı olabilir
    $sheet->setCellValue('B' . $row, $kisi->daire_kodu); // 'islem_id' daha mantıklı olabilir
    $sheet->setCellValue('C' . $row, $kisi->adi_soyadi);
    $sheet->setCellValue('D' . $row, ucfirst($kisi->uyelik_tipi));
    $sheet->setCellValue('E' . $row, ucfirst($kisi->durum));
    $sheet->setCellValue('F' . $row, number_format(-(float)$kisi->kalan_anapara, 2, ',', '.')); // Float'a çevirme
    $sheet->setCellValue('G' . $row, number_format(-(float)$kisi->hesaplanan_gecikme_zammi, 2, ',', '.')); // Float'a çevirme
    $sheet->setCellValue('H' . $row, number_format(-(float)$kisi->toplam_kalan_borc, 2, ',', '.')); // Float'a çevirme
    $sheet->setCellValue('I' . $row, number_format((float)$kisi->kredi_tutari, 2, ',', '.') ?: '-');
    $sheet->setCellValue('J' . $row, number_format(-(float)$kalan_anapara, 2, ',', '.') ?: '-');

 


    // Satır rengini değiştir (çift satırlar açık gri)
    if ($row % 2 == 0) {
        $sheet->getStyle('A' . $row . ':' . $lastHeaderColumn . $row)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F8F9FA']
            ],
            'font' => [
                'size' => 9,
            ]
        ]);
    } else {
        // Tek satırlar için de font ayarla
        $sheet->getStyle('A' . $row . ':' . $lastHeaderColumn . $row)->applyFromArray([
            'font' => [
                'size' => 9,
            ]
        ]);
    }

    $row++;
}

// // Sütun genişliklerini ayarla (sadece kullanılan sütunlar için)
 $sheet->getColumnDimension('A')->setWidth(10); // ID için artırıldı
$sheet->getColumnDimension('B')->setWidth(15); // Daire Kodu
$sheet->getColumnDimension('C')->setWidth(35); // Adı Soyadı
$sheet->getColumnDimension('D')->setWidth(15); // Anapara
$sheet->getColumnDimension('E')->setWidth(10); // Durum
$sheet->getColumnDimension('F')->setWidth(15); // Ödenen
$sheet->getColumnDimension('G')->setWidth(20); // Gecikme Zammı
$sheet->getColumnDimension('H')->setWidth(width: 15); // Açıklama
$sheet->getColumnDimension('I')->setWidth(width: 15); // Açıklama
$sheet->getColumnDimension('J')->setWidth(width: 15); // Açıklama

//D'den G'ye kadar olan sütunları sağa hizala
$sheet->getStyle('D5:J' . ($row - 1))
    ->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

// Açıklama ve genel hücreler için satır kaydır
$sheet->getStyle('H5:H' . ($row - 1))
    ->getAlignment()
    ->setWrapText(true);


//H sütununa indent ekle
$sheet->getStyle('H5:H' . ($row - 1))
    ->getAlignment()
    ->setIndent(1);

$printLastRow = $row - 1;


// Tüm verilere border ekle (son kullanılan sütuna göre)
$sheet->getStyle('A7:' . $lastHeaderColumn . ($row - 1))->applyFromArray([ // Burası düzeltildi
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '222222']
        ],

    ]
]);


// Print ayarları - Başlık satırını her sayfada yinele
//$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(start: 1, end: 9);


$spreadsheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
$spreadsheet->getActiveSheet()->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);



// Dosya adı
$filename = $site->site_adi . ' hesap_ozeti_' . date('Y-m-d_H-i-s');

// Format'a göre export yap
try {
    switch ($format) {
        case 'xlsx':
        case 'excel':
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');


            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            break;

        case 'csv':
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

        case 'html':
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '.html"');
            header('Cache-Control: max-age=0');

            //Fontu ayarla
            // $spreadsheet->getActiveSheet()->getStyle('A1:G100')->getFont()->setBold(true);
            $spreadsheet->getActiveSheet()->getStyle('A1:J100')->getFont()->setName('DejaVu Sans');
            $spreadsheet->getActiveSheet()->getStyle('A1:J100')->getFont()->setSize(10);



            $writer = new Html($spreadsheet);
            $writer->setSheetIndex(0);
            $writer->save('php://output');
            break;

        case 'pdf':
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
            header('Cache-Control: max-age=0');


            $dataLastRow = $row - 1;

            // PDF writer'ı ayarla
            IOFactory::registerWriter('Pdf', Dompdf::class);

            //Sayfayı yatay yap
            $spreadsheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
            $spreadsheet->getActiveSheet()->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        
             // 1. Başlık tekrarını ayarla (bu satır bold sorununu tetikliyor)
            $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 4);

            // 2. Tekrarlanan tüm başlık alanını (A1:K9) ÖNCE normale (bold=false) zorla.
            $sheet->getStyle('A1:J4')->getFont()->setBold(false);



            $sheet->getStyle('A4:J' . ($row - 1))->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'indent' => 1,
                    'wrapText' => true
                ]
            ]);
            $sheet->getStyle('A4:J4')->applyFromArray([
                'font' => [
                    'bold' => false,

                ]
            ]);

            //$sheet->getStyle('K4:K6')->getFont()->setBold(true);

            $sheet->getStyle('A4:' . $lastHeaderColumn . '4')->applyFromArray([
                'font' => [
                    'regular' => true,
                    'color' => ['rgb' => '000000'],
                    'size' => 8,
                ]
            ]);


            //D'den G'ye kadar olan sütunları sağa hizala
            $sheet->getStyle('F5:J' . ($row - 1))
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $sheet->getStyle('A5:J' . ($row - 1))
                ->applyFromArray([
                   'font' => [
                       'size' => 8,
                       'name' => 'DejaVu Sans',
                   ]
                ]);

            $writer = IOFactory::createWriter($spreadsheet, 'Pdf');
            $writer->save('php://output');
            break;

        case "xml":
            header('Content-Type: application/xml; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '.xml"');
            header('Cache-Control: max-age=0');

            // Manuel XML oluştur
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<KisiHesapHareketleri>' . "\n"; // Kök etiketi düzeltildi
            $xml .= '  <Rapor>' . "\n";
            $xml .= '    <Tarih>' . date('Y-m-d H:i:s') . '</Tarih>' . "\n";
            $xml .= '    <KisiId>' . htmlspecialchars($kisi_id, ENT_XML1, 'UTF-8') . '</KisiId>' . "\n"; // KasaId yerine KisiId
            $xml .= '    <AdiSoyadi>' . htmlspecialchars($kisiHareketler[0]->adi_soyadi ?? '', ENT_XML1, 'UTF-8') . '</AdiSoyadi>' . "\n"; // İlk hareketten ad-soyad al
            $xml .= '    <DaireKodu>' . htmlspecialchars($kisiHareketler[0]->daire_kodu ?? '', ENT_XML1, 'UTF-8') . '</DaireKodu>' . "\n"; // İlk hareketten daire kodu al
            $xml .= '  </Rapor>' . "\n";
            $xml .= '  <Hareketler>' . "\n";

            foreach ($kisiHareketler as $hareket) {
                $xml .= '    <Hareket>' . "\n";
                $xml .= '      <ID>' . htmlspecialchars($hareket->islem_id, ENT_XML1, 'UTF-8') . '</ID>' . "\n"; // 'id' yerine 'islem_id'
                $xml .= '      <IslemTarihi>' . htmlspecialchars(date('Y-m-d H:i:s', strtotime($hareket->islem_tarihi)), ENT_XML1, 'UTF-8') . '</IslemTarihi>' . "\n";
                $xml .= '      <IslemTipi>' . htmlspecialchars(ucfirst($hareket->islem_tipi), ENT_XML1, 'UTF-8') . '</IslemTipi>' . "\n";
                $xml .= '      <Anapara>' . htmlspecialchars($hareket->anapara, ENT_XML1, 'UTF-8') . '</Anapara>' . "\n"; // Tutar yerine Anapara, Gecikme Zammı, Ödenen
                $xml .= '      <GecikmeZammi>' . htmlspecialchars($hareket->gecikme_zammi, ENT_XML1, 'UTF-8') . '</GecikmeZammi>' . "\n";
                $xml .= '      <Odenen>' . htmlspecialchars($hareket->odenen, ENT_XML1, 'UTF-8') . '</Odenen>' . "\n";
                $xml .= '      <Aciklama>' . htmlspecialchars($hareket->aciklama ?: '', ENT_XML1, 'UTF-8') . '</Aciklama>' . "\n";
                // KasaHareketModel'deki gibi kaynak tablo, kaynak id, kayıt yapan, oluşturma tarihi bu modelde yok.
                // Eğer olsaydı eklerdik. Şimdilik ilgili alanları XML'den çıkardım.
                $xml .= '    </Hareket>' . "\n";
            }

            $xml .= '  </Hareketler>' . "\n";
            $xml .= '</KisiHesapHareketleri>';

            echo $xml;
            break;

        default:
            throw new Exception('Geçersiz format: ' . $format);
    }

    exit();
} catch (Exception $e) {
    die('Export hatası: ' . $e->getMessage());
}
