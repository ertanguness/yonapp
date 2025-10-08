<?php

namespace Model;

use Model\Model;
use PDO;

class BloklarModel extends Model
{
    protected $table = "bloklar";

    public function __construct()
    {
        parent::__construct($this->table);
    }

    public function SiteBloklari($site_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE site_id = ?");
        $sql->execute([$site_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }
    public function BlokVarmi($site_id, $block_name)
    {
        $query = $this->db->prepare("SELECT COUNT(*) FROM $this->table  WHERE site_id = ? AND blok_adi = ?");
        $query->execute([$site_id, $block_name]);
        return $query->fetchColumn() > 0;
    }
    public function SitedekiBloksayisi($site_id)
    {
        $sql = $this->db->prepare("SELECT COUNT(*) as count FROM $this->table WHERE site_id = ?");
        $sql->execute([$site_id]);
        $result = $sql->fetch(PDO::FETCH_OBJ);
        return $result ? (int)$result->count : 0;
    }
    public function SitedekiDaireSayisi($site_id)
    {
        $sql = $this->db->prepare("SELECT daire_sayisi FROM $this->table WHERE site_id = ?");
        $sql->execute([$site_id]);
        $results = $sql->fetchAll(PDO::FETCH_OBJ);
        $total = 0;
        foreach ($results as $row) {
            $total += (int)$row->daire_sayisi;
        }
        return $total;
    }
    public function Blok($blok_id)
    {
        $sql = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $sql->execute([$blok_id]);
        return $sql->fetch(PDO::FETCH_OBJ);
    }

    /********************************************************************** */
    /**
     * Gelen id'den blok adını getirir
     * @param int $blok_id
     * @return string|null
     */
    public function BlokAdi($blok_id)
    {
        $sql = $this->db->prepare("SELECT blok_adi FROM {$this->table} WHERE id = ?");
        $sql->execute([$blok_id]);
        $result = $sql->fetch(PDO::FETCH_OBJ);
        return $result ? $result->blok_adi : null; // Eğer sonuç varsa blok adını döndür, yoksa null döndür
    }

    /********************************************************************** */
    /** Gelen Site id ve Blok adından blok var mı yok mu kontrol eder
     * @param int $site_id
     * @param string $blok_adi
     * @return object|null
     */
    public function findBlokBySiteAndName($site_id, $blok_adi)
    {
        $sql = $this->db->prepare("SELECT * FROM {$this->table} WHERE site_id = ? AND blok_adi = ?");
        $sql->execute([$site_id, $blok_adi]);
        return $sql->fetch(PDO::FETCH_OBJ); // Eğer blok varsa döndür, yoksa null döndür
    }

  
    /**
     * Verilen site_id'ye göre kaç tane blok olduğunu döndürür
     * @param int $site_id
     * @return int
     */
    public function BlokSayisi($site_id)
    {
        // Blok sayısını döndür
        $sql = $this->db->prepare("SELECT COUNT(*) as count FROM {$this->table} WHERE site_id = ?");
        $sql->execute([$site_id]);
        $result = $sql->fetch(PDO::FETCH_OBJ);
        $blok_sayisi = $result ? (int)$result->count : 0;

        // Daire sayısını topla
        $sql2 = $this->db->prepare("SELECT SUM(daire_sayisi) as toplam_daire FROM {$this->table} WHERE site_id = ?");
        $sql2->execute([$site_id]);
        $result2 = $sql2->fetch(PDO::FETCH_OBJ);
        $toplam_daire = $result2 ? (int)$result2->toplam_daire : 0;

        // Hem blok sayısını hem toplam daire sayısını döndür
        return [
            'blok_sayisi' => $blok_sayisi,
            'toplam_daire' => $toplam_daire
        ];
    }
}
