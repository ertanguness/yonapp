<?php

namespace Model;

//Model klasoru altında bulunan BaseModel sınıfını dahil ediyoruz
use Model\Model;
use PDO;

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
        $sql = $this->db->prepare("SELECT * FROM $this->table");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    //Aidat adını getirir
    public function getDueName($id)
    {
        $sql = $this->db->prepare("SELECT due_name FROM $this->table WHERE id = ?");
        $sql->execute([$id]);
        return $sql->fetch(PDO::FETCH_OBJ)->due_name;
    }
}
