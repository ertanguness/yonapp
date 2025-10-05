<?php 

namespace App\Helper;

use Model\DefinesModel;
use App\Helper\Security;
use PDO;

class DefinesHelper {

    protected $table = 'defines';
    private PDO $db;

    const TYPE_SITE = 1;
    const TYPE_BLOCK = 2;
    const TYPE_APARTMENT = 3;
    const TYPE_USER = 4;

    public function __construct()
    {
        // bootstrap.php'de tanımladığımız global yardımcı fonksiyonu çağırıyoruz.
        $this->db = \getDbConnection();
    }


    
    // public  function KisiSelect($name = 'kisi', $id = null, $disabled = false,$zeroOption = false)
    // {
    //     $site_id = $_SESSION['site_id'] ?? 0; // Kullanıcının site_id'sini al, eğer yoksa 0 olarak ayarla
    //     $query = $this->db->prepare("SELECT 
    //                                         k.id,
    //                                         k.adi_soyadi,
    //                                         d.daire_kodu AS daire_kodu
    //                                         FROM $this->table k 
    //                                         LEFT JOIN daireler d ON d.id = k.daire_id
    //                                         WHERE k.site_id = ?");  // Tüm sütunları seç
    //     $query->execute([$site_id]);  // site_id'ye göre filtrele
    //     $results = $query->fetchAll(PDO::FETCH_OBJ);  // Tüm sonuçları al

    //     $select = '<select name="' . $name . '" class="form-select select2" id="' . $name . '" 
    //     ' . ($disabled ? 'disabled' : '') . '  data-placeholder="Kişi Seçiniz" data-select2-id="' . $name . '"
    //     style="width:100%">';
    //     if($zeroOption){
    //         $select .= '<option value="">Kişi Seçiniz</option>';
    //     }
    //     foreach ($results as $row) {  // $results üzerinde döngü
    //         $selected = $id == $row->id ? ' selected' : '';  // Eğer id varsa seçili yap
    //         $select .= '<option value="' . Security::encrypt($row->id) . '"' . $selected . '>' . $row->daire_kodu . ' | ' . $row->adi_soyadi . '</option>';  // $row->title yerine $row->name kullanıldı
    //     }
    //     $select .= '</select>';
    //     return $select;
    // }

    /** Daire Tipi Seçimi
     * @param string $name
     * @param int|null $id
     */
    public function DaireTipiSelect($name = 'apartment_type', $id = null)
    {
        $site_id = $_SESSION['site_id'] ?? 0;
        $query = $this->db->prepare("SELECT * FROM $this->table WHERE site_id = ? AND type = ?");
        $query->execute([$site_id, self::TYPE_APARTMENT]);
        $results = $query->fetchAll(PDO::FETCH_OBJ);

        $select = '<select name="' . $name . '" class="form-select select2" id="' . $name . '"
            data-placeholder="Daire Tipi Seçiniz" data-select2-id="' . $name . '" style="width:100%">';
        $select .= '<option value="">Daire Tipi Seçiniz</option>';
        foreach ($results as $row) {
            $selected = $id == $row->id ? ' selected' : '';
            $select .= '<option value="' . Security::encrypt($row->id) . '"' . $selected . '>' . $row->define_name . '</option>';
        }
        $select .= '</select>';
        return $select;
    }

}