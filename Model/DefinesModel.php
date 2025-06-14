<?php
namespace Model;


use Model\Model;
use PDO;

class DefinesModel extends Model
{
    protected $table = "defines";
    protected $site_id;

 
    public function __construct()
    {
        parent::__construct($this->table);
    }

    public function getDefinesByType($type)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE site_id = ? and type_id = ?");
        $sql->execute([$this->site_id, $type]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }
    
    

    public function isApartmentTypeNameExists($site_id, $name)
    {
        $sql = $this->db->prepare("SELECT COUNT(*) FROM defines WHERE site_id = :site_id AND define_name = :name");
        $sql->execute([':site_id' => $site_id, ':name' => $name]);
        return $sql->fetchColumn() > 0;
    }
    
    public function getAllByApartmentType($type) {
        $site_id = $_SESSION['site_id']; // aktif site ID’sini alıyoruz
        $sql = "SELECT * FROM defines WHERE type = :type AND site_id = :site_id ORDER BY create_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'type' => $type,
            'site_id' => $site_id
        ]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    public function getDefinesTypes($siteId, $type) {
        $sql = "SELECT * FROM defines WHERE site_id = :site_id AND type = :type ORDER BY define_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':site_id', $siteId, PDO::PARAM_INT);
        $stmt->bindParam(':type', $type, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
   



}