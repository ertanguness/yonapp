<?php 
require_once "BaseModel.php";

class People extends Model{
    protected $table = "people";

    public function __construct(){
        parent::__construct($this->table);
    }

    

}