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
    public function getGelirGiderTipleri(bool $groupByDefineName = false)
    {
            $site_id = $_SESSION['site_id']; // aktif site ID’sini alıyoruz
            $gelirTipi = self::TYPE_GELIR_TIPI;
            $giderTipi = self::TYPE_GIDER_TIPI;
            $groupSql = $groupByDefineName ? ' GROUP BY d.define_name ' : '';
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
                                            {$groupSql}
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
    public function getGelirGiderKategorileri($type, bool $groupByDefineName = false)
    {
        $site_id = $_SESSION['site_id']; // aktif site ID’sini alıyoruz
        $groupSql = $groupByDefineName ? ' GROUP BY define_name ' : '';
        $sql = $this->db->prepare("SELECT * FROM $this->table 
                                          WHERE site_id = ? AND type = ? 
                                          AND silinme_tarihi IS NULL 
                                          {$groupSql}
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

    /**sitenin gelir grublarını select olarak al */
    public function getGelirGrubuSelect($name, $selected){

        $site_id = $_SESSION['site_id']; // aktif site ID’sini alıyoruz
        $sql = $this->db->prepare("SELECT id, define_name FROM $this->table 
                                    WHERE type = :type 
                                    AND site_id = :site_id 
                                    AND silinme_tarihi IS NULL");
        $sql->execute([
            ':type' => self::TYPE_GELIR_TIPI,
            ':site_id' => $site_id
        ]);
        $options = '';

        while ($row = $sql->fetch(PDO::FETCH_ASSOC)) {
            $options .= "<option value=\"{$row['define_name']}\" " . ($selected == $row['define_name'] ? 'selected' : '') . ">{$row['define_name']}</option>";
        }
        return "<select name=\"{$name}\" id=\"{$name}\" class=\"form-select select2\">{$options}</select>";
    }





    /** Gelir veya gider tiplerini select için getirir
     * @param mixed $type
     * @return string
     */
    public function getGelirGiderTipiSelect($name, $type, $selected)
    {
        $tipler = $this->getGelirGiderTipleri(true);
        $options = '';
        foreach ($tipler as $tip) {
            if ($tip->type == $type) {
                $isSelected = ($tip->define_name == $selected) ? 'selected' : '';
                $options .= "<option value=\"{$tip->id}\" {$isSelected}>{$tip->define_name}</option>";
            }
        }
        return "<select name=\"{$name}\" id=\"{$name}\" class=\"form-select select2\">{$options}</select>";
    }


/** Gelir-gider kalemlerini gelen type ve define_name'e göre için getirir */
    public function getGelirGiderKalemleri($type, $define_name)
    {
        $site_id = $_SESSION['site_id'] ?? 1; // aktif site ID’sini alıyoruz
        $sql = $this->db->prepare("SELECT d.*
                                          FROM $this->table d
                                          WHERE site_id = :site_id 
                                            AND type = :type 
                                            AND define_name = :define_name
                                            AND silinme_tarihi IS NULL
                                            AND alt_tur IS NOT NULL AND alt_tur != ''
                                          GROUP BY define_name, alt_tur
                                          ORDER BY alt_tur ASC");
        $sql->execute([
            ':site_id' => $site_id,
            ':type' => $type,
            ':define_name' => $define_name
        ]);
        return $sql->fetchAll(PDO::FETCH_OBJ);

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
    public function getApartmentTypeIdByName($site_id, $type, $name,$mulk_tipi)
    {
        $sql = $this->db->prepare("SELECT id FROM defines 
                                          WHERE site_id = ? 
                                          AND type = ? 
                                          AND define_name = ? 
                                          AND mulk_tipi = ?
                                          LIMIT 1");
        $sql->execute([
            $site_id,
            $type,
            $name,
            $mulk_tipi
        ]);
        $result = $sql->fetch(PDO::FETCH_OBJ);

        return $result ? (int)$result->id : null; // Eğer sonuç varsa ID'yi döndür, yoksa null döndür
    }
}
