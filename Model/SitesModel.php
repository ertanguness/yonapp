<?php

namespace Model;

//Model klasoru altında bulunan BaseModel sınıfını dahil ediyoruz
use Model\Model;
use PDO;

class SitesModel extends Model
{
protected $table = "sites"; 

    public function __construct()
    {
        parent::__construct($this->table);

    }

    public function getSites()
    {
        $sql = $this->db->prepare("SELECT * FROM sites");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    public function getSiteName($id)
    {
        $query = $this->db->prepare("SELECT * FROM sites WHERE id = ?");
        $query->execute([$id]);
        $result = $query->fetch(PDO::FETCH_OBJ);
        return $result;
    }
}
