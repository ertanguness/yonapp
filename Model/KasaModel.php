<?php 

namespace Model;

use Model\Model;

class KasaModel extends Model{
    protected $table = "kasa";

    public function __construct()
    {
        parent::__construct($this->table);
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
    
}