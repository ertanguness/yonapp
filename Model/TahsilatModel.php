<?php 
namespace Model;

use Model\Model;

class TahsilatModel extends Model{
    protected $table = "tahsilatlar";

    public function __construct()
    {
        parent::__construct($this->table);
    }

    
}