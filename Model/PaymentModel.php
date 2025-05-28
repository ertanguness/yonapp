<?php 


namespace Model;

use Model\Model;
use PDO;

class PaymentModel extends Model{
    protected $table = "payments";

    public function __construct()
    {
        parent::__construct($this->table);
    }
    
}