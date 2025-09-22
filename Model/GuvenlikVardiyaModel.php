<?php

namespace Model;

//Model klasoru altında bulunan BaseModel sınıfını dahil ediyoruz
use Model\Model;
use PDO;

class GuvenlikVardiyaModel extends Model
{
    protected $table = "vardiya_tanimlari"; // Vardiya tanımları tablosu

    public function __construct()
    {
        parent::__construct($this->table);
    }

    public function VardiyaBilgileri($id)
    {
        $query = $this->db->prepare("SELECT * FROM $this->table WHERE id = ?");
        $query->execute([$id]);
        return $query->fetch(PDO::FETCH_OBJ); // nesne döner
    }

    public function Vardiyalar()
    {
        $site_id = $_SESSION["site_id"] ?? 0;

$sql = "
    SELECT vt.*, gy.ad AS gorev_yeri_adi
    FROM {$this->table} vt
    LEFT JOIN gorev_yerleri gy ON vt.gorev_yeri_id = gy.id
    WHERE gy.site_id = ? OR gy.site_id IS NULL
    ORDER BY vt.vardiya_adi ASC
";

$query = $this->db->prepare($sql);
$query->execute([$site_id]);
return $query->fetchAll(PDO::FETCH_OBJ);

    }
}
