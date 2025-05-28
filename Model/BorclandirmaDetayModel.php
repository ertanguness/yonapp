<?php 


namespace Model;

use Model\Model;
use PDO;

class BorclandirmaDetayModel extends Model
{
    protected $table = "borclandirma_detayi"; 

    public function __construct()
    {
        parent::__construct($this->table);
    }

    // Borçlandırma detaylarını borç ID'sine göre getirir
    public function borcDetaylariniGetir($borc_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE borc_id = ?");
        $sql->execute([$borc_id]);
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
   
}