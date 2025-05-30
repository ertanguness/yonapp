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
    
}