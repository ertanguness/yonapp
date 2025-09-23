<?php


namespace Model;

use Model\Model;
use PDO;

class UserPaymentModel extends Model
{
    protected $table = "view_kisi_borc_tahsilat_detay"; // Borçlandirma Detayi tablosu



    public function __construct()
    {
        parent::__construct($this->table);
    }



    /**
     * Kullanıcının Gruplanmış Borç Başlıklarını ve Ödeme Durumlarını Getirir
     * @param mixed $user_id
     * @return array
     */
    public function GruplanmisBorcBasliklari($user_id)
    {
        $sql = $this->db->prepare("SELECT 
            borc_adi,
            COUNT(*) AS kayit_sayisi,
            SUM(tutar) AS toplam_tutar,
            para_birimi,
            SUM(CASE WHEN odeme_durumu = 'Ödenmedi' THEN tutar ELSE 0 END) AS odenmeyen_toplam
        FROM 
            $this->table
        WHERE 
            kisi_id = ? AND silinme_tarihi IS NULL
        GROUP BY 
            borc_adi, para_birimi
        ORDER BY 
            borc_adi");

        $sql->execute([$user_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Kullanıcının Toplam Borcu, Ödenen ve Kalan Borç Tutarlarını Getirir
     * @param mixed $user_id
     * @return object|null
     */

    public function KullaniciToplamBorc($kisi_id)
    {
        $sql = $this->db->prepare("SELECT 
                                            COALESCE(b.toplam_borc, 0) AS toplam_borc,
                                            COALESCE(t.toplam_tahsilat, 0) AS toplam_tahsilat,
                                            COALESCE(t.toplam_tahsilat, 0) - COALESCE(b.toplam_borc, 0) AS bakiye
                                        FROM 
                                            (SELECT SUM(tutar) AS toplam_borc FROM borclandirma_detayi WHERE kisi_id = :kisi_id) AS b
                                        CROSS JOIN
                                            (SELECT SUM(tutar) AS toplam_tahsilat FROM tahsilatlar WHERE kisi_id = :kisi_id) AS t;");

        $sql->execute(['kisi_id' => $kisi_id]);
        return $sql->fetch(PDO::FETCH_OBJ);
    }



//  /**
//      * Kullanıcının Kategori Bazlı Özet durumunu getirir
//      * @param mixed $user_id
//      * @param string|null $baslangic_tarihi
//      * @param string|null $bitis_tarihi
//      * @return array
//      */

//     public function KategoriBazliOzet($user_id, $baslangic_tarihi = null, $bitis_tarihi = null)
//     {
//         $query = "SELECT * FROM $this->table WHERE kisi_id = ?";

//         $params = [$user_id];

//         if ($baslangic_tarihi && $bitis_tarihi) {
//             $query .= " AND islem_tarihi BETWEEN ? AND ?";
//             $params[] = $baslangic_tarihi;
//             $params[] = $bitis_tarihi;
//         }

//         $query .= " GROUP BY kategori_adi ORDER BY kategori_adi";

//         $sql = $this->db->prepare($query);
//         $sql->execute($params);

//         return $sql->fetchAll(PDO::FETCH_OBJ);
//     }




    /**
     * Kullanıcının Kategori Bazlı Özet durumunu getirir
     * @param mixed $user_id
     * @param string|null $baslangic_tarihi
     * @param string|null $bitis_tarihi
     * @return array
     */

    public function kisiBorcTahsilatDetay($user_id)
    {
        $query = "SELECT * FROM $this->table WHERE kisi_id = ? 
        ORDER BY borc_adi,islem_tarihi";
                            

        $sql = $this->db->prepare($query);
        $sql->execute([$user_id]);

        return $sql->fetchAll(PDO::FETCH_OBJ);
    }





    /**
     * Kullanıcının Borç Detaylarını Getirir
     * @param mixed $user_id
     * @param string $borc_adi
     * @return array
     */
    public function KullaniciBorcDetaylari($user_id, $borc_adi)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table 
                                            WHERE kisi_id = ? AND islem_adi = ? 
                                            
                                ");

        $sql->execute([$user_id, $borc_adi]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }
}
