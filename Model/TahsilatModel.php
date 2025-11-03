<?php 
namespace Model;

use Model\Model;
use PDO;

class TahsilatModel extends Model{
    protected $table = "tahsilatlar";

    public function __construct()
    {
        parent::__construct($this->table);
    }


    /**
     * İşlenen Tahsilatları Getirir
     * @param int $tahsilat_id
     * @return array
     */
    public function IslenenTahsilatlar($tahsilat_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table 
                                    WHERE tahsilat_onay_id = ?");
        $sql->execute([$tahsilat_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    /**Tahsilatlat ile ilgili son hareket kaydını getirir
     * @param int $tahsilat_id
     * @return string|null
     */
    
    public function SonHareketTarihi($tahsilat_id)
    {
        $sql = $this->db->prepare("SELECT islem_tarihi FROM $this->table 
                                    WHERE tahsilat_onay_id = ? 
                                    ORDER BY islem_tarihi DESC LIMIT 1");
        $sql->execute([$tahsilat_id]);
        $result = $sql->fetch(PDO::FETCH_OBJ);
        return $result ? $result->islem_tarihi : null; // Eğer sonuç varsa tarihi döndür, yoksa null döndür
    }

    /**
     * Kisinin toplam tahsilat tutarını getirir
     * @param int $kisi_id
     * @return float
     */
    public function KisiToplamTahsilat($kisi_id)
    {
        $sql = $this->db->prepare("SELECT SUM(tutar) toplam_tahsilat 
                                          FROM $this->table 
                                          WHERE kisi_id = ? and silinme_tarihi IS NULL
                                          ");
        $sql->execute([$kisi_id]);
        $result = $sql->fetch(PDO::FETCH_OBJ);
        return $result ? (float)$result->toplam_tahsilat : 0.0; 
    }
    /**
     * Belirli bir kişinin tahsilatlarını getirir
     * @param int $kisi_id
     * @return array
     */
    public function KisiTahsilatlari($kisi_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table 
                                    WHERE kisi_id = ?  and silinme_tarihi IS NULL
                                    ORDER BY islem_tarihi DESC");
        $sql->execute([$kisi_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }


    // Tahsilat Modelinizdeki metodun güncellenmiş hali

public function KisiTahsilatlariWithDetails($kisi_id)
{
    // SQL sorgusu, ana tahsilat bilgileri ile detayları birleştirir.
    // LEFT JOIN kullanıyoruz çünkü bir tahsilatın henüz detayı olmayabilir (nadir durum).
    $sql = $this->db->prepare("SELECT 
                                            t.id  AS tahsilat_id,
                                            t.tutar  AS toplam_tutar,
                                            t.islem_tarihi,
                                            t.aciklama  AS ana_aciklama,
                                            td.odenen_tutar  AS detay_tutar,
                                            td.aciklama  AS detay_aciklama,
                                            bd.aciklama  AS borc_adi
                                        FROM tahsilatlar t
                                        LEFT JOIN tahsilat_detay td  ON t.id = td.tahsilat_id
                                        LEFT JOIN borclandirma_detayi bd  ON td.borc_detay_id = bd.id
                                        WHERE t.kisi_id = ? AND t.silinme_tarihi IS NULL

                                        UNION ALL 

                                        SELECT 
                                            tahsilat_id,
                                            t2.tutar AS toplam_tutar,
                                            k.islem_tarihi,
                                            k.aciklama AS ana_aciklama,
                                            k.tutar AS detay_tutar,
                                            k.aciklama AS detay_aciklama,
                                            'Krediye aktarım' AS borc_adi
                                        FROM kisi_kredileri k
                                        LEFT JOIN tahsilatlar t2 ON k.tahsilat_id = t2.id
                                        WHERE k.kisi_id = ? AND k.silinme_tarihi IS NULL
                                        ORDER BY islem_tarihi DESC, tahsilat_id DESC;");
    // $sql = $this->db->prepare("SELECT 
    //                                         t.id AS tahsilat_id,
    //                                         t.tutar AS toplam_tutar,
    //                                         t.islem_tarihi,
    //                                         t.aciklama AS ana_aciklama,
    //                                         td.odenen_tutar AS detay_tutar,
    //                                         td.aciklama AS detay_aciklama,
    //                                         bd.aciklama AS borc_adi
    //                                     FROM 
    //                                         tahsilatlar t
    //                                     LEFT JOIN 
    //                                         tahsilat_detay td ON t.id = td.tahsilat_id
    //                                     LEFT JOIN
    //                                         borclandirma_detayi bd ON td.borc_detay_id = bd.id
    //                                     WHERE 
    //                                         t.kisi_id = ? AND t.silinme_tarihi IS NULL
    //                                     ORDER BY 
    //                                         t.islem_tarihi DESC, t.id DESC
    // ");
    $sql->execute([$kisi_id, $kisi_id]);
    $results = $sql->fetchAll(PDO::FETCH_OBJ);

    // Şimdi sonuçları PHP'de tahsilat bazında gruplayalım.
    $groupedTahsilatlar = [];
    foreach ($results as $row) {
        $tahsilatId = $row->tahsilat_id;

        // Eğer bu tahsilatı daha önce görmediysek, ana bilgileriyle ekleyelim.
        if (!isset($groupedTahsilatlar[$tahsilatId])) {
            $groupedTahsilatlar[$tahsilatId] = [
                'id' => $tahsilatId,
                'toplam_tutar' => $row->toplam_tutar,
                'islem_tarihi' => $row->islem_tarihi,
                'ana_aciklama' => $row->ana_aciklama,
                'detaylar' => [] // Detayları tutacak boş bir dizi
            ];
        }

        // Eğer bu satırda bir detay bilgisi varsa, onu detaylar dizisine ekleyelim.
        if ($row->detay_tutar !== null) {
            $groupedTahsilatlar[$tahsilatId]['detaylar'][] = [
                'borc_adi' => $row->borc_adi,
                'tutar' => $row->detay_tutar,
                'aciklama' => $row->detay_aciklama // "Anapara Ödemesi" veya "Gecikme Zammı Ödemesi"
            ];
        }
    }

    return $groupedTahsilatlar;
}



// Tahsilat Modelinizde veya bir Service sınıfında
public function tahsilatiSil(int $tahsilatId, int $silenKullaniciId)
{
    // 1. Veritabanı Transaction'ını Başlat
    $this->db->beginTransaction();

    try {
        // 2. Silinecek tahsilatın detaylarını bul
        $detaylar = $this->db->prepare("SELECT * FROM tahsilat_detaylari WHERE tahsilat_id = ?");
        $detaylar->execute([$tahsilatId]);
        $tahsilatDetaylari = $detaylar->fetchAll(PDO::FETCH_OBJ);

        // 3. Borç Bakiyelerini Geri Yükle
        foreach ($tahsilatDetaylari as $detay) {
            // Açıklamaya göre hangi kolona ekleme yapacağımızı belirleyelim
            // Bu kısım, tahsilat kaydederken kullandığınız açıklamalara bağlıdır.
            if (str_contains(strtolower($detay->aciklama), 'anapara')) {
                $kolonAdi = 'kalan_tutar';
            } else if (str_contains(strtolower($detay->aciklama), 'gecikme')) {
                $kolonAdi = 'kalan_gecikme_zammi';
            } else {
                // Bilinmeyen bir detay tipi varsa, işlemi iptal et ve hata ver
                throw new \Exception("Bilinmeyen tahsilat detayı türü: " . $detay->aciklama);
            }

            // İlgili borcun bakiyesine, ödenen tutarı geri ekle
            $borcGuncelleSql = "UPDATE borclandirma_detayi SET {$kolonAdi} = {$kolonAdi} + ? WHERE id = ?";
            $borcStmt = $this->db->prepare($borcGuncelleSql);
            $borcStmt->execute([$detay->tutar, $detay->borc_detay_id]);
        }

        // 4. Varsa Kredi Kaydını Geri Al (Bu kısım sizin yapınıza göre değişir)
        // Örnek:
        $krediSilSql = "UPDATE kisi_kredileri SET silinme_tarihi = NOW(), silen_kullanici = ? WHERE tahsilat_id = ?";
        $krediStmt = $this->db->prepare($krediSilSql);
        $krediStmt->execute([$silenKullaniciId, $tahsilatId]);

        // 5. Tahsilat Detaylarını "Silindi" Olarak İşaretle
        $detaySilSql = "UPDATE tahsilat_detaylari SET silinme_tarihi = NOW(), silen_kullanici = ? WHERE tahsilat_id = ?";
        $detaySilStmt = $this->db->prepare($detaySilSql);
        $detaySilStmt->execute([$silenKullaniciId, $tahsilatId]);
        
        // Ana Tahsilat Kaydını "Silindi" Olarak İşaretle
        $tahsilatSilSql = "UPDATE tahsilatlar SET silinme_tarihi = NOW(), silen_kullanici = ? WHERE id = ?";
        $tahsilatSilStmt = $this->db->prepare($tahsilatSilSql);
        $tahsilatSilStmt->execute([$silenKullaniciId, $tahsilatId]);

        // 6. Transaction'ı Onayla
        $this->db->commit();

        return ['status' => true, 'message' => 'Tahsilat başarıyla silindi ve etkileri geri alındı.'];

    } catch (\Exception $e) {
        // Hata durumunda tüm işlemleri geri al
        $this->db->rollBack();
        
        // Hata loglaması yapılabilir
        // getLogger()->error('Tahsilat silme hatası: ' . $e->getMessage());
        
        return ['status' => false, 'message' => 'Tahsilat silinirken bir hata oluştu: ' . $e->getMessage()];
    }
}

/**
     * Site genelindeki tüm tahsilatları (ödemeleri)
     * en yeniden en eskiye doğru sıralı olarak getirir.
     * Bu tahsilatın kime ve hangi kasaya yapıldığı bilgilerini de JOIN ile alır.
     *
     * @param int $siteId İsteğe bağlı, belirli bir siteye ait tahsilatları filtrelemek için.
     * @return array Tüm tahsilat kayıtlarının bir dizisi.
     */
    public function getTumTahsilatlar(int $siteId ): array
    {
        // Kişi adı, daire kodu ve kasa adını almak için gerekli JOIN'leri yapıyoruz.
        $sql = "SELECT 
                    t.id,
                    t.tutar,
                    t.islem_tarihi,
                    t.aciklama,
                    t.makbuz_no,
                    kisi.adi_soyadi,
                    d.daire_kodu,
                    kasa.kasa_adi,
                    kasa.id AS kasa_id,
                    COALESCE(SUM(kkk.kullanilan_tutar), 0) AS kullanilan_kredi
                 
                FROM 
                    tahsilatlar t
                LEFT JOIN 
                    kisiler kisi ON t.kisi_id = kisi.id
                LEFT JOIN 
                    daireler d ON kisi.daire_id = d.id
                LEFT JOIN 
                    bloklar bl ON d.blok_id = bl.id
                LEFT JOIN 
                    kasa kasa ON t.kasa_id = kasa.id
                LEFT JOIN 
                    kisi_kredi_kullanimlari kkk ON t.id = kkk.tahsilat_id
                WHERE 
                    t.silinme_tarihi IS NULL
                    and tutar >= 0
                    -- Eğer site ID'si verilmişse, sadece o siteye ait kayıtları getir.
                    AND (:site_id IS NULL OR bl.site_id = :site_id)
                GROUP BY 
                    t.id, t.tutar, t.islem_tarihi, t.aciklama, t.makbuz_no,
                    kisi.adi_soyadi, d.daire_kodu, kasa.kasa_adi, kasa.id
                ORDER BY 
                    t.islem_tarihi DESC, t.id DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            // Site ID parametresini bağla. Eğer null ise, WHERE koşulu bunu atlayacaktır.
            $stmt->bindParam(':site_id', $siteId, $siteId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);

        } catch (\PDOException $e) {
            error_log("Tüm tahsilatlar getirilirken hata: " . $e->getMessage());
            return [];
        }
    }


    /* Tahsilatın işlenen tutarını getirir */
    public function getIslenenTutar($tahsilat_onay_id)
    {
        $sql = $this->db->prepare("SELECT SUM(tutar) as toplam_tutar 
                                          FROM $this->table 
                                          WHERE tahsilat_onay_id = ? 
                                          AND silinme_tarihi IS NULL");
        $sql->execute([$tahsilat_onay_id]);
        $result = $sql->fetch(PDO::FETCH_OBJ);
        return $result ? (float)$result->toplam_tutar : 0.0; 
    }



/** Tahsilatı kasa adıyla beraber getirir
 * @param int $tahsilat_id
 * @return object
 */
public function getPaymentWithCaseName($tahsilat_id)
{
    $sql = $this->db->prepare("SELECT t.*,k.kasa_adi FROM $this->table t
                                      LEFT JOIN kasa k ON k.id = t.kasa_id
                                      WHERE t.id = ?");
    $sql->execute([$tahsilat_id]);
    return $sql->fetch(PDO::FETCH_OBJ);
    
}

}