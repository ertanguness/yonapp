<?php

namespace Model;

//Model klasoru altında bulunan BaseModel sınıfını dahil ediyoruz
use Model\Model;
use PDO;

class IcraModel extends Model
{
    protected $table = "icralar";

    public function __construct()
    {
        parent::__construct($this->table);
    }
    /**
     * Giriş yapan Kullanıcının sitelerini getirir
     * @return array
     */

    public function Icralar()
    {
        $site_id = $_SESSION['site_id'];

        $sql = "SELECT i.* 
            FROM icralar i
            INNER JOIN kisiler k ON i.kisi_id = k.id
            WHERE k.site_id = :site_id
            ORDER BY i.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['site_id' => $site_id]);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }


    public function IcraBilgileri($id)
    {
        $query = $this->db->prepare("SELECT * FROM $this->table WHERE id = ?");
        $query->execute([$id]);
        return $query->fetch(PDO::FETCH_OBJ);
    }
    public function SakinIcraBilgileri($kisi_id)
    {
        $query = $this->db->prepare("SELECT * FROM $this->table WHERE kisi_id = ?");
        $query->execute([$kisi_id]);
        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Belirli bir dosya numarasıyla kayıt var mı kontrol et
     * 
     * @param string $dosya_no
     * @param int|null $excludeId Güncellemede kendi kaydını hariç tutmak için
     * @return object|false
     */
    public function findByDosyaNo($dosya_no, $excludeId = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE dosya_no = :dosya_no";

        if ($excludeId) {
            $sql .= " AND id != :excludeId";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':dosya_no', $dosya_no);

        if ($excludeId) {
            $stmt->bindValue(':excludeId', $excludeId, \PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_OBJ); // Kayıt varsa obje, yoksa false döner
    }
}
