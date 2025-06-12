<?php

namespace Model;

use Model\BloklarModel;
use Model\Model;
use PDO;

class KisilerModel extends Model
{
    protected $table = 'kisiler';

    public function __construct()
    {
        parent::__construct($this->table);
    }

    // aidat tablosundaki verileri alır
    public function getKisiler()
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    // Bloğun kişilerini getir
    public function BlokKisileri($block_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE blok_id = ?");
        $sql->execute([$block_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Siteye ait blokları ve bu bloklara ait kişileri getirir.
     *
     * @param int $site_id Parametre olarak gelen site ID'si.
     * @return array Kişileri içeren bir dizi döner.
     */
    public function SiteKisileri($site_id)
    {
        $Bloklar = new BloklarModel();
        $bloklar = $Bloklar->SiteBloklari($site_id);
        $kisiler = [];

        foreach ($bloklar as $blok) {
            $blok_kisileri = $this->BlokKisileri($blok->id);
            if (!empty($blok_kisileri)) {
                $kisiler = array_merge($kisiler, $blok_kisileri);
            }
        }

        return $kisiler;
    }

    /**
     * Belirli bir kişinin bilgilerini getirir.
     * @param int $id Kişinin ID'si.
     * @return object|null Kişi bilgilerini içeren nesne veya bulunamazsa null döner.
     */
    public function getPersonById($id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE id = ?");
        $sql->execute([$id]);
        return $sql->fetch(PDO::FETCH_OBJ);
    }

    /***Kişi ID'sinden Kişi Adını Getirir
     * @param int $id Kişinin ID'si.
     * @return string|null Kişinin adı veya bulunamazsa null döner.
     */
    public function KisiAdi($id)
    {
        $sql = $this->db->prepare("SELECT adi_soyadi FROM $this->table WHERE id = ?");
        $sql->execute([$id]);
        $result = $sql->fetch(PDO::FETCH_OBJ);
        return $result ? $result->adi_soyadi : null;
    }

    /**Daire id'si ve uyelik_tipi'nden şu anda aktif olan kiracıyı veya ev sahibini bul
     * @param int $daire_id Daire ID'si.
     * @param string $uyelik_tipi Kullanıcının tipi (ev sahibi veya kiracı).
     */
    public function AktifKisiByDaireId($daire_id, $uyelik_tipi)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE daire_id = ? AND uyelik_tipi = ? AND silinme_tarihi IS NULL ORDER BY id DESC LIMIT 1");
        $sql->execute([$daire_id, $uyelik_tipi]);
        return $sql->fetch(PDO::FETCH_OBJ);
    }

    public function SiteKisileriJoin($site_id)
    {
        if (!$site_id) {
            return [];
        }

        $stmt = $this->db->prepare("
        SELECT 
            kisiler.*, 
            araclar.id AS arac_id, 
            araclar.plaka, 
            araclar.marka_model
        FROM kisiler
        INNER JOIN bloklar ON kisiler.blok_id = bloklar.id
        LEFT JOIN araclar ON kisiler.id = araclar.kisi_id
        WHERE bloklar.site_id = :site_id
    ");
        $stmt->bindParam(':site_id', $site_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function KisiVarmi($kimlikNo)
    {
        $query = $this->db->prepare("SELECT COUNT(*) FROM $this->table WHERE kimlik_no = ?");
        $query->execute([$kimlikNo]);
        return $query->fetchColumn() > 0;
    }
    // Bloğun kişilerini getir
    public function DaireKisileri($daire_id)
    {
        $query = $this->db->prepare("SELECT id, adi_soyadi FROM kisiler WHERE daire_id = :daire_id");
        $query->execute(['daire_id' => $daire_id]);
        return $query->fetchAll(PDO::FETCH_OBJ);
    }
}
