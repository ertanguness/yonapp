<?php

namespace App\Helper;

use Model\BloklarModel;

class BlokHelper
{
    protected $model;
    public function __construct()
    {
        $this->model = new BloklarModel();
    }


    /* Sitenin bloklarını select olarak döndürür 
    * return: string
    */
    public function blokSelect($id = "bloklar",$all = true)
    {
        $site_id = $_SESSION["site_id"];
        $bloklar = $this->model->SiteBloklari($site_id);
        $select = '<select name="' . $id . '" class="form-select select2" id="' . $id . '" style="width:100%">';
        
        if ($all) { // Eğer tüm site seçeneği eklenmek isteniyorsa
            $select .= '<option value="all">Tüm Site</option>'; // Tüm bloklar seçeneği
        }
        foreach ($bloklar as $blok) { // 
            $select .= '<option value="' . $blok->id . '">' . $blok->blok_adi . '</option>'; // 
        }
        $select .= '</select>';
        return $select;
    }
}
