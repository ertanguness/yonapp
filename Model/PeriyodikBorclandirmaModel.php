<?php 

namespace Model;
use Model\Model;
use PDO;


class PeriyodikBorclandirmaModel extends Model
{
    protected $table = "periyodikborclandirma"; 

    public function __construct()
    {
        parent::__construct($this->table);
    }

   
}