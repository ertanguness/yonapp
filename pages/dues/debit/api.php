<?php
require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';



use App\Helper\Security;
use App\Helper\Date;
use App\Helper\Helper;
use App\Services\ExcelHelper;
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
use App\Services\FlashMessageService;



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

        $baslangic_tarihi = new DateTime($data["baslangic_tarihi"]);


        //Borçlandırma tipi kontrol ediliyor
        if ($borclandirma_turu == "all") {

            
            
            $baslangic_tarihi_str = Date::Ymd($_POST["baslangic_tarihi"]);
            $bitis_tarihi_str = Date::Ymd($_POST["bitis_tarihi"]);
            
            // 1. O dönemde aktif olan TÜM kişileri, daire bilgileriyle birlikte çek.
            $borclandirilacakKisiler = $Kisiler->BorclandirilacakAktifKisileriGetir($site_id, $baslangic_tarihi_str, $bitis_tarihi_str);
            
            



            // 2. Bu düz listeyi, daire bazında gruplanmış bir diziye dönüştür.
            $dairelerVeSakinleri = [];
            foreach ($borclandirilacakKisiler as $kisi) {
                // Anahtar olarak daire_id'yi kullanarak kişileri grupla.
                $dairelerVeSakinleri[$kisi->daire_id][] = $kisi;
            }
    
            // 3. DAİRE bazında döngüye başla.
            // Anahtar ($daire_id) ve değer ($sakinler -> o dairedeki kişilerin listesi)
            foreach ($dairelerVeSakinleri as $daire_id => $sakinler) {
                
                //Daire aidattan muaf ise daireyi atla
                $daire = $Daire->find($daire_id);
                if ($daire->aidattan_muaf) {
                    $logger->info("Daire ID {$daire_id} aidattan muaf, atlanıyor.");
                    continue; // Bu daireyi atla
                }
    

                
                // 4. Bu dairedeki EV SAHİBİNİ ve TÜM KİRACILARI ayıkla.
                $evSahibi = null;
                $kiracilar = []; // Kiracıları bir dizi olarak topla
    
                foreach ($sakinler as $sakin) {
                    if ($sakin->uyelik_tipi == 'Ev Sahibi') {
                        $evSahibi = $sakin;
                    } else if ($sakin->uyelik_tipi == 'Kiracı') {
                        $kiracilar[] = $sakin; // Her kiracıyı diziye ekle
                    }
                }
    
                // Güvenlik kontrolü: Eğer bir dairede ev sahibi yoksa (veri tutarsızlığı), bu daireyi atla.
                if (!$evSahibi) {
                    $logger->info("Daire ID {$daire_id} için ev sahibi bulunamadı, atlanıyor.");
                    continue;
                }
                
                // Ortak verileri ve tutarları hazırla
                $tutar = Helper::formattedMoneyToNumber($_POST["tutar"]);
                $faturaBaslangic = new DateTime($baslangic_tarihi_str);
                $faturaBitis = new DateTime($bitis_tarihi_str);
                $toplamKiraciPayi = 0; // O ayki tüm kiracıların toplam borcunu tutacak değişken

                $logger->info("Borçlandırma işlemi başlatılıyor: Daire ID: {$daire_id}, Tutar: {$tutar}, Başlangıç: {$faturaBaslangic->format('Y-m-d')}, Bitiş: {$faturaBitis->format('Y-m-d')}");
    
                // 5. KARAR MEKANİZMASI: Bu dairede borç paylaşımı olacak mı?
                
                // Eğer gün bazlı borçlandırma aktifse VE dairede en az bir kiracı varsa...
                if ($gun_bazli && !empty($kiracilar)) {
                    
                    /********** GÜN BAZLI BORÇLANDIRMA (PAYLAŞTIRMA) **********/
                    
                    // Her bir kiracı için ayrı ayrı dönerek paylarını hesapla ve kaydet.
                    foreach ($kiracilar as $kiraci) {
                        
                        // Bu kiracının payını hesapla
                        $kiraci_tutari = Helper::calculateProratedAmount(
                            $faturaBaslangic->format('Y-m-d'), $faturaBitis->format('Y-m-d'),
                            $kiraci->giris_tarihi, $kiraci->cikis_tarihi, $tutar
                        );
                        
                        // KİRACININ PAYINI KAYDET (Eğer borcu varsa)
                        if ($kiraci_tutari > 0.01) {
                             $kiraciEfektifBaslangic = max($faturaBaslangic, new DateTime($kiraci->giris_tarihi));
                             $kiraciEfektifBitis = ($kiraci->cikis_tarihi && $kiraci->cikis_tarihi !== '0000-00-00') ? min($faturaBitis, new DateTime($kiraci->cikis_tarihi)) : $faturaBitis;


                             //Açıklama alanı boş ise, hesaplanan tutara göre açıklama belirle
                             if(empty($_POST["aciklama"])){

                                 $aciklama = $tutar == $kiraci_tutari
                                 ? " Aidat" . " (" .
                                    $baslangic_tarihi->format('d.m.Y') . " - " .
                                    $faturaBitis->format('d.m.Y') . ")"

                                 : "Oturulan dönem Aidatı (" .
                                   $kiraciEfektifBaslangic->format('d.m.Y') . " - " .
                                   $kiraciEfektifBitis->format('d.m.Y') . ")";
                                }else{
                                    $aciklama = $_POST["aciklama"];

                                    $aciklama .= $tutar != $kiraci_tutari
                                    ?  " (" .
                                       $kiraciEfektifBaslangic->format('d.m.Y') . " - " .
                                       $kiraciEfektifBitis->format('d.m.Y') . ")"
   
                                    : "";
                                
                                }


                             $kiraciData = [
                                "borclandirma_id" => Security::decrypt($lastInsertId), 
                                "kisi_id" => $kiraci->id,
                                "borc_adi" => $_POST["borc_adi"],
                                "daire_id" => $daire_id, "tutar" => $kiraci_tutari,
                                "baslangic_tarihi" => $kiraciEfektifBaslangic->format('Y-m-d'),
                                "bitis_tarihi" => $kiraciEfektifBitis->format('Y-m-d'),
                                "son_odeme_tarihi" => $kiraciEfektifBitis->format('Y-m-d'),
                                "aciklama" => $aciklama,

                                // ... $data'dan gelen diğer ortak alanlar
                             ];
                             $BorcDetay->saveWithAttr($kiraciData);
                             $toplamKiraciPayi += $kiraci_tutari; // Hesaplan kiracı payını toplama ekle
                        }
                    }
    
                    // EV SAHİBİNİN PAYINI HESAPLA VE KAYDET
                    // Ev sahibinin payı = Toplam Tutar - TÜM kiracıların toplam payı
                    $ev_sahibi_tutari = $tutar - $toplamKiraciPayi;
    
                    if ($ev_sahibi_tutari > 0.01) {



                        $evSahibiData = [
                            "borclandirma_id" => Security::decrypt($lastInsertId), 
                            "kisi_id" => $evSahibi->id,
                            "borc_adi" => $_POST["borc_adi"],
                            "daire_id" => $daire_id, "tutar" => $ev_sahibi_tutari,
                            "baslangic_tarihi" => $faturaBaslangic->format('Y-m-d'),
                            "bitis_tarihi" => $faturaBitis->format('Y-m-d'),
                            "son_odeme_tarihi" => $faturaBitis->format('Y-m-d'),
                            "aciklama" => "Aidat (Kiracıdan kalan dönem)",
                            // ... $data'dan gelen diğer ortak alanlar
                        ];
                        $BorcDetay->saveWithAttr($evSahibiData);
                    }
    
                } else {
                    /********** TAM AY BORÇLANDIRMA (TEK KİŞİ) **********/
    
                    // Borçlandırılacak kişiyi belirle: Kiracı varsa öncelik onundur, yoksa ev sahibidir.
                    // Not: !empty($kiracilar) kontrolü, kiracı olup olmadığını doğrular.
                    $borcluKisi = !empty($kiracilar) ? $kiracilar[0] : $evSahibi;

                    //Açıklama alanı boş ise, hesaplanan tutara göre açıklama belirle
                    if(empty($_POST["aciklama"])){
                        $aciklama = "Aidat" . " (" .
                            $baslangic_tarihi->format('d.m.Y') . " - " .
                            $faturaBitis->format('d.m.Y') . ")";
                    
                    }else{
                        $aciklama = $_POST["aciklama"];
                    }
    
                    $tamAyData = [
                        "borclandirma_id" => Security::decrypt($lastInsertId), 
                        "kisi_id" => $borcluKisi->id,
                        "borc_adi" => $_POST["borc_adi"],
                        "daire_id" => $daire_id, "tutar" => $tutar,
                        "baslangic_tarihi" => $faturaBaslangic->format('Y-m-d'),
                        "bitis_tarihi" => $faturaBitis->format('Y-m-d'),
                        "son_odeme_tarihi" => $faturaBitis->format('Y-m-d'),
                        "aciklama" => $aciklama,
                         // ... $data'dan gelen diğer ortak alanlar
                    ];
    
                    $BorcDetay->saveWithAttr($tamAyData);
                }
            } // Daire bazlı döngü sonu




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


if ($_POST["action"] == "delete_debit_detail") {
    try {
        
        $BorcDetay->delete($_POST["id"]);

        $res = [
            "status" => "success",
            "message" => "Borçlandırma başarı ile silindi!!!"
        ];
    } catch (Exception $e) {
        $res = [
            "status" => "error",
            "message" => $e->getMessage()
        
        ];
    };
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

    $data = $Kisiler->SiteTumKisileri($site_id);

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


    // $db->beginTransaction();
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
        // $db->commit(); // İşlemi onayla

        $logger->info("Borçlandırma kaydı başarıyla oluşturuldu: " . json_encode($data));

        $status = "success";
        $message = "Borçlandırma kaydı başarıyla oluşturuldu!";
    } catch (PDOException $ex) {
        //$db->rollBack(); // Hata durumunda işlemi geri al
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


//Excelden Yükleme işlemi
if ($_POST["action"] == "excel_upload_debits") {
    $site_id = $_SESSION['site_id'];
    $file = $_FILES['excelFile'];
    $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($fileType !== 'xlsx' && $fileType !== 'xls') {
        echo json_encode([
            "status" => "error",
            "message" => "Lütfen geçerli bir Excel dosyası yükleyin."
        ]);
        exit;
    }

    $borc = $Borc->findWithDueName(Security::decrypt($_POST["borc_id"]));

    $data = [

        "borc_id" => Security::decrypt($_POST["borc_id"]),
        // "borc_adi" => $_POST["borc_adi"],
        "baslangic_tarihi"      => $borc->baslangic_tarihi,
        "borc_adi"              => $borc->borc_adi, // Borç adı
        "bitis_tarihi"          => $borc->bitis_tarihi,
        "hedef_tipi"            => $borc->hedef_tipi, // Borçlandırma tipi
        "aciklama"              => $borc->aciklama, // Borç açıklaması
    ];


    $result = $BorcDetay->excelUpload($file['tmp_name'], $site_id, $data);


    $errorFileUrl = null;

    // Eğer hatalı satır varsa ExcelHelper'ı kullan
    if (!empty($result['data']['error_rows'])) {
        try {
            // ExcelHelper nesnesini oluştur
            $excelHelper = new ExcelHelper();

            // 1. Orijinal başlıkları al
            $originalHeader = $excelHelper->getHeaders($file['tmp_name']);

            // 2. Hata dosyasını oluştur ve URL'sini al
            $errorFileUrl = $excelHelper->createErrorFile($result['data']['error_rows'], $originalHeader);

            FlashMessageService::add("error", "Bilgi", "Hatalı kayıtlar için bir Excel dosyası oluşturuldu. <a href='{$errorFileUrl}' target='_blank'>Dosyayı İndir</a>");
        } catch (Exception $e) {
            // Loglama zaten helper sınıfı içinde yapılıyor.   
            // Burada ek bir loglama yapabilir veya sessiz kalabilirsiniz.
            error_log("Controller: Hata Excel'i işlenirken bir sorun oluştu: " . $e->getMessage());
        }
    }



    if ($result['status'] === 'success') {
        echo json_encode([
            "status" => "success",
            "message" => $result['message'],
            "data" => $result['data']
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => $result['message']
        ]);
    }
}