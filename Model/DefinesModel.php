<?php

namespace Model;


use Model\Model;
use PDO;

class DefinesModel extends Model
{
    protected $table = 'defines';

    public function __construct()
    {
        parent::__construct($this->table);
    }

    const TYPE_DAIRE_TIPI = 3;

    const TYPE_GELIR_TIPI = 6;
    const TYPE_GIDER_TIPI = 7;


    /**Gelir Gider Tiplerini getirir
     * @return array
     */
    public function getGelirGiderTipleri()
    {
        $site_id = $_SESSION['site_id']; // aktif site ID’sini alıyoruz
        $gelirTipi = self::TYPE_GELIR_TIPI;
        $giderTipi = self::TYPE_GIDER_TIPI;
        $sql = $this->db->prepare("SELECT d.*,
                                            case 
                                                when d.type = :gelirTipi then 'Gelir'
                                                when d.type = :giderTipi then 'Gider'
                                                else 'Diğer'    
                                            end as type_name
                                          FROM $this->table d
                                          WHERE site_id = :site_id 
                                          AND type IN (:gelirTipi, :giderTipi)
                                          AND silinme_tarihi IS NULL 
                                          ORDER BY define_name ASC");
        $sql->execute([
            ':site_id' => $site_id,
            ':gelirTipi' => $gelirTipi,
            ':giderTipi' => $giderTipi
        ]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    /** Gelir veya gider kategorilerini getirir
     * @param mixed $type
     * @return array
     */
    public function getGelirGiderKategorileri($type)
    {
        $site_id = $_SESSION['site_id']; // aktif site ID’sini alıyoruz
        $sql = $this->db->prepare("SELECT * FROM $this->table 
                                          WHERE site_id = ? AND type = ? AND silinme_tarihi IS NULL 
                                          ORDER BY define_name ASC");
        $sql->execute([
            $site_id,
            $type
        ]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }


    /** Gelir Gider Tipini getirir
     * @param mixed $id
     * @return object|null
     */
    public function getGelirGiderTipi($id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE id = ? AND silinme_tarihi IS NULL");
        $sql->execute([$id]);
        return $sql->fetch(PDO::FETCH_OBJ); // sadece bir satır bekleniyorsa
    }

    /** Gelir veya gider tiplerini select için getirir
     * @param mixed $type
     * @return string
     */
    public function getGelirGiderTipiSelect($name, $type, $selected)
    {
        $tipler = $this->getGelirGiderTipleri();
        $options = '';
        foreach ($tipler as $tip) {
            if ($tip->type == $type) {
                $isSelected = ($tip->define_name == $selected) ? 'selected' : '';
                $options .= "<option value=\"{$tip->id}\" {$isSelected}>{$tip->define_name}</option>";
            }
        }
        return "<select name=\"{$name}\" id=\"{$name}\" class=\"form-select select2\">{$options}</select>";
    }

    public function daireTipiGetir($type)
    {
        $site_id = $_SESSION['site_id']; // aktif site ID’sini alıyoruz
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

    public function getAllByApartmentType($type)
    {
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
    public function getDefinesTypes($siteId, $type)
    {
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
    public function getApartmentTypeIdByName($site_id, $type, $name)
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
