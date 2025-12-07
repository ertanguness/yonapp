<?php

namespace Model;

use Model\Model;
use PDO;

class PersonelModel extends Model
{
    protected $table = 'personel';
    public function __construct()
    {
        parent::__construct($this->table);
    }

    public function getPersonel()
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table 
        WHERE site_id = ? and site_id != ? AND silinme_tarihi IS NULL 
        ORDER BY adi_soyadi ASC");
        $sql->execute([$_SESSION['site_id'],0]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Personel sil (soft delete)
     * @param int $id Personel ID'si
     * @return bool Silme sonucu
     */
    public function deletePersonel($id)
    {
        $sql = $this->db->prepare("UPDATE $this->table SET silinme_tarihi = NOW() WHERE id = ?");
        return $sql->execute([$id]);
    }
}
