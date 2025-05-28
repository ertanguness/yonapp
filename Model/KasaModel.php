<?php 

namespace Model;

use Model\Model;

class KasaModel extends Model{
    protected $table = "kasa";

    public function __construct()
    {
        parent::__construct($this->table);
    }
    
}