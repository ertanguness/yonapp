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


    
    public function daireTipiGetir($site_id, $type)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE site_id = ? and id = ?");
        $sql->execute([$site_id, $type]);
        return $sql->fetch(PDO::FETCH_OBJ); // sadece bir satır bekleniyorsa
    }
    
    
    

    public function isApartmentTypeNameExists($site_id, $name)
    {
        $sql = $this->db->prepare("SELECT COUNT(*) FROM defines WHERE site_id = :site_id AND define_name = :name");
        $sql->execute([':site_id' => $site_id, ':name' => $name]);
        return $sql->fetchColumn() > 0;
    }
    
    public function getAllByApartmentType($type) {
        $site_id = $_SESSION['site_id']; // aktif site ID’sini alıyoruz
        $sql = "SELECT * FROM defines WHERE type = :type AND site_id = :site_id ORDER BY define_name DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'type' => $type,
            'site_id' => $site_id
        ]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /* Belirli bir site ve tip için tanımları getirir.
     * @param int $siteId
     * @param int $type
     * @return array
     */
    public function getDefinesTypes($siteId, $type) {
        $sql = "SELECT * FROM defines WHERE site_id = :site_id AND type = :type ORDER BY define_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':site_id', $siteId, PDO::PARAM_INT);
        $stmt->bindParam(':type', $type, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
   

    /**
     * Gelen Daire tipinden id'yi döndürürür.
     * @param mixed $site_id
     * @param mixed $type
     * @return int|null
     */
    public function getApartmentTypeIdByName($site_id,$type, $name)
    {
        $sql = $this->db->prepare("SELECT id FROM defines 
                                          WHERE site_id = ? AND type = ? AND define_name = ? 
                                          LIMIT 1");
        $sql->execute([
            $site_id,
            $type,
            $name
        ]);
        $result = $sql->fetch(PDO::FETCH_OBJ);
        
        return $result ? (int)$result->id : null; // Eğer sonuç varsa ID'yi döndür, yoksa null döndür
    }



}