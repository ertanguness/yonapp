<?php 

use Model\Model;
use PDO;

class FeedBackModel extends Model
{
    public $table = "feedback";

    public function __construct()
    {
        parent::__construct($this->table);
    }

    
   
}