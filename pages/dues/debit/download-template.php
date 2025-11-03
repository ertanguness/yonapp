<?php
// download-template.php
ob_start();
require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php'; // Gerekli dosyaları yükle

use App\Services\FlashMessageService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Model\DairelerModel;
use Model\KisilerModel;

$KisilerModel = new KisilerModel();

// Gelen parametreleri al ve doğrula
$siteId = $_SESSION["site_id"] ?? null;
// $blokId = filter_input(INPUT_GET, 'blok_id', FILTER_VALIDATE_INT);

if (!$siteId) {
    die("Geçersiz site seçimi.");
}

// 1. Veritabanından ilgili daireleri çek
$daireModel = new DairelerModel();


$hedef_tipi = $_POST["hedef_tipi"] ?? '';


// 2. PhpSpreadsheet nesnesi oluştur
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Kisi Yukleme Sablonu');

// 3. Başlıkları yazdır
$sheet->setCellValue('A1', 'Sıra No');
$sheet->setCellValue('B1', 'Daire Kodu*');
$sheet->setCellValue('C1', 'Kişi ID*');
$sheet->setCellValue('D1', 'Adı Soyadı');
$sheet->setCellValue('E1', 'Uyelik Tipi');
$sheet->setCellValue('F1', 'Tutar');
$sheet->setCellValue('G1', 'Ceza Oranı %');
$sheet->setCellValue('H1', 'Açıklama');

$sheet->getStyle('A1:Z1')->getFont()->setBold(true);




switch ($hedef_tipi) {

    case 'all':
        $rowNumber = 2;

        $tum_kisiler = $KisilerModel->SiteAktifKisileri($siteId);
        foreach ($tum_kisiler as $kisi) {

            $sheet->setCellValue('A' . $rowNumber, $rowNumber - 1); // Blok Adı, örnek olarak sıra numarası verildi
            $sheet->setCellValue('B' . $rowNumber, $kisi->daire_kodu); // Daire Kodu, kişi ID'si olarak ayarlandı
            $sheet->setCellValue('C' . $rowNumber, $kisi->kisi_id);
            $sheet->setCellValue('D' . $rowNumber, $kisi->adi_soyadi);
            $sheet->setCellValue('E' . $rowNumber, $kisi->uyelik_tipi);
            $sheet->setCellValue('F' . $rowNumber, 0); // Tutar, başlangıçta 0 olarak ayarlandı
            $sheet->setCellValue('G' . $rowNumber, 0); // Ceza Oranı, başlangıçta 0 olarak ayarlandı
            $sheet->setCellValue('H' . $rowNumber, ''); // Açıklama, başlangıçta boş bırakıldı

            $rowNumber++;
        }

        break;



    case 'person':
        $rowNumber = 2;

        $kisi_Ids = $_POST["hedef_kisi"] ?? '';

        if (!$kisi_Ids) {
            die("Kişi ID'leri geçersiz veya boş. Lütfen geçerli kişi ID'leri seçin.");
        }

        //kişi id'lerini içeren kişi bilgilerini getir
        $kisiler = $KisilerModel->getKisilerByIds($kisi_Ids);

        foreach ($kisiler as $kisi) {

            $sheet->setCellValue('A' . $rowNumber, $rowNumber - 1); // Blok Adı, örnek olarak sıra numarası verildi
            // Daire Kodu, kişi ID'si olarak ayarlandı
            $sheet->setCellValue('B' . $rowNumber, $kisi->daire_kodu); // Daire Kodu
            $sheet->setCellValue('C' . $rowNumber, $kisi->id);
            $sheet->setCellValue('D' . $rowNumber, $kisi->adi_soyadi);
            $sheet->setCellValue('E' . $rowNumber, $kisi->uyelik_tipi);
            $sheet->setCellValue('F' . $rowNumber, 0); // Tutar, başlangıçta 0 olarak ayarlandı
            $sheet->setCellValue('G' . $rowNumber, 0); // Ceza Oranı, başlangıçta 0 olarak ayarlandı
            $sheet->setCellValue('H' . $rowNumber, ''); // Açıklama, başlangıçta boş bırakıldı

            $rowNumber++;
        }



        break;
    case 'blok':
        $sheet->setTitle('Blok Yükleme Sablonu');
        break;
    case 'daire':
        $sheet->setTitle('Daire Yükleme Sablonu');
        break;

     case "0":
        $sheet->setTitle('Genel Borç Yükleme Sablonu');
        break;
    default:
        die("Geçersiz hedef tipi.");
}








// Sütun genişliklerini otomatik ayarla
foreach (range('A', 'Z') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}
ob_end_clean();
// 5. Dosyayı tarayıcıya indirme olarak gönder
$writer = new Xlsx($spreadsheet);

$fileName = 'borc_yukleme_sablonu_' . date('Ymd') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
$writer->save('php://output');
exit();
