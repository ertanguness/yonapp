<?php 

namespace Model;

use Model\Model;
use PDO;

class BlockModel extends Model
{
    protected $table = "blocks"; 

    public function __construct()
    {
        parent::__construct($this->table);
    }

    public function getBlocksBySite($site_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE site_id = ?");
        $sql->execute([$site_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }
   
}