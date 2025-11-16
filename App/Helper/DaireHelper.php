<?php

namespace App\Helper;

use Model\DairelerModel;

class DaireHelper
{
    protected $model;
    public function __construct()
    {
        $this->model = new DairelerModel();
    }


    /* Sitenin bloklarını select olarak döndürür 
    * return: string
    */
    public function DaireSelect($id = "daireler",$blok_id = null,$selected = null)
    {
        $bloklar = $this->model->BlokDaireleri($blok_id);
        $select = '<select name="' . $id . '" class="form-select select2" id="' . $id . '" style="width:100%">';
        
       
        foreach ($bloklar as $blok) { // 
            $selected_attr = $selected == $blok->id ? 'selected' : '';
            $select .= '<option value="' . $blok->id . '" ' . $selected_attr . '>' . $blok->blok_adi . '</option>'; //  
        }
        $select .= '</select>';
        return $select;
    }
}
