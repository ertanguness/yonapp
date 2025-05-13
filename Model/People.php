<?php 
use Model\Model;
use PDO;

class People extends Model{
    protected $table = "people";

    public function __construct(){
        parent::__construct($this->table);
    }

    

}