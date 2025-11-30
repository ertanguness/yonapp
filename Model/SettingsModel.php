<?php

namespace Model;


use Model\Model;
use PDO;

class SettingsModel extends Model
{
    protected $table = 'settings';
    public function __construct()
    {
        parent::__construct($this->table);
    }


    /** 
     * Sitenin tüm ayarlarını anahtar-değer çifti olarak döner
     * @return array|null
     */
    public function getAllSettingsAsKeyValue()
    {

        $siteId = isset($_SESSION['site_id']) ? (int) $_SESSION['site_id'] : 0;
        if ($siteId === 0) {
            return null;
        }

        
        $sql = $this->db->prepare("SELECT set_name, set_value 
                                            FROM $this->table 
                                            WHERE site_id = ?
                                            ORDER BY id DESC");
        $sql->execute([$siteId]);
        return $sql->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /** Sitenin Mesaj Gonderen Başlıklarını döndürür */
    public function getMessageSenders()
    {
        $siteId = isset($_SESSION['site_id']) ? (int) $_SESSION['site_id'] : 0;
        if ($siteId === 0) {
            return null;
        }
        $sql = "SELECT set_value FROM {$this->table} 
                WHERE site_id = :site_id 
                AND set_name = 'sms_baslik'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['site_id' => $siteId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }



    public function getSettings($set_name)
    {
        $siteId = isset($_SESSION['site_id']) ? (int) $_SESSION['site_id'] : 0;
        if ($siteId === 0) {
            return null;
        }

        $sql = $this->db->prepare("SELECT set_value FROM $this->table WHERE site_id = ? AND set_name = ?");
        $sql->execute([$siteId, $set_name]);
        return $sql->fetch(PDO::FETCH_OBJ)->set_value ?? null;
    }

    public function getSettingIdByUserAndAction($user_id, $action_name)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE user_id = ? and set_name = ?");
        $sql->execute([$user_id, $action_name]);
        return $sql->fetch(PDO::FETCH_OBJ);
    }

    //Birden fazla kayıt varsa tüm kayıtları getir
    public function getSettingIdByUserAndActionAll($user_id, $action_name)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE user_id = ? and set_name = ? ORDER BY id DESC");
        $sql->execute([$user_id, $action_name]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }


    //Birden fazla kayıt varsa tüm kayıtları sil
    public function deleteByUserAndAction($user_id, $action_name)
    {
        $sql = $this->db->prepare("DELETE FROM $this->table WHERE user_id = ? and set_name = ?");
        return $sql->execute([$user_id, $action_name]);
    }

    //Program açıldığında tamamlanmamış görevleri getir veya getirme
    public function updateShowCompletedMissions($firm_id, $visible)
    {
        $sql = $this->db->prepare("UPDATE $this->table SET set_value = ? WHERE site_id = ? and set_name = ?");
        return $sql->execute([$visible, $firm_id, "completed_tasks_visible"]);
    }

    /**
     * Aktif site için son ayar satırını döndürür (kolon bazlı model)
     */
    public function Ayarlar()
    {
        $site_id = $_SESSION['site_id'] ?? 0;
        $query = $this->db->prepare("SELECT * FROM {$this->table} WHERE site_id = :site_id ORDER BY id DESC LIMIT 1");
        $query->execute(['site_id' => $site_id]);
        return $query->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Site ve kullanıcı bağlamında bir dizi ayarı upsert eder
     * @param int $siteId
     * @param int|null $userId
     * @param array $pairs [ set_name => [ 'value' => ..., 'aciklama' => ... ] ]
     * @return int
     */
    public function upsertPairs(int $siteId, ?int $userId, array $pairs): int
    {
        if ($siteId === 0 || empty($pairs)) {
            return 0;
        }

        $affected = 0;
        $userIdInt = $userId ?? 0;

        $selectSql = $this->db->prepare(
            "SELECT id FROM {$this->table} WHERE site_id = :site_id AND set_name = :set_name ORDER BY id DESC LIMIT 1"
        );
        $updateByIdSql = $this->db->prepare(
            "UPDATE {$this->table}
             SET set_value = :set_value,
                 aciklama  = :aciklama,
                 user_id   = :user_id
             WHERE id = :id"
        );
        $insertSql = $this->db->prepare(
            "INSERT INTO {$this->table} (site_id, user_id, set_name, set_value, aciklama)
             VALUES (:site_id, :user_id, :set_name, :set_value, :aciklama)"
        );

        foreach ($pairs as $name => $data) {
            $value = $data['value'] ?? null;
            $desc  = $data['aciklama'] ?? null;

            $selectSql->execute([
                ':site_id' => $siteId,
                ':set_name' => $name,
            ]);
            $existingId = $selectSql->fetchColumn();

            if ($existingId) {
                $updateByIdSql->execute([
                    ':set_value' => $value,
                    ':aciklama'  => $desc,
                    ':user_id'   => $userIdInt,
                    ':id'        => (int)$existingId,
                ]);
                $affected += $updateByIdSql->rowCount();
            } else {
                $insertSql->execute([
                    ':site_id'   => $siteId,
                    ':user_id'   => $userIdInt,
                    ':set_name'  => $name,
                    ':set_value' => $value,
                    ':aciklama'  => $desc,
                ]);
                $affected += $insertSql->rowCount();
            }
        }

        return $affected;
    }

}
