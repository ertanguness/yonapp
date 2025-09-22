<?php

namespace Model;

use Model\Model;
use PDO;

//DuesModel sınıfı BaseModel sınıfından miras alır
class DueModel extends Model
{
protected $table = "dues"; 

const PERIOD = [
    '0' => 'Aylık',
    '1' => '3 Aylık',
    '2' => '6 Aylık',
    '3' => 'Yıllık',
    '4' => 'Tek Seferlik',
];



    //DuesModel sınıfının constructor metodunu tanımlıyoruz
    public function __construct()
    {
        parent::__construct($this->table);

    }

    //aidat tablosundaki verileri alır
    public function getDues()
    {
        //Period alanını dizi olarak döndürmek için SQL sorgusunu güncelliyoruz 
        $sql = $this->db->prepare("SELECT * FROM $this->table order by id DESC");
        $sql->execute();

        //period alanını self::PERIOD dizisinden alıyoruz
        $dues = $sql->fetchAll(PDO::FETCH_OBJ);
        foreach ($dues as $due) {
            $due->period_text = self::PERIOD[$due->period];
        }
        return $dues;
    }

    //Aidat adını getirir
    public function getDueName($id)
    {
        $sql = $this->db->prepare("SELECT due_name FROM $this->table WHERE id = ?");
        $sql->execute([$id]);
        return $sql->fetch(PDO::FETCH_OBJ)->due_name;
    }

}
