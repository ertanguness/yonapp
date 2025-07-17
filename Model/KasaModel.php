<?php 

namespace Model;

use Model\Model;

class KasaModel extends Model{
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
     * @return array
     */
    public function KasaFinansalDurum($site_id)
    {
        $query = "SELECT * FROM {$this->view_kasa_finansal_durum} 
                  WHERE site_id = :site_id
                  GROUP BY kasa_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':site_id', $site_id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
    
}