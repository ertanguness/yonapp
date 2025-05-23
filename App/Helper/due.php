<?php 


namespace App\Helper;
use Database\Db;
use App\Helper\Security;
use PDO;

class Due extends Db
{
    protected $table = 'dues';
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getDuesSelect($name = 'dues', $id = null)
    {
        $query = $this->db->prepare('SELECT * FROM dues');  // Tüm sütunları seç
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_OBJ);  // Tüm sonuçları al

        $select = '<select name="' . $name . '" class="form-select select2" id="' . $name . '" style="width:100%">';
        foreach ($results as $row) {  // $results üzerinde döngü
            $selected = $id == $row->id ? ' selected' : '';  // Eğer id varsa seçili yap
            $select .= '<option value="' . Security::encrypt($row->id) . '"' . $selected . '>' . $row->due_name . '</option>';  // $row->title yerine $row->name kullanıldı
        }
        $select .= '</select>';
        return $select;
    }

}