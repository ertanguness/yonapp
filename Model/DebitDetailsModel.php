<?php 


namespace Model;

use Model\Model;
use PDO;

class DebitDetailsModel extends Model
{
    protected $table = "debit_details"; 

    public function __construct()
    {
        parent::__construct($this->table);
    }

    // Borçlandırma detaylarını borç ID'sine göre getirir
    public function getDebitDetailsByDebitId($debit_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE debit_id = ?");
        $sql->execute([$debit_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }
   
}
