<?php 

namespace Model;

use Model\Model;

class KasaHareketModel extends Model{
    protected $table = "kasa_hareketleri";

    public function __construct()
    {
        parent::__construct($this->table);
    }


    /**Kaynak tablo ve kaynek_id alanına göre kayıtları sil 
     * @param string $kaynak_tablo
     * @param int $kaynak_id
     * @return bool
     */
    public function SilKaynakTabloKaynakId($kaynak_tablo, $kaynak_id)
    {
        $query = "DELETE FROM {$this->table} WHERE kaynak_tablo = :kaynak_tablo AND kaynak_id = :kaynak_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':kaynak_tablo', $kaynak_tablo, \PDO::PARAM_STR);
        $stmt->bindParam(':kaynak_id', $kaynak_id, \PDO::PARAM_INT);
        return $stmt->execute();
    }


    /** Kasa hareketlerini getirir.
     * @param int $kasa_id
     * @return array
     * @throws \Exception
     */
    public function getKasaHareketleri($kasa_id)
    {
        $query = "SELECT * FROM {$this->table} WHERE kasa_id = :kasa_id ORDER BY islem_tarihi DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':kasa_id', $kasa_id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }


    
}