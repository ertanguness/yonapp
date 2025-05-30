<?php

namespace Model;

use Model\Model;
use PDO;

class DairelerModel extends Model
{
    protected $table = "daireler"; 

    public function __construct()
    {
        parent::__construct($this->table);
    }


    /**
     * Daire kodundan dairenin id'sini döndürür
     * @param string $daire_kodu
     * @return int|null
     */
    public function DaireId($daire_kodu)
    {

        $query = $this->db->prepare("SELECT id FROM $this->table WHERE daire_kodu = ?");
        $query->execute([$daire_kodu]);
        $result = $query->fetch(PDO::FETCH_OBJ);

        return $result ? $result->id : 0; // Eğer sonuç varsa id'yi döndür, yoksa null döndür
    }
    public function SitedekiDaireler($siteID)
    {
        $query = $this->db->prepare("SELECT * FROM {$this->table} WHERE site_id = ? ORDER BY blok_id ASC, daire_no ASC");
        $query->execute([$siteID]);
        return $query->fetchAll(PDO::FETCH_OBJ);
    }
    public function DaireVarmi($site_id, $block_id, $daire_no)
    {
        $query = $this->db->prepare("SELECT COUNT(*) FROM $this->table WHERE site_id = ? AND blok_id = ? AND daire_no = ?");
        $query->execute([$site_id, $block_id, $daire_no]);
        return $query->fetchColumn() > 0;
    }
    public function DaireKoduVarMi($site_id, $block_id, $daire_kodu)
    {
        $query = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE site_id = ? AND blok_id = ? AND daire_kodu = ?");
        $query->execute([$site_id, $block_id, $daire_kodu]);
        return $query->fetchColumn() > 0;
    }
    public function BlokDaireleri($blok_id)
    {
        $sql = $this->db->prepare("SELECT id, daire_no FROM $this->table WHERE blok_id = ?");
        $sql->execute([$blok_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ); // Her daire bir nesne olarak dönsün
    }
}
