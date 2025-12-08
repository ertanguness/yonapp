<?php


namespace Model;

use Model\Model;
use PDO;

class BorclandirmaModel extends Model
{
    protected $table = "borclandirma";

    public function __construct()
    {
        parent::__construct($this->table);
    }



    /** Borçlandırma bilgilerini, borç adıyla beraber döndürür
     * @param int|string $borclandirma_id
     * @return object
     */
    public function findWithDueName($borclandirma_id)
    {
        $sql = $this->db->prepare("SELECT 
                                            b.*,
                                            d.due_name as borc_adi 
                                            FROM {$this->table} b
                                            LEFT JOIN dues d ON d.id = b.borc_tipi_id 
                                            WHERE b.id = ? AND b.silinme_tarihi IS NULL");
        $sql->execute([$borclandirma_id]);
        return $sql->fetch(PDO::FETCH_OBJ);
    }


    /**
     * Belirtilen site ve (opsiyonel olarak) borçlandırma ID'sine göre 
     * finansal özetle birlikte borçlandırma(ları) getirir.
     * Bu, diğer public fonksiyonlar için temel sorgu oluşturucudur.
     *
     * @param int $site_id
     * @param int|null $borclandirma_id
     * @return array
     */
    protected function getBorclandirmaOzet(int $site_id, ?int $borclandirma_id = null): array
    {
        // Temel sorgu yapısı aynı kalır.
        $query = "SELECT
                    b.*,
                    IFNULL(borc_ozeti.toplam_borc, 0) AS toplam_borc,
                    IFNULL(tahsilat_ozeti.toplam_tahsilat, 0) AS toplam_tahsilat,
                    IFNULL(say_ozet.kisi_sayisi, 0) AS kisi_sayisi,
                    IFNULL(say_ozet.detay_sayisi, 0) AS detay_sayisi,
                    -- Eğer say_ozet.toplam_kalan yoksa, borc - tahsilat farkını kullan
                    IFNULL(say_ozet.toplam_kalan, IFNULL(borc_ozeti.toplam_borc, 0) - IFNULL(tahsilat_ozeti.toplam_tahsilat, 0)) AS toplam_kalan,
                    IFNULL(say_ozet.odenmemis_satir, 0) AS odenmemis_satir
                FROM
                    borclandirma AS b
                LEFT JOIN (
                    -- Borçları toplayan alt sorgu
                    SELECT borclandirma_id, SUM(tutar) AS toplam_borc
                    FROM borclandirma_detayi
                    WHERE silinme_tarihi IS NULL
                    GROUP BY borclandirma_id
                ) AS borc_ozeti ON b.id = borc_ozeti.borclandirma_id
                LEFT JOIN (
                    -- Tahsilatları toplayan alt sorgu
                    SELECT bd.borclandirma_id, SUM(td.odenen_tutar) AS toplam_tahsilat
                    FROM tahsilat_detay AS td
                    JOIN borclandirma_detayi AS bd ON td.borc_detay_id = bd.id
                    WHERE td.silinme_tarihi IS NULL
                    GROUP BY bd.borclandirma_id
                ) AS tahsilat_ozeti ON b.id = tahsilat_ozeti.borclandirma_id
                LEFT JOIN (
                    -- Katılımcı ve kalan özetleri
                    SELECT 
                        borclandirma_id,
                        COUNT(*) AS detay_sayisi,
                        COUNT(DISTINCT kisi_id) AS kisi_sayisi,
                        SUM(COALESCE(kalan_borc, 0)) AS toplam_kalan,
                        SUM(CASE WHEN COALESCE(kalan_borc, 0) > 0 THEN 1 ELSE 0 END) AS odenmemis_satir
                    FROM borclandirma_detayi
                    WHERE silinme_tarihi IS NULL
                    GROUP BY borclandirma_id
                ) AS say_ozet ON b.id = say_ozet.borclandirma_id
                WHERE
                    b.site_id = :site_id
                    AND b.silinme_tarihi IS NULL";

        // Parametreleri hazırla
        $params = [':site_id' => $site_id];

        // Eğer bir borçlandırma ID'si gönderildiyse, WHERE koşuluna ekle
        if (!is_null($borclandirma_id)) {
            $query .= " AND b.id = :borclandirma_id";
            $params[':borclandirma_id'] = $borclandirma_id;
        }

        // Sıralama sadece tüm liste istendiğinde anlamlıdır.
        if (is_null($borclandirma_id)) {
            $query .= " ORDER BY b.id desc, b.bitis_tarihi asc";
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);

        // fetchAll() her zaman bir dizi döndürür.
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }



    /**
     * Siteye ait tüm borçlandırmaları finansal özetleriyle birlikte getirir.
     * @param int $site_id
     * @return array
     */
    public function getAll(int $site_id): array
    {
        return $this->getBorclandirmaOzet($site_id);
    }


    /**
     * ID'si belirtilen tek bir borçlandırmayı, finansal özetiyle birlikte getirir.
     * Kayıt bulunamazsa null döndürür.
     *
     * @param int $site_id
     * @param int $borclandirma_id
     * @return object|null
     */
    public function findByID(int $site_id, int $borclandirma_id): ?object
    {
        // Temel fonksiyonu ID ile çağır
        $results = $this->getBorclandirmaOzet($site_id, $borclandirma_id);

        // Sonuç dizisinin ilk elemanını döndür. Eğer dizi boşsa, null döner.
        return $results[0] ?? null;
    }



    /** Borçlandırmaları aidat adıyla beraber getirir
     * @param int $site_id
     * @return array
     */
    public function getDebitsWithDueName(int $site_id): array
    {
        $sql = $this->db->prepare("SELECT 
                                        b.*,
                                        d.due_name as borc_adi 
                                        FROM {$this->table} b
                                        LEFT JOIN dues d ON d.id = b.borc_tipi_id 
                                        WHERE b.site_id = ? AND b.silinme_tarihi IS NULL
                                        ORDER BY b.id DESC");
        $sql->execute([$site_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Yıl bazında aidat tahsilat durumunu getirir (SQL tabanlı hesaplama).
     * 
     * @param int $site_id
     * @param int $year
     * @return array [month => ['odenecek' => float, 'odenen' => float]]
     */
    public function getAidatYearlyStats(int $site_id, int $year): array
    {
        // Temel sorgu: Borçlandırma Özet mantığını kullanır, ancak aylık gruplar
        $query = "SELECT 
                    MONTH(b.bitis_tarihi) as ay,
                    SUM(IFNULL(borc_ozeti.toplam_borc, 0)) AS aylik_toplam_borc,
                    SUM(IFNULL(tahsilat_ozeti.toplam_tahsilat, 0)) AS aylik_toplam_tahsilat
                FROM 
                    borclandirma AS b
                LEFT JOIN dues d ON b.borc_tipi_id = d.id
                LEFT JOIN (
                    -- Borçları toplayan alt sorgu
                    SELECT borclandirma_id, SUM(tutar) AS toplam_borc 
                    FROM borclandirma_detayi 
                    WHERE silinme_tarihi IS NULL 
                    GROUP BY borclandirma_id 
                ) AS borc_ozeti ON b.id = borc_ozeti.borclandirma_id 
                LEFT JOIN (
                    -- Tahsilatları toplayan alt sorgu
                    SELECT bd.borclandirma_id, SUM(td.odenen_tutar) AS toplam_tahsilat 
                    FROM tahsilat_detay AS td 
                    JOIN borclandirma_detayi AS bd ON td.borc_detay_id = bd.id 
                    WHERE td.silinme_tarihi IS NULL 
                    GROUP BY bd.borclandirma_id 
                ) AS tahsilat_ozeti ON b.id = tahsilat_ozeti.borclandirma_id 
                WHERE 
                    b.site_id = :site_id 
                    AND b.silinme_tarihi IS NULL
                    AND YEAR(b.bitis_tarihi) = :year
                    AND (d.due_name LIKE :aidat_filter OR b.aciklama LIKE :aidat_filter_desc)
                GROUP BY MONTH(b.bitis_tarihi)";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':site_id', $site_id, PDO::PARAM_INT);
        $stmt->bindValue(':year', $year, PDO::PARAM_INT);
        $stmt->bindValue(':aidat_filter', '%aidat%', PDO::PARAM_STR);
        $stmt->bindValue(':aidat_filter_desc', '%aidat%', PDO::PARAM_STR);
        
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);

        // Sonuçları işle ve aylık formata çevir
        $monthlyData = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyData[$m] = ['odenecek' => 0.0, 'odenen' => 0.0];
        }

        foreach ($results as $row) {
            $m = (int)$row->ay;
            if ($m >= 1 && $m <= 12) {
                $monthlyData[$m]['odenecek'] = (float)$row->aylik_toplam_borc;
                $monthlyData[$m]['odenen'] = (float)$row->aylik_toplam_tahsilat;
            }
        }

        return $monthlyData;
    }

    /**
     * Yıl bazında aidat tahsilat durumunu getirir.
     * list.php'deki mantığı kullanarak borçlandırmaları çeker ve işler.
     * 
     * @param int $site_id
     * @param int $year
     * @return array [month => ['odenecek' => float, 'odenen' => float]]
     */
    public function getAidatSummaryByYear(int $site_id, int $year): array
    {
        // Geriye dönük uyumluluk veya alternatif kullanım için wrapper
        return $this->getAidatYearlyStats($site_id, $year);
    }
}
