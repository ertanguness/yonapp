<?php 


namespace App\Helper;
use App\Helper\Security;
use PDO;


class Borclandirma 
{
    protected $table = 'borclandirma';

       /**
     * Aktif PDO veritabanı bağlantısını tutar.
     * @var PDO
     */
    private PDO $db;

    /**
     * Sınıf oluşturulduğunda, merkezi veritabanı bağlantısını alır.
     */
    public function __construct()
    {
        // bootstrap.php'de tanımladığımız global yardımcı fonksiyonu çağırıyoruz.
        $this->db = \getDbConnection();
    }


    /* Borçlandırma Türlerini Select olarak döndürür */
    public function BorclandirmaTuruSelect($name = 'borclandirma', $selectedID = null, $required = true)
    {
        
        $query = $this->db->prepare("SELECT b.*,d.due_name FROM $this->table b 
                                LEFT JOIN dues d ON b.borc_tipi_id = d.id
                                WHERE b.site_id = ? ");  // Tüm sütunları seç
        $query->execute([$_SESSION['site_id']]);  // site_id'ye göre filtrele
        
        $results = $query->fetchAll(PDO::FETCH_OBJ);  // Tüm sonuçları al
        $select = '<select name="' . $name . '" class="form-select select2" id="' . $name . '" 
         data-placeholder="Borç Türü Seçiniz" data-select2-id="' . $name . '"
            ' . ($required ? 'required' : '') . '
         style="width:100%">';

        $select .= '<option value="">Seçiniz</option>';
        foreach ($results as $row) {
            $isSelected = ($row->id == $selectedID) ? ' selected' : '';
            $select .= '<option value="' . $row->id . '"' . $isSelected . '>' . $row->due_name . ' | ' . $row->aciklama . '</option>';
        }

        $select .= '</select>';
        return $select;
    }

}