<?php
namespace Model;


use Model\Model;
use PDO;

class DefinesModel extends Model
{
    protected $table = "defines";
    protected $firm_id;

 
    public function __construct()
    {
        parent::__construct($this->table);
    }


    //Job Groups- İş Grubu tanım : 3
    public function getDefinesByType($type)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE firm_id = ? and type_id = ?");
        $sql->execute([$this->firm_id, $type]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }
    
    

    public function isApartmentTypeNameExists($site_id, $name)
    {
        $sql = $this->db->prepare("SELECT COUNT(*) FROM defines WHERE site_id = :site_id AND define_name = :name");
        $sql->execute([':site_id' => $site_id, ':name' => $name]);
        return $sql->fetchColumn() > 0;
    }
    
    public function getAllByApartmentType($type) {
        $site_id = $_SESSION['firm_id']; // aktif site ID’sini alıyoruz
        $sql = "SELECT * FROM defines WHERE type = :type AND site_id = :site_id ORDER BY create_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'type' => $type,
            'site_id' => $site_id
        ]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
   



}