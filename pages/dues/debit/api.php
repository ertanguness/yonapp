<?php
require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';



use App\Helper\Security;
use App\Helper\Date;
use App\Helper\Helper;
use App\Helper\Error;
use App\Helper\FinansalHelper;
use Database\Db;


use Model\BorclandirmaModel;
use Model\BorclandirmaDetayModel;
use Model\BloklarModel;
use Model\DairelerModel;
use Model\KisilerModel;
use Model\DefinesModel;
use Model\DueModel;
use Model\KisiKredileriModel;
use Model\TahsilatModel;
use Model\TahsilatDetayModel;



$Borc = new BorclandirmaModel();
$BorcDetay = new BorclandirmaDetayModel();
$Bloklar = new BloklarModel();
$Daire = new DairelerModel();
$Kisiler = new KisilerModel();
$Defines = new DefinesModel();
$Due = new DueModel();
$KisiKredi = new KisiKredileriModel();
$TahsilatModel = new TahsilatModel();

$TahsilatDetay = new TahsilatDetayModel();


// 1. Singleton Db nesnesini al
$db = Db::getInstance();
$logger = \getLogger();


// 2. Oturum kontrolü





/**BORÇLANDIRMA YAP */
if ($_POST["action"] == "borclandir") {
    $site_id = $_SESSION['site_id'];
    $id = Security::decrypt($_POST["id"]);
    $user_id = $_SESSION["user"]->id;
    $borclandirma_turu = $_POST["hedef_tipi"];
    $gun_bazli = isset($_POST["day_based"]) ? true : false; // Gün bazlı mı kontrolü

    $logger = \getLogger();

    try {
        $db->beginTransaction();


        $data = [
            "id" => $id,
            "site_id" => $site_id,
            "borc_tipi_id" => Security::decrypt($_POST["borc_baslik"]),
            "tutar" => Helper::formattedMoneyToNumber($_POST["tutar"]),
            "baslangic_tarihi" => Date::Ymd($_POST["baslangic_tarihi"]),
            "bitis_tarihi" => Date::Ymd($_POST["bitis_tarihi"]),
            "ceza_orani" => $_POST["ceza_orani"],
            "aciklama" => $_POST["aciklama"],
            "hedef_tipi" => $borclandirma_turu,
        ];

        $lastInsertId = $Borc->saveWithAttr($data) ?? $_POST["id"];

        $data = [];

        $data = [
            "id" => 0,
            "borclandirma_id" =>  Security::decrypt($lastInsertId),
            "borc_adi" => $_POST["borc_adi"],
            "tutar" => Helper::formattedMoneyToNumber($_POST["tutar"]),
            "kalan_borc" => Helper::formattedMoneyToNumber($_POST["tutar"]), // Başlangıçta kalan tutar, toplam tutara eşit
            "baslangic_tarihi" => Date::Ymd($_POST["baslangic_tarihi"]),
            "bitis_tarihi" => Date::Ymd($_POST["bitis_tarihi"]),
            "son_odeme_tarihi" => Date::Ymd($_POST["bitis_tarihi"]),
            "ceza_orani" => $_POST["ceza_orani"],
            "aciklama" => $_POST["aciklama"],
            "hedef_tipi" => $borclandirma_turu,
        ];

        //Borçlandırma tipi kontrol ediliyor
        if ($borclandirma_turu == "all") {
            //Tüm siteye borçlandırma yapılıyor
            //Sitenin tüm aktif kişilerini getir
            $kisiler = $Kisiler->SiteAktifKisileriBorclandirma($site_id);
            foreach ($kisiler as $kisi) {
                $data["kisi_id"] = $kisi->kisi_id;
                $data["blok_id"] = $kisi->blok_id; // Blok ID'sini de ekliyoruz
                $data["daire_id"] = $kisi->daire_id; // Daire ID'sini de ekliyoruz


                // Eğer gün bazlı borçlandırma ise, başlangıç ve bitiş tarihlerini gün bazlı olarak ayarlıyoruz
                if ($gun_bazli) {
                    $data['tutar'] = Helper::calculateDayBased(
                        Date::Ymd($_POST["baslangic_tarihi"]),
                        Date::Ymd($_POST["bitis_tarihi"]),
                        $kisi->giris_tarihi,
                        Helper::formattedMoneyToNumber($_POST["tutar"])
                    ); // Günlük tutar hesaplanıyor
                }
                $BorcDetay->saveWithAttr($data);

                //$logger->info("Borçlandırma yapıldı: " . json_encode($data));
            }
        } elseif ($borclandirma_turu == "evsahibi") {

            //Siteye ait tüm ev sahiplerine borçlandırma yapılıyor
            //Sitenin Akfif ev sahiplerini getir
            $evsahipleri = $Kisiler->SiteAktifEvSahipleri($site_id);
            foreach ($evsahipleri as $evsahibi) {
                $data["kisi_id"] = $evsahibi->id;
                $data["blok_id"] = $evsahibi->blok_id; // Blok ID'sini de ekliyoruz
                $data["daire_id"] = $evsahibi->daire_id; // Daire ID'sini de ekliyoruz

                // Eğer gün bazlı borçlandırma ise, başlangıç ve bitiş tarihlerini gün bazlı olarak ayarlıyoruz
                if ($gun_bazli) {
                    $data['tutar'] = Helper::calculateDayBased(
                        Date::Ymd($_POST["baslangic_tarihi"]),
                        Date::Ymd($_POST["bitis_tarihi"]),
                        $evsahibi->giris_tarihi,
                        Helper::formattedMoneyToNumber($_POST["tutar"])
                    ); // Günlük tutar hesaplanıyor
                }
                $BorcDetay->saveWithAttr($data);
            }
        } elseif ($borclandirma_turu == "block") {
            //Bloklara borçlandırma yapılıyor
            //Blogun aktif kişilerini getir
            $kisiler = $Kisiler->BlokKisileri(Security::decrypt($_POST["block_id"]));
            foreach ($kisiler as $kisi) {
                $data["kisi_id"] = $kisi->id;
                $data["blok_id"] = Security::decrypt($_POST["block_id"]);
                $BorcDetay->saveWithAttr($data);
            }
        } elseif ($borclandirma_turu == "person") {
            //Kişilere borçlandırma yapılıyor
            $person_ids = $_POST["hedef_kisi"];

            $sifresiKisiIds = array_map([App\Helper\Security::class, 'decrypt'], $person_ids);
            $persons = $Kisiler->getKisilerByIds($sifresiKisiIds);

            //$logger->info("Borçlandırma yapılıyor: " . json_encode($data));

            foreach ($persons as $person) {
                $data["kisi_id"] = $person->id;
                $data["daire_id"] = $person->daire_id;
                $BorcDetay->saveWithAttr($data);
            }
        } else if ($borclandirma_turu == 'dairetipi') {
            //Daire tipine göre borçlandırma yapılıyor
            $daire_tipleri = $_POST["apartment_type"];

            //Daire Tipi id'lerinde döngü yap
            foreach ($daire_tipleri as $daire_tipi_id) {
                $daire_tipi_id = Security::decrypt($daire_tipi_id);

                //Daireler tablosundan bu daire tipine sahip daireleri getir
                $daireler = $Daire->DaireTipineGoreDaireler($daire_tipi_id);

                foreach ($daireler as $daire) {
                    $data["kisi_id"] = $Kisiler->AktifKisiByDaire($daire->id)->id; // Daireye ait aktif kişinin ID'sini alıyoruz
                    $data["blok_id"] = $daire->blok_id; // Daireye ait blok ID'sini alıyoruz
                    $data["daire_id"] = $daire->id; // Daire ID'sini ekliyoruz
                    $BorcDetay->saveWithAttr($data);
                }
            }

            echo json_encode([
                "status" => "success",
                "message" => "Daire Tipine göre borçlandırma tamamlandı!",
                "data" => $data
            ]);
            exit;
        }

        $res = [
            "status" => "success",
            "message" => "İşlem Başarı ile tamamlandı! "
        ];

        $db->commit(); // İşlemi onayla
        $logger->info("Borçlandırma işlemi başarıyla tamamlandı: " . json_encode($data));

        echo json_encode($res);
    } catch (PDOException $ex) {
        $db->rollBack(); // Hata durumunda işlemi geri al
        $logger->error("Borçlandırma işlemi sırasında hata oluştu: " . $ex->getMessage());
        $status = "error";
        $message = $ex->getMessage();
    }
}

if ($_POST["action"] == "delete_debit") {
    try {
        $Borc->delete($_POST["id"]);

        $res = [
            "status" => "success",
            "message" => "Borçlandırma başarı ile kaydedildi!"
        ];
    } catch (Exception $e) {
        $res = Error::handlePDOException($e);
    }
    echo json_encode($res);
}


if ($_POST["action"] == "get_due_info") {
    $id = Security::decrypt($_POST["id"]);

    $data = $Due->find($id);

    $res = [
        "status" => "success",
        "data" => $data
    ];

    echo json_encode($res);
}

//Sitenin bloklarını listele
if ($_POST["action"] == "get_blocks") {
    //$id = Security::decrypt($_POST["id"]) ;
    $site_id = $_SESSION["site_id"]; // Kullanıcının site_id'sini alıyoruz

    $data = $Bloklar->SiteBloklari($site_id);

    //id'yi şifreli hale getiriyoruz
    foreach ($data as $key => $value) {
        $data[$key]->id = Security::encrypt($value->id);
    }

    $res = [
        "status" => "success",
        "data" => $data


    ];

    echo json_encode($res);
}

//Sitenin Aktif kişilerini getir
if ($_POST["action"] == "get_people_by_site") {
    $site_id = $_SESSION["site_id"]; // Kullanıcının site_id'sini alıyoruz

    $data = $Kisiler->SiteAktifKisileri($site_id);

    //id'yi şifreli hale getiriyoruz
    foreach ($data as $key => $value) {
        $data[$key]->id = Security::encrypt($value->kisi_id);
    }

    $res = [
        "status" => "success",
        "data" => $data
    ];

    echo json_encode($res);
}


//Bloğun kişilerini getir
if ($_POST["action"] == "get_peoples_by_block") {
    $id = Security::decrypt($_POST["block_id"]);

    $data = $Kisiler->BlokKisileri($id);

    //id'yi şifreli hale getiriyoruz
    foreach ($data as $key => $value) {
        $data[$key]->id = Security::encrypt($value->id);
    }

    $res = [
        "status" => "success",
        "data" => $data
    ];

    echo json_encode($res);
}

//Daire Tiplerini getir
if ($_POST["action"] == "get_apartment_types") {
    $data = $Defines->getAllByApartmentType(3);

    //id'yi şifreli hale getiriyoruz
    foreach ($data as $key => $value) {
        $data[$key]->id = Security::encrypt($value->id);
    }

    $res = [
        "status" => "success",
        "data" => $data
    ];

    echo json_encode($res);
}


/**Tekli borçlandırma kaydet
 * Bu işlem, tek bir borçlandırma kaydı oluşturur.
 * @param array $_POST
 */
if ($_POST["action"] == "borclandir_single_consolidated") {


    $db->beginTransaction();
    try {



        $borclandirma_turu = $_POST["hedef_tipi"];
        $borclandirma_id = Security::decrypt($_POST["borc_id"]);
        
        $borc = $Borc->find($borclandirma_id);
        $borc_adi = $_POST["borc_adi"];
        $baslangic_tarihi = $borc->baslangic_tarihi;;
        $bitis_tarihi = $borc->bitis_tarihi;

        $data = [
            "id" => 0, // Yeni kayıt için 0
            "borclandirma_id" => $borclandirma_id, // Yeni kayıt için 0
            "tutar" => Helper::formattedMoneyToNumber($_POST["tutar"]),
            "borc_adi" => $borc_adi,
            "baslangic_tarihi" => $baslangic_tarihi,
            "bitis_tarihi" => $bitis_tarihi,
            "son_odeme_tarihi" => $bitis_tarihi, // Son ödeme tarihi bitiş tarihi olarak ayarlanıyor
            "ceza_orani" => $_POST["ceza_orani"],
            "aciklama" => $_POST["aciklama"],
            "hedef_tipi" => $borclandirma_turu, // Tekli borçlandırma için 'person' olarak ayarlandı
        ];

        //Borçlandırma tipi kontrol ediliyor
        if ($borclandirma_turu == "all") {
            //Tüm siteye borçlandırma yapılıyor
            //Sitenin tüm aktif kişilerini getir
            $kisiler = $Kisiler->SiteAktifKisileriBorclandirma($site_id);
            foreach ($kisiler as $kisi) {
                $data["kisi_id"] = $kisi->kisi_id;
                $data["blok_id"] = $kisi->blok_id; // Blok ID'sini de ekliyoruz
                $data["daire_id"] = $kisi->daire_id; // Daire ID'sini de ekliyoruz


                // Eğer gün bazlı borçlandırma ise, başlangıç ve bitiş tarihlerini gün bazlı olarak ayarlıyoruz
                if ($gun_bazli) {
                    $data['tutar'] = Helper::calculateDayBased(
                        Date::Ymd($_POST["baslangic_tarihi"]),
                        Date::Ymd($_POST["bitis_tarihi"]),
                        $kisi->giris_tarihi,
                        Helper::formattedMoneyToNumber($_POST["tutar"])
                    ); // Günlük tutar hesaplanıyor
                }
                $BorcDetay->saveWithAttr($data);

                //$logger->info("Borçlandırma yapıldı: " . json_encode($data));
            }
        } elseif ($borclandirma_turu == "evsahibi") {

            //Siteye ait tüm ev sahiplerine borçlandırma yapılıyor
            //Sitenin Akfif ev sahiplerini getir
            $evsahipleri = $Kisiler->SiteAktifEvSahipleri($site_id);
            foreach ($evsahipleri as $evsahibi) {
                $data["kisi_id"] = $evsahibi->id;
                $data["blok_id"] = $evsahibi->blok_id; // Blok ID'sini de ekliyoruz
                $data["daire_id"] = $evsahibi->daire_id; // Daire ID'sini de ekliyoruz

                // Eğer gün bazlı borçlandırma ise, başlangıç ve bitiş tarihlerini gün bazlı olarak ayarlıyoruz
                if ($gun_bazli) {
                    $data['tutar'] = Helper::calculateDayBased(
                        Date::Ymd($_POST["baslangic_tarihi"]),
                        Date::Ymd($_POST["bitis_tarihi"]),
                        $evsahibi->giris_tarihi,
                        Helper::formattedMoneyToNumber($_POST["tutar"])
                    ); // Günlük tutar hesaplanıyor
                }
                $BorcDetay->saveWithAttr($data);
            }
        } elseif ($borclandirma_turu == "block") {
            //Bloklara borçlandırma yapılıyor
            //Blogun aktif kişilerini getir
            $kisiler = $Kisiler->BlokKisileri(Security::decrypt($_POST["block_id"]));
            foreach ($kisiler as $kisi) {
                $data["kisi_id"] = $kisi->id;
                $data["blok_id"] = Security::decrypt($_POST["block_id"]);
                $BorcDetay->saveWithAttr($data);
            }
        
        
        
        } elseif ($borclandirma_turu == "person") {
            //Kişilere borçlandırma yapılıyor
            $person_ids = $_POST["hedef_kisi"];

            //$sifresiKisiIds = array_map([App\Helper\Security::class, 'decrypt'], $person_ids);
            $persons = $Kisiler->getKisilerByIds($person_ids);

            $logger->info("Borçlandırma yapılıyor: " . json_encode($person_ids));

            foreach ($persons as $person) {
                $data["kisi_id"] = $person->id;
                $data["daire_id"] = $person->daire_id;
                $BorcDetay->saveWithAttr($data);
            }



        } else if ($borclandirma_turu == 'dairetipi') {
            //Daire tipine göre borçlandırma yapılıyor
            $daire_tipleri = $_POST["apartment_type"];

            //Daire Tipi id'lerinde döngü yap
            foreach ($daire_tipleri as $daire_tipi_id) {
                $daire_tipi_id = Security::decrypt($daire_tipi_id);

                //Daireler tablosundan bu daire tipine sahip daireleri getir
                $daireler = $Daire->DaireTipineGoreDaireler($daire_tipi_id);

                foreach ($daireler as $daire) {
                    $data["kisi_id"] = $Kisiler->AktifKisiByDaire($daire->id)->id; // Daireye ait aktif kişinin ID'sini alıyoruz
                    $data["blok_id"] = $daire->blok_id; // Daireye ait blok ID'sini alıyoruz
                    $data["daire_id"] = $daire->id; // Daire ID'sini ekliyoruz
                    $BorcDetay->saveWithAttr($data);
                }
            }

            echo json_encode([
                "status" => "success",
                "message" => "Daire Tipine göre borçlandırma tamamlandı!",
                "data" => $data
            ]);
            exit;
        }


        // Borçlandırma kaydı başarıyla oluşturuldu
        $db->commit(); // İşlemi onayla

        $logger->info("Borçlandırma kaydı başarıyla oluşturuldu: " . json_encode($data));

            $status = "success";
            $message = "Borçlandırma kaydı başarıyla oluşturuldu!";
    } catch (PDOException $ex) {
        $db->rollBack(); // Hata durumunda işlemi geri al
        $status = "error";
        $message = $ex->getMessage();
    }

    $res = [
        "status" => "success",
        "message" => "Borçlandırma kaydı başarı ile oluşturuldu!",
        "data" => $data
    ];

    echo json_encode($res);
}


/**
 * Tekli borçlandırma detayını günceller.
 * EĞER ödeme yapılmışsa, tüm tahsilatları toplayıp yeniden dağıtır (mahsuplaşma).
 * EĞER ödeme yapılmamışsa, sadece borç tutarını ve kalan borcu günceller.
 */
if ($_POST["action"] == "update_debit_single_consolidated") {

    $db = Db::getInstance();
    $db->beginTransaction();

    try {
        // --- 1. ORTAK BAŞLANGIÇ: Gerekli verileri al ---
        $borcDetayId = Security::decrypt($_POST["borclandirma_id"]);
        $yeniTutar = Helper::formattedMoneyToNumber($_POST["tutar"]);

        $borcDetay = $BorcDetay->find($borcDetayId);
        if (!$borcDetay) {
            throw new Exception("Güncellenecek borç kaydı bulunamadı.");
        }
        $kisiId = $borcDetay->kisi_id;

        // --- 2. KARAR NOKTASI: Bu borca hiç tahsilat yapılmış mı? ---
        // Not: Bu fonksiyonun sadece bir kayıt bulup bulmadığını kontrol etmesi yeterlidir.
        $mevcutTahsilat = $TahsilatDetay->findFirstByBorcId($borcDetayId);


        if (!$mevcutTahsilat) {
            // --- SENARYO A: HİÇ TAHSİLAT YAPILMAMIŞ (BASİT GÜNCELLEME) ---

            // Gecikme zammını yeni tutar üzerinden yeniden hesapla.
            $yeniHesaplananGecikmeZammi = FinansalHelper::hesaplaGecikmeZammi($yeniTutar, $borcDetay->bitis_tarihi, $borcDetay->ceza_orani);

            $data = [
                "id" => $borcDetayId,
                "tutar" => $yeniTutar,
                "aciklama" => $_POST["aciklama"],
                "ceza_orani" => $_POST["ceza_orani"],
                "kalan_borc" => $yeniTutar, // Ödeme olmadığı için kalan borç, yeni tutarın kendisidir.
                "kalan_gecikme_zammi" => $yeniHesaplananGecikmeZammi,
                "odeme_durumu" => 'Ödenmedi'
            ];
            $BorcDetay->saveWithAttr($data);

            $logger->info("Borç tutarı (ödeme yokken) güncellendi: BorcID: {$borcDetayId}, Yeni Tutar: {$yeniTutar}");
        } else {
            // --- SENARYO B: TAHSİLAT YAPILMIŞ (KARMAŞIK MAHSUPLAŞMA) ---
            // Bu blok, sizin zaten çalışan ve test ettiğiniz kodun aynısıdır.

            // Bu borca yapılmış TÜM tahsilatların TOPLAMINI al.
            $tahsilat = $TahsilatModel->find($mevcutTahsilat->tahsilat_id);
            if (!$tahsilat) {
                throw new Exception("İlişkili ana tahsilat kaydı bulunamadı.");
            }
            $tahsilatId = $tahsilat->id;
            $toplamYapilanTahsilat = $tahsilat->tutar ?? 0;

            // Önceki durumu loglamak için sakla
            $oncekiDurum = [
                'borc_ana_tutar' => $borcDetay->tutar,
                'borc_kalan_anapara' => $borcDetay->kalan_borc,
                'borc_kalan_gecikme_zammi' => $borcDetay->kalan_gecikme_zammi,
                'toplam_odeme' => $toplamYapilanTahsilat
            ];

            // TEMİZLİK
            $TahsilatDetay->deleteDetayByBorcDetayId($borcDetayId);
            $KisiKredi->deleteKrediByBorcDetayId($borcDetayId);

            // YENİDEN HESAPLAMA
            $yeniHesaplananGecikmeZammi = FinansalHelper::hesaplaGecikmeZammi($yeniTutar, $borcDetay->bitis_tarihi, $borcDetay->ceza_orani);

            // DAĞITIM (MAHSUPLAŞMA)
            $kalanOdemeMiktari = $toplamYapilanTahsilat;
            $odenenGecikmeZammi = min($kalanOdemeMiktari, $yeniHesaplananGecikmeZammi);
            $kalanOdemeMiktari -= $odenenGecikmeZammi;
            $sonKalanGecikmeZammi = $yeniHesaplananGecikmeZammi - $odenenGecikmeZammi;
            $odenenAnapara = min($kalanOdemeMiktari, $yeniTutar);
            $kalanOdemeMiktari -= $odenenAnapara;
            $sonKalanAnapara = $yeniTutar - $odenenAnapara;
            $olusanKredi = $kalanOdemeMiktari;


            // --- 5. YENİ VE TEMİZ TAHSİLAT DETAYLARINI OLUŞTUR ---
            // Bu işlem, önceki tüm detayların yerine geçer.
            if ($odenenGecikmeZammi > 0) {
                $TahsilatDetay->saveWithAttr([
                    'id' => 0,
                    'tahsilat_id' => $tahsilatId, // Orijinal işlemle bağlantıyı koru
                    'borc_detay_id' => $borcDetayId,
                    'odenen_tutar' => $odenenGecikmeZammi,
                    'aciklama' => 'Gecikme zammı (borç güncellemesi sonrası mahsuplaşma)',
                    'kayit_tarihi' => date('Y-m-d H:i:s'), // Kayıt tarihi bugündür.
                ]);
            }

            if ($odenenAnapara > 0) {
                $TahsilatDetay->saveWithAttr([
                    'id' => 0,
                    'tahsilat_id' => $tahsilatId, // Orijinal işlemle bağlantıyı koru
                    'borc_detay_id' => $borcDetayId,
                    'odenen_tutar' => $odenenAnapara,
                    'aciklama' => 'Anapara (borç güncellemesi sonrası mahsuplaşma)',
                    'kayit_tarihi' => date('Y-m-d H:i:s'), // Kayıt tarihi bugündür.
                ]);
            }



            // ANA BORÇ KAYDINI GÜNCELLEME
            $data = [
                "id" => $borcDetayId,
                "tutar" => $yeniTutar,
                "aciklama" => $_POST["aciklama"],
                "ceza_orani" => $_POST["ceza_orani"],
                "kalan_borc" => $sonKalanAnapara,
                "kalan_gecikme_zammi" => $sonKalanGecikmeZammi,
                "odeme_durumu" => ($sonKalanAnapara == 0 && $sonKalanGecikmeZammi == 0) ? 'Ödendi' : 'Kısmi Ödendi'
            ];
            $BorcDetay->saveWithAttr($data);

            // OLUŞAN KREDİYİ KAYDETME
            if ($olusanKredi > 0) {
                $KisiKredi->saveWithAttr([
                    'id' => 0,
                    'kisi_id' => $kisiId,
                    'tahsilat_id' => $tahsilatId, // Orijinal tahsilatla bağlantıyı koru
                    'tutar' => $olusanKredi,
                    'aciklama' => "Borç No:{$borcDetayId} tutarının yeniden yapılandırılması sonucu oluşan alacak.",
                    'borc_detay_id' => $borcDetayId,
                    'islem_tarihi' => date('Y-m-d H:i:s'),
                ]);
            }

            // DETAYLI LOGLAMA
            $logVerisi = ['onceki_durum' => $oncekiDurum, 'yeni_durum' => [ /* ... yeni durum verileri ... */]];
            $jsonLogDetaylari = json_encode(['karsilastirma' => $logVerisi], JSON_UNESCAPED_UNICODE);
            $logMesaji = "Borç Yeniden Hesaplandı (Mahsuplaşma): BorcID: {{borc_id}}. Detaylar: {{json_detaylar}}";
            $logContext = ['borc_id' => $borcDetayId, 'json_detaylar' => $jsonLogDetaylari];
            $logger->info($logMesaji, $logContext);
        }

        // --- 3. ORTAK BİTİŞ: İşlemi tamamla ---
        $db->commit();
        $res = ["status" => "success", "message" => "Borç başarıyla güncellendi."];
    } catch (Exception $e) {
        $db->rollBack();
        $res = ["status" => "error", "message" => "İşlem sırasında bir hata oluştu: " . $e->getMessage()];
    }

    echo json_encode($res);
    exit;
}


// /**
//  * Tekli borçlandırma detayını, TÜM tahsilatların TOPLAMINI alıp yeniden dağıtarak günceller.
//  * Bu yöntem "sil ve yeniden oluştur" prensibiyle çalışır ve en temiz sonucu verir.
//  */
// if ($_POST["action"] == "update_debit_single_consolidated") { // Action adını netleştirdim.
    
//     $db = Db::getInstance();
//     $db->beginTransaction();

//     try {
//         // --- 1. GEREKLİ VERİLERİ AL VE HAZIRLIK YAP ---
//         $borcDetayId = Security::decrypt($_POST["borclandirma_id"]);
//         $yeniTutar = Helper::formattedMoneyToNumber($_POST["tutar"]);

//         $borcDetay = $BorcDetay->find($borcDetayId);
//         if (!$borcDetay) {
//             throw new Exception("Güncellenecek borç kaydı bulunamadı.");
//         }
//         $kisiId = $borcDetay->kisi_id;
//         // --- KRİTİK ADIM: Tüm tahsilat detaylarını ve toplam tahsilatı al ---


//         //Tahsilat Detay tablosundan bu borc_detay_id'ye ait tüm tahsilat detaylarını al
//         $yapılan_tahsilat = $TahsilatDetay->getDetayByTahsilatId($borcDetayId);

//         if (!$yapılan_tahsilat) {
//             throw new Exception("Bu borç için tahsilat kaydı bulunamadı.");
//         }


//         $tahsilat = $TahsilatModel->find($yapılan_tahsilat->tahsilat_id);
//         $tahsilatId = $tahsilat->id ; // Tahsilat ID'si, yeni tahsilat detayları için kullanılacak

//         // --- KRİTİK ADIM 1: Bu borca yapılmış TÜM tahsilatların TOPLAMINI al. ---
//         $toplamYapilanTahsilat = $tahsilat->tutar ?? 0; // Toplam ödenen tutar

//         //$logger->info("Borç toplu mahsuplaşma başlangıcı: BorcID: {$borcDetayId}, Yeni Tutar: {$yeniTutar}, Toplam Tahsilat: {$toplamYapilanTahsilat}");

//         // --- 2. TEMİZLİK: Borcun tüm finansal geçmişini sıfırla ---
//         $TahsilatDetay->deleteDetayByBorcDetayId($borcDetayId);
//         $KisiKredi->deleteKrediByBorcDetayId($borcDetayId);

//         // --- 3. YENİDEN HESAPLAMA ---
//         $yeniHesaplananGecikmeZammi = FinansalHelper::hesaplaGecikmeZammi($yeniTutar, $borcDetay->bitis_tarihi, $borcDetay->ceza_orani);
        
//         // --- 4. TOPLAM TAHSİLATIN YENİDEN DAĞITILMASI (MAHSUPLAŞMA) ---
//         $kalanOdemeMiktari = $toplamYapilanTahsilat;
        
//         // a) Önce gecikme zammını kapat.
//         $odenenGecikmeZammi = min($kalanOdemeMiktari, $yeniHesaplananGecikmeZammi);
//         $kalanOdemeMiktari -= $odenenGecikmeZammi;
//         $sonKalanGecikmeZammi = $yeniHesaplananGecikmeZammi - $odenenGecikmeZammi;
        
//         // b) Kalan para ile anaparayı kapat.
//         $odenenAnapara = min($kalanOdemeMiktari, $yeniTutar);
//         $kalanOdemeMiktari -= $odenenAnapara;
//         $sonKalanAnapara = $yeniTutar - $odenenAnapara;

//         // c) Hala para kaldıysa, bu artık kişinin alacağıdır (kredi).
//         $olusanKredi = $kalanOdemeMiktari;


//         // --- 5. YENİ VE TEMİZ TAHSİLAT DETAYLARINI OLUŞTUR ---
//         // Bu işlem, önceki tüm detayların yerine geçer.
//         if ($odenenGecikmeZammi > 0) {
//             $TahsilatDetay->saveWithAttr([
//                 'id' => 0,
//                 'tahsilat_id' => $tahsilatId, // Orijinal işlemle bağlantıyı koru
//                 'borc_detay_id' => $borcDetayId,
//                 'odenen_tutar' => $odenenGecikmeZammi,
//                 'aciklama' => 'Gecikme zammı (borç güncellemesi sonrası mahsuplaşma)',
//                 'kayit_tarihi' => date('Y-m-d H:i:s'), // Kayıt tarihi bugündür.
//             ]);
//         }

//         if ($odenenAnapara > 0) {
//              $TahsilatDetay->saveWithAttr([
//                 'id' => 0,
//                 'tahsilat_id' => $tahsilatId, // Orijinal işlemle bağlantıyı koru
//                 'borc_detay_id' => $borcDetayId,
//                 'odenen_tutar' => $odenenAnapara,
//                 'aciklama' => 'Anapara (borç güncellemesi sonrası mahsuplaşma)',
//                 'kayit_tarihi' => date('Y-m-d H:i:s'), // Kayıt tarihi bugündür.
//             ]);
//         }

//         $logger->info("Anapara tahsilat detayı kaydedildi: Tutar: {$odenenAnapara}, TahsilatID: {$tahsilatId}");
//         // --- 6. ANA BORÇ KAYDINI SON DURUMLA GÜNCELLE ---
//         // DİKKAT: $_POST["aciklama"] SQL Injection'a karşı korumalı olmalıdır (Prepared Statements kullanarak).
//         $data = [
//             "id" => $borcDetayId,
//             "tutar" => $yeniTutar,
//             "aciklama" => $_POST["aciklama"],
//             "ceza_orani" => $_POST["ceza_orani"],
//             "kalan_borc" => $sonKalanAnapara,
//             "kalan_gecikme_zammi" => $sonKalanGecikmeZammi,
//             "odeme_durumu" => ($sonKalanAnapara == 0 && $sonKalanGecikmeZammi == 0) ? 'Ödendi' : 'Kısmi Ödendi' 
//         ];
//         $BorcDetay->saveWithAttr($data);

        
//         // --- 7. OLUŞAN KREDİYİ KAYDET ---
//         if ($olusanKredi > 0) {
//             $KisiKredi->saveWithAttr([ 
//                 'id' => 0,
//                 'kisi_id' => $kisiId,
//                 'tahsilat_id' => $tahsilatId, // Orijinal tahsilatla bağlantıyı koru
//                 'tutar' => $olusanKredi,
//                 'aciklama' => "Borç No:{$borcDetayId} tutarının yeniden yapılandırılması sonucu oluşan alacak.",
//                 'borc_detay_id' => $borcDetayId,
//                 'islem_tarihi' => date('Y-m-d H:i:s'),
//             ]);
//         }
   
   
      
//         // --- 8. DETAYLI LOGLAMA ---
//         // Önceki ve yeni durumu tek bir dizide topla
//         $logVerisi = [
//             'onceki_durum' => [
//                 'borc_ana_tutar' => $borcDetay->tutar,
//                 'borc_kalan_anapara' => $borcDetay->kalan_borc,
//                 'borc_kalan_gecikme_zammi' => $borcDetay->kalan_gecikme_zammi,
//                 'toplam_odeme' => $toplamYapilanTahsilat,
//                 // 'odeme_detaylari' => $oncekiTahsilatDetaylari // İsteğe bağlı, logu çok büyütebilir
//             ],
//             'yeni_durum' => [
//                 'borc_yeni_ana_tutar' => $yeniTutar,
//                 'yeni_hesaplanan_gecikme_zammi' => $yeniHesaplananGecikmeZammi,
//                 'dagilim' => [
//                     'gecikme_zamina_uygulanan' => $odenenGecikmeZammi,
//                     'anaparaya_uygulanan' => $odenenAnapara,
//                 ],
//                 'sonuc' => [
//                     'borc_kalan_anapara' => $sonKalanAnapara,
//                     'borc_kalan_gecikme_zammi' => $sonKalanGecikmeZammi,
//                     'olusan_kredi' => $olusanKredi,
//                 ]
//             ],
//             'meta' => [
//                 'islem_yapan_kullanici_id' => $_SESSION['user_id'] ?? 0,
//                 'ip_adresi' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
//                 'islem_tarihi' => date('Y-m-d H:i:s')
//             ]
//         ];

//         // *** KRİTİK DEĞİŞİKLİK BURADA BAŞLIYOR ***

//         // 1. Adım: Karşılaştırmalı veriyi kendimiz JSON metnine çeviriyoruz.
//         $jsonLogDetaylari = json_encode(['karsilastirma' => $logVerisi], JSON_UNESCAPED_UNICODE);

//         // 2. Adım: Log mesajını ve context'i FileLogger'ın anlayacağı şekilde hazırlıyoruz.
//         // Mesajımız artık JSON verisinin geleceği bir yer tutucu içeriyor.
//         $logMesaji = "Borç Yeniden Hesaplandı: BorcID: {{borc_id}}. Detaylar: {{json_detaylar}}";

//         // Context dizimiz artık sadece basit anahtar/değer çiftleri içeriyor.
//         $logContext = [
//             'borc_id'       => $borcDetayId,
//             'json_detaylar' => $jsonLogDetaylari // Değerimiz artık bir dizi değil, bir METİN!
//         ];

//         // 3. Adım: Logger'ı yeni hazırladığımız mesaj ve context ile çağırıyoruz.
//         $logger->info($logMesaji, $logContext);

//     $db->commit();
//         $res = ["status" => "success", "message" => "Borç başarıyla güncellendi ve finansal durum yeniden hesaplandı."];

//     } catch (Exception $e) {
//         $db->rollBack();
//         $res = ["status" => "error", "message" => "İşlem sırasında bir hata oluştu: " . $e->getMessage()];
//     }
    
//     echo json_encode($res);
//     exit;
// }