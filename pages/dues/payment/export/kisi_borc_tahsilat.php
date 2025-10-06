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
use Dompdf\Options;
use Dompdf\Dompdf as PDF;
use Model\KisilerModel;



// Yetki kontrolü
//Gate::can('gelir_gider_export');
$KisiModel = new KisilerModel();
$FinansalRaporModel = new FinansalRaporModel();

$format = $_GET['format'] ?? 'pdf'; // Varsayılan format pdf
$kisi_id = $_GET['kisi_id'] ?? 0;


//Kişi bilgilerini getir
$kisi = $KisiModel->getKisiByDaireId($kisi_id);
$kisiFinansalDurum = $FinansalRaporModel->kisiFinansalDurum($kisi_id);

$kisiHareketler = $FinansalRaporModel->kisiHesapHareketleri($kisi_id);

//echo '<pre>'; print_r($kisiHareketler); echo '</pre>'; exit;

if (empty($kisiHareketler)) {
    die('Dışarı aktarılacak veri bulunamadı.');
}
//echo '<pre>'; print_r($kisiHareketler); echo '</pre>'; exit;

// Spreadsheet oluştur
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Tüm spreadsheet için varsayılan fontu ayarla
$spreadsheet->getDefaultStyle()->getFont()->setName('Source Sans Pro');
$spreadsheet->getDefaultStyle()->getFont()->setSize(11);



// Başlık satırı
$headers = [
    'A9' => 'ID',
    'B9' => 'İşlem Tarihi',
    'C9' => 'İşlem Tipi',
    'D9' => 'Borç',
    'E9' => 'Gecikme Zammı',
    'F9' => 'Ödenen',
    'G9' => 'Bakiye',
    'H9' => 'Açıklama',
];

// Başlıkları set et
foreach ($headers as $cell => $value) {
    $sheet->setCellValue($cell, $value);
}

/* BAŞLIK AYARLARI */
//a1'den G3'e kadar birleştir
$spreadsheet->getActiveSheet()->setTitle('Kişi Hesap Hareketleri');
$spreadsheet->setActiveSheetIndex(0);
$sheet->mergeCells('A1:K3');
$sheet->setCellValue('A1', 'Kişi Hesap Hareketleri');

//Font Size ve Bold yap
$sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);

//Ortala
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

//Kenarlık ekle
$sheet->getStyle('A1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

/**A4 Hücresine Daire No: ekle, Sağa yasla */
$sheet->mergeCells('A4:B4');
$sheet->setCellValue('A4', 'Adı Soyadı:');
$sheet->getStyle('A4')->getFont()->setBold(true);
$sheet->getStyle('A4:B4')->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
    ->setVertical(Alignment::VERTICAL_CENTER);

//C4
$sheet->mergeCells('C4:H4');
$sheet->setCellValue('C4', $kisi->adi_soyadi ?? '');
$sheet->getStyle('C4')->getFont()->setBold(true);

$sheet->mergeCells('I4:J4');
$sheet->setCellValue('I4', 'Toplam Borç:  ');
$sheet->setCellValue('K4', Helper::formattedMoney($kisiFinansalDurum->toplam_borc ?? '0,00'));
//Bold yap
$sheet->getStyle('K4')->getFont()->setBold(true);
$sheet->getStyle('I4:J4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->getStyle('K4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

//Daire kodu
$sheet->getStyle('C5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
$sheet->mergeCells('A5:B5');
$sheet->setCellValue('A5', 'Daire Kodu:');
$sheet->mergeCells('C5:H5');
$sheet->setCellValue('C5', $kisi->daire_kodu ?? '');
$sheet->getStyle('C5')->getFont()->setBold(true);

$sheet->mergeCells('I5:J5');
$sheet->setCellValue('I5', 'Toplam Ödenen:  ');
$sheet->setCellValue('K5', Helper::formattedMoney($kisiFinansalDurum->toplam_tahsilat ?? '0,00'));
$sheet->getStyle('I5:J5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->getStyle('K5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
$sheet->getStyle('K5')->getFont()->setBold(true);



$sheet->mergeCells('A6:B6');
$sheet->mergeCells('C6:H6');

$sheet->setCellValue('A6', 'Oturum Şekli:');
$sheet->setCellValue('C6', $kisi->uyelik_tipi ?? '');

$sheet->mergeCells('I6:J6');
$sheet->setCellValue('I6', 'Bakiye:  ');
$sheet->setCellValue('K6', Helper::formattedMoney($kisiFinansalDurum->bakiye ?? '0,00'));

$sheet->getStyle('I6:J6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->getStyle('K6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
$sheet->getStyle('K6')->getFont()->setBold(true);

$sheet->mergeCells('A7:B7');
$sheet->mergeCells('C7:K7');

$sheet->setCellValue('A7', 'Rapor Tarihi:');
$sheet->setCellValue('C7', date('d.m.Y H:i'));


$sheet->mergeCells('A8:K8');
$sheet->getStyle('A1:K8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

//Satır yüksekliği 5 yap
$sheet->getRowDimension(8)->setRowHeight(5);
$sheet->getStyle('A2:K8')->getFont()->setSize(10);


$sheet->mergeCells('H9:K9');


// Son başlık hücresini bul
$lastHeaderColumn = 'K'; // 'A1'den 'G1'e kadar başlıklarınız var

// Başlık satırını formatla
$sheet->getStyle('A9:' . $lastHeaderColumn . '9')->applyFromArray([
    'font' => [
        'bold' => true,
        'color' => ['rgb' => '000000'],
        'size' => 9,
        'name' => 'Source Sans Pro'
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


// Veri satırlarını doldur
$row = 10;
foreach ($kisiHareketler as $hareket) {
    $sheet->setCellValue('A' . $row, $hareket->islem_id); // 'islem_id' daha mantıklı olabilir
    $sheet->setCellValue('B' . $row, date('d.m.Y H:i', strtotime($hareket->islem_tarihi)));
    $sheet->setCellValue('C' . $row, ucfirst($hareket->islem_tipi));
    $sheet->setCellValue('D' . $row, number_format((float)$hareket->anapara, 2, ',', '.')); // Float'a çevirme
    $sheet->setCellValue('E' . $row, number_format((float)$hareket->gecikme_zammi, 2, ',', '.')); // Float'a çevirme
    $sheet->setCellValue('F' . $row, number_format((float)$hareket->odenen, 2, ',', '.')); // Float'a çevirme
    $sheet->setCellValue('G' . $row, number_format((float)$hareket->yuruyen_bakiye, 2, ',', '.') ?: '-');

    //H ve K sütunlarını birleştir
    $sheet->mergeCells('H' . $row . ':K' . $row);
    $sheet->setCellValue('H' . $row, $hareket->aciklama ?: '-');


    // Satır rengini değiştir (çift satırlar açık gri)
    if ($row % 2 == 0) {
        $sheet->getStyle('A' . $row . ':' . $lastHeaderColumn . $row)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F8F9FA']
            ],
            'font' => [
                'name' => 'Source Sans Pro',
                'size' => 9,
            ]
        ]);
    } else {
        // Tek satırlar için de font ayarla
        $sheet->getStyle('A' . $row . ':' . $lastHeaderColumn . $row)->applyFromArray([
            'font' => [
                'name' => 'Source Sans Pro',
                'size' => 9,
            ]
        ]);
    }

    $row++;
}

// // Sütun genişliklerini ayarla (sadece kullanılan sütunlar için)
// $sheet->getColumnDimension('A')->setWidth(15); // ID için artırıldı
$sheet->getColumnDimension('B')->setWidth(13); // İşlem Tarihi
$sheet->getColumnDimension('C')->setWidth(12); // İşlem Tipi
$sheet->getColumnDimension('D')->setWidth(10); // Anapara
$sheet->getColumnDimension('E')->setWidth(10); // Gecikme Zammı
$sheet->getColumnDimension('F')->setWidth(10); // Ödenen
$sheet->getColumnDimension('G')->setWidth(10); // Bakiye
$sheet->getColumnDimension('H')->setWidth(width: 15); // Açıklama
$sheet->getColumnDimension('I')->setWidth(width: 15); // Açıklama
$sheet->getColumnDimension('J')->setWidth(width: 15); // Açıklama
$sheet->getColumnDimension('K')->setWidth(width: 15); // Açıklama

//D'den G'ye kadar olan sütunları sağa hizala
$sheet->getStyle('D10:G' . ($row - 1))
    ->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

// Açıklama ve genel hücreler için satır kaydır
$sheet->getStyle('H10:H' . ($row - 1))
    ->getAlignment()
    ->setWrapText(true);


//H sütununa indent ekle
$sheet->getStyle('H10:H' . ($row - 1))
    ->getAlignment()
    ->setIndent(1);

$printLastRow = $row - 1;


// Tüm verilere border ekle (son kullanılan sütuna göre)
$sheet->getStyle('A9:' . $lastHeaderColumn . ($row - 1))->applyFromArray([ // Burası düzeltildi
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '222222']
        ],

    ]
]);


// Print ayarları - Başlık satırını her sayfada tekrarla
$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(start: 1, end: 9);

$spreadsheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
$spreadsheet->getActiveSheet()->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);



// Dosya adı
$filename = 'hesap_ozeti_' . date('Y-m-d_H-i-s');

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
            $spreadsheet->getActiveSheet()->getStyle('A1:G100')->getFont()->setBold(true);
            $spreadsheet->getActiveSheet()->getStyle('A1:G100')->getFont()->setName('Source Sans Pro');
            $spreadsheet->getActiveSheet()->getStyle('A1:G100')->getFont()->setSize(12);



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


            // PDF case içinde (writer'dan önce):

            $sheet->getStyle('A8:K' . ($row - 1))->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'indent' => 1,
                    'wrapText' => true
                ]
            ]);

            $sheet->getStyle('K4:K6')->getFont()->setBold(true);

            $sheet->getStyle('A9:' . $lastHeaderColumn . '9')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '000000'],
                    'size' => 10,
                ]
            ]);


            //D'den G'ye kadar olan sütunları sağa hizala
            $sheet->getStyle('D10:G' . ($row - 1))
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

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
