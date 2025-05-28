<?php 


namespace Model;

use Model\Model;
use PDO;

class BorclandirmaModel extends Model
{
    protected $table = "borclandirma"; 

    public function __construct()
    {
        parent::__construct($this->table);
    }

    //aidat tablosundaki verileri alır
    public function getDebits()
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }
}
