<?php

namespace Model;

//Model klasoru altında bulunan BaseModel sınıfını dahil ediyoruz
use PDO;
use Model\Model;
use App\Services\Gate;

class SitelerModel extends Model
{
    protected $table = "siteler";

    public function __construct()
    {
        parent::__construct($this->table);
    }
    /**
     * Giriş yapan Kullanıcının sitelerini getirir
     * @return array
     */
    public function Sitelerim()
    {

      
        $user_id = $_SESSION['user']->id; // Kullanıcının ID'sini alıyoruz
        /** Kullanıcı alt kullanıcı ise kontrol yapma */
        $isSubUser = $_SESSION['user']->owner_id > 0 ? true : false;
        if ($isSubUser) {
            $user_id = $_SESSION['user']->owner_id;
        }
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE user_id = ?");
        $sql->execute([$user_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }


  

    public function SiteBilgileri($id)
    {
        $query = $this->db->prepare("SELECT * FROM $this->table WHERE id = ?");
        $query->execute([$id]);
        $result = $query->fetch(PDO::FETCH_OBJ);
        return $result;
    }

    public function siteSonID()
    {
        $query = $this->db->query("SHOW TABLE STATUS LIKE '$this->table'");
        $result = $query->fetch(PDO::FETCH_OBJ);
        return $result ? $result->Auto_increment : null;
    }
}
