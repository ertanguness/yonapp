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



}

