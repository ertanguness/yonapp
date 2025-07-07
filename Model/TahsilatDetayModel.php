<?php 

namespace Model;

use App\Helper\Security;
use Model\Model;

class TahsilatDetayModel extends Model
{
    protected $table = "tahsilat_detay";

    public function __construct()
    {
        parent::__construct($this->table);
    }

    /**
     * Tahsilat detaylarını getirir
     * @param int $tahsilat_id
     * @return array
     */
    public function getTahsilatDetaylari($tahsilat_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE tahsilat_id = ?");
        $sql->execute([$tahsilat_id]);
        return $sql->fetchAll(\PDO::FETCH_OBJ);
    }
}

