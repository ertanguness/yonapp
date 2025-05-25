<?php 
namespace App\Helper;


use Database\Db;
use App\Helper\Security;
use Model\SitesModel;

class Site extends Db{

    protected $table = 'sites'; 

    protected $SiteModel;

    public function __construct()
    {
        $this->SiteModel = new SitesModel();
    }


    
    
    public static function mySitesSelect($name = 'companies', $id = null, $disabled = null)
    {
        $siteModel = new SitesModel();
        $results = $siteModel->getMySitesByUserId();
        $select = '<select name="' . $name . '" class="form-select select2 w-100" id="' . $name . '" style="min-width:200px;width:100%" ' . $disabled . '>';
        foreach ($results as $row) {  // $results üzerinde döngü
            $selected = $id == $row->id ? ' selected' : '';  // Eğer id varsa seçili yap
            $select .= '<option value="' . Security::encrypt($row->id) . '"' . $selected . '>' . $row->firm_name . '</option>';  // $row->title yerine $row->name kullanıldı
        }
        $select .= '</select>';
        return $select;
    }
}