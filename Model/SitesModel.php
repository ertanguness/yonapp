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

<<<<<<< HEAD
=======

    //Giriş yapan Kullanıcının sitelerini getir
    public function getMySitesByUserId()
    {
        $user_id = $_SESSION['user']->id; // Kullanıcının ID'sini alıyoruz
        $sql = $this->db->prepare("SELECT * FROM sites WHERE user_id = ?");
        $sql->execute([$user_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    //aidat tablosundaki verileri alır
>>>>>>> e2408f2d71a6526d11f09835a3f838bad29f803b
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
