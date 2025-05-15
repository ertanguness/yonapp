<?php

namespace Model;

use Model\Model;

use PDO;

class PeoplesModel extends Model
{
    protected $table = "peoples"; 

    public function __construct()
    {
        parent::__construct($this->table);
    }

    //aidat tablosundaki verileri alır
    public function getPeoples()
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }
}