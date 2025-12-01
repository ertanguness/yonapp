<?php
require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';




use Database\Db;
use App\Helper\Alert;
use App\Helper\Date;
use Model\KasaModel;
use App\Helper\Error;
use App\Helper\Helper;
use App\Services\Gate;
use Model\BloklarModel;
use Model\KisilerModel;
use App\Helper\Security;
use Model\DairelerModel;
use Model\TahsilatModel;
use Model\KasaHareketModel;
use Model\BorclandirmaModel;
use Model\TahsilatOnayModel;
use Model\FinansalRaporModel;
use Model\KisiKredileriModel;
use Model\KisiKrediKullanimModel;
use Model\TahsilatDetayModel;
use App\Helper\FinansalHelper;
use App\Helper\KisiHelper;


use Model\TahsilatHavuzuModel;
use Model\BorclandirmaDetayModel;
use \PhpOffice\PhpSpreadsheet\IOFactory;
use \PhpOffice\PhpSpreadsheet\Style\NumberFormat;

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
$KisiKrediKullanim = new KisiKrediKullanimModel();
$FinansalRapor = new FinansalRaporModel();
$Kasa = new KasaModel();
$KasaHareket = new KasaHareketModel();
$KisiHelper = new KisiHelper();


Security::checkLogin();

$logger = \getLogger();
$db = Db::getInstance();


/* Excel dosyasından toplu ödeme yükleme işlemi */
/**excelden-odeme-yukle */
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
    $kisi_id = 0;

    try {

        /** transaction başlat */
        $db->beginTransaction();

        /**
         * Başarılı eşleşen daire için tahsilat kaydını veritabanına ekler.
         * @param $Tahsilat TahsilatModel öği
         * @param $data Satır verisi (array)
         * @param $daire Eşleşen daire ID'si
         * @return mixed Son eklenen kaydın ID'si
         */
        /**
         * Gelen farklı tarih formatlarını güvenli şekilde MySQL biçimine (Y-m-d H:i) dönüştürür.
         * Desteklenen örnek formatlar: "7.11.2025 20:42", "07.11.2025 20:42:00", Excel serial vb.
         * @param mixed $val
         * @return string|null biçim: 'Y-m-d H:i' veya null (parslanamazsa)
         */


        function kaydetTahsilatOnay($TahsilatOnay, $data, $daire_id)
        {

            $islem_tarihi =  Date::YmdHIS($data[0]);  // İşlem tarihi

            // Tutar artık temizlenmiş durumda gelmeli
            $tutar = is_numeric($data[1]) ? floatval($data[1]) : Helper::formattedMoneyToNumber($data[1]);

            global $logger;
            $logger->info("Tahsilat Onay Kaydı: islem_tarihi: $islem_tarihi, tutar: $tutar, daire_id: $daire_id");


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
            $islem_tarihi =  Date::YmdHis($data[0]);  // İşlem tarihi
            $ham_aciklama = $data[5] ?? '';  // Ham açıklama alanı, varsa kullanılır
            $referans_no = $data[6] ?? '';  // Makbuz no, varsa kullanılır
            // Tutar artık temizlenmiş durumda gelmeli
            $tutar = floatval(str_replace(',', '.', $data[1]));
            $tutar = is_numeric($data[1]) ? floatval($data[1]) : Helper::formattedMoneyToNumber($data[1]);


            return $TahsilatHavuzu->saveWithAttr([
                'id' => 0,
                'islem_tarihi' => $islem_tarihi,
                'site_id' => $_SESSION['site_id'],  // Site ID'si
                'tahsilat_tutari' => $tutar,  // Tutar
                'ham_aciklama' => $ham_aciklama,
                'referans_no' => $referans_no,
                'banka_ref_no' => $referans_no ?? '',
                'aciklama' => $aciklamaEk,  // Ek açıklama veya hata
            ]);
        }

        // Excel dosyasını oku ve satırları işle
        $spreadsheet = IOFactory::load($fileTmpName);
        $sheet = $spreadsheet->getActiveSheet();


        // B sütununu (tutar sütunu) text olarak ayarla - bilimsel gösterimi engelle
        $sheet->getStyle('B:B')->getNumberFormat()->setFormatCode('@');

        $rows = $sheet->toArray();
        foreach ($rows as $i => $data) {
            if ($i == 0)
                continue;  // Başlık satırını atla
            try {



                $daire_id = 0;
                $daireKodu = null;
                $daire_kodu = $data[2] ?? '';  // Daire kodu
                $iyelik_tipi = $data[3] ?? 'Ev Sahibi';  // Ödeyen tipi (Ev Sahibi, Kiracı)
                $aciklama = $data[5];
                // TUTAR GÜVENLİ DÖNÜŞÜMÜ
                $tutarHam = trim($data[1] ?? '0');
                $tutar = round((float) str_replace(',', '.', $tutarHam), 2);
                // Debug için log ekle
                $logger->info("Excel'den okunan tutar: " . $tutar . " (Tip: " . gettype($tutar) . ")");


                // Bilimsel gösterim kontrolü ve temizlik
                if (is_string($tutar)) {
                    // Bilimsel gösterim var mı kontrol et (E+ veya e+ içeriyor mu)
                    if (stripos($tutar, 'E') !== false) {
                        // Bilimsel gösterimden normal sayıya çevir
                        $tutar = sprintf("%.2f", floatval($tutar));
                    }

                    // Türkçe formatlı sayı temizliği
                    $tutar = str_replace([' ', '.'], ['', ''], $tutar); // Binlik ayırıcıları temizle
                    $tutar = str_replace(',', '.', $tutar); // Virgülü noktaya çevir
                }




                // Öncelikle doğrudan daire kodu ile eşleşme dene
                if (!empty($daire_kodu)) {
                    $daire_id = $Daire->DaireId($daire_kodu) ?? 0;
                    $kisi_id = $Kisi->AktifKisiByDaireId($daire_id, $iyelik_tipi)->id ?? 0;
                }
                // Daire kodu yoksa açıklamadan blok/daire bilgisi çıkar
                else if (!empty($aciklama)) {
                    $daireKodu = Helper::extractApartmentInfo($aciklama);
                    if ($daireKodu) {


                        $daire_id = $Daire->DaireId($daireKodu) ?? 0;

                        //Dairedeki tüm kişileri al

                        $kisi_id = 0; // Başlangıçta kişi ID'si 0 olarak ayarla
                        $daire_kisileri = $Kisi->getKisilerByDaireId($daire_id);

                        //Açıklamada kişi adı varsa, o kişiyi bul
                        $kisi_id = Helper::findMatchingPersonInDescription($aciklama, $daire_kisileri);
                    }
                    $logger->info("Açıklamadan daire bilgisi çıkarıldı: " . json_encode(
                        [
                            'daireKodu' => $daireKodu,
                            'daire_id' => $daire_id,
                            'kisi_id' => $kisi_id,
                            'iyelik_tipi' => $iyelik_tipi,
                            'aciklama' => $aciklama,
                            "bulunan_kisi" => $bulunan_kisi ?? null

                        ]
                    ));
                }

                // Eşleşen daire bulunduysa tahsilat kaydet
                if ($daire_id > 0 && !empty($kisi_id)) {
                    $data['kisi_id'] = $kisi_id;  // Kişi ID'sini ekle
                    kaydetTahsilatOnay($TahsilatOnay, $data, $daire_id);
                    $bulunan_daireler[] = $daireKodu ?? $daire_kodu . 'kisi_id: ' . $data['kisi_id'];
                    $successCount++;
                } else {
                    // Eşleşmeyen kayıtları havuza kaydet
                    $aciklamaEk = !empty($daire_kodu)
                        ? 'Daire Kodu eşleşmedi: ' . $daire_kodu
                        : ('Bilgi var ' . ($aciklama ?? ''));
                    kaydetHavuz($TahsilatHavuzu, $data, $aciklamaEk);
                    $eslesmeyen_daireler[] = $daireKodu ?? $daire_kodu;
                    $logger->info("Eşleşmeyen kayıt havuza kaydedildi: " . json_encode($data));
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
        $db->commit();
    } catch (Exception $ex) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }

        $status = "error";
        $message = $ex->getMessage();
    }


    if ($status !== 'error') {
        $status = 'success';
        $message = "Yükleme tamamlandı.<br> Başarılı: $successCount, <br>Hatalı: $failCount";
        if ($failCount > 0) {
            $message .= '. <br>Hatalı satırlar: ' . implode(', ', $failRows);
        }
        if ($eşleşmeyen_kayıtlar > 0) {
            $message .= '. <br>Eşleşmeyen kayıt sayısı: ' . $eşleşmeyen_kayıtlar;
        }
    }

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
        $message = 'Tahsilat onaylama işlemi başarılı. Kalan tutar: ' . Helper::formattedMoney($kalan_tutar);
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



// Tahsilat Kaydet (TAHSİLAT GİR MODALINDAN KAYIT İŞLEMİ)
if ($_POST['action'] == 'tahsilat-kaydet') {
    // 1. Verileri Al (Bu kısım aynı kalabilir)
    $kisi_id = Security::decrypt($_POST['kisi_id']);
    $odenen_toplam_tutar = Helper::formattedMoneyToNumber($_POST['tutar']);
    $kasa_id = Security::decrypt($_POST['kasa_id']);
    $islem_tarihi = Date::YmdHIS($_POST['islem_tarihi']);
    $aciklama = $_POST['tahsilat_aciklama'] ?? '';
    $borcDetayIdsString = $_POST['borc_detay_ids'] ?? '';

    $kalanOdenecekTutar = $odenen_toplam_tutar;
    $borcDetayIds = !empty($borcDetayIdsString) ? array_map([App\Helper\Security::class, 'decrypt'], explode(',', $borcDetayIdsString)) : [];

    $db->beginTransaction();
    try {
        // 2. Ana Tahsilat Kaydını Oluştur
        $tahsilatData = [
            'id' => 0,
            'site_id' => $_SESSION['site_id'], // Site ID'si
            'kisi_id' => $kisi_id,
            'kasa_id' => $kasa_id,
            'tutar' => $odenen_toplam_tutar,
            'islem_tarihi' => $islem_tarihi,
            'aciklama' => $aciklama,
        ];
        $tahsilatId = $Tahsilat->saveWithAttr($tahsilatData);


        //Eğer gelen tahsilat tutar 0'dan küçük ise kredilere kaydet ve döngüden çık
        if ($odenen_toplam_tutar < 0) {
            $krediData = [
                'id' => 0,
                'kisi_id' => $kisi_id,
                "tahsilat_id" => Security::decrypt($tahsilatId),
                'tutar' => $odenen_toplam_tutar,
                'aciklama' => $aciklama,
            ];
            $krediId = $KisiKredi->saveWithAttr($krediData);

            //Tahsilatı kasa hareketi olarak kaydet
            $data = [
                'id' => 0,
                'site_id' => $_SESSION['site_id'], // Site ID'si
                'kasa_id' => $kasa_id,
                'tahsilat_id' => Security::decrypt($tahsilatId), // Tahsilat ID'si
                'kisi_id' => $kisi_id, // Kişi ID'si
                'tutar' => $odenen_toplam_tutar,
                'islem_tarihi' => $islem_tarihi,
                'islem_tipi' =>  'gider', // Tahsilat geliri
                'kategori' => 'İADE', // Kategori aidat
                'kaynak_tablo' => 'kisi_kredi', // Tahsilat kaynağı
                'kaynak_id' => Security::decrypt($krediId), // Tahsilat ID'si
                'kayit_yapan' => $_SESSION['user']->id, // Kayıt yapan kullanıcı ID'si
                'aciklama' => $aciklama ?: 'İade olarak kredi eklendi',
            ];
            $KasaHareket->saveWithAttr($data);


            $db->commit();
            $logger->info("Kişi ID {$kisi_id} için tahsilat tutarı negatif olduğundan kredi olarak eklendi: " . $odenen_toplam_tutar . " TL");
            echo json_encode([
                'status' => 'success',
                'message' => 'Tahsilat kaydı başarıyla oluşturuldu. Kredi olarak eklendi.',
            ]);
            exit;
        }

        // 3. Borçları VIEW ÜZERİNDEN Getir ve Sırala
        $secilenBorclar = [];
        if (!empty($borcDetayIds)) {
            // DİKKAT: Borçları ana tablodan değil, her zaman doğru bakiyeyi veren VIEW'den çekiyoruz.
            $secilenBorclar = $FinansalRapor->findWhereIn('id', $borcDetayIds, 'bitis_tarihi ASC, id ASC');
        }


        // Kredi kullanımı mantığı
        $kullanilmakIstenenTutar = $_POST['kullanilacak_kredi'] ?? 0;
        //$kullanilmakIstenenTutar = Helper::formattedMoneyToNumber($kullanilmakIstenenTutar);

        if ($kullanilmakIstenenTutar > 0) {
            // Kredi tutarını kalan ödenecek tutara ekle
            $kalanOdenecekTutar += $kullanilmakIstenenTutar; // Kredi tutarını ekle



            // ---> DÜZELTME 1: Kredileri en eskiden yeniye doğru sırala (FIFO mantığı) <---
            // Bu, kredilerin her zaman tutarlı bir sırada kullanılmasını sağlar.
            // 'kayit_tarihi' veya 'id' sütununa göre sıralama yapabilirsiniz.
            $krediler = $KisiKredi->findWhere(
                [
                    'kisi_id' => $kisi_id,
                    'kullanildi_mi' => 0, // Kullanılmamış krediler
                ]
            );

            if (empty($krediler)) {

                $logger->warning("Kişi ID {$kisi_id} için kullanılacak kredi talep edildi ancak uygun kredi bulunamadı.");
                // throw new Exception('Kredi bulunamadı veya kullanılmamış kredi yok.');
            } else {

                // ---> DÜZELTME 2: Daha Anlaşılır Değişken İsimleri <---
                $kalanKullanilacakMiktar = $kullanilmakIstenenTutar;

                // Kredilerde döngü ile dön
                foreach ($krediler as $kredi) {
                    // Eğer kullanmak istediğimiz miktarı karşıladıysak döngüden çık.
                    if ($kalanKullanilacakMiktar <= 0) {
                        break;
                    }

                    // Bu krediden ne kadar kullanabiliriz?
                    // Ya kalan miktarın tamamını ya da kredinin tamamını (hangisi daha küçükse).
                    $buKredidenKullanilacak = min($kalanKullanilacakMiktar, $kredi->tutar);

                    if ($buKredidenKullanilacak <= 0) continue;

                    //$logger->info("Kredi ID {$kredi->id} ({$kredi->tutar} TL) üzerinden {$buKredidenKullanilacak} TL kullanılacak.");

                    // ---> DÜZELTME 3: Kredinin Tamamı mı Kullanıldı? <---
                    $yeniKrediDurumu = ($buKredidenKullanilacak >= $kredi->tutar) ? 1 : 0; // Eğer kredinin tamamı kullanıldıysa 1 yap

                    // Kredi kullanım kaydı
                    // Not: Bu işlem, krediyi tamamen 'kullanıldı' olarak işaretlemek yerine
                    // krediden ne kadar kullanıldığını kaydetmeli ve kalanını güncellemelidir.
                    $KisiKrediKullanim->saveWithAttr([
                        'id' => 0,
                        'kredi_id' => $kredi->id,
                        'tahsilat_id' => Security::decrypt($tahsilatId),
                        'kullanilan_tutar' => Helper::formattedMoneyToNumber($kredi->kullanilan_tutar ?? 0) + $buKredidenKullanilacak, // Önceki kullanıma ekle
                        'aciklama' => 'Tahsilat ID ' . Security::decrypt($tahsilatId) . ' için kredi kullanımı',
                    ]);

                    $logger->info("Kredi ID {$kredi->id} için kullanım güncellendi: Kullanıldı mı: {$yeniKrediDurumu}, Kullanılan Tutar: " . ($kredi->kullanilan_tutar + $buKredidenKullanilacak) . " TL");
                    // Kalan kullanılacak miktarı güncelle
                    $kalanKullanilacakMiktar -= $buKredidenKullanilacak;
                }

                if ($kalanKullanilacakMiktar > 0) {
                    $logger->warning("Kişinin toplam kredisi ({$kullanilmakIstenenTutar} TL) talebini karşılamaya yetmedi. Kalan miktar: {$kalanKullanilacakMiktar} TL");
                }
            }
        }

        // 4. Ödemeyi Doğru Mantıkla Dağıt
        foreach ($secilenBorclar as $borc) {
            if ($kalanOdenecekTutar <= 0) break;


            // Öncelik 1: NET Gecikme Zammı Borcunu Kapat
            // $borc->kalan_gecikme_zammi_borcu, VIEW'den gelen ve ödenmesi gereken net tutardır.
            $odenecekGecikmeTutari = min($kalanOdenecekTutar, $borc->hesaplanan_gecikme_zammi);

            if ($odenecekGecikmeTutari > 0) {
                $TahsilatDetay->saveWithAttr([
                    'id' => 0,
                    'tahsilat_id' => Security::decrypt($tahsilatId),
                    'borc_detay_id' => $borc->id,
                    'odenen_tutar' => $odenecekGecikmeTutari,
                    'islem_tarihi' => $_POST['islem_tarihi'],
                    'aciklama' => 'Gecikme zammı ödemesi',
                ]);
                $kalanOdenecekTutar -= $odenecekGecikmeTutari;
            }

            $logger->info("Gecikme zammı ödemesi: {$odenecekGecikmeTutari} TL, kalan ödenecek tutar: {$kalanOdenecekTutar} TL");
            if ($kalanOdenecekTutar <= 0) continue;

            // Öncelik 2: KALAN ANAPARA Borcunu Kapat
            // $borc->kalan_anapara, VIEW'den gelen ve ödenmesi gereken net anapara tutarıdır.
            $odenecekAnaParaTutari = min($kalanOdenecekTutar, $borc->kalan_anapara);

            if ($odenecekAnaParaTutari > 0) {
                $TahsilatDetay->saveWithAttr([
                    'id' => 0,
                    'tahsilat_id' => Security::decrypt($tahsilatId),
                    'borc_detay_id' => $borc->id,
                    'odenen_tutar' => $odenecekAnaParaTutari,
                    'islem_tarihi' => $_POST['islem_tarihi'],
                    'aciklama' => 'Anapara ödemesi',
                ]);
                $kalanOdenecekTutar -= $odenecekAnaParaTutari;
            }
        }

        // 5. Borçlar Kapandıktan Sonra Para Arttıysa Kredi Olarak Kaydet
        if ($kalanOdenecekTutar > 0.009) { // Kuruş farkları için küçük bir tolerans
            $KisiKredi->saveWithAttr([
                'id' => 0,
                'kisi_id' => $kisi_id,
                'tahsilat_id' => Security::decrypt($tahsilatId),
                'tutar' => $kalanOdenecekTutar,
                'aciklama' => 'Tahsilat fazlası alacak kaydı',
            ]);
        }

        //Tahsilatı kasa hareketi olarak kaydet
        $data = [
            'id' => 0,
            'site_id' => $_SESSION['site_id'], // Site ID'si
            'kasa_id' => $kasa_id,
            'tahsilat_id' => Security::decrypt($tahsilatId), // Tahsilat ID'si
            'kisi_id' => $kisi_id, // Kişi ID'si
            'tutar' => $odenen_toplam_tutar,
            'islem_tarihi' => $islem_tarihi,
            'islem_tipi' => $odenen_toplam_tutar > 0 ? 'gelir' : 'gider', // Tahsilat geliri
            'kategori' => 'AİDAT', // Kategori aidat
            'kaynak_tablo' => 'tahsilat', // Tahsilat kaynağı
            'kaynak_id' => Security::decrypt($tahsilatId), // Tahsilat ID'si
            'kayit_yapan' => $_SESSION['user']->id, // Kayıt yapan kullanıcı ID'si
            'aciklama' => $aciklama ?: 'Tahsilat kaydı',
        ];
        $KasaHareket->saveWithAttr($data);

        $db->commit();
        $status = 'success';
        $message = 'Tahsilat başarıyla kaydedildi ve borçlara dağıtıldı.';
    } catch (Exception $ex) {
        $db->rollBack();
        $status = 'error';
        $message = 'İşlem sırasında bir hata oluştu: ' . $ex->getMessage();
    }
    // 6. Son Finansal Durumu Hesapla ve Gönder
    $kisiFinansalDurum = $FinansalRapor->KisiFinansalDurum($kisi_id);
    $rowData = $FinansalRapor->getKisiGuncelBorcOzet($kisi_id);

    echo json_encode([
        'status' => $status,
        'message' => $message,
        "finansalDurum" => [
            'toplam_borc' => Helper::formattedMoney($kisiFinansalDurum->toplam_borc ?? 0),
            'toplam_odeme' => Helper::formattedMoney($kisiFinansalDurum->toplam_odeme ?? 0),
            'bakiye' => Helper::formattedMoney($kisiFinansalDurum->bakiye ?? 0)
        ],
        'rowData' => [
            'data' => $rowData,
            'kalan_anapara' => '<i class="feather-trending-down fw-bold text-danger me-1"></i>' . Helper::formattedMoney($rowData->kalan_anapara ?? 0),
            'hesaplanan_gecikme_zammi' => Helper::formattedMoney($rowData->hesaplanan_gecikme_zammi ?? 0),
            'toplam_kalan_borc' => Helper::formattedMoney($rowData->kalan_anapara ?? 0),
            'kredi_tutari' => Helper::formattedMoney($rowData->kredi_tutari ?? 0),
            'guncel_borc' => Helper::formattedMoney($rowData->guncel_borc ?? 0),

        ],
    ]);
}




//Tahsilat sil(modaldan)
if ($_POST['action'] == 'tahsilat-sil') {
    $id = ($_POST['id']);
    $decryptedId = Security::decrypt($id);
    $tableRow = [];

    Gate::can('tahsilat_ekle_sil');

    try {
        $db->beginTransaction();
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


        //eğer kayıt varsa silme işlemine devam et
        $krediKullanimiVarmi = $KisiKrediKullanim->findWhere(['tahsilat_id' => $decryptedId]);
        if ($krediKullanimiVarmi) {
            //Once ilgili kredi kullanımlarını sil
            $kullanilanKredilerSilindi = $KisiKrediKullanim->softDeleteByColumn('tahsilat_id', $decryptedId);
            $logger->info("Tahsilat ID {$decryptedId} için ilişkili kredi kullanımları silindi.");
        }

        //Eğer Kredi kaydı varsa silme işlemine devam et
        $krediVarmi = $KisiKredi->findWhere(['tahsilat_id' => $decryptedId]);
        if ($krediVarmi) {
            //Once ilgili kredi kayıtlarını sil
            $kisiKredilerSilindi = $KisiKredi->softDeleteByColumn('tahsilat_id', $decryptedId);
            $logger->info("Tahsilat ID {$decryptedId} için ilişkili kredi kayıtları silindi.");
        }


        //Kasa kaydı varsa silme işlemine devam et
        $kasaHareketVarmi = $KasaHareket->findWhere(['tahsilat_id' => $decryptedId]);
        if ($kasaHareketVarmi) {
            //Kasa hareketini sil
            $KasaHareket->softDeleteByColumn('tahsilat_id', $decryptedId);
            $logger->info("Tahsilat ID {$decryptedId} için ilişkili kasa hareketleri silindi.");
        }



        //Tahsilat detayı var mı kontrol et
        $tahsilatDetayVarmi = $TahsilatDetay->findWhere(['tahsilat_id' => $decryptedId]);
        if ($tahsilatDetayVarmi) {
            //Tahsilat detaylarını sil
            $tahsilatDetaySilindi = $TahsilatDetay->softDeleteByColumn('tahsilat_id', $decryptedId);
            $logger->info("Tahsilat ID {$decryptedId} için ilişkili tahsilat detayları silindi.");
        }

        //tahsilat onay kaydı var mı kontrol et
        $tahsilatOnayVarmi = $TahsilatOnay->findWhere(['id' => $tahsilat->tahsilat_onay_id]);
        if ($tahsilatOnayVarmi) {
            //Eğer tahsilat onay kaydı varsa onay durumunu 0 yap
            $tahsilatOnay = $TahsilatOnay->find($tahsilat->tahsilat_onay_id);
            if ($tahsilatOnay) {
                $data = [
                    'id' => $tahsilatOnay->id,
                    'onay_durumu' => 0,
                    'onay_tarihi' => null,
                    'onay_aciklamasi' => null,
                ];
                $TahsilatOnay->saveWithAttr($data);
            }
        }

        // Tahsilat kaydını sil
        $Tahsilat->softDeleteByColumn('id', $decryptedId);
        $logger->info("Tahsilat ID {$decryptedId} kaydı ve ilişkili tüm veriler silindi.");



        //Finansal Durumu Getir
        $finansalDurum = $BorcDetay->KisiFinansalDurum($kisi_id);
        $borc = Helper::formattedMoney($finansalDurum->toplam_borc ?? 0);
        $odeme = Helper::formattedMoney($finansalDurum->toplam_odeme ?? 0);
        $bakiye = Helper::formattedMoney($finansalDurum->bakiye ?? 0);

        $db->commit();

        $status = 'success';
        $message = 'Tahsilat kaydı başarıyla silindi. ';
        $rowData = $FinansalRapor->getKisiGuncelBorcOzet($kisi_id);
    } catch (Exception $e) {
        $db->rollBack();
        $status = 'error';
        $message = 'İşlem sırasında bir hata oluştu: ' . $e->getMessage();
    }

    $res = [
        'status' => $status,
        'message' => $message,
        'borc' => $borc ?? '0,00',
        'odeme' => $odeme ?? '0,00',
        'bakiye' => $bakiye ?? '0,00',
        'rowData' => [
            'data' => $rowData,
            'kalan_anapara' => '<i class="feather-trending-down fw-bold text-danger me-1"></i>' . Helper::formattedMoney($rowData->kalan_anapara ?? 0),
            'hesaplanan_gecikme_zammi' => Helper::formattedMoney($rowData->hesaplanan_gecikme_zammi ?? 0),
            'toplam_kalan_borc' => Helper::formattedMoney($rowData->toplam_kalan_borc ?? 0),
            'kredi_tutari' => Helper::formattedMoney($rowData->kredi_tutari ?? 0),
            'guncel_borc' =>  Helper::formattedMoney($rowData->guncel_borc ?? 0),
        ]
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
        $odenmemisBorclar = $FinansalRapor->getKisiGuncelBorclar($kisiId);
        // echo json_encode(['status' => 'success', 'message' => 'Kişi ID alındı.' . $kisiId ,
        //          'data' => $odenmemisBorclar]);
        // exit();
        $responseBorclar = [];
        foreach ($odenmemisBorclar as $borc) {
            // Her borç için o anki güncel gecikme zammını hesapla
            // (Bu fonksiyonu bir önceki cevaplarımızda oluşturmuştuk)
            $gecikmeZammi = FinansalHelper::hesaplaGecikmeZammi(
                $borc->kalan_anapara,
                $borc->bitis_tarihi,
                $borc->ceza_orani
            );

            $responseBorclar[] = [
                'id' => Security::encrypt($borc->id),
                'borc_adi' => htmlspecialchars($borc->borc_adi),
                "aciklama" => htmlspecialchars($borc->aciklama),
                'kisi_id' => Security::encrypt($borc->kisi_id), // Kişi ID'sini şifrele
                'son_odeme_tarihi' => Date::dmY($borc->bitis_tarihi),
                'anapara' => Helper::formattedMoney($borc->kalan_anapara),
                'gecikme_zammi' => Helper::formattedMoney($gecikmeZammi),
                'toplam_borc' => $borc->kalan_anapara + $gecikmeZammi // JS tarafında hesaplama için
            ];
        }

        echo json_encode(['status' => 'success', 'data' => $responseBorclar]);
    } catch (Exception $e) {
        http_response_code(400); // Hata durumunda uygun bir HTTP kodu gönder
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}


/** Gelen Daire id'sine göre dairenin kişilerini döndürür
 * @param int $daire_id
 * @return void
 */

if ($_POST['action'] == 'get_daire_kisileri') {
    header('Content-Type: application/json');

    try {
        $daire_id = ($_POST['daire_id']);
        $kisiList = $Kisi->getKisilerByDaireId($daire_id);

        $response = [];
        foreach ($kisiList as $kisi) {
            $response[] = [
                'id' => Security::encrypt($kisi->id),
                'adi_soyadi' => htmlspecialchars($kisi->adi_soyadi),
                'uyelik_tipi' => $kisi->uyelik_tipi,
            ];
        }

        echo json_encode(['status' => 'success', 'kisiler' => $response]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}


//Yapılan borçlandırmayı detay modalindan sil butonu ile silmek için
if ($_POST['action'] == 'borc_sil') { {

        $id = Security::decrypt($_POST['id']);
        Gate::can('borclandirma_ekle_sil');

        try {
            $db->beginTransaction();

            //bu id ile tahsilat_detay tablosunda kayıt var mı kontrol et
            $tahsilatDetay = $TahsilatDetay->findWhere(['borc_detay_id' => $id, 'silinme_tarihi' => null]);
            //Tahsilat detayını sil
            if ($tahsilatDetay) {

                $TahsilatDetay->softDeleteByColumn('borc_detay_id', $id);

                //Borçlandırma silindiğinde ilgili tahsilat tutarını kredi olarak ekle

                //Birden fazla tahsilat detayı olabilir, hepsi için krediyi toplam olarak ekle

                foreach ($tahsilatDetay as $tDetay) {
                    $kisi_id = $BorcDetay->find($tDetay->borc_detay_id)->kisi_id ?? 0;

                    $data = [
                        'id' => 0,
                        'kisi_id' => $kisi_id,
                        'tahsilat_id' => $tDetay->tahsilat_id,
                        'tutar' => $tDetay->odenen_tutar,
                        'aciklama' => 'Borçlandırma silindiği için tahsil edilen tutar kredi olarak eklendi. Borç Detay ID: ' . $id,
                    ];

                    $logger->info("Borç Detay ID {$id} silindi. İlgili tahsilat tutarı kredi olarak eklendi: " . ($tDetay->odenen_tutar) . " TL");

                    $KisiKredi->saveWithAttr($data);
                }
            }


            $borcDetay = $BorcDetay->find($id);
            if (!$borcDetay) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Borçlandırma detayı bulunamadı.'
                ]);
                exit;
            }

            // Borç detayını sil
            $BorcDetay->softDeleteByColumn('id', $id);

            //Kişinin güncel finansal durumunu al
            $finansalDurum = $FinansalRapor->KisiFinansalDurum($borcDetay->kisi_id);

            $db->commit();
            $status = 'success';
            $message = 'Borçlandırma detayı başarıyla silindi.';
        } catch (Exception $e) {
            $db->rollBack();
            $status = 'error';
            $message = 'İşlem sırasında bir hata oluştu: ' . $e->getMessage();
        }

        echo json_encode([
            'status' => $status,
            'message' => $message,
            'finansalDurum' => [
                'toplam_borc' => Helper::formattedMoney($finansalDurum->toplam_borc ?? 0),
                'toplam_odeme' => Helper::formattedMoney($finansalDurum->toplam_odeme ?? 0),
                'bakiye' => Helper::formattedMoney($finansalDurum->bakiye ?? 0)
            ]
        ]);
    }
}


/**Eşleşmeyen havuzdan Eşleşen havuza gönderme işlemi*/
if ($_POST['action'] == 'eslesen_havuza_gonder') {
    $id = Security::decrypt($_POST['id']);
    $kisi_id = Security::decrypt($_POST['kisi_id']);
    $islenen_tutar = Helper::formattedMoneyToNumber($_POST['islenen_tutar'] ?? 0);

    //Daire id'yi kisi bilgilerinden getir
    $kisi = $Kisi->find($kisi_id);
    $daire_id = $kisi->daire_id ?? 0;

    try {
        $db->beginTransaction();
        $havuzKaydi = $TahsilatHavuzu->find($id);
        if (!$havuzKaydi) {
            throw new Exception('Havuz kaydı bulunamadı.');
        }

        //Daire id kontrolü
        if ($daire_id <= 0) {
            throw new Exception('Kişiye ait geçerli bir daire bulunamadı. Daire bilgisi olmadan eşleşen havuza aktarılamaz.');
        }

        // Havuz kaydını onay tablosuna taşı
        $data = [
            'id' => 0,
            'site_id' => $_SESSION['site_id'],
            'kisi_id' => $kisi_id,
            'islem_tarihi' => $havuzKaydi->islem_tarihi,
            'daire_id' => $daire_id,
            'tutar' => $islenen_tutar,
            'tahsilat_tipi' => "Eşleşmeyen havuzundan aktarıldı.",
            'aciklama' =>  $havuzKaydi->ham_aciklama
        ];
        $lastInsertId = $TahsilatOnay->saveWithAttr($data);

        //tekrar havuz kaydını bul
        $havuzKaydi = $TahsilatHavuzu->find($id);


        //kalan tutari al
        $toplam_islenen =  $islenen_tutar + $havuzKaydi->islenen_tutar;
        $kalan_tutar = $havuzKaydi->tahsilat_tutari - $toplam_islenen;


        //Eğer işlenecek tutar tahsil edilen tutara eşitse havuz kaydını sil
        if ($kalan_tutar == 0) {
            // Havuz kaydını sil
            $TahsilatHavuzu->delete($_POST['id']);
        } else {
            // Havuz kaydını güncelle
            $data = [
                'id' => $havuzKaydi->id,
                'islenen_tutar' => $toplam_islenen,
            ];
            $TahsilatHavuzu->saveWithAttr($data);
        }


        $db->commit();
        $status = 'success';
        $message = 'Kayıt başarıyla onay tablosuna taşındı.';
    } catch (Exception $e) {
        $db->rollBack();
        $status = 'error';
        $message = 'İşlem sırasında bir hata oluştu: ' . $e->getMessage();
    }

    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data,
        'islenen_tutar_formatted' => Helper::formattedMoney($toplam_islenen ?? 0),
        'islenen_tutar' => Helper::formattedMoneyToNumber($toplam_islenen ?? 0),
        'kalan_tutar' => Helper::formattedMoneyToNumber($kalan_tutar ?? 0)
    ]);
}

/*Eşleşmeyen tahsilat sayfasında tahsilatı silmek için */
if ($_POST['action'] == 'eslesmeyen_odeme_sil') {
    $id = Security::decrypt($_POST['id']);

    try {
        //Gate::can('eslesmeyen_odeme_sil');

        $db->beginTransaction();



        $havuzKaydi = $TahsilatHavuzu->find($id);
        if (!$havuzKaydi) {
            throw new Exception('Havuz kaydı bulunamadı.');
        }

        // Havuz kaydını sil
        $TahsilatHavuzu->delete($_POST['id']);

        $db->commit();
        $status = 'success';
        $message = 'Kayıt başarıyla silindi.';
    } catch (Exception $e) {
        $db->rollBack();
        $status = 'error';
        $message = 'İşlem sırasında bir hata oluştu: ' . $e->getMessage();
    }

    echo json_encode([
        'status' => $status,
        'message' => $message
    ]);
}


/* Yapılan ödemeyi kasaya aktarma işlemi Gelir veya Gider olarak */
if ($_POST['action'] == 'kasaya_aktar') {
    $id = Security::decrypt($_POST['id']);
    $kasa_id = $Kasa->varsayilanKasa()->id;


    try {
        //Gate::can('eslesmeyen_odeme_sil');

        $db->beginTransaction();

        $havuzKaydi = $TahsilatHavuzu->find($id);
        if (!$havuzKaydi) {
            throw new Exception('Havuz kaydı bulunamadı.');
        }

        $islem_tipi = $havuzKaydi->tahsilat_tutari >= 0 ? 'gelir' : 'gider';

        // Tahsilatı kasa hareketi olarak kaydet
        $data = [
            'id' => 0,
            'site_id' => $_SESSION['site_id'], // Site ID'si
            'kasa_id' => $kasa_id,
            'tahsilat_id' => 0, // Eşleşmeyen tahsilat olduğu için null
            'kisi_id' => 0, // Kişi ID'si yok
            'tutar' => $havuzKaydi->tahsilat_tutari,
            'islem_tarihi' => $havuzKaydi->islem_tarihi,
            'kategori' => $_POST['kategori'], // Özel kategori
            'islem_tipi' => $islem_tipi,
            'kaynak_tablo' => 'tahsilat_havuzu', // Kaynak tablo
            'kaynak_id' => $havuzKaydi->id, // Havuz kaydı ID'si
            'kayit_yapan' => $_SESSION['user']->id, // Kayıt yapan kullanıcı ID'si
            'guncellenebilir' => 1,
            'aciklama' => $havuzKaydi->ham_aciklama ?: 'Kasaya aktarım',
        ];
        $KasaHareket->saveWithAttr($data);

        //Havuzdaki kayda aciklama yaz
        $data = [
            'id' => $id,
            'aciklama' => 'Kasaya ' . $islem_tipi . ' olarak aktarıldı: ' . $havuzKaydi->ham_aciklama . ' | ',
        ];
        $TahsilatHavuzu->saveWithAttr($data);

        // Havuz kaydını sil
        $TahsilatHavuzu->softDelete($id);
        $logger->info("Eşleşmeyen tahsilat ID {$id} kasaya aktarıldı ve havuz kaydı silindi.");

        $db->commit();
        $status = 'success';
        $message = 'Ödeme başarıyla kasaya aktarıldı.';
    } catch (Exception $e) {
        $db->rollBack();
        $status = 'error';
        $message = 'İşlem sırasında bir hata oluştu: ' . $e->getMessage();
    }

    echo json_encode([
        'status' => $status,
        'message' => $message
    ]);
}

/** Eşleşmeyen tahsilat sayfasında tahsilatı eşleştirmek için kişi arama işlemi */
if (isset($_POST['action']) && $_POST['action'] == 'kisi_ara') {
    // Güvenlik kontrollerinizi burada yapın (oturum kontrolü vb.)

    // Select2 tarafından gönderilen arama terimini al
    $searchTerm = $_POST['term'] ?? '';
    $site_id = $_SESSION['site_id'] ?? 0;

    // Kişileri aramak için bir metodunuz olduğunu varsayalım.
    // Bu metodun arama terimine göre filtrelenmiş sonuçlar döndürmesi gerekir.
    $kisiler = $KisiHelper->searchKisiler($site_id, $searchTerm);

    $results = [];
    foreach ($kisiler as $kisi) {
        // Select2'nin beklediği format: { id: 'deger', text: 'gosterilecek_metin' }
        $results[] = [
            'id'   => Security::encrypt($kisi['id']), // veya $kisi->id
            'text' => $kisi['daire_kodu'] . ' | ' . $kisi['adi_soyadi']  // Örnek gösterim
        ];
    }

    // JSON olarak sonuçları döndür
    header('Content-Type: application/json');
    echo json_encode(['results' => $results]);
    exit;
}


/* Borc detay modalinden borç ekle modali açılarak borç ekleme işlemi */
if ($_POST['action'] == 'borc_ekle') {
    $id = Security::decrypt($_POST['borc_detay_id']);
    $kisi_id = Security::decrypt($_POST['kisi_id']);
    $borclandirma_id = $_POST['borclandirmalar'];
    $borclandirma_tarihi = Date::YmdHIS($_POST['borc_islem_tarihi']);
    $aciklama = $_POST['borc_aciklama'] ?? '';
    $tutar = Helper::formattedMoneyToNumber($_POST['borc_tutar'] ?? 0);

    $borc = $Borc->findWithDueName($borclandirma_id);

    $kisi = $Kisi->find($kisi_id);


    if (!$borc) {
        Alert::error('Lütfen önce bir borçlandırma seçiniz.');
    }

    if (empty($kisi_id)) {
        Alert::error('Lütfen önce bir kişi seçiniz.');
    }

    try {
        $db->beginTransaction();

        $data = [
            'id' => $id,
            'borclandirma_id' => $borclandirma_id,
            'borclandirma_tarihi' => $borclandirma_tarihi,
            'borc_adi' => $borc->borc_adi,
            'aciklama' => $aciklama ?: $borc->aciklama,
            'baslangic_tarihi' => $borc->baslangic_tarihi,
            'bitis_tarihi' => $borc->bitis_tarihi,
            'son_odeme_tarihi' => $borc->bitis_tarihi,
            'tutar' => $tutar,
            'ceza_orani' => $borc->ceza_orani,
            'kisi_id' => $kisi_id,
            'daire_id' => $kisi->daire_id,

        ];


        $lastInsertId = $BorcDetay->saveWithAttr($data);


        $db->commit();
        $status = "success";
        $message = "Borçlandırma detayı başarıyla eklendi.";
    } catch (PDOException $ex) {
        $db->rollBack();
        $status = "error";
        $message = $ex->getMessage();
    }

    $res = [
        'status' => $status,
        'message' => $message
    ];
    echo json_encode($res);
}
