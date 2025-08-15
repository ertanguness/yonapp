<?php

namespace Model;

//Model klasoru altında bulunan BaseModel sınıfını dahil ediyoruz
use Model\Model;
use PDO;

class BakimModel extends Model
{
    protected $table = "bakim";

    public function __construct()
    {
        parent::__construct($this->table);
    }
    /**
     * Giriş yapan Kullanıcının sitelerini getirir
     * @return array
     */
    
     public function Bakimlar()
     {
         $site_id = $_SESSION['site_id']; // Kullanıcının ID'sini alıyoruz
         $sql = $this->db->prepare("SELECT * FROM $this->table WHERE site_id = ?");
         $sql->execute([$site_id]);
         return $sql->fetchAll(PDO::FETCH_OBJ);
     }
    public function BakimSonID()
    {
        $query = $this->db->query("SELECT MAX(id) AS last_id FROM bakim");
        $row = $query->fetch(PDO::FETCH_ASSOC);
        $lastId = $row && $row['last_id'] ? (int)$row['last_id'] + 1 : 1;
        return ['last_id' => $lastId];
    }
    public function BakimBilgileri($id)
    {
        $query = $this->db->prepare("SELECT * FROM $this->table WHERE id = ?");
        $query->execute([$id]);
        return $query->fetch(PDO::FETCH_OBJ);
    }
}
