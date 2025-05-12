<?php

namespace Model;

//Model klasoru altında bulunan BaseModel sınıfını dahil ediyoruz
use Model\Model;

//DuesModel sınıfı BaseModel sınıfından miras alır
class DueModel extends Model
{
protected $table = "dues"; 

    //DuesModel sınıfının constructor metodunu tanımlıyoruz
    public function __construct()
    {
        parent::__construct($this->table);

    }

    //aidat tablosundaki verileri alır
    public function getDues()
    {
        $sql = $this->db->prepare("SELECT * FROM dues");
        $sql->execute();
        return $sql->fetchAll(\PDO::FETCH_OBJ);
    }
}
