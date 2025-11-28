<?php

namespace Model;

use App\Helper\Security;
use Model\Model;

class TahsilatDetayModel extends Model
{
    protected $table = "tahsilat_detay";
    protected $vw_detay_table = "view_tahsilat_detay"; // Görünüm tablosu


    public function __construct()
    {
        parent::__construct($this->table);
    }

    /**
     * Tahsilat detaylarını getirir
     * @param int $tahsilat_id
     * @return array
     */
    public function getDetaylarByTahsilatId($tahsilat_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE tahsilat_id = ?");
        $sql->execute([$tahsilat_id]);
        return $sql->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * Tahsilat detaylarını tahsilat listesinde detay olarak gösterir
     * @param int $tahsilat_id
     * @return array
     */
    public function getDetaylarForList($tahsilat_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->vw_detay_table 
                                          WHERE tahsilat_id = ?");
        $sql->execute([$tahsilat_id]);
        $detaylar = $sql->fetchAll(\PDO::FETCH_OBJ);

        // Detayları formatlamak için
        foreach ($detaylar as &$detay) {
            $detay->odenen_tutar = number_format($detay->odenen_tutar, 2, ',', '.');
            $detay->islem_tarihi = date('d.m.Y', strtotime($detay->islem_tarihi));
        }

        return $detaylar;
    }

    /** Tahsilat detayını, toplam tutar ve tahsilat id olarak döndürür */
    public function getDetayByTahsilatId($borc_detay_id)
    {
        $sql = $this->db->prepare("SELECT SUM(odenen_tutar) AS toplam_odenen, tahsilat_id 
                                   FROM $this->table 
                                   WHERE borc_detay_id = ?");
        $sql->execute([$borc_detay_id]);
        return $sql->fetch(\PDO::FETCH_OBJ);
    }


    /*Tahsilat detaylarını sırasıyla al
        * @param int $tahsilat_id
        * @return array
        */
    public function findAllByBorcIdOrderedByDate($tahsilat_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table 
                                   WHERE borc_detay_id = ? 
                                   ORDER BY kayit_tarihi ASC");
        $sql->execute([$tahsilat_id]);
        return $sql->fetchAll(\PDO::FETCH_OBJ);
    
    }


    /*Borç id'sine gore toplam tahsilat tutarını getirir
        * @param int $borc_id
        * @return float
        */
    public function getToplamTahsilatByBorcId($borc_id)
    {
        $sql = $this->db->prepare("SELECT SUM(odenen_tutar) as toplam_tahsilat 
                                   FROM $this->table 
                                   WHERE borc_detay_id = ?");
        $sql->execute([$borc_id]);
        $result = $sql->fetch(\PDO::FETCH_OBJ);
        
        return $result ? (float)$result->toplam_tahsilat : 0.0;
    }


    /**Borç Güncellemeden dolayı oluşan tahsilat detayını silmek için
     * @param int $borc_detay_id
     * @return bool
     */
    public function deleteDetayByBorcDetayId(int $borc_detay_id): bool
    {
        // Borç detayına bağlı kredileri siler
        $sql = "DELETE FROM {$this->table} WHERE borc_detay_id = :borc_detay_id";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([':borc_detay_id' => $borc_detay_id]);
    }

    /* Verilen bir borc_detay_id'ye ait İLK tahsilat detay kaydını bulur ve nesne olarak döndürür.
    * Eğer kayıt bulunamazsa `false` döndürür.
    * @param int $borc_detay_id
    * @return object|false
    */
   public function findFirstByBorcId(int $borc_detay_id): object|false
   {
       // COUNT(*) yerine SELECT * ile kaydın tüm verisini istiyoruz.
       // LIMIT 1 ekleyerek sadece ilk bulduğunu getirmesini ve daha hızlı çalışmasını sağlıyoruz.
       $sql = "SELECT * FROM {$this->table} WHERE borc_detay_id = :borc_detay_id LIMIT 1";
       $stmt = $this->db->prepare($sql);
       $stmt->execute([':borc_detay_id' => $borc_detay_id]);
       
       // fetch() metodu, kayıt bulursa onu bir nesne/dizi olarak, bulamazsa 'false' olarak döndürür.
       // Bu, tam olarak ihtiyacımız olan şeydir.
       return $stmt->fetch(\PDO::FETCH_OBJ);
   }

   /**Borclandırma id'sine göre, yapılan tahsilatları listeler
    * @param int $borclandirma_id
    * @return array
    */
    public function getTahsilatlarByBorclandirmaId(int $borclandirma_id): array
    {
        $sql = $this->db->prepare("SELECT 
                                                td.* ,
                                                bd.borc_adi,
                                                bd.aciklama as borc_aciklama,
                                                k.adi_soyadi,
                                                ks.kasa_adi,
                                                d.daire_kodu
                                            FROM tahsilat_detay td
                                            LEFT JOIN tahsilatlar t ON t.id = td.tahsilat_id
                                            LEFT JOIN borclandirma_detayi bd ON bd.id = td.borc_detay_id
                                            LEFT JOIN kisiler k ON k.id = t.kisi_id
                                            LEFT JOIN daireler d ON d.id = k.daire_id
                                            LEFT JOIN kasa ks ON ks.id = t.kasa_id
                                            WHERE borc_detay_id IN (SELECT id FROM borclandirma_detayi WHERE borclandirma_id = ?)
                                            and td.silinme_tarihi IS NULL
                                         ");

        $sql->execute([$borclandirma_id]);
        return $sql->fetchAll(\PDO::FETCH_OBJ);
    }


    /**
     * Borçlandırmaya ait tahsilat var mı kontrolü
     * @param int $borclandirma_id
     * @return bool
     */
    public function BorclandirmaTahsilatVarmi($borclandirma_id): bool
    {
        $borclandirma_id = Security::decrypt($borclandirma_id);
        $sql = $this->db->prepare("SELECT 1 
                                   FROM $this->table 
                                   WHERE borc_detay_id IN (SELECT id 
                                                            FROM borclandirma_detayi 
                                                            WHERE borclandirma_id = ? )
                                                            and silinme_tarihi IS NULL
                                   LIMIT 1");
        $sql->execute([$borclandirma_id]);
        return $sql->fetchColumn() !== false;
    }

    /** Borçlandırma Detay bilgisi ile beraber detay döndürür
     * @param int $id
     * @return array
     */
    public function findAllByTahsilatIdWithDueDetails($tahsilat_id)
    {
        $sql = $this->db->prepare("SELECT td.*, bd.borc_adi,bd.aciklama as borc_aciklama 
                                   FROM $this->table td
                                   LEFT JOIN borclandirma_detayi bd ON bd.id = td.borc_detay_id
                                   WHERE td.tahsilat_id = ?");
        $sql->execute([$tahsilat_id]);
        return $sql->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * Birden fazla borç ID'si için toplam tahsilat tutarını getirir
     * @param array $borc_ids Borç detay ID'leri dizisi
     * @return float Toplam tahsilat tutarı
     */
    public function getTahsilatByBorcIds(array $borc_ids): float
    {
        if (empty($borc_ids)) {
            return 0.0;
        }

        $placeholders = implode(',', array_fill(0, count($borc_ids), '?'));
        $sql = "SELECT COALESCE(SUM(odenen_tutar), 0) as toplam_tahsilat
                FROM {$this->table}
                WHERE borc_detay_id IN ({$placeholders})
                  AND silinme_tarihi IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($borc_ids);
        $result = $stmt->fetch(\PDO::FETCH_OBJ);
        
        return (float)($result->toplam_tahsilat ?? 0.0);
    }




}
