<?php

namespace Model;

use App\Helper\Security;
use Model\Model;

class TahsilatDetayModel extends Model
{
    protected $table = "tahsilat_detay";
    protected $vw_detay_table = "view_tahsilat_detay"; // Görünüm tablosu


    public function __construct()
    {
        parent::__construct($this->table);
    }

    /**
     * Tahsilat detaylarını getirir
     * @param int $tahsilat_id
     * @return array
     */
    public function getDetaylarByTahsilatId($tahsilat_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE tahsilat_id = ?");
        $sql->execute([$tahsilat_id]);
        return $sql->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * Tahsilat detaylarını tahsilat listesinde detay olarak gösterir
     * @param int $tahsilat_id
     * @return array
     */
    public function getDetaylarForList($tahsilat_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->vw_detay_table 
                                          WHERE tahsilat_id = ?");
        $sql->execute([$tahsilat_id]);
        $detaylar = $sql->fetchAll(\PDO::FETCH_OBJ);

        // Detayları formatlamak için
        foreach ($detaylar as &$detay) {
            $detay->odenen_tutar = number_format($detay->odenen_tutar, 2, ',', '.');
            $detay->islem_tarihi = date('d.m.Y', strtotime($detay->islem_tarihi));
        }

        return $detaylar;
    }
}
