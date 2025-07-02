<?php 

namespace App\Helper;


use App\Helper\Security;
use Model\PeoplesModel;
use PDO;

class Debit 
{
    protected $table = 'debit';
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

  
    public function getPeopleSelect($name = 'target_person', $id = null)
    {
      
        //PeopleModel sınıfını dahil ediyoruz
        $Peoples = new PeoplesModel();
        $peoples = $Peoples->getPeoples();  // PeopleModel sınıfından verileri alıyoruz


        $select = '<select name="' . $name . '[]" class="form-select select2" multiple id="' . $name . '" style="width:100%">'; // 'multiple' attribute added
        foreach ($peoples as $row) {  // $peoples üzerinde döngü
            $selected = is_array($id) && in_array($row->id, $id) ? ' selected' : '';  // Check if $id is an array and contains the current id
            $select .= '<option value="' . Security::encrypt($row->id) . '"' . $selected . '>' . $row->fullname . '</option>';
        }
        $select .= '</select>';
        return $select;
    }



}