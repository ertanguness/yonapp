<?php

require_once dirname(__DIR__, levels: 4) . '/configs/bootstrap.php';

use App\Helper\Helper;
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

    try {
        // --- 1. Girdi Doğrulaması ---
        if (empty($onayId)) {
            throw new Exception('Onay ID gönderilmedi.');
        }
        if (empty($borcIdleri) || !is_array($borcIdleri)) {
            throw new Exception('En az bir borç seçilmelidir.');
        }
        if ($islenecekTutar <= 0) {
            throw new Exception('İşlenecek tutar sıfırdan büyük olmalıdır.');
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
            'islem_tarihi' => date('Y-m-d H:i:s'),
            'kasa_id' => $varsayilanKasa->id,
            'aciklama' => 'Yönetici onayı ile borçlara mahsup edildi.(Banka havuzundan)',
            'olusturan' => $_SESSION['user']->id ?? null,
            'olusturulma_tarihi' => date('Y-m-d H:i:s'),
        ];
        $tahsilatId = $Tahsilat->saveWithAttr($tahsilatData);
        if (!$tahsilatId) throw new Exception('Ana tahsilat kaydı oluşturulamadı.');
        $tahsilatId = Security::decrypt($tahsilatId);

        $logger->info("Ana tahsilat kaydı oluşturuldu.", ['tahsilat_id' => $tahsilatId]);

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
            


            $onay_data = [
                'id' => $onayId,
                'onay_durumu' => 1,
                "onaylayan_yonetici" => $_SESSION['user']->id ?? null,
                'onay_tarihi' => date('Y-m-d H:i:s'),
                "onay_aciklamasi" => 'Borçlara mahsup edildi.'
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
                'islem_tarihi' => date('Y-m-d H:i:s'),
                'tahsilat_id' => $tahsilatId
            ];
            $KisiKredi->saveWithAttr($krediData);
            $logger->info("Artan tutar krediye eklendi.", ['kisi_id' => $onayKaydi->kisi_id, 'kredi_tutari' => $kalanDagitilacakTutar]);
        }

        // --- 7. Onay Kaydını İşlendi Olarak Güncelle ---
        // $TahsilatOnay->update($onayId, ['durum' => 'islem_tamamlandi', 'islenen_tutar' => $toplamIslenenTutar]);

        // --- 8. İşlemi Sonlandır ---
        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Tahsilat başarıyla borçlara yansıtıldı.',
            'data' => [
                'onay_id' => $onayId,
                'toplam_tahsilat' => $islenecekTutar,
                'borclara_islenen' => $toplamIslenenTutar,
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
