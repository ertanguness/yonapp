<?php

namespace Model;

//Model klasoru altında bulunan BaseModel sınıfını dahil ediyoruz
use Model\Model;
use PDO;

class AyarlarModel extends Model
{
    protected $table = "ayarlar";

    public function __construct()
    {
        parent::__construct($this->table);
    }
    /**
     * Giriş yapan Kullanıcının ayarlarını getirir
     * @return array
     */

     public function Ayarlar()
     {
         $site_id = $_SESSION['site_id'];
        
         $query = $this->db->prepare("SELECT * FROM {$this->table} WHERE site_id = :site_id ORDER BY id DESC LIMIT 1");
         $query->execute(['site_id' => $site_id]);
         $result = $query->fetch(PDO::FETCH_OBJ);
         return $result;
     }
     

}
