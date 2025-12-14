<?php

require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';



use App\Helper\Security;
use App\Helper\Date;
use App\Services\Gate;
use App\Helper\Helper;
use App\Services\ExcelHelper;
use App\Helper\FinansalHelper;
use Database\Db;


use Model\BorclandirmaModel;
use Model\BorclandirmaDetayModel;
use Model\DueModel;
use Model\DueDetailModel;
use Model\BloklarModel;
use Model\DairelerModel;
use Model\KisilerModel;
use Model\DefinesModel;
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
$DueDetail = new DueDetailModel();
$KisiKredi = new KisiKredileriModel();
$TahsilatModel = new TahsilatModel();

$TahsilatDetay = new TahsilatDetayModel();


// 1. Singleton Db nesnesini al
$db = Db::getInstance();
$logger = \getLogger();


$site_id = $_SESSION['site_id'];


/**Borçlandirma sayfasından tanımlı borç tiplerinden borçlandırılır */
if ($_POST["action"] == "tanimli_borc_ekle") {
    $id                         = Security::decrypt($_POST["tanimli_borc_tipi_id"]);
    $borc_tipi_id               = Security::decrypt($_POST["borc_tipi_id"]);
    $baslangic_tarihi_str       = Date::Ymd($_POST["baslangic_tarihi"]);
    $bitis_tarihi_str           = Date::Ymd($_POST["bitis_tarihi"]);
    $borclandirma_tarihi_str    = Date::YmdHis($_POST["borclandirma_tarihi"]);

    $due                        = $Due->find($borc_tipi_id);
    $tanimliBorc                = $DueDetail->find($id);


    //Borç tipine ait tanımlanmış borçlar getirilir
    $tanimliBorclar             = $DueDetail->findWhere([
        "due_id"                => $borc_tipi_id,
    ]) ?? [];


    try {
        $db->beginTransaction();

        $data = [
            "id" => 0,
            "site_id"               => $site_id,
            "borc_tipi_id"          => $borc_tipi_id,
            "baslangic_tarihi"      => $baslangic_tarihi_str,
            "bitis_tarihi"          => $bitis_tarihi_str,
            "aciklama"              => $_POST["aciklama"]
        ];

        // if ($id == 0) {
        //     $data["borc_tipi_id"] = Security::decrypt($_POST["borc_baslik"]);
        // }

        $lastInsertId = $Borc->saveWithAttr($data) ?? $_POST["tanimli_borc_tipi_id"];;

        $data = [];
        foreach ($tanimliBorclar as $borc) {
            $borclandirma_tipi      = $borc->borclandirma_tipi;
            $tutar                  = $borc->tutar;

            $logger->info("Borçlandırma tipi: " . $borclandirma_tipi . ", Tutar: " . $tutar);

            if ($borclandirma_tipi == "all") {

                $baslangic_tarihi_str = Date::Ymd($_POST["baslangic_tarihi"]);
                $bitis_tarihi_str = Date::Ymd($_POST["bitis_tarihi"]);

                // 1. O dönemde aktif olan TÜM kişileri, daire bilgileriyle birlikte çek.
                //$borclandirilacakKisiler = $Kisiler->BorclandirilacakAktifKisileriGetir($site_id, $baslangic_tarihi_str, $bitis_tarihi_str);
                $borclandirilacakKisiler = $Kisiler->BorclandirilacakAktifKisileriGetir($site_id, $baslangic_tarihi_str, $bitis_tarihi_str);


                $logger->info("Borçlandırılacak kişiler: " . json_encode([
                    "site_id" => $site_id,
                    "baslangic_tarihi" => $baslangic_tarihi_str,
                    "bitis_tarihi" => $bitis_tarihi_str,
                ]));

                // 2. Bu düz listeyi, daire bazında gruplanmış bir diziye dönüştür.
                $dairelerVeSakinleri = [];
                foreach ($borclandirilacakKisiler as $kisi) {
                    // Anahtar olarak daire_id'yi kullanarak kişileri grupla.
                    $dairelerVeSakinleri[$kisi->daire_id][] = $kisi;
                }

                // *** PERFORMANS OPTİMİZASYONU: Tüm daireleri tek seferde çek ***
                $daireIds = array_keys($dairelerVeSakinleri);
                $dairelerMap = $Daire->findByIds($daireIds);

                // Batch insert için kayıtları topla
                $batchRecords = [];

                // 3. DAİRE bazında döngüye başla.
                // Anahtar ($daire_id) ve değer ($sakinler -> o dairedeki kişilerin listesi)
                foreach ($dairelerVeSakinleri as $daire_id => $sakinler) {

                    //Daire aidattan muaf ise daireyi atla (batch'ten al)
                    $daire = $dairelerMap[$daire_id] ?? null;
                    if (!$daire || $daire->aidattan_muaf) {
                        $logger->info("Daire ID {$daire_id} aidattan muaf veya bulunamadı, atlanıyor.");
                        continue; // Bu daireyi atla
                    }

                    // 4. Bu dairedeki EV SAHİBİNİ ve TÜM KİRACILARI ayıkla.
                    $evSahibi = null;
                    $kiracilar = []; // Kiracıları bir dizi olarak topla

                    foreach ($sakinler as $sakin) {
                        if ($sakin->uyelik_tipi == 'Ev Sahibi' || $sakin->uyelik_tipi == 'Kat Maliki' || $sakin->uyelik_tipi == 1) {
                            $evSahibi = $sakin;
                        } else if ($sakin->uyelik_tipi == 'Kiracı' || $sakin->uyelik_tipi == 2) {
                            $kiracilar[] = $sakin; // Her kiracıyı diziye ekle
                        }
                    }

                    // Güvenlik kontrolü: Eğer bir dairede ev sahibi yoksa (veri tutarsızlığı), bu daireyi atla.
                    if (!$evSahibi) {
                        $logger->info("Daire ID {$daire_id} için ev sahibi bulunamadı, atlanıyor.");
                        /**Kullanıcıya bilgi için bir atlanan daireleri değişkene ata */
                        $atlananDaireler[] = $daire->daire_no;
                        continue;
                    }

                    // Ortak verileri ve tutarları hazırla
                    $faturaBaslangic = new DateTime($baslangic_tarihi_str);
                    $faturaBitis = new DateTime($bitis_tarihi_str);
                    $toplamKiraciPayi = 0; // O ayki tüm kiracıların toplam borcunu tutacak değişken

                    $logger->info("Daireye borçlandirma yapılıyor : Daire ID: {$daire_id}, 
                                                                          Tutar: {$tutar}, 
                                                                          Ev Sahibi ID: {$evSahibi->id},
                                                                          Kiracı : " . json_encode($kiracilar) . ",
                                                                          Başlangıç: {$faturaBaslangic->format('Y-m-d')}, 
                                                                          Bitiş: {$faturaBitis->format('Y-m-d')}");


                    /********** TAM AY BORÇLANDIRMA (TEK KİŞİ) **********/

                    // Borçlandırılacak kişiyi belirle: Kiracı varsa öncelik onundur, yoksa ev sahibidir.
                    // Not: !empty($kiracilar) kontrolü, kiracı olup olmadığını doğrular.
                    $borcluKisi = !empty($kiracilar) ? $kiracilar[0] : $evSahibi;

                    //Açıklama alanı boş ise, hesaplanan tutara göre açıklama belirle
                    if (empty($_POST["aciklama"])) {
                        $aciklama = "Aidat" . " (" .
                            $baslangic_tarihi->format('d.m.Y') . " - " .
                            $faturaBitis->format('d.m.Y') . ")";
                    } else {
                        $aciklama = $_POST["aciklama"];
                    }

                    $tamAyData = [
                        "borclandirma_id"           => Security::decrypt($lastInsertId),
                        "kisi_id"                   => $borcluKisi->id,
                        "borc_adi"                 => $due->due_name,
                        "daire_id"                  => $daire_id,
                        "tutar"                     => $tutar,
                        "ceza_orani"                => $borc->ceza_orani,
                        "baslangic_tarihi"          => $faturaBaslangic->format('Y-m-d'),
                        "bitis_tarihi"              => $faturaBitis->format('Y-m-d'),
                        "son_odeme_tarihi"          => $faturaBitis->format('Y-m-d'),
                        "aciklama"                  => $aciklama,
                        "kalan_borc"                => $tutar, // Başlangıçta kalan borç = tutar
                    ];

                    // Batch için topla
                    $batchRecords[] = $tamAyData;
                } // Daire bazlı döngü sonu

                // *** PERFORMANS OPTİMİZASYONU: Tüm kayıtları tek sorguda ekle ***
                if (!empty($batchRecords)) {
                    $BorcDetay->batchInsert($batchRecords);
                    $logger->info("Batch insert: " . count($batchRecords) . " kayıt eklendi.");
                }
            } else if ($borclandirma_tipi == "evsahibi" || $borclandirma_tipi == "sakinler" || $borclandirma_tipi == "isyerisakinleri") {


                $mulk_tipi = ($borclandirma_tipi == "sakinler" || $borclandirma_tipi == "evsahibi") ? "Konut" : "İşyeri";

                // 1. O dönemde aktif olan TÜM kişileri, daire bilgileriyle birlikte çek.
                $borclandirilacakKisiler = $Kisiler->BorclandirilacakAktifKisileriGetir(
                    $site_id,
                    $baslangic_tarihi_str,
                    $bitis_tarihi_str,
                    $mulk_tipi
                );


                $logger->info("Borçlandırılacak kişiler: " . json_encode([
                    "kisiler" => $borclandirilacakKisiler,
                    "mulk_tipi" => $mulk_tipi,
                    "borclandirma_tipi" => $borclandirma_tipi,
                    "baslangic_tarihi" => $baslangic_tarihi_str,
                    "bitis_tarihi" => $bitis_tarihi_str,
                ]));

                // 2. Bu düz listeyi, daire bazında gruplanmış bir diziye dönüştür.
                $dairelerVeSakinleri = [];
                foreach ($borclandirilacakKisiler as $kisi) {
                    // Anahtar olarak daire_id'yi kullanarak kişileri grupla.
                    $dairelerVeSakinleri[$kisi->daire_id][] = $kisi;
                }

                // *** PERFORMANS OPTİMİZASYONU: Tüm daireleri tek seferde çek ***
                $daireIds = array_keys($dairelerVeSakinleri);
                $dairelerMap = $Daire->findByIds($daireIds);

                // Batch insert için kayıtları topla
                $batchRecords = [];

                // 3. DAİRE bazında döngüye başla.
                // Anahtar ($daire_id) ve değer ($sakinler -> o dairedeki kişilerin listesi)
                foreach ($dairelerVeSakinleri as $daire_id => $sakinler) {

                    //Daire aidattan muaf ise daireyi atla (batch'ten al)
                    $daire = $dairelerMap[$daire_id] ?? null;
                    if (!$daire || $daire->aidattan_muaf) {
                        $logger->info("Daire ID {$daire_id} aidattan muaf veya bulunamadı, atlanıyor.");
                        continue; // Bu daireyi atla
                    }

                    // 4. Bu dairedeki EV SAHİBİNİ ve TÜM KİRACILARI ayıkla.
                    $evSahibi = null;
                    $kiracilar = []; // Kiracıları bir dizi olarak topla

                    foreach ($sakinler as $sakin) {
                        if ($sakin->uyelik_tipi == 'Ev Sahibi' || $sakin->uyelik_tipi == 'Kat Maliki' || $sakin->uyelik_tipi == 1) {
                            $evSahibi = $sakin;
                        } else if ($borclandirma_tipi != "evsahibi" && ($sakin->uyelik_tipi == 'Kiracı' || $sakin->uyelik_tipi == 2)) {
                            $kiracilar[] = $sakin; // Her kiracıyı diziye ekle
                        }
                    }

                    // Güvenlik kontrolü: Eğer bir dairede ev sahibi yoksa (veri tutarsızlığı), bu daireyi atla.
                    if (!$evSahibi) {
                        $logger->info("Daire ID {$daire_id} için ev sahibi bulunamadı, atlanıyor.");
                        continue;
                    }

                    // Ortak verileri ve tutarları hazırla
                    $faturaBaslangic = new DateTime($baslangic_tarihi_str);
                    $faturaBitis = new DateTime($bitis_tarihi_str);
                    $toplamKiraciPayi = 0; // O ayki tüm kiracıların toplam borcunu tutacak değişken

                    $logger->info("Daireye borçlandirma yapılıyor : Daire ID: {$daire_id}, 
                                                                          Tutar: {$tutar}, 
                                                                          Ev Sahibi ID: {$evSahibi->id},
                                                                          Kiracı : " . json_encode($kiracilar) . ",
                                                                          Başlangıç: {$faturaBaslangic->format('Y-m-d')}, 
                                                                          Bitiş: {$faturaBitis->format('Y-m-d')}");

                // 5. KARAR MEKANİZMASI: Bu dairede borç paylaşımı olacak mı?


                    /********** TAM AY BORÇLANDIRMA (TEK KİŞİ) **********/
                    // Borçlandırılacak kişiyi belirle: Kiracı varsa öncelik onundur, yoksa ev sahibidir.
                    // Not: !empty($kiracilar) kontrolü, kiracı olup olmadığını doğrular.
                    $borcluKisi = !empty($kiracilar) ? $kiracilar[0] : $evSahibi;

                    //Açıklama alanı boş ise, hesaplanan tutara göre açıklama belirle
                    if (empty($_POST["aciklama"])) {
                        $aciklama = "Aidat" . " (" .
                            $baslangic_tarihi->format('d.m.Y') . " - " .
                            $faturaBitis->format('d.m.Y') . ")";
                    } else {
                        $aciklama = $_POST["aciklama"];
                    }

                    $tamAyData = [
                        "borclandirma_id"       => Security::decrypt($lastInsertId),
                        "kisi_id"               => $borcluKisi->id,
                        "borc_adi"              => $due->due_name,
                        "daire_id"              => $daire_id,
                        "tutar"                 => $tutar,
                        "ceza_orani"            => $borc->ceza_orani,
                        "borclandirma_tarihi"   => $borclandirma_tarihi_str,
                        "baslangic_tarihi"      => $faturaBaslangic->format('Y-m-d'),
                        "bitis_tarihi"          => $faturaBitis->format('Y-m-d'),
                        "son_odeme_tarihi"      => $faturaBitis->format('Y-m-d'),
                        "aciklama"              => $aciklama,
                        "kalan_borc"            => $tutar, // Başlangıçta kalan borç = tutar
                        // ... $data'dan gelen diğer ortak alanlar
                    ];

                    // Batch için topla
                    $batchRecords[] = $tamAyData;
                } // Daire bazlı döngü sonu

                // *** PERFORMANS OPTİMİZASYONU: Tüm kayıtları tek sorguda ekle ***
                if (!empty($batchRecords)) {
                    $BorcDetay->batchInsert($batchRecords);
                    $logger->info("Batch insert: " . count($batchRecords) . " kayıt eklendi.");
                }
            } elseif ($borclandirma_tipi == "dairetipi") {
                // Daire tipi bazlı borçlandırma işlemleri burada yapılacak
                // İlgili kodları ekleyin

            }
        }
        $db->commit();

        $logger->info("Borçlandırma işlemi tamamlandı.");
        $status = "success";
        $message = "Tanımlı borçlandırma işlemi başarılı.";
        $atlananDaireler = json_encode($atlananDaireler);
        /** Atlanan daire varsa mesaj ile birleştir */
        $message .= "<br> Ev Sahibi olmadığı için atlanan daireler numaraları : " . $atlananDaireler;

    } catch (Exception $e) {
        $db->rollBack();
        $logger->error("Borçlandırma işlemi sırasında hata: " . $e->getMessage());
        $status = "error";
        $message = "Borçlandırma işlemi sırasında bir hata oluştu: " . $e->getMessage();
    }

    echo json_encode([
        "status" => $status,
        "message" => $message,
        "data" => $tanimliBorclar,
    ]);
    exit;
}

/**BORÇLANDIRMA YAP */
if ($_POST["action"] == "borclandir") {
    $id = Security::decrypt($_POST["borc_id"]) ?? 0;
    $user_id = $_SESSION["user"]->id;
    $gun_bazli = isset($_POST["day_based"]) ? true : false; // Gün bazlı mı kontrolü

    $logger = \getLogger();

    try {
        $db->beginTransaction();

        //borclandirma_tipini yeni kayıtsa post ile al, güncelleme ise veritabanından al
        if ($id != 0) {
            $existingBorc = $Borc->find($id);
            $logger->info("Mevcut borçlandırma kaydı: " . json_encode($existingBorc));

            if ($existingBorc) {
                $borclandirma_turu = $existingBorc->hedef_tipi;
            } else {
                $borclandirma_turu = $_POST["hedef_tipi"];
            }
        } else {
            $borclandirma_turu = $_POST["hedef_tipi"];
        }




        $data = [
            "id" => $id,
            "site_id" => $site_id,
            "tutar" => Helper::formattedMoneyToNumber($_POST["tutar"]),
            "baslangic_tarihi" => Date::Ymd($_POST["baslangic_tarihi"]),
            "bitis_tarihi" => Date::Ymd($_POST["bitis_tarihi"]),
            "ceza_orani" => $_POST["ceza_orani"],
            "aciklama" => $_POST["aciklama"],
            "hedef_tipi" => $borclandirma_turu,
            "borclandirma_sekli" => 'manuel',
        ];

        if ($id == 0) {
            $data["borc_tipi_id"] = Security::decrypt($_POST["borc_baslik"]);
        }

        $lastInsertId = $Borc->saveWithAttr($data) ?? $_POST["id"];

        $data = [];

        $data = [
            "id"                  => 0,
            "borclandirma_id"     =>  Security::decrypt($lastInsertId),
            "borc_adi"            => $_POST["borc_adi"],
            "tutar"               => Helper::formattedMoneyToNumber($_POST["tutar"]),
            "kalan_borc"          => Helper::formattedMoneyToNumber($_POST["tutar"]), // Başlangıçta kalan tutar, toplam tutara eşit
            "borclandirma_tarihi" => Date::Ymd($_POST["baslangic_tarihi"]),
            "baslangic_tarihi"    => Date::Ymd($_POST["baslangic_tarihi"]),
            "bitis_tarihi"        => Date::Ymd($_POST["bitis_tarihi"]),
            "son_odeme_tarihi"    => Date::Ymd($_POST["bitis_tarihi"]),
            "ceza_orani"          => $_POST["ceza_orani"],
            "aciklama"            => $_POST["aciklama"],
            "hedef_tipi"          => $borclandirma_turu,
        ];

        $baslangic_tarihi = new DateTime($data["baslangic_tarihi"]);


        //Borçlandırma tipi kontrol ediliyor
        if ($borclandirma_turu == "all") {

            $baslangic_tarihi_str = Date::Ymd($_POST["baslangic_tarihi"]);
            $bitis_tarihi_str = Date::Ymd($_POST["bitis_tarihi"]);

            // 1. O dönemde aktif olan TÜM kişileri, daire bilgileriyle birlikte çek.
            //$borclandirilacakKisiler = $Kisiler->BorclandirilacakAktifKisileriGetir($site_id, $baslangic_tarihi_str, $bitis_tarihi_str);
            $borclandirilacakKisiler = $Kisiler->BorclandirilacakAktifKisileriGetir($site_id, $baslangic_tarihi_str, $bitis_tarihi_str);


            $logger->info("Borçlandırılacak kişiler: " . json_encode([
                "site_id" => $site_id,
                "baslangic_tarihi" => $baslangic_tarihi_str,
                "bitis_tarihi" => $bitis_tarihi_str,
            ]));

            // 2. Bu düz listeyi, daire bazında gruplanmış bir diziye dönüştür.
            $dairelerVeSakinleri = [];
            foreach ($borclandirilacakKisiler as $kisi) {
                // Anahtar olarak daire_id'yi kullanarak kişileri grupla.
                $dairelerVeSakinleri[$kisi->daire_id][] = $kisi;
            }

            // *** PERFORMANS OPTİMİZASYONU: Tüm daireleri tek seferde çek ***
            $daireIds = array_keys($dairelerVeSakinleri);
            $dairelerMap = $Daire->findByIds($daireIds);

            // Batch insert için kayıtları topla
            $batchRecords = [];

            // 3. DAİRE bazında döngüye başla.
            // Anahtar ($daire_id) ve değer ($sakinler -> o dairedeki kişilerin listesi)
            foreach ($dairelerVeSakinleri as $daire_id => $sakinler) {

                //Daire aidattan muaf ise daireyi atla (batch'ten al)
                $daire = $dairelerMap[$daire_id] ?? null;
                if (!$daire || $daire->aidattan_muaf) {
                    $logger->info("Daire ID {$daire_id} aidattan muaf veya bulunamadı, atlanıyor.");
                    continue; // Bu daireyi atla
                }



                // 4. Bu dairedeki EV SAHİBİNİ ve TÜM KİRACILARI ayıkla.
                $evSahibi = null;
                $kiracilar = []; // Kiracıları bir dizi olarak topla

                foreach ($sakinler as $sakin) {
                    $logger->info("Kişi ID: {$sakin->id}, Uyelik Tipi: {$sakin->uyelik_tipi}, Adı Soyadı: {$sakin->adi_soyadi}");
                    if ($sakin->uyelik_tipi == 'Ev Sahibi' || $sakin->uyelik_tipi == 'Kat Maliki' || $sakin->uyelik_tipi == 1) {
                        $evSahibi = $sakin;
                    } else if ($sakin->uyelik_tipi == 'Kiracı' || $sakin->uyelik_tipi == 2) {
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

                $logger->info("Daireye borçlandirma yapılıyor : Daire ID: {$daire_id}, 
                                                                          Tutar: {$tutar}, 
                                                                          Ev Sahibi ID: {$evSahibi->id},
                                                                          Kiracı : " . json_encode($kiracilar) . ",
                                                                          Başlangıç: {$faturaBaslangic->format('Y-m-d')}, 
                                                                          Bitiş: {$faturaBitis->format('Y-m-d')}");

                // 5. KARAR MEKANİZMASI: Bu dairede borç paylaşımı olacak mı?

                // Eğer gün bazlı borçlandırma aktifse VE dairede en az bir kiracı varsa...
                if ($gun_bazli && !empty($kiracilar)) {

                    /********** GÜN BAZLI BORÇLANDIRMA (PAYLAŞTIRMA) **********/

                    // Her bir kiracı için ayrı ayrı dönerek paylarını hesapla ve kaydet.
                    foreach ($kiracilar as $kiraci) {

                        // Bu kiracının payını hesapla
                        $kiraci_tutari = Helper::calculateProratedAmount(
                            $faturaBaslangic->format('Y-m-d'),
                            $faturaBitis->format('Y-m-d'),
                            $kiraci->giris_tarihi,
                            $kiraci->cikis_tarihi,
                            $tutar
                        );

                        // KİRACININ PAYINI KAYDET (Eğer borcu varsa)
                        if ($kiraci_tutari > 0.01) {
                            $kiraciEfektifBaslangic = max($faturaBaslangic, new DateTime($kiraci->giris_tarihi));
                            $kiraciEfektifBitis = ($kiraci->cikis_tarihi && $kiraci->cikis_tarihi !== '0000-00-00') ? min($faturaBitis, new DateTime($kiraci->cikis_tarihi)) : $faturaBitis;


                            //Açıklama alanı boş ise, hesaplanan tutara göre açıklama belirle
                            if (empty($_POST["aciklama"])) {

                                $aciklama = $tutar == $kiraci_tutari
                                    ? " Aidat" . " (" .
                                    $baslangic_tarihi->format('d.m.Y') . " - " .
                                    $faturaBitis->format('d.m.Y') . ")"

                                    : "Oturulan dönem Aidatı (" .
                                    $kiraciEfektifBaslangic->format('d.m.Y') . " - " .
                                    $kiraciEfektifBitis->format('d.m.Y') . ")";
                            } else {
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
                                "daire_id" => $daire_id,
                                "tutar" => $kiraci_tutari,
                                "ceza_orani" => $_POST["ceza_orani"],
                                "baslangic_tarihi" => $kiraciEfektifBaslangic->format('Y-m-d'),
                                "bitis_tarihi" => $kiraciEfektifBitis->format('Y-m-d'),
                                "son_odeme_tarihi" => $kiraciEfektifBitis->format('Y-m-d'),
                                "aciklama" => $aciklama,
                                "kalan_borc" => $kiraci_tutari, // Başlangıçta kalan borç = tutar

                                // ... $data'dan gelen diğer ortak alanlar
                            ];
                            // Batch için topla
                            $batchRecords[] = $kiraciData;
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
                            "daire_id" => $daire_id,
                            "tutar" => $ev_sahibi_tutari,
                            "ceza_orani" => $_POST["ceza_orani"],
                            "baslangic_tarihi" => $faturaBaslangic->format('Y-m-d'),
                            "bitis_tarihi" => $faturaBitis->format('Y-m-d'),
                            "son_odeme_tarihi" => $faturaBitis->format('Y-m-d'),
                            "aciklama" => "Aidat (Kiracıdan kalan dönem)",
                            "kalan_borc" => $ev_sahibi_tutari, // Başlangıçta kalan borç = tutar
                            // ... $data'dan gelen diğer ortak alanlar
                        ];
                        // Batch için topla
                        $batchRecords[] = $evSahibiData;
                    }
                } else {
                    /********** TAM AY BORÇLANDIRMA (TEK KİŞİ) **********/

                    // Borçlandırılacak kişiyi belirle: Kiracı varsa öncelik onundur, yoksa ev sahibidir.
                    // Not: !empty($kiracilar) kontrolü, kiracı olup olmadığını doğrular.
                    $borcluKisi = !empty($kiracilar) ? $kiracilar[0] : $evSahibi;

                    //Açıklama alanı boş ise, hesaplanan tutara göre açıklama belirle
                    if (empty($_POST["aciklama"])) {
                        $aciklama = "Aidat" . " (" .
                            $baslangic_tarihi->format('d.m.Y') . " - " .
                            $faturaBitis->format('d.m.Y') . ")";
                    } else {
                        $aciklama = $_POST["aciklama"];
                    }

                    $tamAyData = [
                        "borclandirma_id" => Security::decrypt($lastInsertId),
                        "kisi_id" => $borcluKisi->id,
                        "borc_adi" => $_POST["borc_adi"],
                        "daire_id" => $daire_id,
                        "tutar" => $tutar,
                        "ceza_orani" => $_POST["ceza_orani"],
                        "baslangic_tarihi" => $faturaBaslangic->format('Y-m-d'),
                        "bitis_tarihi" => $faturaBitis->format('Y-m-d'),
                        "son_odeme_tarihi" => $faturaBitis->format('Y-m-d'),
                        "aciklama" => $aciklama,
                        "kalan_borc" => $tutar, // Başlangıçta kalan borç = tutar
                        // ... $data'dan gelen diğer ortak alanlar
                    ];

                    // Batch için topla
                    $batchRecords[] = $tamAyData;
                }
            } // Daire bazlı döngü sonu

            // *** PERFORMANS OPTİMİZASYONU: Tüm kayıtları tek sorguda ekle ***
            if (!empty($batchRecords)) {
                $BorcDetay->batchInsert($batchRecords);
                $logger->info("Batch insert: " . count($batchRecords) . " kayıt eklendi.");
            }
        } elseif ($borclandirma_turu == "evsahibi") {

            //Siteye ait tüm ev sahiplerine borçlandırma yapılıyor
            //Sitenin Akfif ev sahiplerini getir
            $evsahipleri = $Kisiler->SiteAktifEvSahipleri($site_id);
            $batchRecords = []; // Batch için kayıt toplama

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
                $batchRecords[] = $data;
            }

            // Batch insert
            if (!empty($batchRecords)) {
                $BorcDetay->batchInsert($batchRecords);
            }
        } elseif ($borclandirma_turu == "block") {
            //Eğer kişiler boş ise tüm bloga borçlandir
            $kisiler = $_POST["hedef_kisi"];

            //Bloklara borçlandırma yapılıyor
            //Blogun aktif kişilerini getir
            if (empty($kisiler)) {

                $kisiler = $Kisiler->BlokKisileri(Security::decrypt($_POST["block_id"]));
            }

            $batchRecords = []; // Batch için kayıt toplama
            foreach ($kisiler as $kisi) {
                $data["kisi_id"] = Security::decrypt($kisi);
                $data["blok_id"] = Security::decrypt($_POST["block_id"]);
                $batchRecords[] = $data;
            }

            // Batch insert
            if (!empty($batchRecords)) {
                $BorcDetay->batchInsert($batchRecords);
            }
        } elseif ($borclandirma_turu == "person") {
            //Kişilere borçlandırma yapılıyor
            $person_ids = $_POST["hedef_kisi"];
            $batchRecords = []; // Batch için kayıt toplama

            /** Eğer birden fazla kişi gelmişse */
            if (is_array($person_ids) && count($person_ids) > 1) {
                $logger->info("Kişi Borçlandırma yapılıyor: " . json_encode($person_ids));

                $sifresiKisiIds = array_map([App\Helper\Security::class, 'decrypt'], $person_ids);
                $persons = $Kisiler->getKisilerByIds($sifresiKisiIds);

                foreach ($persons as $person) {
                    $data["id"] = $BorcDetay->getDetayId($id, $person->id) ?? 0;
                    $data["kisi_id"] = $person->id;
                    $data["daire_id"] = $person->daire_id;
                    $batchRecords[] = $data;
                }
            } else {
                //Tek kişi gelmişse 
                $person = $Kisiler->find(Security::decrypt($person_ids[0]));
                $logger->info("Bulunan kişi: " . json_encode($person));

                /** Eğer id 0'dan farklı ise */
                if($id != 0){
                    $data["id"] = $BorcDetay->getDetayId($id, $person->id) ?? 0;
                }
                //$logger->info("Borc Detay id: " . json_encode($BorcDetay->getDetayId($id,$persons->id)));

                $data["kisi_id"] = $person->id;
                $data["daire_id"] = $person->daire_id;
                $batchRecords[] = $data;
            }
        

            // Batch insert
            if (!empty($batchRecords)) {
                /** Tüm veritabanı işlemlerini tek seferde yapar. Insert veya Update */
                $BorcDetay->batchUpsert($batchRecords);
            }
        } else if ($borclandirma_turu == 'dairetipi') {
            //Daire tipine göre borçlandırma yapılıyor
            $daire_tipleri = $_POST["apartment_type"];

            $batchRecords = []; // Batch için kayıt toplama

            //Daire Tipi id'lerinde döngü yap
            foreach ($daire_tipleri as $daire_tipi_id) {
                $daire_tipi_id = Security::decrypt($daire_tipi_id);

                //Daireler tablosundan bu daire tipine sahip daireleri getir
                $daireler = $Daire->DaireTipineGoreDaireler($daire_tipi_id);

                foreach ($daireler as $daire) {
                    $data["kisi_id"] = $Kisiler->AktifKisiByDaire($daire->id)->id; // Daireye ait aktif kişinin ID'sini alıyoruz
                    $data["blok_id"] = $daire->blok_id; // Daireye ait blok ID'sini alıyoruz
                    $data["daire_id"] = $daire->id; // Daire ID'sini ekliyoruz
                    $batchRecords[] = $data;
                }
            }

            // Batch insert
            if (!empty($batchRecords)) {
                $BorcDetay->batchInsert($batchRecords);
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

if ($_POST["action"] == "borclandirma_sil") {



    $id = $_POST["id"];
    //İlişkili tahsilatlar varsa silinmesin
    $tahsilat = $TahsilatDetay->BorclandirmaTahsilatVarmi($id);
    if ($tahsilat) {
        $res = [
            "status" => "error",
            "message" => "Bu borçlandırma ile ilişkili tahsilatlar var, silinemez! id:" . Security::decrypt($id)
        ];
        echo json_encode($res);
        exit;
    }

    try {
        $BorcDetay->BorclandirmaDetaylariniSil($id);
        $Borc->softDelete(Security::decrypt($id));

        $res = [
            "status" => "success",
            "message" => "Borçlandırma başarı ile silindi!",
            "id" => $id
        ];
    } catch (PDOException $e) {
        $res = [
            "status" => "error",
            "message" => $e->getMessage()
        ];
    }
    echo json_encode($res);
}


if ($_POST["action"] == "delete_debit_detail") {
    try {
        // Güvenlik: ID'yi çöz ve ilişkili tahsilat var mı kontrol et
        $borcDetayId = is_numeric($_POST["id"]) ? (int)$_POST["id"] : Security::decrypt($_POST["id"]);

        // İlgili borç detayına ait herhangi bir tahsilat varsa silme
        $varMi = $TahsilatDetay->findFirstByBorcId((int)$borcDetayId);
        if ($varMi) {
            echo json_encode([
                "status" => "error",
                "message" => "Bu borç detayı ile ilişkili tahsilatlar mevcut. Önce tahsilatları iptal/taşıyın ya da borcu yeniden yapılandırın (silinemez)."
            ]);
            exit;
        }

        $BorcDetay->delete($borcDetayId);

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


/** Tekli borçlandırma kaydet
 * Bu işlem, tek bir borçlandırma kaydı oluşturur.
 * @param array $_POST
 */
if ($_POST["action"] == "save_debit_single") {


    $db->beginTransaction();
    try {


        //echo json_encode($_POST);        exit;
        $borc_id = Security::decrypt($_POST["borc_id"]);
        $kisi_id = Security::decrypt($_POST["kisi_id"]);

        //kisinin daire id'sini al
        $kisi = $Kisiler->find($kisi_id);

        // echo json_encode(['borc_id'=>$borc_id,'kisi_id'=>$kisi_id]);        exit;

        $borc = $Borc->find($borc_id);
        $borc_adi = $Borc->findWithDueName($borc_id)->borc_adi ?? '';
        if (!$borc) {
            throw new Exception("Borç kaydı bulunamadı. ID: {$borc_id}");
        }

        $borc_detay_id = Security::decrypt($_POST["borc_detay_id"]) ?? 0;
        $site_id = $_SESSION['site_id'];

        $data = [
            "id" => $borc_detay_id,
            "borclandirma_id" =>  $borc_id,
            "borc_adi" => $borc_adi,
            "kisi_id" => $kisi_id,
            "daire_id" => $kisi->daire_id,
            "tutar" => Helper::formattedMoneyToNumber($_POST["tutar"]),
            "baslangic_tarihi" => $borc->baslangic_tarihi,
            "bitis_tarihi" => $borc->bitis_tarihi,
            "son_odeme_tarihi" => $borc->bitis_tarihi,
            'ceza_orani' => $_POST["ceza_orani"],
            'aciklama' => $_POST["aciklama"],
            'borclandirma_tarihi' => $_POST["baslangic_tarihi"] ?? $borc->baslangic_tarihi,


        ];

        $BorcDetay->saveWithAttr($data);

        // Borçlandırma kaydı başarıyla oluşturuldu
        // $db->commit(); // İşlemi onayla

        $logger->info("Borçlandırma kaydı başarıyla oluşturuldu: " . json_encode($data));

        $db->commit(); // İşlemi onayla

        $status = "success";
        $message = "Borçlandırma kaydı başarıyla oluşturuldu!";
    } catch (PDOException $ex) {
        $db->rollBack(); // Hata durumunda işlemi geri al
        $status = "error";
        $message = $ex->getMessage();
    }

    $res = [
        "status" => $status,
        "message" => $message,
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


/* Sitede yapılan borçlandırmaların bilgilerini getirir
* @param borclandirma_id (şifreli)
*/
if ($_POST["action"] == "get_borclandirma_info") {
    $borclandirma_id = $_POST["id"];

    $data = $Borc->find($borclandirma_id);

    if (empty($data)) {
        $res = [
            "status" => "error",
            "message" => "Borçlandırma bilgisi bulunamadı!"
        ];

        echo json_encode($res);
        exit;
    }

    //id'yi şifreli hale getiriyoruz
    $data->id = Security::encrypt($data->id);
    $data->baslangic_tarihi = Date::dmy($data->baslangic_tarihi);
    $data->bitis_tarihi = Date::dmy($data->bitis_tarihi);

    $res = [
        "status" => "success",
        "data" => $data
    ];

    echo json_encode($res);
}



//Borcu düzenlemek için borç bilgisini döndürür
if ($_POST['action'] == 'get_borclandirma_detay') {
    //Gate::can('borclandirma_bilgisi_getir');
    try {
        if (empty($_POST['id'])) {
            throw new Exception("Borç Detay ID'si gönderilmedi.");
        }

        $borcDetayId = Security::decrypt($_POST['id']);
        $borcDetay = $BorcDetay->find($borcDetayId);
        if (!$borcDetay) {
            throw new Exception("Borç Detay kaydı bulunamadı.");
        }
        $status = "success";
        $message = "Borç Detay bilgisi başarıyla getirildi.";
    } catch (Exception $e) {
        http_response_code(400); // Hata durumunda uygun bir HTTP kodu gönder
        $status = "error";
        $message = $e->getMessage();
    }
    $res = [
        "status" => $status,
        "message" => $message,
        "data" => $borcDetay ?? null
    ];
    echo json_encode($res);
    exit;
}
