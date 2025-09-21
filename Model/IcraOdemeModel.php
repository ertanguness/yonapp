<?php

namespace Model;

//Model klasoru altında bulunan BaseModel sınıfını dahil ediyoruz
use Model\Model;
use PDO;

class IcraOdemeModel extends Model
{
    protected $table = "icra_odemeler";

    public function __construct()
    {
        parent::__construct($this->table);
    }
    /**
     * Giriş yapan Kullanıcının sitelerini getirir
     * @return array
     */

    public function IcraOdemeler()
    {
        $site_id = $_SESSION['site_id'];

        $sql = "SELECT i.* 
            FROM icralar i
            INNER JOIN kisiler k ON i.kisi_id = k.id
            WHERE k.site_id = :site_id
            ORDER BY i.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['site_id' => $site_id]);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }


    public function IcraOdemeBilgileri($id)
    {
        $query = $this->db->prepare("SELECT * FROM $this->table WHERE icra_id = ?");
        $query->execute([$id]);
        return $query->fetchAll(PDO::FETCH_OBJ);
    }
    public function IcraTaksitBilgileri($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
}
