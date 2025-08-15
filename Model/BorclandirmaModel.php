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
                    IFNULL(tahsilat_ozeti.toplam_tahsilat, 0) AS toplam_tahsilat
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
        $query .= " ORDER BY b.bitis_tarihi DESC";
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



}
