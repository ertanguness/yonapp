<?php 
namespace App\Helper;


use Database\Db;
use App\Helper\Security;
use Model\SitelerModel;

class Site extends Db{

    protected $table = 'siteler'; 

    protected $SiteModel;

    public function __construct()
    {
        $this->SiteModel = new SitelerModel();
    }


    
    
    public static function SitelerimSelect($name = 'companies', $id = null, $disabled = null)
    {
        
        $Siteler = new SitelerModel();  // SitelerModel sınıfından bir örnek oluştur
        $results = $Siteler->Sitelerim();
        $select = '<select name="' . $name . '" class="form-select select2 w-100" id="' . $name . '" style="min-width:200px;width:100%" ' . $disabled . '>';
        foreach ($results as $row) {  // $results üzerinde döngü
            $selected = $id == $row->id ? ' selected' : '';  // Eğer id varsa seçili yap
            $select .= '<option value="' . Security::encrypt($row->id) . '"' . $selected . '>' . $row->site_adi . '</option>';  // $row->title yerine $row->name kullanıldı
        }
        $select .= '</select>';
        return $select;
    }
}