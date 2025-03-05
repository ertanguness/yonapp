<?php


require_once "BaseModel.php";

class DefinesModel extends Model
{
    protected $table = "defines";
    protected $job_table = "job_groups";
    protected $firm_id;

    const TYPES = [
        1 => "Servis Konusu",
        2 => "Gelir-Gider Türü",
        3 => "İş Grubu",
        4 => "Ödeme Türü"
    ];
    public function __construct()
    {
        parent::__construct($this->table);
        $this->firm_id = $_SESSION['firm_id'];
    }


    //Job Groups- İş Grubu tanım : 3
    public function getDefinesByType($type)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE firm_id = ? and type_id = ?");
        $sql->execute([$this->firm_id, $type]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }


    //servis konusu tanım : 1
    public function getServiceHeads()
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE user_id = ? and statu = ?");
        $sql->execute([$_SESSION['user_id'], 1]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }


    //gelir-gider türü tanım : 2
    public function getIncExpTypesByFirm()
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE firm_id = ? and type_id IN (1, 2)");
        $sql->execute([$this->firm_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    public function getIncExpTypesByFirmandType($type)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE firm_id = ? and type_id = ?");
        $sql->execute([$this->firm_id, $type]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    //Proje Durumu getirme : 5
    public function getProjectStatus()
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE firm_id = ? and type_id = ?");
        $sql->execute([$this->firm_id, 5]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

   



}