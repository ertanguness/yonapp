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
}