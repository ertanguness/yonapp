<?php 

namespace Model;

use App\Helper\Helper;
use Model\Model;
use PDO;

class FinansalRaporModel extends Model
{
    protected $table = "view_guncel_borclar";

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
                                    SUM(kalan_anapara) AS kalan_anapara,  
                                    SUM(hesaplanan_gecikme_zammi) AS hesaplanan_gecikme_zammi,
                                    SUM(toplam_borc) AS toplam_borc 
                                    FROM $this->table 
                                    WHERE site_id = ? 
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
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE kisi_id = ? ");
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

        $sql = "SELECT SUM(toplam_borc) as toplam FROM {$this->table} WHERE id IN ({$placeholders})";

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




}
