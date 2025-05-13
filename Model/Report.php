<?php

use Model\Model;
use PDO;


class Reports extends Model
{
    protected $table = "reports";
    public function __construct()
    {
        parent::__construct($this->table);
    }

    public function saveReports($data)
    {
        $this->attributes = $data;
        if (isset($data["id"]) && $data["id"] > 0) {
            $this->isNew = false;
        }
        return parent::save();
    }
    
}
