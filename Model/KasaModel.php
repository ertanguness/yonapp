<?php

namespace Model;

use Model\Model;

class KasaModel extends Model
{
    protected $table = "kasa";

    protected $view_kasa_finansal_durum = "view_kasa_finansal_durum"; // Görünüm tablosu, eğer kullanacaksanız

    public function __construct()
    {
        parent::__construct($this->table);
    }


    /**sitenin Kasa listesini, kasanın finansal durumu ile beraber getirir 
     * @param int $site_id
     * @return array
     */
    public function SiteKasaListesiFinansOzet($site_id)
    {
        $query = "SELECT 
                        k.*,
                        kfd.toplam_gelir,
                        kfd.toplam_gider,
                        kfd.bakiye
                    FROM $this->table k  
                    LEFT JOIN $this->view_kasa_finansal_durum kfd ON k.id = kfd.kasa_id
                    WHERE k.site_id = :site_id AND k.aktif_mi = 1
                    ORDER BY k.id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':site_id', $site_id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }




    /**Sitenin Kasalarini getirir
     * @param int $site_id
     * @return array
     */
    public function SiteKasalari()
    {

        $site_id = $_SESSION['site_id'] ?? 0; // Kullanıcının site_id'sini al, eğer yoksa 0 olarak ayarla
        $query = "SELECT * FROM {$this->table} WHERE site_id = :site_id AND aktif_mi = 1 ORDER BY id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':site_id', $site_id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * Kasaların gelir gider toplamlarını ve bakiyesini getirir
     * @param int $site_id
     * @return object|null
     */
    public function KasaFinansalDurum($kasa_id)
    {
        $query = "SELECT * FROM {$this->view_kasa_finansal_durum} 
                  WHERE kasa_id = :kasa_id
                  GROUP BY kasa_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':kasa_id', $kasa_id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }

    /**
     * Kasanın belirtilen tarih aralığındaki finansal özetini döndürür.
     * @param int $kasa_id
     * @param string $baslangic YYYY-MM-DD veya YYYY-MM-DD HH:ii:ss
     * @param string $bitis YYYY-MM-DD veya YYYY-MM-DD HH:ii:ss
     * @param string $yon 'Gelir' | 'Gider' | '' (opsiyonel ek tür filtresi)
    * @return object
     */
    public function KasaFinansalDurumByDateRange(int $kasa_id, string $baslangic, string $bitis, string $yon = ''): object
    {
        // Gün başı/sonu normalizasyonu
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', trim((string)$baslangic))) {
            $baslangic .= ' 00:00:00';
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', trim((string)$bitis))) {
            $bitis .= ' 23:59:59';
        }

        $query = "SELECT 
                    SUM(CASE WHEN islem_tipi='Gelir' THEN tutar ELSE 0 END) AS toplam_gelir,
                    SUM(CASE WHEN islem_tipi='Gider' THEN ABS(tutar) ELSE 0 END) AS toplam_gider,
                    SUM(tutar) AS bakiye
                  FROM kasa_hareketleri
                  WHERE kasa_id = :kasa_id
                  AND silinme_tarihi IS NULL
                  AND tutar != 0
                  AND islem_tarihi BETWEEN :baslangic AND :bitis";

        if ($yon && in_array($yon, ['Gelir','Gider'])) {
            $query .= " AND islem_tipi = :yon";
        }

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':kasa_id', $kasa_id, \PDO::PARAM_INT);
        $stmt->bindParam(':baslangic', $baslangic, \PDO::PARAM_STR);
        $stmt->bindParam(':bitis', $bitis, \PDO::PARAM_STR);
        if ($yon && in_array($yon, ['Gelir','Gider'])) {
            $stmt->bindParam(':yon', $yon, \PDO::PARAM_STR);
        }
        $stmt->execute();
        $res = $stmt->fetch(\PDO::FETCH_OBJ) ?: (object)['toplam_gelir'=>0,'toplam_gider'=>0,'bakiye'=>0];
        // Null güvenliği
        $res->toplam_gelir = (float)($res->toplam_gelir ?? 0);
        $res->toplam_gider = (float)($res->toplam_gider ?? 0);
        $res->bakiye       = (float)($res->bakiye ?? 0);
        return $res;
    }

    /** Varsayılan kasayı getirir
     * @return object|null
     */
    public function varsayilanKasa()
    {
        $query = "SELECT * FROM {$this->table} WHERE varsayilan_mi = 1 LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }


    /** Varsayılan kasa yapar
     * @param int $kasa_id
     * @return bool
     */
    public function varsayilanKasaYap($kasa_id)
    {

        // Önce tüm kasaların varsayılan_mi alanını 0 yap
        $query = "UPDATE {$this->table} SET varsayilan_mi = 0";
        $stmt = $this->db->prepare($query);
        $stmt->execute();

        // Sonra belirtilen kasanın varsayılan_mi alanını 1 yap
        $query = "UPDATE {$this->table} SET varsayilan_mi = 1 WHERE id = :kasa_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':kasa_id', $kasa_id, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    /** ID'ye göre kasa getir
     * @param int $id
     * @return object|null
     */
    public function getById($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }

    /** Sadece banka hesaplarını getir
     * @return array
     */
    public function getBankaHesaplari()
    {
        $site_id = $_SESSION['site_id'] ?? 0;
        $query = "SELECT * FROM {$this->table} 
                  WHERE site_id = :site_id 
                  AND kasa_tipi = 'Banka' 
                  AND aktif_mi = 1 
                  ORDER BY kasa_adi ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':site_id', $site_id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
}
