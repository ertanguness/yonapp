<?php

namespace Model;

//Model klasoru altında bulunan BaseModel sınıfını dahil ediyoruz
use Model\Model;
use PDO;

class GuvenlikPersonelModel extends Model
{
    protected $table = "personel"; // Vardiya tanımları tablosu

    public function __construct()
    {
        parent::__construct($this->table);
    }

    public function PersonelBilgileri($id)
    {
        $query = $this->db->prepare("SELECT * FROM $this->table WHERE id = ?");
        $query->execute([$id]);
        return $query->fetch(PDO::FETCH_OBJ); // nesne döner
    }

    public function Personeller()
    {
        $site_id = $_SESSION["site_id"] ?? 0;

        $sql = "SELECT id, adi_soyadi, tc_kimlik_no, telefon, gorev_yeri, 
                   durum, baslama_tarihi, bitis_tarihi, 
                   acil_kisi, acil_telefon
            FROM {$this->table}
            WHERE site_id = ?
            ORDER BY adi_soyadi ASC";

        $query = $this->db->prepare($sql);
        $query->execute([$site_id]);
        return $query->fetchAll(PDO::FETCH_OBJ);
    }
}
