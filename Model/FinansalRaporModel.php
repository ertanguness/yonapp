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

    protected $vw_hesap_hareket = "view_kisiler_hesap_hareket";

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
        $sql = $this->db->prepare("SELECT kisi_id,daire_kodu,k.adi_soyadi,k.uyelik_tipi, kalan_kredi as kredi_tutari,
                                                SUM(kalan_anapara) AS kalan_anapara,  
                                                SUM(hesaplanan_gecikme_zammi) AS hesaplanan_gecikme_zammi,
                                                SUM(toplam_kalan_borc) AS toplam_kalan_borc,
                                                 k.cikis_tarihi,
                                            -- oturan veya kiracının durumunu alıyoruz
                                            CASE 
                                                WHEN k.cikis_tarihi IS NULL OR k.cikis_tarihi = 0000-00-00 THEN 'Aktif'
                                                ELSE 'Pasif' END AS durum
                                          FROM $this->table vb
                                          LEFT JOIN kisiler k ON k.id = vb.kisi_id
                                          WHERE vb.site_id = ? 
                                          GROUP BY kisi_id, daire_kodu, adi_soyadi, uyelik_tipi");
        $sql->execute([$site_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }



    /** Kişi güncel borç özeti
     * Kalan anapara, gecikme zammı toplamından kredi tutarını düşer.
     * sp_kisi_borc procedüründen verileri getirir
     * @param int $kisi_id
     * @return object
     */
    public function getKisiGuncelBorcOzet($kisi_id)
    {
        $sql = $this->db->prepare("CALL sp_kisi_borc(?)");
        $sql->execute([$kisi_id]);
        return $sql->fetch(PDO::FETCH_OBJ);
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
                                          AND toplam_kalan_borc > 0
                                          ORDER BY bitis_tarihi ASC");
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
                                          WHERE kisi_id = ? 
                                          ORDER BY bitis_tarihi DESC");
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


    // /** Kişinin hesap hareketlerini (borçlandırma, tahsilat) getirir.
    //  * @param int $kisi_id
    //  * @return array
    //  */
    // public function kisiHesapHareketleri($kisi_id)
    // {
    //     $sql = $this->db->prepare("SELECT * 
    //                                       FROM $this->vw_hesap_hareket 
    //                                       WHERE kisi_id = ? 
    //                                       ORDER BY islem_tarihi asc");
    //     $sql->execute([$kisi_id]);
    //     return $sql->fetchAll(PDO::FETCH_OBJ);
    // }


    /** Kişinin hesap hareketlerini (borçlandırma, tahsilat) getirir.
     * @param int $kisi_id
     * @return array
     */
    public function kisiHesapHareketleri($kisi_id)
    {
        $sql = $this->db->prepare("SELECT 
                                                h.*,                                            -- 1. Calculate and alias the 'hareket_tutari' column (no rounding specified for this one)
                                                ROUND((CASE 
                                                    WHEN h.islem_tipi = 'Ödeme' 
                                                    THEN - COALESCE(h.odenen, 0)
                                                    ELSE COALESCE(h.anapara, 0) + COALESCE(h.gecikme_zammi, 0)
                                                END),2) AS hareket_tutari,

                                                -- 2. Calculate the 'yuruyen_bakiye' column, applying ROUND to the final SUM result
                                                ROUND(
                                                    SUM(
                                                        CASE 
                                                            WHEN h.islem_tipi = 'Ödeme' 
                                                            THEN - COALESCE(h.odenen, 0)
                                                            ELSE COALESCE(h.anapara, 0) + COALESCE(h.gecikme_zammi, 0)
                                                        END
                                                    ) OVER (
                                                        PARTITION BY h.kisi_id
                                                        ORDER BY h.islem_tarihi, h.islem_id
                                                        ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
                                                    ),
                                                    2 -- The number of decimal places for the ROUND function
                                                ) AS yuruyen_bakiye
                                          FROM $this->vw_hesap_hareket h
                                          WHERE h.kisi_id = ? 
                                          ORDER BY h.islem_tarihi");
        $sql->execute([$kisi_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
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
