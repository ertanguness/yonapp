<?php

namespace Model;

//Model klasoru altında bulunan BaseModel sınıfını dahil ediyoruz
use Model\Model;
use PDO;

//DuesModel sınıfı BaseModel sınıfından miras alır
class SitesModel extends Model
{
protected $table = "sites"; 

    //DuesModel sınıfının constructor metodunu tanımlıyoruz
    public function __construct()
    {
        parent::__construct($this->table);

    }

    //aidat tablosundaki verileri alır
    public function getSites()
    {
        $sql = $this->db->prepare("SELECT * FROM sites");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }
}
