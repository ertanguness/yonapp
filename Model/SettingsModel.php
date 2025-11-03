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
     * @return object|null
     */
    public function getAllSettingsAsKeyValue()
    {
        $firm_id = $_SESSION['site_id'];
        $sql = $this->db->prepare("SELECT set_name, set_value FROM $this->table WHERE site_id = ?");
        $sql->execute([$firm_id]);
        return $sql->fetchAll(PDO::FETCH_KEY_PAIR) ?? null;
    }

    public function getSettings($set_name)
    {
        $firm_id = $_SESSION['firm_id'];
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE site_id = ? AND set_name = ?");
        $sql->execute([$firm_id, $set_name]);
        return $sql->fetch(PDO::FETCH_OBJ) ?? null;
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
        $sql = $this->db->prepare("UPDATE $this->table SET set_value = ? WHERE firm_id = ? and set_name = ?");
        return $sql->execute([$visible, $firm_id, "completed_tasks_visible"]);
    }
}