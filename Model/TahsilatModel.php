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
}