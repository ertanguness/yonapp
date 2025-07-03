<?php

namespace Model;

//Model klasoru altında bulunan BaseModel sınıfını dahil ediyoruz
use Model\Model;
use PDO;

class ApartmentModel extends Model
{
    protected $table = "apartment";

    public function __construct()
    {
        parent::__construct($this->table);
    }


    public function getApartmentBySite($siteID)
    {
        $query = $this->db->prepare("SELECT * FROM {$this->table} WHERE site_id = ?");
        $query->execute([$siteID]);
        return $query->fetchAll(PDO::FETCH_OBJ);
    }
    public function isApartmentNameExists($site_id, $block_id, $daire_no)
    {
        $query = $this->db->prepare("SELECT COUNT(*) FROM apartment WHERE site_id = ? AND blok_id = ? AND daire_no = ?");
        $query->execute([$site_id, $block_id, $daire_no]);
        return $query->fetchColumn() > 0;
    }



}
