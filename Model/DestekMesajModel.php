<?php 

namespace Model;

use Model\Model;

class DestekMesajModel extends Model
{
    protected $table = "destek_talep_yanitlari";

    public function __construct()
    {
        parent::__construct($this->table);
    }
}

