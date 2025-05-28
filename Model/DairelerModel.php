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
}
