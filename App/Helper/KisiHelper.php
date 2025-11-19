<?php


namespace App\Helper;

use App\Helper\Security;
use PDO;


class KisiHelper
{
    protected $table = 'kisiler';

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



    public  function KisiSelect($name = 'kisi', $id = null, $disabled = false,$zeroOption = false,$multiple = false)
    {
        $site_id = $_SESSION['site_id'] ?? 0; // Kullanıcının site_id'sini al, eğer yoksa 0 olarak ayarla
        $query = $this->db->prepare("SELECT 
                                            k.id,
                                            k.adi_soyadi,
                                            d.daire_kodu AS daire_kodu
                                            FROM $this->table k 
                                            LEFT JOIN daireler d ON d.id = k.daire_id
                                            WHERE k.site_id = ?");  // Tüm sütunları seç
        $query->execute([$site_id]);  // site_id'ye göre filtrele
        $results = $query->fetchAll(PDO::FETCH_OBJ);  // Tüm sonuçları al

        $select = '<select name="' . $name . '" class="form-select select2" id="' . $name . '" 
        ' . ($disabled ? 'disabled' : '') . '  data-placeholder="Kişi Seçiniz" data-select2-id="' . $name . '"
        style="width:100%" ' . ($multiple ? 'multiple' : '') . '>';
        if($zeroOption){
            $select .= '<option value="">Kişi Seçiniz</option>';
        }
        foreach ($results as $row) {  // $results üzerinde döngü
            $selected = $id == $row->id ? ' selected' : '';  // Eğer id varsa seçili yap
            $select .= '<option value="' . Security::encrypt($row->id) . '"' . $selected . '>' . $row->daire_kodu . ' | ' . $row->adi_soyadi . '</option>';  // $row->title yerine $row->name kullanıldı
        }
        $select .= '</select>';
        return $select;
    }

    /**
     * Kişi arama fonksiyonu
     */
    public function searchKisiler($site_id, $searchTerm)
    {
        $query = $this->db->prepare("SELECT k.id, k.adi_soyadi, d.daire_kodu
                                       FROM $this->table k
                                       LEFT JOIN daireler d ON d.id = k.daire_id
                                       WHERE k.site_id = ? AND (k.adi_soyadi LIKE ? OR d.daire_kodu LIKE ?)");
        $query->execute([$site_id, "%$searchTerm%", "%$searchTerm%"]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

}
