<?php
namespace App\Helper;

use App\Helper\Security;
use Model\KasaModel;

class Financial
{
    protected $KasaModel = null;

    public function __construct()
    {
        $this->KasaModel = new KasaModel(); // KasaModel sınıfından bir örnek oluştur
    }   
   
    /**Sitenin Kasa Listesini getir, Aktif olan seçili gelir
     * @param string $site_id
     * @return string
     */
    public static function KasaSelect($name = 'kasa', $id = null, $disabled = null)
    {
        $KasaModel = new KasaModel(); // KasaModel sınıfından bir örnek oluştur
        $results = $KasaModel->SiteKasalari(); // Kasa listesini al
        $select = '<select name="' . $name . '" class="form-select select2 w-100" id="' . $name . '" style="min-width:200px;width:100%" ' . $disabled . '>';
        
        foreach ($results as $row) { // $results üzerinde döngü
            $selected = ($id == $row->id || (empty($id) && $row->varsayilan_mi == 1)) ? ' selected' : ''; // Eğer id eşitse veya id boşsa ve varsayılan kasa ise seçili yap
            $select .= '<option value="' . Security::encrypt($row->id) . '"' . $selected . '>' . htmlspecialchars($row->kasa_adi) . '</option>'; // Kasa adını güvenli şekilde ekle
        }
        
        $select .= '</select>';
        return $select;
    }
}