<?php

namespace Model;

//Model klasoru altında bulunan BaseModel sınıfını dahil ediyoruz
use Model\Model;
use PDO;

class SitelerModel extends Model
{
protected $table = "siteler"; 

    public function __construct()
    {
        parent::__construct($this->table);

    }
    /**
     * Giriş yapan Kullanıcının sitelerini getirir
     * @return array
     */
    public function Sitelerim()
    {
        $user_id = $_SESSION['user']->id; // Kullanıcının ID'sini alıyoruz
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE user_id = ?");
        $sql->execute([$user_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

  

    public function SiteBilgileri($id)
    {
        $query = $this->db->prepare("SELECT * FROM $this->table WHERE id = ?");
        $query->execute([$id]);
        $result = $query->fetch(PDO::FETCH_OBJ);
        return $result;
    }
}
