<?php

namespace Model;

//Model klasoru altında bulunan BaseModel sınıfını dahil ediyoruz
use Model\Model;
use PDO;

class ZiyaretciModel extends Model
{
    protected $table = "ziyaretci";

    public function __construct()
    {
        parent::__construct($this->table);
    }
    /**
     * Giriş yapan Kullanıcının sitelerini getirir
     * @return array
     */

     public function Ziyaretciler()
     {
         $sql = "SELECT 
                     z.id,
                     z.ad_soyad,
                     z.giris_tarihi,
                     z.giris_saati,
                     z.cikis_saati,
                     z.durum,
                     k.adi_soyadi AS ziyaret_edilen,
                     b.blok_adi,
                     d.daire_no
                 FROM ziyaretci z
                 LEFT JOIN kisiler k ON z.ziyaret_edilen_id = k.id
                 LEFT JOIN bloklar b ON k.blok_id = b.id
                 LEFT JOIN daireler d ON k.daire_id = d.id
                 ORDER BY z.id DESC";
     
         $stmt = $this->db->query($sql);
         return $stmt->fetchAll(\PDO::FETCH_OBJ);
     }
    
    public function ZiyaretciBilgileri($id)
    {
        $query = $this->db->prepare("SELECT * FROM $this->table WHERE id = ?");
        $query->execute([$id]);
        return $query->fetch(PDO::FETCH_OBJ);
    }
}
