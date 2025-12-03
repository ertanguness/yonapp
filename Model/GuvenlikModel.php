<?php

namespace Model;

//Model klasoru altında bulunan BaseModel sınıfını dahil ediyoruz
use Model\Model;
use PDO;

class GuvenlikModel extends Model
{
    protected $table = "guvenlik_vardiyalar"; // Vardiya tanımları tablosu

    public function __construct()
    {
        parent::__construct($this->table);
    }

    public function GuvenlikVardiyaBilgileri($id)
    {
        $query = $this->db->prepare("SELECT * FROM $this->table WHERE id = ?");
        $query->execute([$id]);
        return $query->fetch(PDO::FETCH_OBJ); // nesne döner
    }

    public function GuvenlikVardiyalari()
{
    $site_id = $_SESSION["site_id"] ?? 0;

    $sql = "SELECT gv.*, 
                   p.adi_soyadi AS personel_adi, 
                   p.telefon AS personel_telefon
            FROM {$this->table} gv
            INNER JOIN personel p ON gv.personel_id = p.id
            WHERE p.site_id = ?
              AND p.silinme_tarihi IS NULL
              AND LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(p.personel_tipi,' ',''),'ü','u'),'ö','o'),'ğ','g'),'ş','s'),'ç','c'),'ı','i')) = 'guvenlik'
            ORDER BY gv.id DESC";

    $query = $this->db->prepare($sql);
    $query->execute([$site_id]);
    return $query->fetchAll(PDO::FETCH_OBJ);
}

}
