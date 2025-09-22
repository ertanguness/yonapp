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
use Database\Db;




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
    try {
        // JavaScript'ten gelen verileri al
        $onayId = $_POST['onay_id'] ?? null;
        $islenecekTutar = $_POST['islenecek_tutar'] ?? 0;
        $borcIdleri = $_POST['borc_idler'] ?? []; // Array olarak gelir

        //Varsayılan Kasayı al
        $varsayilanKasa = $Kasa->varsayilanKasa();

            // Verileri kontrol et
        if (empty($onayId)) {
            echo json_encode([
                'success' => false,
                'message' => 'Onay ID gerekli.'
            ]);
            exit;
        }


        if (empty($borcIdleri) || !is_array($borcIdleri)) {
            echo json_encode([
                'success' => false,
                'message' => 'En az bir borç seçilmelidir.'
            ]);
            exit;
        }

        if ($islenecekTutar <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Geçerli bir tutar girilmelidir.'
            ]);
            exit;
        }
 
        // Verileri debug için log'a yazdır
        $logger->info("Tahsilat eşleştirme işlemi başlatıldı", [
            'onay_id' => $onayId,
            'islenecek_tutar' => $islenecekTutar,
            'borc_idleri' => $borcIdleri[0],
            'kullanici_id' => $_SESSION['user']->id ?? null
        ]);



        // Transaction başlat
        $db->beginTransaction();

        // Burada işlem mantığınızı yazabilirsiniz
        // Örnek:

        // 1. Onay kaydını kontrol et
        $onayKaydi = $TahsilatOnay->find($onayId);
        if (!$onayKaydi) {
            throw new Exception('Onay kaydı bulunamadı.');
        }


        //Tahsilat tablosuna kayıt yap
        $tahsilatData = [
            'kisi_id' => $onayKaydi->kisi_id,
            'tutar' => $islenecekTutar,
            'islem_tarihi' => date('Y-m-d H:i:s'),
            'kasa_id' => $varsayilanKasa->id ?? null,
            'aciklama' => 'Yönetici onaylı tahsilat ile eklendi',
            'olusturulma_tarihi' => $_SESSION['user']->id ?? null,
            'olusturan' => date('Y-m-d H:i:s'),
        ];
        $tahsilatId = $Tahsilat->saveWithAttr($tahsilatData);
        if (!$tahsilatId) {
            throw new Exception('Tahsilat kaydı oluşturulamadı.');
        };

        $logger->info("Borç kaydına ait tahsilat eklendi", ['tahsilat_id' => Security::decrypt($tahsilatId), 'data' => json_encode($tahsilatData, JSON_UNESCAPED_UNICODE)]);
     

     
 

        // 2. Borç kayıtlarını kontrol et
        foreach ($borcIdleri as $borcId) {
            // Her borç ID'sini kontrol et ve işle
            $borcKaydi = $BorcDetay->find($borcId, true);
            if (!$borcKaydi) {
                throw new Exception("Borç kaydı bulunamadı: ID $borcId");
            }

            //Gelen Tutar kadar borçtan düş
            $kalanBorc = $borcKaydi->tutar - $borcKaydi->odenen_tutar;
            if ($kalanBorc <= 0) {
                continue; // Bu borç zaten tamamen ödenmiş
            }

            // Borç kaydını güncelle (örnek)
            $data = [
                'borc_detay_id' => Security::decrypt($borcId),
                'tahsilat_id' => Security::decrypt($tahsilatId),
                'odenen_tutar' =>  $islenecekTutar,
                'aciklama' => 'Yönetici onaylı tahsilat ile güncellendi',
                'islem_tarihi' => date('Y-m-d H:i:s'),
            ];
            $TahsilatDetay->saveWithAttr($data);


            $logger->info("Borç kaydına ait tahsilat eklendi", ['borc_id' => Security::decrypt($borcId), 'data' => json_encode($data, JSON_UNESCAPED_UNICODE)]);
        }
   
        // 3. Tahsilat işlemlerini gerçekleştir
        // ... işlem mantığınız buraya ...

        // Transaction commit
        $db->commit();
     

        echo json_encode([
            'success' => true,
            'message' => 'Tahsilat başarıyla borçlara yansıtıldı.',
            'data' => [
                'onay_id' => $onayId,
                'islenen_tutar' => $islenecekTutar,
                'etkilenen_borc_sayisi' => count($borcIdleri)
            ]
        ]);
    } catch (Exception $e) {
        // Hata durumunda rollback
        $db->rollback();

        $logger->error("Tahsilat eşleştirme hatası", [
            'error' => $e->getMessage(),
            'onay_id' => $onayId ?? null,
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        echo json_encode([
            'success' => false,
            'message' => 'İşlem sırasında hata oluştu: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Diğer action'lar buraya...
