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
        $sql = $this->db->prepare("SELECT * FROM $this->table 
                                            WHERE user_id = ? 
                                            and silinme_tarihi IS NULL
                                            ORDER BY favori_mi DESC, click_count DESC, aktif_mi DESC, site_adi ASC");
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

    public function getFavorites()
    {
        $user_id = $_SESSION['user']->id;
        $isSubUser = $_SESSION['user']->owner_id > 0 ? true : false;
        if ($isSubUser) {
            $user_id = $_SESSION['user']->owner_id;
        }
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE user_id = ? AND favori_mi = 1 ORDER BY click_count DESC, site_adi ASC");
        $sql->execute([$user_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    public function incrementClickCount($id)
    {
        $stmt = $this->db->prepare("UPDATE $this->table SET click_count = click_count + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function setFavorite($id, $isFavorite)
    {
        $stmt = $this->db->prepare("UPDATE $this->table SET favori_mi = ? WHERE id = ?");
        return $stmt->execute([intval($isFavorite) ? 1 : 0, $id]);
    }

    public function siteSonID()
    {
        $query = $this->db->query("SHOW TABLE STATUS LIKE '$this->table'");
        $result = $query->fetch(PDO::FETCH_OBJ);
        return $result ? $result->Auto_increment : null;
    }
}
