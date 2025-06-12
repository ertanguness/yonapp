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
        $query = $this->db->prepare("SELECT COUNT(*) FROM blocks WHERE site_id = ? AND block_name = ?");
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
}
