<?php
namespace App\Helper;

use Model\SettingsModel;

class SettingsHelper extends SettingsModel
{
    protected $table = 'settings';

    /** Sitenin Mesaj başlıklarını select olarak döndürür */
    public function getMessageSubjects()
    {
      /** SettingsModelden başlıkları getirir */
        $senders =  $this->getMessageSenders();

        /** Select oluşturur */
        $select = '<select name="sms_baslik" id="sms_baslik" class="form-control select2">';
        foreach ($senders as $sender) {
            $select .= "<option value=\"{$sender->set_value}\">{$sender->set_value}</option>";
        }
        $select .= '</select>';
        return $select;
    }

    /** sitenin mesaj başlıklarının sayısı döndürür */
    public function getMessageSubjectsCount()
    {   
        $site_id = $_SESSION['site_id'] ?? null;
        $query = $this->db->query("SELECT COUNT(*) AS count 
                                          FROM {$this->table} 
                                          WHERE set_name = 'sms_baslik' 
                                          AND site_id = {$site_id}");
        $result = $query->fetch();
        return $result->count;
    }

}

