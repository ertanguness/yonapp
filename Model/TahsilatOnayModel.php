<?php

namespace Model;

use Model\Model;
use PDO;

class TahsilatOnayModel extends Model
{
    protected $table = "tahsilat_onay";

    public function __construct()
    {
        parent::__construct($this->table);
    }



  /*
     * 
     */

    /**
     * Sitenin Onay Bekleyen Tahsilatlarını Getirir
     * @param int $site_id
     * @return array
     */
    public function BekleyenTahsilatlar($site_id)
    {
        $sql = $this->db->prepare("SELECT 
                                                tao.*,
                                                k.adi_soyadi, 
                                                d.daire_kodu,
                                                COALESCE(SUM(CASE WHEN t.silinme_tarihi IS NULL THEN t.tutar ELSE 0 END), 0) AS onaylanan_toplam_tutar,
                                                tao.tutar - COALESCE(SUM(CASE WHEN t.silinme_tarihi IS NULL THEN t.tutar ELSE 0 END), 0) AS kalan_tutar
                                            FROM tahsilat_onay tao
                                            LEFT JOIN kisiler k ON k.id = tao.kisi_id
                                            LEFT JOIN daireler d ON d.id = tao.daire_id 
                                            LEFT JOIN tahsilatlar t ON t.tahsilat_onay_id = tao.id
                                            WHERE tao.site_id = ? 
                                            and eslesmeyen_havuzunda = 0
                                            AND tao.onay_durumu = 0
                                            and tao.silinme_tarihi IS NULL
                                            GROUP BY tao.id
                                            HAVING kalan_tutar > 0");

        $sql->execute([$site_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    /**Onaylanmış tahsilatların toplam tutarını getirir
     * @param int $tahsilat_onay_id
     * @return float
     * 
     */
    public function OnaylanmisTahsilatToplami($tahsilat_onay_id)
    {
        $sql = $this->db->prepare("SELECT SUM(tutar) as toplam_tutar FROM tahsilatlar 
                                            WHERE tahsilat_onay_id = ?
                                            AND silinme_tarihi IS NULL");
        $sql->execute([$tahsilat_onay_id]);
        $result = $sql->fetch(PDO::FETCH_OBJ);
        return $result ? $result->toplam_tutar : 0.0; // Eğer sonuç varsa toplam tutarı döndür, yoksa 0 döndür
    }
}
