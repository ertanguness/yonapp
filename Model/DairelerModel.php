<?php

namespace Model;

use Model\Model;
use PDO;

class DairelerModel extends Model
{
    protected $table = "daireler"; 

    public function __construct()
    {
        parent::__construct($this->table);
    }


    /**
     * Daire kodundan dairenin id'sini döndürür
     * @param string $daire_kodu
     * @return int|null
     */
    public function DaireId($daire_kodu)
    {

   
        $query = $this->db->prepare("SELECT id FROM $this->table WHERE daire_kodu = ?");
        $query->execute([$daire_kodu]);
        $result = $query->fetch(PDO::FETCH_OBJ);
        
        return $result ? $result->id : 0; // Eğer sonuç varsa id'yi döndür, yoksa null döndür
    }

    /** 
     * Daire Adını döndürür
     * @param string $daire_id
     * @return string
     */

     public function DaireAdi($daire_id)   {
            $sql = $this->db->prepare("Select daire_adi from $this->table WHERE id = ?");
            $sql->execute([$daire_id]);
            return $sql->fetch(PDO::FETCH_OBJ) ?? "";

     }
     public function DaireKodu($daire_id)   {
            $sql = $this->db->prepare("Select daire_kodu from $this->table WHERE id = ?");
            $sql->execute([$daire_id]);
            return $sql->fetch(PDO::FETCH_OBJ)->daire_kodu ?? "";

     }
}
