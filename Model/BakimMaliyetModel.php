<?php

namespace Model;

use Model\Model;
use PDO;

class BakimMaliyetModel extends Model
{
    protected $table = "bakim_maliyeti";

    public function __construct()
    {
        parent::__construct($this->table);
    }
    /**
     * Giriş yapan Kullanıcının sitelerini getirir
     * @return array
     */

    public function Bakimlar($type)
    {
        $site_id = $_SESSION['site_id'];

        $table = match ((int)$type) {
            1 => 'bakim',
            2 => 'periyodik_bakimlar',
            default => null,
        };

        if (!$table) {
            return [];
        }

        $sql = $this->db->prepare("SELECT id, talep_no FROM {$table} WHERE site_id = ?");
        $sql->execute([$site_id]);

        return $sql->fetchAll(PDO::FETCH_OBJ);
    }
    public function MaliyetKayitlari()
    {
        $site_id = $_SESSION['site_id'];

        $sql = $this->db->prepare("
            SELECT * FROM $this->table 
            WHERE (
            (talep_no IN (
                SELECT id FROM bakim WHERE site_id = :site_id
            ))
            OR
            (talep_no IN (
                SELECT id FROM periyodik_bakimlar WHERE site_id = :site_id
            ))
            )
        ");
        $sql->execute(['site_id' => $site_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }
    public function MaliyetBilgileri($id)
    {
        $query = $this->db->prepare("SELECT * FROM $this->table WHERE id = ?");
        $query->execute([$id]);
        return $query->fetch(PDO::FETCH_OBJ);
    }
    public function TalepNoBul($id, $type)
    {
        $table = match ((int)$type) {
            1 => 'bakim',
            2 => 'periyodik_bakimlar',
            default => null,
        };

        if (!$table) {
            return null;
        }

        $sql = $this->db->prepare("SELECT talep_no FROM {$table} WHERE id = ?");
        $sql->execute([$id]);
        $result = $sql->fetch(PDO::FETCH_OBJ);

        return ($result && isset($result->talep_no)) ? $result->talep_no : null;
    }
    public function MaliyetNoVarmi($talepNo)
    {
        $sql = $this->db->prepare("SELECT COUNT(*) as count FROM $this->table WHERE talep_no = ?");
        $sql->execute([$talepNo]);
        $result = $sql->fetch(PDO::FETCH_OBJ);
        return ($result && $result->count > 0);
    }
    public function insertBakimMakbuz($maliyet_id, $dosya_yolu)
    {
        $sql = "INSERT INTO bakim_makbuzlari (maliyet_id, dosya_yolu) VALUES (:maliyet_id, :dosya_yolu)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':maliyet_id' => $maliyet_id,
            ':dosya_yolu' => $dosya_yolu
        ]);
    }
    public function MakbuzaAitTumDosyalar($maliyet_id)
    {
        $sql = "SELECT * FROM bakim_makbuzlari WHERE maliyet_id = :maliyet_id ORDER BY kayit_tarihi DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':maliyet_id' => $maliyet_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function MakbuzSayisi($maliyet_id)
    {
        $sql = "SELECT COUNT(*) as count FROM bakim_makbuzlari WHERE maliyet_id = :maliyet_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':maliyet_id' => $maliyet_id]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ? (int)$result->count : 0;
    }
}
