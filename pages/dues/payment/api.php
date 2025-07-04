<?php

require_once '../../../vendor/autoload.php';
session_start();



use App\Helper\Date;
use App\Helper\Error;
use App\Helper\Helper;
use App\Helper\Security;
use Model\BloklarModel;
use Model\BorclandirmaDetayModel;
use Model\BorclandirmaModel;
use Model\DairelerModel;
use Model\DueModel;
use Model\KisilerModel;
use Model\TahsilatHavuzuModel;
use Model\TahsilatModel;
use Model\TahsilatOnayModel;
use \PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use \PhpOffice\PhpSpreadsheet\IOFactory;

$Borc = new BorclandirmaModel();
$BorcDetay = new BorclandirmaDetayModel();
$Due = new DueModel();
$Bloklar = new BloklarModel();
$Tahsilat = new TahsilatModel();
$TahsilatHavuzu = new TahsilatHavuzuModel();
$TahsilatOnay = new TahsilatOnayModel();
$Daire = new DairelerModel();
$Kisi = new KisilerModel();

Security::checkLogin();

/* Excel dosyasından toplu ödeme yükleme işlemi */
if ($_POST['action'] == 'payment_file_upload') {
    $file = $_FILES['payment_file'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];

    // Yalnızca belirli dosya uzantılarına izin ver
    $allowedExtensions = ['csv', 'xlsx', 'xls'];
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

    if (!in_array($fileExtension, $allowedExtensions)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Geçersiz dosya uzantısı. Yalnızca CSV, XLSX veya XLS dosyaları yüklenebilir.'
        ]);
        exit;
    }

    // Sayaçlar ve sonuçlar
    $successCount = 0;
    $failCount = 0;
    $failRows = [];
    $bulunan_daireler = [];
    $eşleşmeyen_kayıtlar = 0;
    $eslesmeyen_daireler = [];

    /**
     * Başarılı eşleşen daire için tahsilat kaydını veritabanına ekler.
     * @param $Tahsilat TahsilatModel öği
     * @param $data Satır verisi (array)
     * @param $daire Eşleşen daire ID'si
     * @return mixed Son eklenen kaydın ID'si
     */
    function kaydetTahsilatOnay($TahsilatOnay, $data, $daire_id)
    {
        //

        $islem_tarihi = Date::YmdHIS($data[0]);  // İşlem tarihi
        $tutar = $data[1];  // Tutar, sayıya dönüştürülür
        $tahsilat_tipi = $data[4] ?? '';  // Tahsilat tipi (Ödeme Türü)
        $aciklama = $data[5] ?? '';  // Açıklama alanı, varsa kullanılır

        // Gerekirse diğer alanlar eklenebilir
        return $TahsilatOnay->saveWithAttr([
            'id' => 0,
            'kisi_id' => $data['kisi_id'] ?? 0,  // Kişi ID'si
            'site_id' => $_SESSION['site_id'],  // Site ID'si
            'tahsilat_tipi' => $tahsilat_tipi,  // Tahsilat tipi
            'islem_tarihi' => $islem_tarihi,
            'daire_id' => $daire_id,
            'tutar' => $tutar,
            // 'makbuz_no' => $data[4],
            'aciklama' => $aciklama,
        ]);
    }

    /**
     * Eşleşmeyen veya hatalı kayıtları tahsilat havuzuna ekler.
     * @param $TahsilatHavuzu TahsilatHavuzuModel örneği
     * @param $data Satır verisi (array)
     * @param $aciklamaEk Açıklama veya hata mesajı
     * @return mixed Son eklenen kaydın ID'si
     */
    function kaydetHavuz($TahsilatHavuzu, $data, $aciklamaEk = '')
    {
        $islem_tarihi = Date::Ymd($data[0]);  // İşlem tarihi
        $ham_aciklama = $data[5] ?? '';  // Ham açıklama alanı, varsa kullanılır
        $referans_no = $data[6] ?? '';  // Makbuz no, varsa kullanılır

        return $TahsilatHavuzu->saveWithAttr([
            'id' => 0,
            'islem_tarihi' => $islem_tarihi,
            'tahsilat_tutari' => Helper::formattedMoneyToNumber($data[1]),  // Tutar
            'ham_aciklama' => $ham_aciklama,
            'referans_no' => $referans_no,
            'aciklama' => $aciklamaEk,  // Ek açıklama veya hata
        ]);
    }

    // Excel dosyasını oku ve satırları işle
    $spreadsheet = IOFactory::load($fileTmpName);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    // B Sütununun veri türü SAyı olarak ayarlanması
    $sheet->getStyle('B')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);

    foreach ($rows as $i => $data) {
        if ($i == 0)
            continue;  // Başlık satırını atla
        try {
            $daire_id = 0;
            $apartmentInfo = null;
            $daire_kodu = $data[2] ?? '';  // Daire kodu
            $iyelik_tipi = $data[3] ?? 'Ev Sahibi';  // Ödeyen tipi (Ev Sahibi, Kiracı)
            $aciklama = $data[5];

            // Öncelikle doğrudan daire kodu ile eşleşme dene
            if (!empty($daire_kodu)) {
                $daire_id = $Daire->DaireId($daire_kodu) ?? 0;
                $kisi_id = $Kisi->AktifKisiByDaireId($daire_id, $iyelik_tipi)->id ?? 0;
            }
            // Daire kodu yoksa açıklamadan blok/daire bilgisi çıkar
            else if (!empty($aciklama)) {
                $apartmentInfo = Helper::extractApartmentInfo($aciklama);
                if ($apartmentInfo) {
                    $daire_id = $Daire->DaireId($apartmentInfo) ?? 0;
                    $kisi_id = $Kisi->AktifKisiByDaireId($daire_id, $iyelik_tipi)->id ?? 0;
                }
            }

            // Eşleşen daire bulunduysa tahsilat kaydet
            if ($daire_id > 0 && !empty($kisi_id)) {
                $data['kisi_id'] = $kisi_id;  // Kişi ID'sini ekle
                kaydetTahsilatOnay($TahsilatOnay, $data, $daire_id);
                $bulunan_daireler[] = $apartmentInfo ?? $daire_kodu . 'kisi_id: ' . $data['kisi_id'];
                $successCount++;
            } else {
                // Eşleşmeyen kayıtları havuza kaydet
                $aciklamaEk = !empty($daire_kodu)
                    ? 'Daire Kodu eşleşmedi: ' . $daire_kodu
                    : ('Bilgi var ' . ($aciklama ?? ''));
                kaydetHavuz($TahsilatHavuzu, $data, $aciklamaEk);
                $eslesmeyen_daireler[] = $apartmentInfo ?? $daire_kodu;
                $eşleşmeyen_kayıtlar++;
            }
        } catch (Exception $e) {
            // Hatalı satırları havuza kaydet ve sayaçları güncelle
            $failCount++;
            $failRows[] = $i + 1;
            kaydetHavuz($TahsilatHavuzu, $data, $e->getMessage());
            $eşleşmeyen_kayıtlar++;
        }
    }

    // Sonuç mesajı oluştur
    $status = 'success';
    $message = "Yükleme tamamlandı.<br> Başarılı: $successCount, <br>Hatalı: $failCount";
    if ($failCount > 0)
        $message .= '. <br>Hatalı satırlar: ' . implode(', ', $failRows);
    if ($eşleşmeyen_kayıtlar > 0)
        $message .= '. <br>Eşleşmeyen kayıt sayısı: ' . $eşleşmeyen_kayıtlar;

    // Sonuçları JSON olarak döndür
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'bulunan_daireler' => $bulunan_daireler,
        'eslesmeyen_daireler' => $eslesmeyen_daireler,
    ]);
}

// Tahsilat onaylama işlemi
if ($_POST['action'] == 'tahsilat_onayla') {
    $id = Security::decrypt($_POST['id']);
    $tutar = Helper::formattedMoneyToNumber($_POST['islenecek_tutar']);
    $tahsilat_turu = $_POST['tahsilat_turu']; // Tahsilat tipi varsayılan olarak Nakit
    $islenen_tahsilatlar = 0;


    $tahsilat = $TahsilatOnay->find($id);

    $tahsilat_tutari = $tahsilat->tutar ?? 0;
    $islenen_tutar = $TahsilatOnay->OnaylanmisTahsilatToplami($id) ?? 0;
    $kalan_tutar = $tahsilat_tutari - $islenen_tutar;

    try {
        $onay = $TahsilatOnay->find($id);


        //gelen tutar kalan tutardan büyükse onaylama
        if ($tutar > $kalan_tutar) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Tahsilat tutarı kalan tutardan büyük olamaz. Kalan tutar: ' . Helper::formattedMoney($kalan_tutar)

            ]);
            exit;
        }

        if (!$onay) {
            echo json_encode(throw new Exception('Tahsilat onayı bulunamadı.'));
            exit;
        }

        $data = [
            'id' => 0,
            'kisi_id' => $onay->kisi_id, // Kişi ID'si
            'tahsilat_onay_id' => $onay->id, // Tahsilat onay ID'si
            'tahsilat_tipi' => $tahsilat_turu, // Tahsilat tipi
            'tutar' => $tutar,
            "islem_tarihi" => date("Y-m-d H:i:s"), // İşlem tarihi
            'aciklama' => $onay->aciklama,
        ];

        // Tahsilat kaydını oluştur
        $lastInsertId = $Tahsilat->saveWithAttr($data);

        $islenen_tahsilatlar = $TahsilatOnay->OnaylanmisTahsilatToplami($onay->id);
        $kalan_tutar = $onay->tutar - $islenen_tahsilatlar;


        //Eğer kalan tutar 0 ise tahsilat onayını güncelle
        if ($kalan_tutar <= 0) {
            $data = [
                'id' => $onay->id,
                'onay_durumu' => 1,
            ];
            $TahsilatOnay->saveWithAttr($data);
        }

        $status = 'success';
        $message = 'Tahsilat onaylama işlemi başarılı.';
    } catch (PDOException $ex) {
        $status = 'error';
        $message = $ex->getMessage();
    }

    $res = [
        'status' => $status,
        'message' => $message,
        "islenen_tahsilatlar" => Helper::formattedMoney($islenen_tahsilatlar),
        "kalan_tutar" => Helper::formattedMoney($kalan_tutar),
    ];
    echo json_encode($res);
}


//onayli_tahsilat_sil
if ($_POST['action'] == 'onayli_tahsilat_sil') {
    $id = Security::decrypt($_POST['id']);
    try {
        $tahsilat = $Tahsilat->find($id);
        if (!$tahsilat) {
            throw new Exception('Tahsilat kaydı bulunamadı.');
        }

        // Tahsilat kaydını sil
        $Tahsilat->delete($_POST['id']);

        //İşlenmiş tahsilatların toplamını al
        $tahsilat_tutar = $TahsilatOnay->find($tahsilat->tahsilat_onay_id)->tutar ?? 0;
        $islenen_tutar = $TahsilatOnay->OnaylanmisTahsilatToplami($tahsilat->tahsilat_onay_id);
        $kalan_tutar = $tahsilat_tutar - $islenen_tutar;

        // Başarılı mesajı
        $status = 'success';
        $message = 'Tahsilat kaydı başarıyla silindi.';
        $islenen_tahsilatlar = $islenen_tutar;
    } catch (Exception $e) {
        $status = 'error';
        $message = $e->getMessage();
    }

    echo json_encode([
        'status' => $status,
        'message' => $message,
        'islenen_tahsilatlar' => Helper::formattedMoney($islenen_tahsilatlar) ?? 0,
        'kalan_tutar' => Helper::formattedMoney($kalan_tutar) ?? 0
    ]);
}

///Tahsilat Kaydet
if ($_POST['action'] == 'tahsilat-kaydet') {
    $id = Security::decrypt($_POST['tahsilat_id']);
    $tahsilat_tipi = $_POST['tahsilat_turu'];
    
    $kisi_id = Security::decrypt($_POST['kisi_id']); // Kişi ID'si
    $kisi = $Kisi->find($kisi_id);
    $daire_id = $kisi->daire_id ?? 0; // Kişinin daire ID'si
    
    $tutar = Helper::formattedMoneyToNumber($_POST['tutar']);
    //  $tahsilat_turu = $_POST['tahsilat_turu']; // Tahsilat tipi varsayılan olarak Nakit

    try {
        // $Tahsilat = $Tahsilat->find($id);
        // if (!$due) {
        //     Error::handlePDOException($e);
        // }

        $data = [
            'id' => 0,
            'kisi_id' => $kisi_id , // Kişi ID'si
            'daire_id' => $daire_id, // Daire ID'si
            'tahsilat_tipi' => $tahsilat_tipi, // Tahsilat tipi
            'kasa_id' => Security::decrypt($_POST['kasa_id']), // Kasa ID'si
            'tutar' => $tutar,
            'islem_tarihi' => Date::YmdHIS($_POST['islem_tarihi']), // İşlem tarihi
            'aciklama' => $_POST['tahsilat_aciklama'] ?? '', // Açıklama
        ];

        // // Tahsilat kaydını oluştur
        $Tahsilat->saveWithAttr($data);

        $status = 'success';
        $message = 'Tahsilat kaydı başarıyla oluşturuldu.';
        $tableRow = $Kisi->TableRow($kisi_id) ;
    } catch (PDOException $ex) {
        $status = 'error';
        $message = Error::handlePDOException($ex);
    }


    echo json_encode([
        'status' => $status,
        'message' => $message,
        'tableRow' => $tableRow ?? 'tablo satırı eklenemedi',
    ]);
}


//Tahsilat sil(modaldan)
if ($_POST['action'] == 'tahsilat-sil') {
    $id =($_POST['id']);
     try {    
        $tahsilat = $Tahsilat->find($id, true); // ID'yi şifreli olarak al
        if (!$tahsilat) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Tahsilat kaydı bulunamadı.'
            ]);
            exit();
        }
        //Güncel finansal durumu getirmek için kişi ID'sini al
           $kisi_id = $tahsilat->kisi_id;

        // Tahsilat kaydını sil
         $Tahsilat->delete($id);

        //Finansal Durumu Getir
        $finansalDurum = $BorcDetay->KisiFinansalDurum($kisi_id);
        $borc = Helper::formattedMoney($finansalDurum->toplam_borc ?? 0);
        $odeme = Helper::formattedMoney($finansalDurum->toplam_odeme ?? 0);
        $bakiye = Helper::formattedMoney($finansalDurum->bakiye ?? 0);

        $tableRow = $Kisi->TableRow($kisi_id) ;


    //     // Başarılı mesajı
         $status = 'success';
         $message = 'Tahsilat kaydı başarıyla silindi.';
     } catch (Exception $e) {
        $status = 'error';
        $message = Error::handlePDOException($e);
     }

    $res = [
        'status' => $status,
        'message' => $message ,
        'borc' => $borc ?? '0,00',
        'odeme' => $odeme ?? '0,00',
        'bakiye' => $bakiye ?? '0,00',
        'tableRow' => $tableRow ,
    ];
    echo json_encode($res);
}