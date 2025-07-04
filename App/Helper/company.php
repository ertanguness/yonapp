<?php

use Database\Db;
use App\Helper\Security;
use Model\MyFirmModel;

class CompanyHelper extends Db
{

    protected $table = 'companies';
    protected $MyFirmModel = null;
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
        $this->MyFirmModel = new MyFirmModel();
    }
 
    public function getCompanySelect($name = 'companies', $id = null)
    {
        $firm_id = $_SESSION['user']->id;
        $query = $this->db->prepare('SELECT * FROM companies where user_id = ?');  // Tüm sütunları seç
        $query->execute([$firm_id]);
        $results = $query->fetchAll(PDO::FETCH_OBJ);  // Tüm sonuçları al

        $select = '<select name="' . $name . '" class="form-select select2" id="' . $name . '" style="width:100%">';
        $select .= '<option value="0">Firma Seçiniz</option>';
        foreach ($results as $row) {  // $results üzerinde döngü
            $selected = $id == $row->id ? ' selected' : '';  // Eğer id varsa seçili yap
            $select .= '<option value="' . Security::encrypt($row->id) . '"' . $selected . '>' . $row->company_name . '</option>';  // $row->title yerine $row->name kullanıldı
        }
        $select .= '</select>';
        return $select;
    }

    public function getCompanyName($id)
    {
        if ($id == null) {
            return 'bilinmiyor';
        }
        $query = $this->db->prepare('SELECT company_name FROM companies WHERE id = :id');
        $query->execute(array('id' => $id));
        $result = $query->fetch(PDO::FETCH_OBJ);
        if ($result) {
            return $result->company_name ?? '';
        } else {
            return '';
        }
    }

    public function myCompanySelect($name = 'companies', $id = null, $disabled = null)
    {
        $results = $this->MyFirmModel->getMyFirmByUserId();
        $select = '<select name="' . $name . '" class="form-select select2 w-100" id="' . $name . '" style="min-width:200px;width:100%" ' . $disabled . '>';
        foreach ($results as $row) {  // $results üzerinde döngü
            $selected = $id == $row->id ? ' selected' : '';  // Eğer id varsa seçili yap
            $select .= '<option value="' . Security::encrypt($row->id) . '"' . $selected . '>' . $row->firm_name . '</option>';  // $row->title yerine $row->name kullanıldı
        }
        $select .= '</select>';
        return $select;
    }

    public function getFirmName($id)
    {
        $query = $this->db->prepare('SELECT firm_name FROM myfirms WHERE id = :id');
        $query->execute(array('id' => $id));
        $result = $query->fetch(PDO::FETCH_OBJ);
        if ($result) {
            return $result->firm_name;
        } else {
            return 'Bilinmiyor';
        }
    }
}
