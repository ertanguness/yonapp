<?php



namespace Database;



use PDO;



class Db {

    protected $db;



    public function __construct() {

        // $this->db = new PDO("mysql:host=localhost;dbname=mbeyazil_puantoryeni;charset=utf8", "mbeyazil_root", "5M0c?HZK}-Ak");

        $this->db = new PDO("mysql:host=localhost;dbname=yonapp", "root", "");

    }



     // $db özelliğine dışarıdan erişim sağlayan metod

     public function connect() {

        return $this->db;

    }



    public function disconnect() {

        $this->db = null;

    }



    

    // Transaction başlatma

    public function beginTransaction() {

        return $this->db->beginTransaction();

    }



    // Transaction commit etme

    public function commit() {

        return $this->db->commit();

    }



    // Transaction rollback etme

    public function rollBack() {

        return $this->db->rollBack();

    }

}