<?php 


namespace Model;

use Model\Model;
use PDO;

class DebitModel extends Model
{
    protected $table = "debit"; 

    public function __construct()
    {
        parent::__construct($this->table);
    }

    //aidat tablosundaki verileri alır
    public function getDebits()
    {
        $sql = $this->db->prepare("SELECT * FROM debit");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }
}
