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
                   gp.adi_soyadi AS personel_adi, 
                   gp.telefon AS personel_telefon
            FROM {$this->table} gv
            INNER JOIN guvenlik_personel gp ON gv.personel_id = gp.id
            WHERE gp.site_id = ?
            ORDER BY gv.id DESC";

    $query = $this->db->prepare($sql);
    $query->execute([$site_id]);
    return $query->fetchAll(PDO::FETCH_OBJ);
}

}
