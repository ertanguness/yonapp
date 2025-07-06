<?php 

namespace Model;

use Model\Model;
use App\Helper\Security;

class KisiKredileriModel extends Model
{
    protected $table = "kisi_kredileri";

    public function __construct()
    {
        parent::__construct($this->table);
    }

    /**
     * Kisi kredilerini getirir
     * @param int $kisi_id
     * @return array
     */
    public function getKisiKredileri($kisi_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE kisi_id = ?");
        $sql->execute([$kisi_id]);
        return $sql->fetchAll(\PDO::FETCH_OBJ);
    }
}
