<?php 

namespace Model;

use App\Helper\Helper;
use Model\Model;
use PDO;

class FinansalRaporModel extends Model
{
    protected $table = "view_borclandirma_detay_raporu";

    //view_kisiler_hesap_ozet
    protected $vw_hesap_ozet = "view_kisiler_hesap_ozet"; // Eğer view kullanıyorsanız, bu satırı ekleyebilirsiniz.


    public function __construct()
    {
        parent::__construct($this->table);
    }


    /**
     * Kişilerin Ödenmemiş güncel borçlarını getirir.
     * @param int $site_id
     * @return array
     */

    public function getGuncelBorclar($site_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE site_id = ? ");
        $sql->execute([$site_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Kişilerin Ödenmemiş güncel borçlarını gruplanmış olarak getirir.
     * @param int $site_id
     * @return array
     */

    public function getGuncelBorclarGruplu($site_id)
    {
        $sql = $this->db->prepare("SELECT kisi_id,daire_kodu,adi_soyadi,uyelik_tipi, 
                                    SUM(tutar) AS kalan_anapara,  
                                    SUM(hesaplanan_gecikme_zammi) AS hesaplanan_gecikme_zammi,
                                    SUM(toplam_kalan_borc) AS toplam_kalan_borc 
                                    FROM $this->table 
                                    WHERE site_id = ?  and toplam_kalan_borc > 0
                                    GROUP BY kisi_id, daire_kodu, adi_soyadi, uyelik_tipi");
        $sql->execute([$site_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }


    /**
     * Kişinin Ödenmemiş güncel borçlarını olarak getirir.
     * @param int $kisi_id
     * @return array
     */
    public function getKisiGuncelBorclar($kisi_id)
    {
        $sql = $this->db->prepare("SELECT 
                                            * 
                                          FROM $this->table 
                                          WHERE kisi_id = ? 
                                          AND toplam_kalan_borc > 0");
        $sql->execute([$kisi_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    /** Kişinin borçlarını getirir.
     * @param int $kisi_id
     * @return array
     */
    public function getKisiBorclar($kisi_id)
    {
        $sql = $this->db->prepare("SELECT * 
                                          FROM $this->table 
                                          WHERE kisi_id = ? ");
        $sql->execute([$kisi_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }





    /**Kisinin finansal durumunu getirir(Toplam Anapara Borç, Toplam Gecikme, Toplam Borç, Toplam Tahsilat, Toplam Kalan Borç)
     * @param int $kisi_id
     * @return object|null
     */
    public function KisiFinansalDurum($kisi_id)
    {
        $sql = $this->db->prepare("SELECT *
                                          FROM $this->vw_hesap_ozet 
                                          WHERE kisi_id = ? ");
        $sql->execute([$kisi_id]);
        return $sql->fetch(PDO::FETCH_OBJ);
    }


    
    /**
     * Verilen ID dizisine ait borçların toplam tutarını döndürür.
     *
     * @param array $idler Borç detay ID'lerini içeren dizi.
     * @return float|false Toplam tutar veya hata durumunda false.
     */
    public function getToplamTutarByIds(array $idler): float|false
    {
        if (empty($idler)) {
            return 0.0;
        }

        // IN sorgusu için placeholder'lar oluştur (?,?,?)
        $placeholders = implode(',', array_fill(0, count($idler), '?'));

        $sql = "SELECT SUM(toplam_kalan_borc) as toplam FROM {$this->table} WHERE id IN ({$placeholders})";

        try {
            $stmt = $this->db->prepare($sql);
            // execute() metoduna ID dizisini doğrudan ver
            $stmt->execute($idler);
            // fetchColumn() tek bir sütunun değerini döndürür
            $toplam = ($stmt->fetchColumn());
            return (float)$toplam; // Sonucu float'a çevirerek döndür
        } catch (\PDOException $e) {
            // Hata loglama
            error_log("Toplam tutar alınırken hata: " . $e->getMessage());
            return false;
        }
    }



    
    /** Finds records where a specific column matches a value.
     * @param string $column The column to search in.
     * @param mixed $value The value to match against the column.
     */
    public function findWhereIn($column, $values, $sorting = "column asc")
    {
        if (empty($values)) {
            return [];
        }

        $placeholders = rtrim(str_repeat('?, ', count($values)), ', ');
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE $column IN ($placeholders) ORDER BY $sorting");
        $sql->execute($values);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }



}
