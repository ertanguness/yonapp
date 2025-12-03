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
        $sql = "SELECT 
                    id, site_id, adi_soyadi, tc_kimlik_no, dogum_tarihi, cinsiyet, telefon,
                    personel_tipi, eposta, adres, gorev_yeri, durum,
                    ise_baslama_tarihi AS baslangic_tarihi,
                    isten_ayrilma_tarihi AS bitis_tarihi,
                    acil_kisi, yakinlik, acil_telefon,
                    silinme_tarihi, kayit_tarihi, guncelleme_tarihi
                FROM {$this->table}
                WHERE id = ?
                  AND silinme_tarihi IS NULL
                  AND LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(personel_tipi,' ',''),'ü','u'),'ö','o'),'ğ','g'),'ş','s'),'ç','c'),'ı','i')) = 'guvenlik'";
        $query = $this->db->prepare($sql);
        $query->execute([$id]);
        return $query->fetch(PDO::FETCH_OBJ);
    }

    public function Personeller()
    {
        $site_id = $_SESSION["site_id"] ?? 0;

        $sql = "SELECT 
                    id, site_id, adi_soyadi, tc_kimlik_no, dogum_tarihi, cinsiyet, telefon,
                    personel_tipi, eposta, adres, gorev_yeri, durum,
                    ise_baslama_tarihi AS baslama_tarihi,
                    isten_ayrilma_tarihi AS bitis_tarihi,
                    acil_kisi, yakinlik, acil_telefon,
                    silinme_tarihi, kayit_tarihi, guncelleme_tarihi
                FROM {$this->table}
                WHERE site_id = ?
                  AND silinme_tarihi IS NULL
                  AND LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(personel_tipi,' ',''),'ü','u'),'ö','o'),'ğ','g'),'ş','s'),'ç','c'),'ı','i')) = 'guvenlik'
                ORDER BY adi_soyadi ASC";

        $query = $this->db->prepare($sql);
        $query->execute([$site_id]);
        return $query->fetchAll(PDO::FETCH_OBJ);
    }
}
