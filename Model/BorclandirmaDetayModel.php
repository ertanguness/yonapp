<?php


namespace Model;

use Model\Model;
use Model\BloklarModel;
use PDO;

class BorclandirmaDetayModel extends Model
{
    protected $table = "borclandirma_detayi";

    public function __construct()
    {
        parent::__construct($this->table);
    }


    /**
     * Borçlandırma tipi blok olan kayıtların gruplanmış blok id'lerini döndürür
     * @param int $borclandirma_id
     * @return array
     * @throws \Exception
     */
    public function BorclandirilmisBloklar($borclandirma_id)
    {
        $query = "SELECT DISTINCT blok_id FROM {$this->table} WHERE borclandirma_id = :borclandirma_id AND hedef_tipi = 'block'";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':borclandirma_id', $borclandirma_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Borçlandırılmış Blokların isimlerini getirir
     * @param int $borclandirma_id
     *  @return array
     */
    public function BorclandirilmisBlokIsimleri($borclandirma_id){
        $Bloklar = new BloklarModel();
        $boclandirilmis_bloklar = $this->BorclandirilmisBloklar($borclandirma_id);
        $blokIsimleri = [];
        foreach ($boclandirilmis_bloklar as $blok) {
            $blokIsimleri[] = $Bloklar->BlokAdi($blok->blok_id);
        }
        return implode(', ', $blokIsimleri); // Blok isimlerini virgülle ayırarak döndürür

    }


    /**
     * Toplam Borcandirma Tutarını getirir
     * @param int $borclandirma_id
     * @return float
     */
    public function ToplamBorclandirmaTutar($borclandirma_id)
    {
        $sql = $this->db->prepare("SELECT SUM(tutar) AS toplam_borc FROM $this->table WHERE borclandirma_id = ? AND silinme_tarihi IS NULL");
        $sql->execute([$borclandirma_id]);
        $result = $sql->fetch(PDO::FETCH_OBJ);
        return $result ? (float)$result->toplam_borc : 0.0; // Eğer sonuç varsa toplam borcu döndür, yoksa 0 döndür
    }


    // Borçlandırma detaylarını borç ID'sine göre getirir
    public function KisiBorclandirmalari($kisi_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE kisi_id = ? AND silinme_tarihi IS NULL");
        $sql->execute([$kisi_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }
  


    /**
     * Kişilerin borç listenini gruplanmış olarak getirir
     * 
     * @return array
     */
    public function gruplanmisBorcListesi()
    {
        $sql = $this->db->prepare("SELECT
                        bd.id as borc_id,
                        k.adi_soyadi AS kisi_adi,
                        bd.borc_adi,
                        b.blok_adi AS blok_adi,
                        bd.baslangic_tarihi,
                        bd.bitis_tarihi,
                        bd.ceza_orani,
                        bd.aciklama,
                        SUM(tutar) AS toplam_borc,
                        COUNT(*) AS borc_sayisi,
                        GROUP_CONCAT(DISTINCT borc_adi SEPARATOR ', ') AS borc_turleri
                    FROM
                        $this->table bd
                        LEFT JOIN kisiler k ON k.id = bd.kisi_id 
                        LEFT JOIN bloklar b ON b.id = k.blok_id
                    WHERE
                        bd.silinme_tarihi IS NULL  -- Silinmemiş kayıtlar
                    GROUP BY
                        k.id, b.id
                    ORDER BY toplam_borc DESC;");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Belirli bir kişinin toplam borçlarını getirir
     * @param int $kisi_id
     * @return float
     */
    public function KisiToplamBorc($kisi_id)
    {
        $sql = $this->db->prepare("SELECT SUM(tutar) AS toplam_borc FROM $this->table WHERE kisi_id = ? AND silinme_tarihi IS NULL");
        $sql->execute([$kisi_id]);
        $result = $sql->fetch(PDO::FETCH_OBJ);
        return $result ? (float)$result->toplam_borc : 0.0; // Eğer sonuç varsa toplam borcu döndür, yoksa 0 döndür
    }


    /**
     * Kişinin finansal durumunu özetler (toplam borç, toplam ödeme, bakiye)
     * @param int $kisi_id
     * @return object
     */
    public function KisiFinansalDurum($kisi_id)
    {
        $sql = $this->db->prepare("
       SELECT 
    (SELECT COALESCE(SUM(tutar), 0) FROM $this->table WHERE kisi_id = :kisi_id) AS toplam_borc,
    (SELECT COALESCE(SUM(tutar), 0) FROM tahsilatlar WHERE kisi_id = :kisi_id) AS toplam_odeme,
    (SELECT COALESCE(SUM(tutar), 0) FROM tahsilatlar WHERE kisi_id = :kisi_id) - 
    (SELECT COALESCE(SUM(tutar), 0) FROM $this->table WHERE kisi_id = :kisi_id) AS bakiye;
    ");
        $sql->execute([
            ':kisi_id' => $kisi_id
        ]);
        return $sql->fetch(PDO::FETCH_OBJ);
    }



}
