<?php

namespace Model;

//Model klasoru altında bulunan BaseModel sınıfını dahil ediyoruz
use Model\Model;
use PDO;

class GuvenlikGorevYeriModel extends Model
{
    protected $table = "gorev_yerleri";

    public function __construct()
    {
        parent::__construct($this->table);
    }
    
     public function GorevYeriBilgileri($id)
     {
         $query = $this->db->prepare("SELECT * FROM $this->table WHERE id = ?");
         $query->execute([$id]);
         return $query->fetch(PDO::FETCH_OBJ); // nesne döner
     }
     
    public function GorevYerleri()
    {
        $site_id = $_SESSION["site_id"] ?? 0;
        $query = $this->db->prepare("SELECT * FROM $this->table WHERE site_id = ? ORDER BY ad ASC");
        $query->execute([$site_id]);
        return $query->fetchAll(PDO::FETCH_OBJ);
    }
}
