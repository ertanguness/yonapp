<?php 
require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';


use Database\Db;
use App\Helper\Security;
use Model\TahsilatModel;
use Model\TahsilatDetayModel;
use Model\KisiKredileriModel;
use Model\BorclandirmaModel;
use Model\BorclandirmaDetayModel;
use Model\KasaHareketModel;
use App\Helper\Date;

$db = Db::getInstance();
$logger = \getLogger();

$borcModel = new BorclandirmaModel();
$borcDetayModel = new BorclandirmaDetayModel();

$tahsilatModel = new TahsilatModel();
$tahsilatDetayModel = new TahsilatDetayModel();
$kisiKrediModel = new KisiKredileriModel();
$KasaHareket = new KasaHareketModel();


// action: 'get_tahsilat_detaylari'
if (isset($_POST['action']) && $_POST['action'] == 'get_tahsilat_detaylari') {
    header('Content-Type: application/json');

    try {
        if (empty($_POST['id'])) {
            throw new Exception("Tahsilat ID'si gönderilmedi.");
        }

        $tahsilatId = Security::decrypt($_POST['id']);

        // TahsilatDetayModel'iniz olduğunu varsayıyorum
        $tahsilatDetayModel = new TahsilatDetayModel();
        // Bu metot, bir tahsilat ID'si alıp tüm detaylarını getirmeli
        $detaylar = $tahsilatDetayModel->getDetaylarForList($tahsilatId);

        echo json_encode(['status' => 'success', 'data' => $detaylar]);

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}




////TASHİLAT SİLME İŞLEMİ
if ($_POST['action'] == 'delete_tahsilat') {
    header('Content-Type: application/json');

    $db->beginTransaction();
    try {
        if (empty($_POST['id'])) {
            throw new Exception("Tahsilat ID'si gönderilmedi.");
        }

        $sifreliTahsilatId = $_POST['id'];
        $tahsilatId = Security::decrypt($sifreliTahsilatId); // Temiz, şifresiz ID
        $silenKullaniciId = $_SESSION['user']->id; // Mevcut kullanıcı ID'si

        // 1. İlgili tüm kayıtları silmeden ÖNCE OKU (loglama ve işlem için)
        $tahsilatDetaylar = $tahsilatDetayModel->getDetaylarByTahsilatId($tahsilatId);
        $krediKayitlari = $kisiKrediModel->getKredilerByTahsilatId($tahsilatId);

     
        $tahsilat = $tahsilatModel->find($tahsilatId); // find metodu şifresiz ID almalı

        if (!$tahsilat) {
            throw new Exception("Silinecek tahsilat kaydı bulunamadı.");
        }


        // 2. BORÇ BAKİYELERİNİ GERİ YÜKLE (EN ÖNEMLİ ADIM)
        foreach ($tahsilatDetaylar as $detay) {
            $kolonAdi = str_contains(strtolower($detay->aciklama), 'gecikme') ? 'kalan_gecikme_zammi' : 'kalan_borc';
            // UPDATE borclandirma_detayi SET {$kolonAdi} = {$kolonAdi} + ? WHERE id = ?
            $borcDetayModel->increaseColumnValue($detay->borc_detay_id, $kolonAdi, $detay->odenen_tutar);
        }


        // 3. İlgili tüm kayıtları SİL (Tercihen Soft Delete)
        $tahsilatDetayModel->softDeleteByColumn('tahsilat_id', $tahsilatId, $silenKullaniciId);
        $kisiKrediModel->softDeleteByColumn('tahsilat_id', $tahsilatId, $silenKullaniciId);
        $tahsilatModel->softDelete($tahsilatId, $silenKullaniciId);

        
        //Eğer bu tahsilat için kasa hareketi varsa, onu da sil
        $KasaHareket->SilKaynakTabloKaynakId('tahsilat', $tahsilatId);

        // 4. Loglama
        $logger->info("Tahsilat (soft) silindi ve etkileri geri alındı", [
            'tahsilat_id' => json_encode($tahsilatId),
            'silen_kullanici_id' => $silenKullaniciId,
            'geri_alinan_detaylar' =>json_encode($tahsilatDetaylar),
            'geri_alinan_krediler' => json_encode($krediKayitlari),
            'silinen_tahsilat_ana_kaydi' => json_encode($tahsilat)
        ]);

        // 5. Başarılı olduysa transaction'ı onayla
        $db->commit();
        echo json_encode(['status' => 'success', 'message' => 'Tahsilat başarıyla silindi ve ilgili borçlar güncellendi.']);

    } catch (Exception $e) {
        $db->rollBack();
        http_response_code(400);
        // ... (hata loglama ve cevap kısmı aynı) ...
        $logger->error("Tahsilat silme işlemi sırasında hata oluştu", [
            'error' => $e->getMessage(),
            'tahsilat_id' => $_POST['id'],
            'silen_kullanici_id' => $_SESSION['user']->id ?? 'Bilinmiyor',
        ]);
        

        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
////TASHİLAT SİLME İŞLEMİ


//Tahsila detayı silme işlemi
if ($_POST['action'] == 'tahsilat_detay_sil') {

    $db->beginTransaction();
    try {
        if (empty($_POST['id'])) {
            throw new Exception("Tahsilat Detay ID'si gönderilmedi.");
        }

        $sifreliTahsilatDetayId = $_POST['id'];
        $tahsilatDetayId = Security::decrypt($sifreliTahsilatDetayId); // Temiz, şifresiz ID
        $silenKullaniciId = $_SESSION['user']->id; // Mevcut kullanıcı ID'si

        // 1. İlgili tüm kayıtları silmeden ÖNCE OKU (loglama ve işlem için)
        $tahsilatDetay = $tahsilatDetayModel->find($tahsilatDetayId);
        if (!$tahsilatDetay) {
            throw new Exception("Silinecek tahsilat detay kaydı bulunamadı.");
        }

        // 3. İlgili tüm kayıtları SİL (Tercihen Soft Delete)
       // $tahsilatDetayModel->softDelete($tahsilatDetayId, $silenKullaniciId);

        //Delete
        $tahsilatDetayModel->delete($sifreliTahsilatDetayId);

        // 4. Loglama
        $logger->info("Tahsilat Detayı (soft) silindi ve etkileri geri alındı", [
            'tahsilat_detay_id' => json_encode($tahsilatDetayId),
            'silen_kullanici_id' => $silenKullaniciId,
            'geri_alinan_tahsilat_detay_kaydi' => json_encode($tahsilatDetay),
        ]);

        // 5. Başarılı olduysa transaction'ı onayla
        $db->commit();
        echo json_encode(['status' => 'success', 'message' => 'Tahsilat detayı başarıyla silindi.']);
    } catch (Exception $e) {
        $db->rollBack();
        http_response_code(400);
        // ... (hata loglama ve cevap kısmı aynı) ...
        $logger->error("Tahsilat detayı silme işlemi sırasında hata oluştu", [
            'error' => $e->getMessage(),
            'tahsilat_detay_id' => $tahsilatDetayId,
            'silen_kullanici_id' => $_SESSION['user']->id ?? 'Bilinmiyor',
        ]);
        

        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}