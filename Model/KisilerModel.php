<?php

namespace Model;

use Model\BloklarModel;
use Model\Model;
use PDO;

class KisilerModel extends Model
{
    protected $table = 'kisiler';

    public function __construct()
    {
        parent::__construct($this->table);
    }

    // aidat tablosundaki verileri alır
    public function getKisiler()
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    // Bloğun kişilerini getir
    public function BlokKisileri($block_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE blok_id = ?");
        $sql->execute([$block_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Siteye ait blokları ve bu bloklara ait kişileri getirir.
     *
     * @param int $site_id Parametre olarak gelen site ID'si.
     * @return array Kişileri içeren bir dizi döner.
     */
    public function SiteKisileri($site_id)
    {
        $Bloklar = new BloklarModel();
        $bloklar = $Bloklar->SiteBloklari($site_id);
        $kisiler = [];

        foreach ($bloklar as $blok) {
            $blok_kisileri = $this->BlokKisileri($blok->id);
            if (!empty($blok_kisileri)) {
                $kisiler = array_merge($kisiler, $blok_kisileri);
            }
        }

        return $kisiler;
    }

    /**
     * Belirli bir kişinin bilgilerini getirir.
     * @param int $id Kişinin ID'si.
     * @return object|null Kişi bilgilerini içeren nesne veya bulunamazsa null döner.
     */
    public function getPersonById($id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE id = ?");
        $sql->execute([$id]);
        return $sql->fetch(PDO::FETCH_OBJ);
    }

    /***Kişi ID'sinden Kişi Adını Getirir
     * @param int $id Kişinin ID'si.
     * @return string|null Kişinin adı veya bulunamazsa null döner.
     */
    public function KisiAdi($id)
    {
        $sql = $this->db->prepare("SELECT adi_soyadi FROM $this->table WHERE id = ?");
        $sql->execute([$id]);
        $result = $sql->fetch(PDO::FETCH_OBJ);
        return $result ? $result->adi_soyadi : null;
    }
 
    /**
     * Belirli bir blok_id'ye sahip kişilerin daire numaralarını getirir.
     * @param int $blok_id Blok ID'si.
     * @return array Daire numaralarını içeren bir dizi döner.
     */
   
}
