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
        $sql = $this->db->prepare("SELECT kisi_id,daire_kodu,k.adi_soyadi,k.uyelik_tipi,k.telefon,k.giris_tarihi,daire_tipi,
                                                CASE WHEN k.cikis_tarihi IS NULL OR k.cikis_tarihi = '0000-00-00' THEN ''
                                                        ELSE DATE_FORMAT(k.cikis_tarihi, '%d.%m.%Y') END AS cikis_tarihi,
                                                Round(kalan_kredi, 2) as kredi_tutari,
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


    /** Gelen tutardan büyük ve eşit tutarda borcu olanları getirir.
     * @param float $minAmount
     * @return array
     */
    public function getKisiBorclarByMinAmount($minAmount): array
    {
        $sql = $this->db->prepare("SELECT * 
                                          FROM $this->vw_hesap_ozet 
                                          WHERE bakiye <= ? 
                                          ORDER BY bakiye asc
                                          LIMIT 10");
        $sql->execute([$minAmount]);
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






    /** Toplam Aidat gelirini getirir
     * @param int $site_id
     * @return float
     */
    public function getToplamAidatGeliri(int $site_id): float
    {
        $sql = $this->db->prepare("SELECT  
                                             SUM(yapilan_tahsilat) AS toplam_aidat_geliri
                                        FROM view_borclandirma_detay_raporu
                                        WHERE aciklama  LIKE '%Aidat%'
                                            AND site_id = ?");
        $sql->execute([$site_id]);
        $result = $sql->fetch(PDO::FETCH_OBJ);
        return $result ? (float)$result->toplam_aidat_geliri : 0.0;
    }


    /** Geciken Ödem tutarini getirir
     * @param int $site_id
     * @return float
     */
    public function getGecikenOdemeTutar(int $site_id): float
    {
        $sql = $this->db->prepare("SELECT  
                                            (SUM(tutar) -SUM(yapilan_tahsilat)) AS geciken_tutar
                                        FROM view_borclandirma_detay_raporu
                                        WHERE bitis_tarihi < CURDATE()
                                            AND yapilan_tahsilat < tutar
                                            AND site_id = ?");
        $sql->execute([$site_id]);
        $result = $sql->fetch(PDO::FETCH_OBJ);
        return $result ? (float)$result->geciken_tutar : 0.0;
    }


    /** Toplam giderleri getirir
     * @param int $site_id
     * @return float
     */
    public function getToplamGiderler(int $site_id): float
    {
        $sql = $this->db->prepare("SELECT  
                                             SUM(tutar) AS toplam_giderler
                                        FROM kasa_hareketleri
                                        WHERE tutar < 0
                                            AND site_id = ?");
        $sql->execute([$site_id]);
        $result = $sql->fetch(PDO::FETCH_OBJ);
        return $result ? (float)$result->toplam_giderler : 0.0;
    }




    /**Geciken Tahsilat Sayısını getirir
     * @param int $site_id
     * @return int
     */
    public function getGecikenTahsilatSayisi(int $site_id): int
    {
        $sql = $this->db->prepare("SELECT COUNT(id) as geciken_sayisi 
                                      FROM $this->table
                                      WHERE yapilan_tahsilat <= 0 
                                      AND bitis_tarihi < CURDATE()
                                      AND site_id = ?");
        $sql->execute([$site_id]);
        $result = $sql->fetch(PDO::FETCH_OBJ);
        return $result ? (int)$result->geciken_sayisi : 0;
    }

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
     * Kişi borçlarını borç adı/kategoriye göre özetler.
     * Dönen: [ (object){ kategori, toplam_borc, toplam_odeme, kalan } ]
     */
    public function getKisiKategoriOzet(int $kisi_id): array
    {
        $sql = $this->db->prepare(
            "SELECT 
                COALESCE(borc_adi, aciklama, 'Diğer') AS kategori,
                SUM(COALESCE(tutar,0))               AS toplam_borc,
                SUM(COALESCE(yapilan_tahsilat,0))    AS toplam_odeme,
                SUM(COALESCE(tutar,0) - COALESCE(yapilan_tahsilat,0)) AS kalan
             FROM {$this->table}
             WHERE kisi_id = ?
             GROUP BY kategori
             ORDER BY kategori ASC"
        );
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

    /**
     * Site genelinde kişi ve borçlandırma bazında yapılan tahsilat toplamlarını döndürür.
     * Kaynak: view_borclandirma_detay_raporu (kisi_id, borclandirma_id, yapilan_tahsilat)
     *
     * Dönen veri: [ (object) { kisi_id, borclandirma_id, toplam_odeme } ]
     */
    public function getOdemelerPivotBySite(int $site_id): array
    {
        $sql = $this->db->prepare(
            "SELECT 
                kisi_id, 
                borclandirma_id, 
                SUM(COALESCE(yapilan_tahsilat,0)) AS toplam_odeme
             FROM {$this->table}
             WHERE site_id = ?
             GROUP BY kisi_id, borclandirma_id"
        );
        $sql->execute([$site_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Tarih başlangıcından önceki hareketlerden açılış (devir) bileşenlerini getirir.
     * Döner: [ (object){ kisi_id, open_anapara, open_gecikme, open_odenen } ]
     */
    public function getOpeningBreakdownByDate(string $startDate): array
    {
        $sql = $this->db->prepare(
                "SELECT kisi_id,
                        SUM(COALESCE(anapara,0))         AS open_anapara,
                        SUM(COALESCE(gecikme_zammi,0))   AS open_gecikme,
                        SUM(CASE WHEN islem_tipi='Ödeme' THEN COALESCE(odenen,0) ELSE 0 END) AS open_odenen
                FROM {$this->vw_hesap_hareket}
                WHERE islem_tarihi < ?
                GROUP BY kisi_id"
        );
        $sql->execute([$startDate]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Tarih başlangıcından önceki hareketlerden tahsilat bileşenlerini getirir.
     * Döner: [ (object){ kisi_id, payed_anapara, pay
     */
    public function getPaymentBreakdownByDate(string $startDate): array
    {   
        $sql = $this->db->prepare(
                "SELECT kisi_id,
                        SUM(CASE WHEN islem_tipi='Ödeme' THEN COALESCE(odenen,0) ELSE 0 END) AS payed_odenen,
                        SUM(CASE WHEN islem_tipi='Ödeme' THEN COALESCE(anapara,0) ELSE 0 END) AS payed_anapara,
                        SUM(CASE WHEN islem_tipi='Ödeme' THEN COALESCE(gecikme_zammi,0) ELSE 0 END) AS payed_gecikme
                FROM {$this->vw_hesap_hareket}
                WHERE islem_tarihi < ?
                GROUP BY kisi_id"
        );
        $sql->execute([$startDate]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Dönem içi ödemeleri (tahsilatlar) kişi bazında toplar.
     * Döner: [ (object){ kisi_id, donem_odenen } ]
     */
    public function getPaymentsByDateRange(string $startDate, string $endDate): array
    {
        $sql = $this->db->prepare(
            "SELECT kisi_id,
                    SUM(COALESCE(odenen,0)) AS donem_odenen
             FROM {$this->vw_hesap_hareket}
             WHERE islem_tipi='Ödeme' AND islem_tarihi BETWEEN ? AND ?
             GROUP BY kisi_id"
        );
        $sql->execute([$startDate, $endDate]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Dönem içi tahakkukları kişi ve kategori (borç adı) bazında toplar.
     * Döner: [ (object){ kisi_id, kategori, toplam_tahakkuk } ]
     */
    public function getAccrualsBySiteBetween(int $site_id, string $startDate, string $endDate): array
    {
        $sql = $this->db->prepare(
            "SELECT kisi_id,
                    COALESCE(borc_adi, aciklama, 'Diğer') AS kategori,
                    SUM(COALESCE(tutar,0)) AS toplam_tahakkuk
             FROM {$this->table}
             WHERE site_id = ? AND baslangic_tarihi BETWEEN ? AND ?
             GROUP BY kisi_id, kategori"
        );
        $sql->execute([$site_id, $startDate, $endDate]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Borçlandırma (due) bazında, verilen tarih aralığında tahakkuk ve tahsilat toplamlarını getirir.
     * Döner: [ (object){ borclandirma_id, borc_adi, toplam_tahakkuk, toplam_tahsilat } ]
     */
    public function getBorclandirmaSummaryByDateRange(int $site_id, string $startDate, string $endDate): array
    {
        // Not: view_borclandirma_detay_raporu sütunları: borclandirma_id, site_id, baslangic_tarihi, tutar (tahakkuk), yapilan_tahsilat, borc_adi/aciklama
        $sql = $this->db->prepare(
            "SELECT 
                v.borclandirma_id,
                COALESCE(v.borc_adi, v.aciklama, 'Diğer') AS borc_adi,
                SUM(COALESCE(v.tutar,0)) AS toplam_tahakkuk,
                SUM(COALESCE(v.yapilan_tahsilat,0)) AS toplam_tahsilat,
                v.aciklama as aciklama
             FROM {$this->table} v
             WHERE v.site_id = ?
               AND v.baslangic_tarihi BETWEEN ? AND ?
             GROUP BY v.borclandirma_id, borc_adi
             ORDER BY borc_adi ASC"
        );
        $sql->execute([$site_id, $startDate, $endDate]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }
}
