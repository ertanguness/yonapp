<?php
require_once dirname(__DIR__ ,levels: 3). '/configs/bootstrap.php';




use App\Helper\Date;
use App\Helper\Error;
use App\Helper\FinansalHelper;
use App\Helper\Helper;
use App\Helper\Security;
use Model\BloklarModel;
use Model\BorclandirmaDetayModel;
use Model\BorclandirmaModel;
use Model\DairelerModel;
use Model\KisilerModel;
use Model\KisiKredileriModel;
use Model\TahsilatHavuzuModel;
use Model\TahsilatModel;
use Model\TahsilatDetayModel;
use Model\TahsilatOnayModel;
use Model\FinansalRaporModel;


use \PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use \PhpOffice\PhpSpreadsheet\IOFactory;
use Database\Db;

$Borc = new BorclandirmaModel();
$BorcDetay = new BorclandirmaDetayModel();
$Bloklar = new BloklarModel();
$Tahsilat = new TahsilatModel();
$TahsilatHavuzu = new TahsilatHavuzuModel();
$TahsilatOnay = new TahsilatOnayModel();
$TahsilatDetay = new TahsilatDetayModel();
$Daire = new DairelerModel();
$Kisi = new KisilerModel();
$KisiKredi = new KisiKredileriModel();
$FinansalRapor = new FinansalRaporModel();


Security::checkLogin();

$logger = \getLogger();

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

///Tahsilat Kaydet(TAHSİLAT GİR MODALINDAN KAYIT İŞLEMİ)
// if ($_POST['action'] == 'tahsilat-kaydet') {
//     $id = Security::decrypt($_POST['tahsilat_id']);
//     $tahsilat_tipi = $_POST['tahsilat_turu'];
    
//     $kisi_id = Security::decrypt($_POST['kisi_id']); // Kişi ID'si
//     $kisi = $Kisi->find($kisi_id);
//     $daire_id = $kisi->daire_id ?? 0; // Kişinin daire ID'si
    
//     $tutar = Helper::formattedMoneyToNumber($_POST['tutar']);
//     //  $tahsilat_turu = $_POST['tahsilat_turu']; // Tahsilat tipi varsayılan olarak Nakit

//     try {
//         $data = [
//             'id' => 0,
//             'kisi_id' => $kisi_id , // Kişi ID'si
//             'kasa_id' => Security::decrypt($_POST['kasa_id']), // Kasa ID'si
//             'tutar' => $tutar,
//             'islem_tarihi' => Date::YmdHIS($_POST['islem_tarihi']), // İşlem tarihi
//             'aciklama' => $_POST['tahsilat_aciklama'] ?? '', // Açıklama
//         ];

//         // // Tahsilat kaydını oluştur
//         $lastInsertId = $Tahsilat->saveWithAttr($data);

//         //seçilen borcları diziden alıyoruz
//         $borcDetayIds = $_POST['borc_detay_ids'] ?? [];

//          //bu tahsilatı detay tablosuna ekliyoruz
//         $dataDetay = [
//             'id' => 0,
//             'tahsilat_id' => $lastInsertId, // Tahsilat ID'si
//             'borc_detay_id' => $id, // Borç detay ID'si
//             'tutar' => $tutar,
//             'aciklama' => $_POST['tahsilat_aciklama'] ?? '', // Açıklama
//         ];



//         $kisiFinansalDurum = $BorcDetay->KisiFinansalDurum($kisi_id);

//         $status = 'success';
//         $message = 'Tahsilat kaydı başarıyla oluşturuldu.';
       
//     } catch (PDOException $ex) {
//         $status = 'error';
//         $message = Error::handlePDOException($ex);
//     }


//     echo json_encode([
//         'status' => $status,
//         'message' => $message,
//         'tableRow' => $tableRow ?? 'tablo satırı eklenemedi',
//         "finansalDurum" => [
//             'toplam_borc' => Helper::formattedMoney($kisiFinansalDurum->toplam_borc ?? 0),
//             'toplam_odeme' => Helper::formattedMoney($kisiFinansalDurum->toplam_odeme ?? 0),
//             'bakiye' => Helper::formattedMoney($kisiFinansalDurum->bakiye ?? 0) 
//         ]
//     ]);
// };



// /// Tahsilat Kaydet (TAHSİLAT GİR MODALINDAN KAYIT İŞLEMİ)
// if ($_POST['action'] == 'tahsilat-kaydet') {
//     // 1. Gelen Verileri Al ve Temizle
//     $kisi_id = Security::decrypt($_POST['kisi_id']);
//     $odenen_toplam_tutar = Helper::formattedMoneyToNumber($_POST['tutar']);
//     $kasa_id = Security::decrypt($_POST['kasa_id']);
//     $islem_tarihi = Date::YmdHIS($_POST['islem_tarihi']);
//     $aciklama = $_POST['tahsilat_aciklama'] ?? '';
//     $borcDetayIdsString = $_POST['borc_detay_ids'] ?? [];

//     // Ödenecek tutarı takip etmek için bir kopya oluşturalım
//     $kalanOdenecekTutar = $odenen_toplam_tutar;

//     // Herhangi bir borç seçilmediyse ve tutar girildiyse, bunu direkt kredi olarak işle.
//     if (empty($borcDetayIds) && $kalanOdenecekTutar > 0) {
//         // Bu senaryo için ayrı bir mantık kurabilirsiniz. 
//         // Şimdilik borç seçilmesi gerektiğini varsayıyoruz.
//     }

//    // 1. Singleton Db nesnesini al
//    $db = Db::getInstance();

//    // 2. Veritabanı transaction'ını bu nesne üzerinden başlat
//    $db->beginTransaction();

//     try {
//         // 2. Ana Tahsilat Kaydını Oluştur 
//         // Bu kayıt, kasaya giren toplam parayı temsil eder.
//         $tahsilatData = [
//             'id'        => 0,
//             'kisi_id'   => $kisi_id,
//             'kasa_id'   => $kasa_id,
//             'tutar'     => $odenen_toplam_tutar, // Kasaya giren toplam tutar
//             'islem_tarihi' => $islem_tarihi,
//             'aciklama'  => $aciklama,
//         ];
//         $tahsilatId = $Tahsilat->saveWithAttr($tahsilatData);



//         $encryptedBorcDetayIds = []; // Önce boş bir dizi olarak tanımla
//         if (!empty($borcDetayIdsString)) {
//             $encryptedBorcDetayIds = explode(',', $borcDetayIdsString);
//         }
//         $borcDetayIds = array_map([App\Helper\Security::class, 'decrypt'], $encryptedBorcDetayIds);

      
//         // 3. Seçilen Borçları Getir ve Sırala
//         // "hangisi daha önce eklendiyse" kuralı için ID'ye göre küçükten büyüğe sıralıyoruz.
//         $secilenBorclar = $BorcDetay->findWhereIn('id', $borcDetayIds, 'id ASC');

//         // 4. Borçları Döngüye Alarak Ödemeyi Dağıt
//         foreach ($secilenBorclar as $borc) {
//             // Eğer ödenecek para kalmadıysa döngüden çık
//             if ($kalanOdenecekTutar <= 0) {
//                 break;
//             }


//             $guncelGecikmeZammi = FinansalHelper::hesaplaGecikmeZammi(
//                 $borc->kalan_borc, 
//                 $borc->son_odeme_tarihi, 
//                 $borc->ceza_orani
//             );


//             // Borcun kalan anapara ve gecikme zammını kontrol et
//             if ($borc->kalan_borc <= 0 && $borc->kalan_gecikme_zammi <= 0) {
//                 // Bu borç tamamen kapatılmış, sonraki borca geç
//                 continue;
//             }
//             // Borcun kalan anapara ve gecikme zammını güncelle
            
//             // Öncelik 1: Gecikme Zammını Kapat
//             $odenecekGecikmeTutari = min($kalanOdenecekTutar, $borc->kalan_gecikme_zammi);

//             if ($odenecekGecikmeTutari > 0) {
//                 // Tahsilat detayına bu kısmı kaydet
//                 $TahsilatDetay->saveWithAttr([
//                     'id' => 0,
//                     'tahsilat_id' => $tahsilatId,
//                     'borc_detay_id' => $borc->id,
//                     'tutar' => $odenecekGecikmeTutari,
//                     'aciklama' => 'Gecikme zammı ödemesi',
//                 ]);

//                 // Borcun kalan gecikme zammını güncelle
//                 $borc->kalan_gecikme_zammi -= $odenecekGecikmeTutari;
//                 $kalanOdenecekTutar -= $odenecekGecikmeTutari;
//                 $logger->info("Gecikme zammı ödendi: {$odenecekGecikmeTutari} TL, kalan gecikme zammı: {$borc->kalan_gecikme_zammi} TL");
//             }

//             // Eğer hala ödenecek para varsa anaparaya geç
//             if ($kalanOdenecekTutar <= 0) {
//                  // Borcun güncel halini veritabanına kaydet ve sonraki borca geç
//                 $BorcDetay->updateSingle($borc->id, ['kalan_gecikme_zammi' => $borc->kalan_gecikme_zammi]);
//                 continue;
//             }

//             // Öncelik 2: Anaparayı Kapat
//             $odenecekAnaParaTutari = min($kalanOdenecekTutar, $borc->kalan_borc);

//             if ($odenecekAnaParaTutari > 0) {
//                 // Tahsilat detayına bu kısmı kaydet
                
                
//                 $data=([
//                     'id' => 0,
//                     'tahsilat_id' => Security::decrypt($tahsilatId),
//                     'borc_detay_id' => $borc->id,
//                     'odenen_tutar' => $odenecekAnaParaTutari,
//                     'aciklama' => 'Anapara ödemesi',
//                 ]);
//                 //$logger->info("Tahsilat detay kaydı: " . json_encode($data));
//                 $TahsilatDetay->saveWithAttr($data);


                
//                 // Borcun kalan anaparasını güncelle
//                 $borc->kalan_borc -= $odenecekAnaParaTutari;
//                 $kalanOdenecekTutar -= $odenecekAnaParaTutari;
//                 $logger->info("Anapara ödendi: {$odenecekAnaParaTutari} TL, kalan anapara: {$borc->kalan_borc} TL");
//             }
            
//             // Borcun son durumunu (kalan anapara ve gecikme zammı) veritabanına kaydet
//             $BorcDetay->updateSingle($borc->id, [
//                 'kalan_borc' => $borc->kalan_borc,
//                 'kalan_gecikme_zammi' => $borc->kalan_gecikme_zammi
//             ]);
//         }

//         // 5. Borçlar Kapandıktan Sonra Para Arttıysa Kredi Olarak Kaydet
//         if ($kalanOdenecekTutar > 0) {
//             $KisiKredi->saveWithAttr([
//                 'id' => 0,
//                 'kisi_id' => $kisi_id,
//                 'tahsilat_id' => Security::decrypt($tahsilatId), // Hangi tahsilattan geldiğini belirtmek için
//                 'tutar' => $kalanOdenecekTutar,
//                 'aciklama' => 'Tahsilat fazlası alacak kaydı',
//             ]);
//         }

//         // Tüm işlemler başarılı, transaction'ı onayla
//         $db->commit();

//         $status = 'success';
//         $message = 'Tahsilat başarıyla kaydedildi ve borçlara dağıtıldı.';

//     } catch (Exception $ex) { // PDOException yerine genel Exception yakalamak daha güvenli
//         // Bir hata oluştu, tüm işlemleri geri al
//         $db->rollBack();

//         $status = 'error';
//         $message = 'İşlem sırasında bir hata oluştu: ' . $ex->getMessage(); // Geliştirme için hatayı görmek faydalı
//         // $message = Error::handlePDOException($ex); // Üretim ortamı için
//     }

//     // 6. Son Finansal Durumu Hesapla ve Gönder
//     // Bu kısım transaction dışında olmalı ki, commit veya rollback sonrası son durumu çeksin.
//     $kisiFinansalDurum = $BorcDetay->KisiFinansalDurum($kisi_id);

//     echo json_encode([
//         'status' => $status,
//         'message' => $message,
//         "finansalDurum" => [
//             'toplam_borc' => Helper::formattedMoney($kisiFinansalDurum->toplam_borc ?? 0),
//             'toplam_odeme' => Helper::formattedMoney($kisiFinansalDurum->toplam_odeme ?? 0),
//             'bakiye' => Helper::formattedMoney($kisiFinansalDurum->bakiye ?? 0) 
//         ]
//     ]);
// };


/// Tahsilat Kaydet (TAHSİLAT GİR MODALINDAN KAYIT İŞLEMİ)
if ($_POST['action'] == 'tahsilat-kaydet') {
    // 1. Gelen Verileri Al ve Temizle
    $kisi_id = Security::decrypt($_POST['kisi_id']);
    $odenen_toplam_tutar = Helper::formattedMoneyToNumber($_POST['tutar']);
    $kasa_id = Security::decrypt($_POST['kasa_id']);
    $islem_tarihi = Date::YmdHIS($_POST['islem_tarihi']);
    $aciklama = $_POST['tahsilat_aciklama'] ?? '';
    $borcDetayIdsString = $_POST['borc_detay_ids'] ?? '';

    $kalanOdenecekTutar = $odenen_toplam_tutar;

    // Şifreli ID'leri deşifre et
    $borcDetayIds = [];
    if (!empty($borcDetayIdsString)) {
        $encryptedBorcDetayIds = explode(',', $borcDetayIdsString);
        $borcDetayIds = array_map([App\Helper\Security::class, 'decrypt'], $encryptedBorcDetayIds);
    }

    if (empty($borcDetayIds) && $kalanOdenecekTutar > 0) {
        // Borç seçilmeden yapılan ödemeyi direkt kredi olarak işleme mantığı buraya gelebilir.
        // Şimdilik borç seçildiğini varsayıyoruz.
    }

    $db = Db::getInstance();
    $db->beginTransaction();

    try {
        // 2. Ana Tahsilat Kaydını Oluştur
        $tahsilatData = [
            'id' => 0,
            'kisi_id' => $kisi_id,
            'kasa_id' => $kasa_id,
            'tutar' => $odenen_toplam_tutar,
            'islem_tarihi' => $islem_tarihi,
            'aciklama' => $aciklama,
        ];
        // $tahsilatId'nin temiz (şifresiz) ID olduğunu varsayıyoruz.
        $tahsilatId = $Tahsilat->saveWithAttr($tahsilatData);
        
        // 3. Seçilen Borçları Getir ve Sırala
        $secilenBorclar = [];
        if (!empty($borcDetayIds)) {
            $secilenBorclar = $BorcDetay->findWhereIn('id', $borcDetayIds, 'son_odeme_tarihi ASC, id ASC');
        }

        // 4. Borçları Döngüye Alarak Ödemeyi Dağıt
        foreach ($secilenBorclar as $borc) {
            if ($kalanOdenecekTutar <= 0) break;

            // DİKKAT 1: Ödeme anındaki GÜNCEL gecikme zammını hesapla
            $guncelGecikmeZammi = FinansalHelper::hesaplaGecikmeZammi(
                $borc->kalan_borc, 
                $borc->son_odeme_tarihi, 
                $borc->ceza_orani
            );
            
            // Öncelik 1: Gecikme Zammını Kapat
            // DİKKAT 2: Ödenecek tutar, o anki güncel zamma göre belirlenir.
            $odenecekGecikmeTutari = min($kalanOdenecekTutar, $guncelGecikmeZammi);

            if ($odenecekGecikmeTutari > 0) {
                // Tahsilat detayına bu kısmı kaydet (YENİ EKLENEN KISIM)
                $data=([
                    'id' => 0,
                    'tahsilat_id' => Security::decrypt($tahsilatId), // Temiz ID
                    'borc_detay_id' => $borc->id,
                    'odenen_tutar' => $odenecekGecikmeTutari,
                    'aciklama' => 'Gecikme zammı ödemesi',
                ]);
                // $logger->info("Tahsilat detay kaydı: " . json_encode($data));
                $TahsilatDetay->saveWithAttr($data);
                
                // Ödenen tutarı toplamdan düş
                $kalanOdenecekTutar -= $odenecekGecikmeTutari;
                
                // Gecikme zammını sıfırla. 
                // Not: Kısmi faiz ödemesi durumunda kalan faizi saklamak için 
                // 'kalan_gecikme_zammi' kolonu kullanılabilir. Şimdilik tamamen kapandığını varsayıyoruz.
                // Eğer kalan faizi tutmak isterseniz: $borc->kalan_gecikme_zammi = $guncelGecikmeZammi - $odenecekGecikmeTutari;
                 $BorcDetay->updateSingle($borc->id, ['kalan_gecikme_zammi' => 0]); // veya kalanını güncelle
            }

            if ($kalanOdenecekTutar <= 0) continue;

            // Öncelik 2: Anaparayı Kapat
            $odenecekAnaParaTutari = min($kalanOdenecekTutar, $borc->kalan_borc);

            if ($odenecekAnaParaTutari > 0) {
                // Tahsilat detayına anapara ödemesini kaydet
                $TahsilatDetay->saveWithAttr([
                    'id' => 0,
                    'tahsilat_id' => Security::decrypt($tahsilatId), // Temiz ID
                    'borc_detay_id' => $borc->id,
                    'odenen_tutar' => $odenecekAnaParaTutari, // Kolon adının 'tutar' olduğunu varsaydım
                    'aciklama' => 'Anapara ödemesi',
                ]);
                
                // Borcun kalan anaparasını güncelle
                $yeniKalanBorc = $borc->kalan_borc - $odenecekAnaParaTutari;
                $BorcDetay->updateSingle($borc->id, ['kalan_borc' => $yeniKalanBorc]);
                
                // Ödenen tutarı toplamdan düş
                $kalanOdenecekTutar -= $odenecekAnaParaTutari;
            }
        }

        // 5. Borçlar Kapandıktan Sonra Para Arttıysa Kredi Olarak Kaydet
        if ($kalanOdenecekTutar > 0.009) { // Kuruş farkları için küçük bir tolerans
            $KisiKredi->saveWithAttr([
                'id' => 0,
                'kisi_id' => $kisi_id,
                'tahsilat_id' =>Security::decrypt($tahsilatId),
                'tutar' => $kalanOdenecekTutar,
                'aciklama' => 'Tahsilat fazlası alacak kaydı',
            ]);
        }

        $db->commit();

        $status = 'success';
        $message = 'Tahsilat başarıyla kaydedildi ve borçlara dağıtıldı.';

    } catch (Exception $ex) {
        $db->rollBack();
        $status = 'error';
        $message = 'İşlem sırasında bir hata oluştu: ' . $ex->getMessage();
    }

    // 6. Son Finansal Durumu Hesapla ve Gönder
    $kisiFinansalDurum = $BorcDetay->KisiFinansalDurum($kisi_id);

    echo json_encode([
        'status' => $status,
        'message' => $message,
        "finansalDurum" => [
            'toplam_borc' => Helper::formattedMoney($kisiFinansalDurum->toplam_borc ?? 0),
            'toplam_odeme' => Helper::formattedMoney($kisiFinansalDurum->toplam_odeme ?? 0),
            'bakiye' => Helper::formattedMoney($kisiFinansalDurum->bakiye ?? 0)
        ]
    ]);
};


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







//GElen id'lerin hesaplanmış tutarlarını getirir
if (isset($_POST['action']) && $_POST['action'] == 'hesapla_toplam_tutar') {
    header('Content-Type: application/json');

    $sifreliBorcIdleri = $_POST['borc_idler'] ?? [];

    if (!is_array($sifreliBorcIdleri) || empty($sifreliBorcIdleri)) {
        echo json_encode(['success' => true, 'toplam_tutar' => 0]);
        exit;
    }

   
    $cozulmusBorcIdleri = [];
    foreach ($sifreliBorcIdleri as $sifreliId) {
        try {
            // Her bir ID'nin şifresini çöz
            $cozulmusId = Security::decrypt($sifreliId);
            // Çözülen ID'nin geçerli bir sayı olduğundan emin ol (ekstra güvenlik)
            if (is_numeric($cozulmusId) && $cozulmusId > 0) {
                $cozulmusBorcIdleri[] = (int)$cozulmusId;
            }
        } catch (\Exception $e) {
            // Şifre çözme başarısız olursa (örneğin manipüle edilmiş ID)
            // Hata logla ve isteği reddet.
            error_log("Geçersiz şifreli ID çözme denemesi: " . $sifreliId);
            echo json_encode([
                'success' => false,
                'message' => 'Geçersiz veri gönderildi. İşlem iptal edildi.'
            ]);
            exit;
        }
    }
    
    // Eğer şifresi çözülen geçerli ID kalmamışsa, boş dön.
    if (empty($cozulmusBorcIdleri)) {
        echo json_encode(['success' => true, 'toplam_tutar' => 0, 'data' => $sifreliBorcIdleri]);
        exit;
    }

    $FinansalRapor = new FinansalRaporMOdel();
    // Veritabanından toplam tutarı güvenli bir şekilde al
    // Model metoduna artık şifresi çözülmüş, temiz ID dizisini gönderiyoruz.
    $toplamTutar = $FinansalRapor->getToplamTutarByIds($cozulmusBorcIdleri);
    // Eğer toplam tutar başarılı bir şekilde alındıysa, JSON olarak döndür.


    if ($toplamTutar !== false) {
        echo json_encode([
            'success' => true,
            'toplam_tutar' => ($toplamTutar)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Borçlar bulunurken bir veritabanı hatası oluştu.'
        ]);
    }
    exit;
}




// action: 'get_kisi_borclari'
if ($_POST['action'] == 'get_kisi_borclari') {
    
    header('Content-Type: application/json');

    try {
        if (empty($_POST['kisi_id'])) {
            throw new Exception("Kişi ID'si gönderilmedi.");
        }

        $kisiId = Security::decrypt($_POST['kisi_id']);
        

        $BorcDetay = new BorclandirmaDetayModel();
        // Bu metodun, sadece ödenmemiş (kalan_tutar > 0) borçları getirmesi gerekir.
        $odenmemisBorclar = $BorcDetay->getOdenmemisBorclarByKisi($kisiId);
 //echo json_encode(['status' => 'success', 'message' => 'Kişi ID alındı.' . $kisiId ,
   //          'data' => $odenmemisBorclar]);
       // exit();
        $responseBorclar = [];
        foreach ($odenmemisBorclar as $borc) {
            // Her borç için o anki güncel gecikme zammını hesapla
            // (Bu fonksiyonu bir önceki cevaplarımızda oluşturmuştuk)
            $gecikmeZammi = FinansalHelper::hesaplaGecikmeZammi(
                $borc->kalan_borc, 
                $borc->son_odeme_tarihi, 
                $borc->ceza_orani
            );

            $responseBorclar[] = [
                'id' => Security::encrypt($borc->id),
                'borc_adi' => htmlspecialchars($borc->borc_adi),
                'kisi_id' => Security::encrypt($borc->kisi_id), // Kişi ID'sini şifrele
                'son_odeme_tarihi' => Date::dmY($borc->son_odeme_tarihi),
                'anapara' => Helper::formattedMoney($borc->kalan_borc),
                'gecikme_zammi' => Helper::formattedMoney($gecikmeZammi),
                'toplam_borc' => $borc->kalan_borc + $gecikmeZammi // JS tarafında hesaplama için
            ];
        }

        echo json_encode(['status' => 'success', 'data' => $responseBorclar]);

    } catch (Exception $e) {
        http_response_code(400); // Hata durumunda uygun bir HTTP kodu gönder
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
