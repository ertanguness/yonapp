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

 
   
}
