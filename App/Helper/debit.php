<?php 

namespace App\Helper;

use Database\Db;
use App\Helper\Security;
use Model\PeoplesModel;
use PDO;

class Debit extends Db
{
    protected $table = 'debit';
    public function __construct()
    {
        parent::__construct();
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