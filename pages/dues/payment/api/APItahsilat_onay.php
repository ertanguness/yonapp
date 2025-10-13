<?php

require_once dirname(__DIR__, levels: 4) . '/configs/bootstrap.php';

use App\Helper\Helper;
use App\Services\Gate;
use App\Helper\Security;
use Model\DairelerModel;
use Model\BorclandirmaModel;
use Model\BorclandirmaDetayModel;
use Model\KisilerModel;
use Model\KisiKredileriModel;
use Model\TahsilatHavuzuModel;
use Model\TahsilatModel;
use Model\TahsilatDetayModel;
use Model\TahsilatOnayModel;
use Model\FinansalRaporModel;
use Model\KasaModel;
use Model\KasaHareketModel;
use App\Helper\FinansalHelper;
use App\Helper\Alert;
use Database\Db;
use PhpOffice\PhpSpreadsheet\Calculation\Engine\Logger;

$Borc = new BorclandirmaModel();
$BorcDetay = new BorclandirmaDetayModel();
$Tahsilat = new TahsilatModel();
$TahsilatHavuzu = new TahsilatHavuzuModel();
$TahsilatOnay = new TahsilatOnayModel();
$TahsilatDetay = new TahsilatDetayModel();
$Daire = new DairelerModel();
$Kisi = new KisilerModel();
$KisiKredi = new KisiKredileriModel();
$FinansalRapor = new FinansalRaporModel();
$Kasa = new KasaModel();
$KasaHareket = new KasaHareketModel();


Security::checkLogin();

$logger = \getLogger();
$db = Db::getInstance();


if ($_POST['action'] == 'tahsilati_borc_ile_eslestir') {
    $onayId = $_POST['onay_id'] ?? null;
    $islenecekTutar = (float)($_POST['islenecek_tutar'] ?? 0);
    $borcIdleri = $_POST['borc_idler'] ?? [];
    $toplamIslenenTutar = 0;
    $kasa_id = $Kasa->varsayilanKasa()->id ?? null;
    $artani_kredi_olarak_ekle = $_POST['artani_kredi_olarak_ekle'];

    try {
        // --- 1. Girdi Doğrulaması ---
        if (empty($onayId)) {
            throw new Exception('Onay ID gönderilmedi.');
        }
        if (!$artani_kredi_olarak_ekle && (empty($borcIdleri) || !is_array($borcIdleri))) {
            Alert::error('En az bir borç seçilmelidir.');
        }
        if ($islenecekTutar <= 0) {
            Alert::error('İşlenecek tutar sıfırdan büyük olmalıdır.');
        }

        $logger->info("Tahsilat-Borç eşleştirme işlemi başlatıldı", [
            'onay_id' => $onayId,
            'gelen_tutar' => $islenecekTutar,
            'kullanici_id' => $_SESSION['user']->id ?? null
        ]);

        // --- 2. Veritabanı Transaction Başlat ---
        $db->beginTransaction();

        // --- 3. Gerekli Kayıtları Kontrol Et ---
        $onayKaydi = $TahsilatOnay->find($onayId);
        if (!$onayKaydi) throw new Exception('Onay kaydı bulunamadı.');

        $varsayilanKasa = $Kasa->varsayilanKasa();
        if (!$varsayilanKasa) throw new Exception('Varsayılan kasa bulunamadı. Lütfen sistem ayarlarını kontrol edin.');

        // --- 4. Ana Tahsilat Kaydını Oluştur ---
        $tahsilatData = [
            'kisi_id' => $onayKaydi->kisi_id,
            'tutar' => $islenecekTutar,
            'islem_tarihi' => $onayKaydi->islem_tarihi ?? date('Y-m-d H:i:s'),
            'kasa_id' => $kasa_id,
            'tahsilat_onay_id' => $onayId, // Toplam onaylanan tutarları almak için
            'aciklama' => $onayKaydi->aciklama ?? 'Borç mahsubu',
            'olusturan' => $_SESSION['user']->id ?? null,
            'olusturulma_tarihi' => date('Y-m-d H:i:s'),
        ];
        $tahsilatId = $Tahsilat->saveWithAttr($tahsilatData);
        if (!$tahsilatId) throw new Exception('Ana tahsilat kaydı oluşturulamadı.');
        $tahsilatId = Security::decrypt($tahsilatId);



        // Kasa hareketi ekle
        $kasaHareketData = [
            'id' => 0,
            'kasa_id' => $kasa_id,
            'tahsilat_id' => $tahsilatId,
            'kisi_id' => $onayKaydi->kisi_id,
            'islem_tarihi' => $onayKaydi->islem_tarihi ?? date('Y-m-d H:i:s'),
            'tutar' => $islenecekTutar,
            'aciklama' =>  $onayKaydi->aciklama ?? 'Banka Tahsilatı',
            'kayit_yapan' => $_SESSION['user']->id ?? null,
            'kaynak_tablo' => 'tahsilat_onay',
            'kaynak_id' => $onayId
        ];
        $kasaHareketId = $KasaHareket->saveWithAttr($kasaHareketData);
        if (!$kasaHareketId) throw new Exception('Kasa hareketi kaydı oluşturulamadı.');

        $logger->info("Ana tahsilat kaydı oluşturuldu ve kasaya eklendi.", [
            'tahsilat_id' => $tahsilatId,
            'kasa_hareket_id' => $kasaHareketId,
            'tutar' => $islenecekTutar
        ]);



        //eğer seçilen borç yoksa ve artanı kredi olarak ekle işaretli ise direk krediye ekle
        if (empty($borcIdleri) && $artani_kredi_olarak_ekle) {
            $krediData = [
                'kisi_id' => $onayKaydi->kisi_id,
                'tutar' => $islenecekTutar,
                'aciklama' => 'Borç mahsubu sonrası artan tutar.',
                'islem_tarihi' => $onayKaydi->islem_tarihi ?? date('Y-m-d H:i:s'),
                'tahsilat_id' => $tahsilatId
            ];
            $KisiKredi->saveWithAttr($krediData);
            $logger->info("Artan tutar krediye eklendi.", ['kisi_id' => $onayKaydi->kisi_id, 'kredi_tutari' => $islenecekTutar]);
            //onay kaydını işlenmiş yap
            $onay_data = [
                'id' => $onayId,
                'onay_durumu' => 1,
                "onaylayan_yonetici" => $_SESSION['user']->id ?? null,
                'onay_tarihi' => date('Y-m-d H:i:s'),
                "onay_aciklamasi" => 'Borçlara mahsup edildi.',

            ];

            $TahsilatOnay->saveWithAttr($onay_data);
            $db->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Tahsilat başarıyla krediye eklendi.',
                'data' => [
                    'onay_id' => $onayId,
                    'bankaya_yatan' => $onayKaydi->tutar,
                    'toplam_tahsilat' => $islenecekTutar,
                    'borclara_islenen' => 0,
                    'islenen_tutar' => 0,
                    'kalan_tutar' => 0,
                    'krediye_aktarilan' => round($islenecekTutar, 2),
                ]
            ]);
            exit;
        }





        // --- 5. Tahsilat Tutarını Borçlara Dağıtma Mantığı ---
        $kalanDagitilacakTutar = $islenecekTutar;

        // NOT: borc_detaylari_param fonksiyonunun, borcun 'id', 'kalan_gecikme_zammi',
        // 'kalan_toplam_borc' (anapara kalanı), 'odenen_tutar' ve 'odenen_gecikme_zammi'
        // gibi temel alanları döndürdüğünden emin olun.
        $borc_detaylari = $BorcDetay->borc_detaylari_param(implode(',', array_map(function ($id) {
            return Security::decrypt($id);
        }, $borcIdleri)));
        if (empty($borc_detaylari)) {
            throw new Exception('Seçilen borçlara ait detaylar bulunamadı.');
        }
        $logger->info("Borç detayları alındı.", ['borc_detay_bilgisi' => json_encode($borc_detaylari, JSON_PRETTY_PRINT)]);

        foreach ($borc_detaylari as $borcKaydi) {
            if ($kalanDagitilacakTutar <= 0.001) { // Kuruş farkları için tolerans
                break; // Dağıtılacak para kalmadıysa döngüden çık
            }

            $logger->info("Borç kaydı işleniyor.", ['borc_detay_id' => $borcKaydi->id, 'kalan_dagitilacak_tutar' => $kalanDagitilacakTutar]);

            // Borcun toplam kalanını hesapla (gecikme zammı + anapara)
            $kalanGecikmeZammi = (float)($borcKaydi->kalan_gecikme_zammi ?? 0);
            $kalanAnaPara = (float)($borcKaydi->kalan_toplam_borc ?? 0);
            $toplamKalanBuBorc = $kalanGecikmeZammi + $kalanAnaPara;


            if ($toplamKalanBuBorc <= 0) {
                $logger->info("Borç zaten ödenmiş, atlanıyor.", ['borc_detay_id' => $borcKaydi->id]);
                continue;
            }

            // 1. Bu borca ne kadar ödeme yapılabileceğini hesapla.
            $buBorcaUygulanacakTutar = min($kalanDagitilacakTutar, $toplamKalanBuBorc);

            // 2. Ödemeyi önce gecikme zammına, kalanı anaparaya dağıt.
            $gecikmeyeOdenen = min($buBorcaUygulanacakTutar, $kalanGecikmeZammi);

            $logger->info("işlenecek tutarlar hesaplanıyor.", [
                'borc_detay_id' => $borcKaydi->id,
                'kalan_gecikme_zammi' => $kalanGecikmeZammi,
                'gecikmeye_odenebilecek' => $gecikmeyeOdenen
            ]);
            $anaParayaOdenen = $buBorcaUygulanacakTutar - $gecikmeyeOdenen;


            // --- YENİ MANTIK: GECİKME ZAMMI VE ANAPARA İÇİN AYRI KAYITLAR ---

            // 3a. Gecikme zammı için ayrı bir tahsilat detayı oluştur (eğer ödeme varsa)
            if ($gecikmeyeOdenen > 0) {
                $tahsilatDetayGecikme = [
                    'borc_detay_id' => $borcKaydi->id,
                    'tahsilat_id' => $tahsilatId,
                    'odenen_tutar' => $gecikmeyeOdenen,
                    'aciklama' => 'Gecikme zammı ödemesi',
                    'islem_tarihi' => date('Y-m-d H:i:s'),
                ];
                $TahsilatDetay->saveWithAttr($tahsilatDetayGecikme);
                $logger->info("Gecikme zammı için tahsilat detayı oluşturuldu.", [
                    'borc_detay_id' => $borcKaydi->id,
                    'odenen_gecikme' => $gecikmeyeOdenen
                ]);
            }

            // 3b. Anapara için ayrı bir tahsilat detayı oluştur (eğer ödeme varsa)
            if ($anaParayaOdenen > 0) {
                $tahsilatDetayAnapara = [
                    'borc_detay_id' => $borcKaydi->id,
                    'tahsilat_id' => $tahsilatId,
                    'odenen_tutar' => $anaParayaOdenen,
                    'aciklama' => 'Anapara ödemesi',
                    'islem_tarihi' => date('Y-m-d H:i:s'),
                ];
                $TahsilatDetay->saveWithAttr($tahsilatDetayAnapara);
                $logger->info("Anapara için tahsilat detayı oluşturuldu.", [
                    'borc_detay_id' => $borcKaydi->id,
                    'odenen_anapara' => $anaParayaOdenen
                ]);
            }

            //kalan_borc, kalan_gecikme_zammi güncelle
            $borc_data = [
                'id' => $borcKaydi->id,
                'kalan_borc' => $kalanAnaPara - $anaParayaOdenen,
                'kalan_gecikme_zammi' => $kalanGecikmeZammi - $gecikmeyeOdenen
            ];
            $BorcDetay->saveWithAttr($borc_data);


            //Eğer tutar kalan borca eşit veya büyükse, borç durumunu "ödendi" yap
            $onay_durumu = $onayKaydi->tutar - $buBorcaUygulanacakTutar <= 0.01 ? 1 : 0; // 1: Tamamlandı, 0: Kısmi

            $onay_data = [
                'id' => $onayId,
                'onay_durumu' => $onay_durumu,
                "onaylayan_yonetici" => $_SESSION['user']->id ?? null,
                'onay_tarihi' => date('Y-m-d H:i:s'),
                "onay_aciklamasi" => 'Borçlara mahsup edildi.',

            ];

            $TahsilatOnay->saveWithAttr($onay_data);


            // 5. Kalan tutarı ve toplam işlenen tutarı güncelle
            $kalanDagitilacakTutar -= $buBorcaUygulanacakTutar;
            $toplamIslenenTutar += $buBorcaUygulanacakTutar;
        }

        // --- 6. Artan Para Varsa Krediye Ekle ---
        if ($kalanDagitilacakTutar > 0.01) {
            $krediData = [
                'kisi_id' => $onayKaydi->kisi_id,
                'tutar' => $kalanDagitilacakTutar,
                'aciklama' => 'Borç mahsubu sonrası artan tutar.',
                'islem_tarihi' => $onayKaydi->islem_tarihi ?? date('Y-m-d H:i:s'),
                'tahsilat_id' => $tahsilatId
            ];
            $KisiKredi->saveWithAttr($krediData);
            $logger->info("Artan tutar krediye eklendi.", ['kisi_id' => $onayKaydi->kisi_id, 'kredi_tutari' => $kalanDagitilacakTutar]);
        }

        // --- 7. Onay Kaydınının güncel bilgilerini tekrar al ---
        $onaylanan_toplam_tutar = $TahsilatOnay->OnaylanmisTahsilatToplami($onayId);

        // --- 8. İşlemi Sonlandır ---
        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Tahsilat başarıyla borçlara yansıtıldı.',
            'data' => [
                'onay_id' => $onayId,
                'bankaya_yatan' => $onayKaydi->tutar,
                'toplam_tahsilat' => $islenecekTutar,
                'borclara_islenen' => Helper::formattedMoney($toplamIslenenTutar),
                'islenen_tutar' => Helper::formattedMoney($onaylanan_toplam_tutar),
                'kalan_tutar' => ($onayKaydi->tutar - $onaylanan_toplam_tutar),
                'krediye_aktarilan' => round($kalanDagitilacakTutar, 2),
            ]
        ]);
    } catch (Exception $e) {
        $db->rollback();
        $logger->error("Tahsilat eşleştirme hatası", [
            'error' => $e->getMessage(),
            'onay_id' => $onayId,
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        echo json_encode(['success' => false, 'message' => 'Hata: ' . $e->getMessage()]);
    }
    exit;
}

/**
 * Eşleşmeyen havuzuna gönderir
 * /
 * / NOT: Bu işlem, tahsilat onay kaydını "işlendi" olarak işaretlemez.
 */
if ($_POST['action'] == 'eslesmeyen_havuza_gonder') {
    $onayId = Security::decrypt($_POST['tahsilat_id'] ?? null);

    try {
        // --- 1. Girdi Doğrulaması ---
        if (empty($onayId)) {
            throw new Exception('Onay ID gönderilmedi.');
        }

        //eğer daha önce havuza gönderilmişse hata ver
        $onay_kaydi = $TahsilatOnay->find($onayId);
        if ($onay_kaydi->eslesmeyen_havuzunda == 1) {
            throw new Exception('Bu tahsilat zaten eşleşmeyen havuzunda.');
        }

        $logger->info("Tahsilat eşleşmeyen havuzuna gönderme işlemi başlatıldı", [
            'onay_id' => $onayId,
            'kullanici_id' => $_SESSION['user']->id ?? null
        ]);

        // --- 2. Veritabanı Transaction Başlat ---
        $db->beginTransaction();

        // --- 3. Gerekli Kayıtları Kontrol Et ---
        $onayKaydi = $TahsilatOnay->find($onayId);
        if (!$onayKaydi) throw new Exception('Onay kaydı bulunamadı.');

        // İşlenen tutarları hesapla
        $islenen_tutar = $Tahsilat->getIslenenTutar($onayId) ?? 0;
       //if (!$islenen_tutar) throw new Exception('İşlenen tutar bulunamadı.');

        $logger->info("Onay kaydı ve işlenen tutar doğrulandı.", [
            'onay_id' => $onayId,
            'islenen_tutar' => $islenen_tutar
        ]);


        // --- 4. Tahsilat Havuzu Kaydını Oluştur ---
        $havuzData = [
            'id' => 0,
            'site_id' => $onayKaydi->site_id,
            'tahsilat_tutari' => $onayKaydi->tutar,
            'islenen_tutar' => $islenen_tutar,
            'islem_tarihi' => $onayKaydi->islem_tarihi ?? date('Y-m-d H:i:s'),
            'aciklama' => 'Eşleşmeyen havuzuna aktarım - ' . ($onayKaydi->aciklama ?? ''),
            'ham_aciklama' => $onayKaydi->aciklama,
            'olusturulma_tarihi' => date('Y-m-d H:i:s'),
            'tahsilat_onay_id' => $onayId // İleride referans için
        ];
        $havuzId = $TahsilatHavuzu->saveWithAttr($havuzData);
        if (!$havuzId) throw new Exception('Tahsilat havuzu kaydı oluşturulamadı.');
        $havuzId = Security::decrypt($havuzId);

        $logger->info("Tahsilat havuzu kaydı oluşturuldu.", [
            'tahsilat_havuzu_id' => $havuzId
        ]);
        // --- 5. Onay Kaydını "İşlendi" Olarak Güncelle ---
        $onay_data = [
            'id' => $onayId,
            'eslesmeyen_havuzunda' => 1, // Tamamlandı
            "havuza_gonderilme_tarihi" => date('Y-m-d H:i:s')
        ];
        $TahsilatOnay->saveWithAttr($onay_data);
        $logger->info("Tahsilat onay kaydı güncellendi.", [
            'onay_id' => $onayId,
            'onay_durumu' => 1
        ]);
        // --- 6. İşlemi Sonlandır ---
        $db->commit();
        echo json_encode([
            'status' => "success",
            'message' => 'Tahsilat başarıyla eşleşmeyen havuzuna gönderildi.',
            'data' => [
                'onay_id' => $onayId,
                'havuz_id' => $havuzId
            ]
        ]);
    } catch (Exception $e) {
        $db->rollback();
        $logger->error("Tahsilat havuzuna gönderme hatası", [
            'error' => $e->getMessage(),
            'onay_id' => $onayId,
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        echo json_encode(['status' => "error", 'message' => 'Hata: ' . $e->getMessage()]);
    }
    exit;
}



/**
 * Yüklenen tahsilat verisini sil
 * Excelden yüklenen ve tahsilat onayında bekleyen verileri siler
 */
if ($_POST['action'] == 'yuklenen_tahsilat_sil') {


    $id = Security::decrypt($_POST['tahsilat_id']);
    Gate::can('tahsilat_ekle_sil');

    try {
        $tahsilatOnay = $TahsilatOnay->find($id);
        if (!$tahsilatOnay) {
            throw new Exception('Tahsilat onay kaydı bulunamadı.');
        }

        // Tahsilat onay kaydını sil
        $TahsilatOnay->softDeleteByColumn('id', $id);

        // Başarılı mesajı
        $status = 'success';
        $message = 'Tahsilat onay kaydı başarıyla silindi.';
    } catch (Exception $e) {
        $status = 'error';
        $message = $e->getMessage();
    }

    echo json_encode([
        'status' => $status,
        'message' => $message,
    ]);
}
