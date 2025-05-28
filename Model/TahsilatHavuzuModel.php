<?php 
namespace Model;

use Model\Model;

class TahsilatHavuzuModel extends Model{
    protected $table = "tahsilat_havuzu";

    public function __construct()
    {
        parent::__construct($this->table);
    }

    
}