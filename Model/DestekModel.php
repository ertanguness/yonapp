<?php 

namespace Model;


use Model\Model;
use PDO;

class DestekModel extends Model
{
    protected $table = "destek_talepleri";

    public function __construct()
    {
        parent::__construct($this->table);
    }
}

