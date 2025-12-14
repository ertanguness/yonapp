<?php

namespace Model;

use PDO;

class DuyuruModel extends Model
{
    protected $table = 'duyurular';

    public function __construct()
    {
        parent::__construct($this->table);
    }


    /**Bir siteye yapılan duyurular */
    public function site($siteId)
    {
        $query = $this->db->prepare("SELECT * FROM {$this->table} WHERE site_id = ?");
        $query->execute([$siteId]);
        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    /**site Sakini için duyurularını getir */
    public function sakinDuyurulari($kisi_id)
    {
        $query = $this->db->prepare("SELECT d.*
                                    FROM $this->table d
                                    JOIN kisiler k ON k.id = ?
                                    WHERE d.site_id = k.site_id
                                    AND d.silinme_tarihi IS NULL
                                    AND d.durum = 'published'
                                    AND d.baslangic_tarihi <= CURDATE()
                                    AND (
                                            d.bitis_tarihi = '0000-00-00'
                                            OR d.bitis_tarihi >= CURDATE()
                                        )

                                    AND (d.target_type = 'all'
                                            OR (
                                                d.target_type = 'block'
                                                AND JSON_CONTAINS(d.target_ids, JSON_ARRAY(k.blok_id))
                                            )
                                            OR (
                                                d.target_type = 'kisi'
                                                AND JSON_CONTAINS(d.target_ids, JSON_ARRAY(k.id))
                                            )
                                        )
                                    ORDER BY d.olusturulma_tarihi DESC;");
        $query->execute([$kisi_id]);
        return $query->fetchAll(PDO::FETCH_OBJ);
    }

}
