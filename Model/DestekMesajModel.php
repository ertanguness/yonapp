<?php 

namespace Model;

use Model\Model;

class DestekMesajModel extends Model
{
    protected $table = "destek_mesajlari";

    public function __construct()
    {
        parent::__construct($this->table);
    }
}

