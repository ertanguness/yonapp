<?php

namespace Model;

use Model\Model;
use PDO;

//DuesModel sınıfı BaseModel sınıfından miras alır
class DueDetailModel extends Model
{
    protected $table = "dues_details";

    //DuesModel sınıfının constructor metodunu tanımlıyoruz
    public function __construct()
    {
        parent::__construct($this->table);
    }



    /** Tanımlı Borçlandırmaları getirir 
     * 
    */
    public function getTanimliBorclandirmalar()
    {
        $sql = "SELECT 
                    dd.id,
                    dd.due_id,
                    GROUP_CONCAT(
                        DISTINCT CONCAT(dd.borclandirma_tipi, ' (', dd.tutar, ' ₺)', ' (', dd.ceza_orani, '%)')
                            SEPARATOR '<br>'
                        ) AS borclandirma_tipi_tutar,
                    dd.tutar,
                    d.start_date,
                    d.end_date,
                    dd.kayit_tarihi,
                    d.due_name,
                    GROUP_CONCAT(DISTINCT b.blok_adi SEPARATOR ', ') AS blok_adlari,
                    GROUP_CONCAT(DISTINCT k.adi_soyadi SEPARATOR ', ') AS kisi_adlari,
                    GROUP_CONCAT(DISTINCT dfn.define_name SEPARATOR ', ') AS daire_tipleri
                FROM dues_details dd
                INNER JOIN dues d ON d.id = dd.due_id
                LEFT JOIN bloklar b 
                    ON dd.blok_ids LIKE CONCAT('%\"', b.id, '\"%')
                LEFT JOIN kisiler k 
                    ON dd.kisi_ids LIKE CONCAT('%\"', k.id, '\"%')
                LEFT JOIN defines dfn 
                    ON dd.daire_tipi_ids LIKE CONCAT('%\"', dfn.id, '\"%')
                WHERE dd.silinme_tarihi IS null
                GROUP BY dd.due_id;)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
