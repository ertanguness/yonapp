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


    //Giriş yapan Kullanıcının sitelerini getir
    public function getMySitesByUserId()
    {
        $user_id = $_SESSION['user']->id; // Kullanıcının ID'sini alıyoruz
        $sql = $this->db->prepare("SELECT * FROM sites WHERE user_id = ?");
        $sql->execute([$user_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
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

    public function getAllSitesWithOwners()
    {
        $sql = $this->db->prepare("
            SELECT s.*, u.full_name as owner_name, u.phone as owner_phone, u.email as owner_email 
            FROM sites s 
            LEFT JOIN users u ON s.user_id = u.id
        ");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }
}
