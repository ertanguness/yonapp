<?php
namespace Model;
use PDO;
use Model\Model;

class GelirGiderRaporuModel extends Model
{
    protected $table = "gelir_gider";

    public function __construct()
    {
        parent::__construct($this->table);
    }

    /**
     * Gelir-Gider raporu verilerini getirir
     * @param int $site_id
     * @param string $start
     * @param string $end
     * @param string $tur 'hepsi', 'gelir', 'gider'
     * @return array
     */
    public function getGelirGiderRaporu($site_id, $start, $end, $tur = 'hepsi')
    {
        $query = "SELECT 
            gg.id,
            gg.tarih,
            gg.islem_turu,
            k.kategori_adi,
            gg.aciklama,
            gg.tutar,
            gg.odeme_yontemi,
            gg.belge_no,
            gg.olusturma_tarihi
          FROM gelir_gider gg
          LEFT JOIN gelir_gider_kategoriler k ON gg.kategori_id = k.id
          WHERE gg.site_id = :site_id 
          AND gg.tarih BETWEEN :start AND :end
          AND gg.silinme_tarihi IS NULL";

        if ($tur == 'gelir') {
            $query .= " AND gg.islem_turu = 'Gelir'";
        } elseif ($tur == 'gider') {
            $query .= " AND gg.islem_turu = 'Gider'";
        }

        $query .= " ORDER BY gg.tarih ASC, gg.id ASC";

        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':site_id' => $site_id,
            ':start' => $start,
            ':end' => $end
        ]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
