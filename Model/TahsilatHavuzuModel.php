<?php 
namespace Model;

use Model\Model;
use PDO;



class TahsilatHavuzuModel extends Model{
    protected $table = "tahsilat_havuzu";

    public function __construct()
    {
        parent::__construct($this->table);
    }


    /*     * Sitenin Tahsilat Havuzunu Getirir
     * @param int $site_id
     * @return array
     */
    public function TahsilatHavuzu($site_id)
    {
        $sql = $this->db->prepare("SELECT th.* 
                                   FROM $this->table th
                                   WHERE th.site_id = ?");
        $sql->execute([$site_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    
}