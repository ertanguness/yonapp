<?php

namespace Model;

use Model\Model;
use PDO;

class PersonelOdemeModel extends Model
{
    protected $table = 'personel_odemeleri';

    public function __construct()
    {
        parent::__construct($this->table);
    }

    /**
     * Personele ait tüm ödemeleri getir (silinmemiş kayıtlar)
     * @param int $personel_id Personel ID'si
     * @return array Ödemeler dizisi
     */
    public function getOdemelerByPersonel($personel_id)
    {
        $sql = $this->db->prepare("
            SELECT * FROM $this->table 
            WHERE personel_id = ? AND silinme_tarihi IS NULL
            ORDER BY odeme_tarihi DESC
        ");
        $sql->execute([$personel_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Personelin toplam ödemelerini getir
     * @param int $personel_id Personel ID'si
     * @return object İstatistikler nesnesi
     */
    public function getTotalOdemelerByPersonel($personel_id)
    {
        $sql = $this->db->prepare("
            SELECT 
                COUNT(*) as odeme_sayisi,
                SUM(tutar) as toplam_tutar,
                AVG(tutar) as ortalama_tutar,
                MIN(odeme_tarihi) as ilk_odeme_tarihi,
                MAX(odeme_tarihi) as son_odeme_tarihi
            FROM $this->table 
            WHERE personel_id = ? AND silinme_tarihi IS NULL
        ");
        $sql->execute([$personel_id]);
        return $sql->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Ödeme kaydet
     * @param array $data Ödeme verileri
     * @return mixed Son eklenen kaydın ID'si
     */
    public function saveOdeme($data)
    {
        return $this->saveWithAttr($data);
    }

    /**
     * Ödeme güncelle
     * @param int $id Ödeme ID'si
     * @param array $data Güncelleme verileri
     * @return mixed Güncelleme sonucu
     */
    public function updateOdeme($id, $data)
    {
        $data['id'] = $id;
        return $this->saveWithAttr($data);
    }

    /**
     * Ödeme sil (soft delete)
     * @param int $id Ödeme ID'si
     * @return mixed Silme sonucu
     */
    public function deleteOdeme($id)
    {
        $sql = $this->db->prepare("
            UPDATE $this->table 
            SET silinme_tarihi = NOW() 
            WHERE id = ?
        ");
        return $sql->execute([$id]);
    }

    /**
     * Ödeme detayını getir
     * @param int $id Ödeme ID'si
     * @return object|null Ödeme bilgisi
     */
    public function getOdemeById($id)
    {
        $sql = $this->db->prepare("
            SELECT * FROM $this->table 
            WHERE id = ? AND silinme_tarihi IS NULL
        ");
        $sql->execute([$id]);
        return $sql->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Tarih aralığındaki ödemeleri getir
     * @param int $personel_id Personel ID'si
     * @param string $startDate Başlangıç tarihi (Y-m-d)
     * @param string $endDate Bitiş tarihi (Y-m-d)
     * @return array Ödemeler dizisi
     */
    public function getOdemelerByDateRange($personel_id, $startDate, $endDate)
    {
        $sql = $this->db->prepare("
            SELECT * FROM $this->table 
            WHERE personel_id = ? 
            AND odeme_tarihi BETWEEN ? AND ? 
            AND silinme_tarihi IS NULL
            ORDER BY odeme_tarihi DESC
        ");
        $sql->execute([$personel_id, $startDate, $endDate]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Ödeme türüne göre ödemeleri getir
     * @param int $personel_id Personel ID'si
     * @param string $odemeTuru Ödeme türü
     * @return array Ödemeler dizisi
     */
    public function getOdemelerByTuru($personel_id, $odemeTuru)
    {
        $sql = $this->db->prepare("
            SELECT * FROM $this->table 
            WHERE personel_id = ? 
            AND odeme_turu = ? 
            AND silinme_tarihi IS NULL
            ORDER BY odeme_tarihi DESC
        ");
        $sql->execute([$personel_id, $odemeTuru]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Belirli bir ayın ödemelerini getir
     * @param int $personel_id Personel ID'si
     * @param int $year Yıl
     * @param int $month Ay
     * @return array Ödemeler dizisi
     */
    public function getOdemelerByMonth($personel_id, $year, $month)
    {
        $startDate = date('Y-m-01', mktime(0, 0, 0, $month, 1, $year));
        $endDate = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));
        
        return $this->getOdemelerByDateRange($personel_id, $startDate, $endDate);
    }

    /**
     * Ödeme detayını ve personel bilgisini birlikte getir
     * @param int $id Ödeme ID'si
     * @return object|null Ödeme ve personel bilgisi
     */
    public function getOdemeWithPersonelDetails($id)
    {
        $sql = $this->db->prepare("
            SELECT po.*, p.adi_soyadi, p.email, p.telefon
            FROM $this->table po
            LEFT JOIN personel p ON po.personel_id = p.id
            WHERE po.id = ? AND po.silinme_tarihi IS NULL
        ");
        $sql->execute([$id]);
        return $sql->fetch(PDO::FETCH_OBJ);
    }
}
