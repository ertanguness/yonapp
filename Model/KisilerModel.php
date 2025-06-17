<?php

namespace Model;

use App\Helper\Security;
use App\Helper\Helper;

use Model\BloklarModel;
use Model\Model;
use PDO;

class KisilerModel extends Model
{
    protected $table = 'kisiler';
    protected $siteaktifkisiler = 'site_aktif_kisiler'; 

    protected $kisilerborcozet = 'view_kisi_borc_ozet'; 

    public function __construct()
    {
        parent::__construct($this->table);
    }


    //**************************************************************************************************** */
    /**Siteye ait aktif tüm kişileri getirir.
     * @param int $site_id Sitenin ID'si.
     * @return array Aktif Kişileri içeren bir dizi döner.
     */
    public function SiteAktifKisileri($site_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->siteaktifkisiler  
                                          WHERE site_id = ? 
                                          ");
        $sql->execute([$site_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }


    /**************************************************************************************************** */
    // Bloğun kişilerini getir
    public function BlokKisileri($block_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE blok_id = ?");
        $sql->execute([$block_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }
    //----------------------------------------------------------------------------------------------------\\



    /**************************************************************************************************** */
    /**Blokta oturan aktif kişileri getirir
     * @param int $block_id Blok ID'si
     * @return array Aktif kişileri içeren bir dizi döner.
     * @throws \Exception
     */

    public function BlokAktifKisileri($block_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table 
                                          WHERE blok_id = ? 
                                          AND cikis_tarihi IS NULL
                                          AND silinme_tarihi IS NULL");
        $sql->execute([$block_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }
    //----------------------------------------------------------------------------------------------------\\



    /**************************************************************************************************** */
    /**
     * Siteye ait blokları ve bu bloklara ait kişileri getirir.
     *
     * @param int $site_id Parametre olarak gelen site ID'si.
     * @return array Kişileri içeren bir dizi döner.
     */
    public function SiteKisileri($site_id)
    {
        $Bloklar = new BloklarModel();
        $bloklar = $Bloklar->SiteBloklari($site_id);
        $kisiler = [];

        foreach ($bloklar as $blok) {
            $blok_kisileri = $this->BlokKisileri($blok->id);
            if (!empty($blok_kisileri)) {
                $kisiler = array_merge($kisiler, $blok_kisileri);
            }
        }

        return $kisiler;
    }
    //----------------------------------------------------------------------------------------------------\\



    /**************************************************************************************************** */
    /**
     * Belirli bir kişinin bilgilerini getirir.
     * @param int $id Kişinin ID'si.
     * @return object|null Kişi bilgilerini içeren nesne veya bulunamazsa null döner.
     */
    public function getPersonById($id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE id = ?");
        $sql->execute([$id]);
        return $sql->fetch(PDO::FETCH_OBJ);
    }

    //----------------------------------------------------------------------------------------------------\\



    /**************************************************************************************************** */
    /***Kişi ID'sinden Kişi Adını Getirir
     * @param int $id Kişinin ID'si.
     * @return string|null Kişinin adı veya bulunamazsa null döner.
     */
    public function KisiAdi($id)
    {
        $sql = $this->db->prepare("SELECT adi_soyadi FROM $this->table WHERE id = ?");
        $sql->execute([$id]);
        $result = $sql->fetch(PDO::FETCH_OBJ);
        return $result ? $result->adi_soyadi : null;
    }
    //----------------------------------------------------------------------------------------------------\\




    /**************************************************************************************************** */
    /**Daire id'si ve uyelik_tipi'nden şu anda aktif olan kiracıyı veya ev sahibini bul
     * @param int $daire_id Daire ID'si.
     * @param string $uyelik_tipi Kullanıcının tipi (ev sahibi veya kiracı).
     */
    public function AktifKisiByDaireId($daire_id, $uyelik_tipi)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE daire_id = ? AND uyelik_tipi = ? AND silinme_tarihi IS NULL ORDER BY id DESC LIMIT 1");
        $sql->execute([$daire_id, $uyelik_tipi]);
        return $sql->fetch(PDO::FETCH_OBJ);
    }
    //----------------------------------------------------------------------------------------------------\\




    /**************************************************************************************************** */
    /**Daire id'sinden, şu anda dairede oturan aktif kişiyi getirir
     * @param int $daire_id Daire ID'si.
     * @return object|null Dairede oturan kişinin bilgilerini içeren nesne veya bulunamazsa null döner.
     */
    public function AktifKisiByDaire($daire_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table 
                                            WHERE daire_id = ? 
                                            AND (giris_tarihi IS NOT NULL AND giris_tarihi != '0000-00-00') 
                                            AND cikis_tarihi IS NULL 
                                            AND silinme_tarihi IS NULL 
                                            ORDER BY daire_id");
        $sql->execute([$daire_id]);
        return $sql->fetch(PDO::FETCH_OBJ);
    }
    //----------------------------------------------------------------------------------------------------\\


    /**************************************************************************************************** */
    /**Siteye ait kişilerin toplam borç ve tahsilatlarını getirir
     * @param int $site_id Sitenin ID'si.
     * @return array Siteye ait kişilerin toplam borç ve tahsilatlarını içeren bir dizi döner.
     */
    public function SiteKisiBorcOzet($site_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->kisilerborcozet WHERE site_id = ?");
        $sql->execute([$site_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }


    public function SiteKisileriJoin($site_id, $filter = null)
    {
        if (!$site_id) return [];

        switch ($filter) {
            case 'acil':
                $stmt = $this->db->prepare("
                SELECT 
                    kisiler.*, 
                    acil.id AS acil_id,
                    acil.adi_soyadi AS acil_adi_soyadi,
                    acil.telefon AS acil_telefon,
                    acil.yakinlik AS acil_yakinlik
                FROM kisiler
                INNER JOIN bloklar ON kisiler.blok_id = bloklar.id
                INNER JOIN acil_durum_kisileri acil ON kisiler.id = acil.kisi_id
                WHERE bloklar.site_id = :site_id
            ");
                break;

            case 'arac':
                $stmt = $this->db->prepare("
                SELECT 
                    kisiler.*, 
                    arac.id AS arac_id,
                    arac.plaka,
                    arac.marka_model
                FROM kisiler
                INNER JOIN bloklar ON kisiler.blok_id = bloklar.id
                INNER JOIN araclar arac ON kisiler.id = arac.kisi_id
                WHERE bloklar.site_id = :site_id
            ");
                break;

            default:
                $stmt = $this->db->prepare("
            SELECT 
                kisiler.*,
                GROUP_CONCAT(arac.plaka SEPARATOR '<br>') AS plaka_listesi
            FROM kisiler
            INNER JOIN bloklar ON kisiler.blok_id = bloklar.id
            LEFT JOIN araclar arac ON kisiler.id = arac.kisi_id
            WHERE bloklar.site_id = :site_id
            GROUP BY kisiler.id
        ");
        }

        $stmt->bindParam(':site_id', $site_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }




    public function KisiVarmi($kimlikNo)
    {
        $query = $this->db->prepare("SELECT COUNT(*) FROM $this->table WHERE kimlik_no = ?");
        $query->execute([$kimlikNo]);
        return $query->fetchColumn() > 0;
    }
    // Bloğun kişilerini getir
    public function DaireKisileri($daire_id)
    {
        $query = $this->db->prepare("SELECT id, adi_soyadi FROM kisiler WHERE daire_id = :daire_id");
        $query->execute(['daire_id' => $daire_id]);
        return $query->fetchAll(PDO::FETCH_OBJ);
    }



    /**
     * Tahsilat kaydı yapıldıktan sonra anlık olarak satırdaki veriyi güncellemek için kullanılır
     * @param int $kisi_id
     * @return string
     */

    public function TableRow($kisi_id)
    {


    //     <th class="wd-30 no-sorting" style="width: 40px;">
    //     Sıra
    //   </th>
    //   <th>Daire Adı</th>
    //   <th>Ad Soyad</th>
    //   <th class="text-end" style="width:11%">Borç Tutarı</th>
    //   <th class="text-end" style="width:11%">Ödenen</th>
    //   <th class="text-end" style="width:11%">BAKİYE</th>
    //   <th>İşlem</th>

        $sql = $this->db->prepare("SELECT * FROM $this->kisilerborcozet WHERE kisi_id = ?");
        $sql->execute([$kisi_id]);
        $result = $sql->fetch(PDO::FETCH_OBJ);

        return '
        
        <td>' . 1 . '</td>
        <td>' . $result->daire_kodu . '</td>
        <td>' . $result->adi_soyadi . '</td>
            <td class="text-end">
                <i class="feather-trending-down fw-bold text-danger"></i>
                ' . Helper::formattedMoney($result->toplam_borc) . '
            </td>
            <td class="text-end">' . Helper::formattedMoney($result->toplam_tahsilat) . '</td>
            <td class="text-end">' . Helper::formattedMoney($result->bakiye) . '</td>
            <td>
                <div class="hstack gap-2">
                    <a href="javascript:void(0);" 
                    data-id= "' . Security::encrypt($kisi_id) . '"
                    class="avatar-text avatar-md kisi-borc-detay" title="Görüntüle">
                        <i class="feather-eye"></i>
                    </a>
                    <a href="javascript:void(0);" 
                    data-id="' . Security::encrypt($kisi_id) . '"
                    data-kisi-id="' . Security::encrypt($result->kisi_id) . '"
                    class="avatar-text avatar-md tahsilat-gir" title="Düzenle">
                        <i class="bi bi-credit-card-2-front"></i>
                    </a>
                </div>
            </td>
        ';
    }
}
