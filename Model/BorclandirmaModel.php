<?php 


namespace Model;

use Model\Model;
use PDO;

class BorclandirmaModel extends Model
{
    protected $table = "borclandirma"; 

    public function __construct()
    {
        parent::__construct($this->table);
    }

   /**
    * Siteye ait tüm borçlandırmaları getirir.
    *@param int $site_id
    *@return array
    */
    public function all($site_id = null)
    {

        $query = "SELECT * FROM {$this->table} WHERE site_id = :site_id ORDER BY id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':site_id', $site_id, );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }


}
