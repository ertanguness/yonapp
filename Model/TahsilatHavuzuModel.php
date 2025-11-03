<?php 
namespace Model;

use Model\Model;
use PDO;



class TahsilatHavuzuModel extends Model{
    protected $table = "tahsilat_havuzu";

    public function __construct()
    {
        parent::__construct($this->table);
    }


    /*     * Sitenin Tahsilat Havuzunu Getirir
     * @param int $site_id
     * @return array
     */
    public function TahsilatHavuzu($site_id)
    {
        $sql = $this->db->prepare("SELECT th.* 
                                   FROM $this->table th
                                   WHERE th.site_id = ?
                                   AND th.silinme_tarihi IS NULL");
        $sql->execute([$site_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Eşleşmemiş tahsilat sayısını getirir
     * @param int $site_id
     * @return int
     */
    public function getEslesmeyenSayisi($site_id)
    {
        $sql = $this->db->prepare("SELECT COUNT(*) as sayi 
                                   FROM $this->table 
                                   WHERE site_id = ? 
                                   AND (daire_id IS NULL OR daire_id = 0)
                                   AND (kalan_tutar > 0 OR kalan_tutar IS NULL)");
        $sql->execute([$site_id]);
        $result = $sql->fetch(PDO::FETCH_OBJ);
        return $result ? (int)$result->sayi : 0;
    }

    /**
     * Banka API'sinden gelen işlemi havuza ekler
     * @param array $data
     * @return bool
     */
    public function insertBankaIslemi($data)
    {
        $sql = $this->db->prepare("INSERT INTO $this->table 
                                   (site_id, kasa_id, islem_tarihi, aciklama, tahsilat_tutari, 
                                    kalan_tutar, hareket_yonu, banka_ref_no, created_at)
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        return $sql->execute([
            $data['site_id'],
            $data['kasa_id'],
            $data['islem_tarihi'],
            $data['aciklama'],
            $data['tahsilat_tutari'],
            $data['kalan_tutar'] ?? $data['tahsilat_tutari'],
            $data['hareket_yonu'] ?? 'Gelir',
            $data['banka_ref_no'] ?? null
        ]);
    }

    /**
     * Belirli bir banka referans numarasının daha önce kaydedilip kaydedilmediğini kontrol eder
     * @param string $banka_ref_no
     * @param int $site_id
     * @return bool
     */
    public function isRefExists($banka_ref_no, $site_id)
    {
        $sql = $this->db->prepare("SELECT COUNT(*) as sayi 
                                   FROM $this->table 
                                   WHERE banka_ref_no = ? AND site_id = ?");
        $sql->execute([$banka_ref_no, $site_id]);
        $result = $sql->fetch(PDO::FETCH_OBJ);
        return $result && $result->sayi > 0;
    }

    
}