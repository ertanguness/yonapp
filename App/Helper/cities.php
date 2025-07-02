<?php

namespace App\Helper;

use PDO;

class Cities
{

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

    
    public function citySelect($name = 'city', $id = null)
    {

        $query = $this->db->prepare('SELECT * FROM il');  // Tüm sütunları seç
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_OBJ);  // Tüm sonuçları al

        $select = '<select name="' . $name . '" class="form-select select2" id="' . $name . '" style="width:100%">';
        $select .= '<option value="">Şehir Seçiniz</option>';
        foreach ($results as $row) {  // $results üzerinde döngü
            $selected = $id == $row->id ? ' selected' : '';  // Eğer id varsa seçili yap
            $select .= '<option value="' . $row->id . '"' . $selected . '>' . $row->city_name . '</option>';  // $row->title yerine $row->name kullanıldı
        }
        $select .= '</select>';
        return $select;
    }

    public function getCityName($id)
    {

        $query = $this->db->prepare('SELECT city_name FROM il WHERE id = :id');
        $query->execute(array('id' => $id));
        $result = $query->fetch(PDO::FETCH_OBJ);
        if ($result) {
            return $result->city_name;
        } else {
            return '';
        }
    }

    public function getTownName($id)
    {
        $query = $this->db->prepare('SELECT ilce_adi FROM ilce WHERE id = :id');
        $query->execute(array('id' => $id));
        $result = $query->fetch(PDO::FETCH_OBJ);
        if ($result) {
            return $result->ilce_adi;
        } else {
            return 'Bilinmiyor';
        }
    }
    public function getCityTowns($city_id, $selected_town_id = null)
    {
        $query = $this->db->prepare('SELECT * FROM ilce WHERE il_id = :city_id');
        $query->execute(['city_id' => $city_id]);
        $towns = $query->fetchAll(PDO::FETCH_OBJ);

        $select = '<option value="">İlçe Seçiniz</option>';
        foreach ($towns as $town) {
            $selected = ($selected_town_id == $town->id) ? ' selected' : '';
            $select .= "<option value=\"{$town->id}\"{$selected}>{$town->ilce_adi}</option>";
        }

        return $select;
    }
}
