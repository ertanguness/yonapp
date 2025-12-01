<?php

namespace Model;

use PDO;
use Model\Model;
use App\Helper\Helper;

use Model\BloklarModel;
use Model\DairelerModel;
use App\Helper\Security;
use App\Helper\DefinesHelper;
use PhpOffice\PhpSpreadsheet\Calculation\Financial\TreasuryBill;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Helper\Date as Date;

class KisilerModel extends Model
{
    protected $table = 'kisiler';
    protected $siteaktifkisiler = 'site_aktif_kisiler'; //Aktif oturan sakinler(Ev Sahipleri ve Kiracılar)

    protected $view_site_aktif_evsahipleri = 'view_site_aktif_evsahipleri'; // Sadece ev sahiplerini içeren görünüm

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

    /**Sitenin tüm kişilerini getirir 
     * @param int $site_id Sitenin ID'si.
     * @return array Tüm kişileri içeren bir dizi döner.
    */
    public function SiteTumKisileri($site_id)
    {
        $sql = $this->db->prepare("SELECT 
                                                k.*,
                                                d.daire_kodu
                                            FROM $this->table k
                                            LEFT JOIN daireler d ON d.id = k.daire_id  
                                            WHERE k.site_id = ? 
                                          ");
        $sql->execute([$site_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }


    /**Sitenin tüm kişilerini güncel bakiyesi ile beraber getirir
     * @param int $site_id Sitenin ID'si.
     * @return array Güncel bakiyesi ile beraber tüm kişileri içeren bir dizi döner.
     */
    public function SiteTumKisileriGuncelBakiyesi($site_id)
    {
        $sql = $this->db->prepare("SELECT 
                                                k.*,
                                                d.daire_kodu,
                                                vb.bakiye
                                            FROM $this->table k
                                            LEFT JOIN daireler d ON d.id = k.daire_id
                                            LEFT JOIN view_kisiler_hesap_ozet vb ON vb.kisi_id = k.id
                                            WHERE k.site_id = ? 
                                          ");
        $sql->execute([$site_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

      //**************************************************************************************************** */
    /**Siteye ait aktif tüm kişileri getirir.
     * @param int $site_id Sitenin ID'si.
     * @return array Aktif Kişileri içeren bir dizi döner.
     */
    public function SiteAktifKisileriBorclandirma($site_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->siteaktifkisiler  
                                          WHERE site_id = ? AND kisi_id IS NOT NULL 
                                          ");
        $sql->execute([$site_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

       /**Siteye ait aktif tüm kişileri getirir.
     * @param int $site_id Sitenin ID'si.
     * @return array Aktif Kişileri içeren bir dizi döner.
     */
    public function SiteKisileriBorclandirma($site_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table  
                                          WHERE site_id = ? 
                                          ");
        $sql->execute([$site_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }




    /*Kolon adına göre 
    * @param string $column Kolon adı
     * @param mixed $value Aranacak değer
     * @param string $site_column Sitenin ID'si için kolon adı
     * @param int $site_id Sitenin ID'si
     * @return object|null Bulunan kayıt veya null
    */
    public function findByColumn($column, $value)
    {
        $site_id = $_SESSION['site_id'] ?? null;
        $query = "SELECT * FROM $this->table WHERE $column = ?";
        $params = [$value];

        if ($site_id !== null) {
            $query .= " AND site_id = ?";
            $params[] = $site_id;
        }

        $sql = $this->db->prepare($query);
        $sql->execute($params);
        return $sql->fetch(PDO::FETCH_OBJ);
    }


    /**************************************************************************************************** */
    // Bloğun kişilerini getir
    public function BlokKisileri($block_id)
    {
        $sql = $this->db->prepare("SELECT k.*, d.daire_kodu 
                                          FROM $this->table k
                                          LEFT JOIN daireler d ON d.id = k.daire_id
                                          WHERE K.blok_id = ?");
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


    /** isExistsEmail
     * Email kontrolü
     * @param string $email Kontrol edilecek email adresi.
     */
    public function isExistsEmail($email)
    {
        $query = "SELECT COUNT(*) FROM $this->table WHERE eposta = ?";
  
        $sql = $this->db->prepare($query);
        $sql->execute([$email]);
        return $sql->fetchColumn() > 0 ? true : false;
    }
    


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
     * @return object|null Kişi bilgilerini içeren nesne veya bulunamazsa null döner.
     */

    public function KisiBilgileri($id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE id = ?");
        $sql->execute([$id]);
        return $sql->fetch(PDO::FETCH_OBJ); // sadece tek bir kayıt döndürür
    }

    //----------------------------------------------------------------------------------------------------\\


    /** Kişi id'sinden site id'sini getirir
     * @param int $kisi_id Kişi ID'si.
     * @return int|null Site ID'si veya bulunamazsa null döner.
     */
    public function getSiteIdByKisiId($kisi_id)
    {
        $sql = $this->db->prepare("SELECT site_id FROM $this->table WHERE id = ?");
        $sql->execute([$kisi_id]);
        $result = $sql->fetch(PDO::FETCH_OBJ);
        return $result ? (int)$result->site_id : null;
    }

    /**
     * Siteye ait toplam kişi sayısını döndürür.
     * @param int $site_id Sitenin ID'si.
     * @return int Kişi sayısı.
     */
    public function sitedekiKisiSayisi($site_id)
    {
        $sql = $this->db->prepare("SELECT COUNT(*) FROM $this->table WHERE blok_id IN (SELECT id FROM bloklar WHERE site_id = ?)");
        $sql->execute([$site_id]);
        return (int)$sql->fetchColumn();
    }

    /**************************************************************************************************** */
    /**Daire id'si ve uyelik_tipi'nden şu anda aktif olan kiracıyı veya ev sahibini bul
     * @param int $daire_id Daire ID'si.
     * @param string $uyelik_tipi Kullanıcının tipi (ev sahibi veya kiracı).
     */
    public function AktifKisiByDaireId($daire_id, $uyelik_tipi)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table 
                                          WHERE daire_id = ? 
                                          AND uyelik_tipi = ? 
                                          AND silinme_tarihi IS NULL 
                                          ORDER BY id DESC LIMIT 1");
        $sql->execute([$daire_id, $uyelik_tipi]);
        return $sql->fetch(PDO::FETCH_OBJ);
    }
    //----------------------------------------------------------------------------------------------------\\

/** Kişi id'sine göre kişi bilgilerini getirir
 * @param int $id Kişi ID'si.
 * @return object|null Kişi bilgilerini içeren nesne veya bulunamazsa null döner.
 */
    public function getKisiByDaireId($id)
    {
        $sql = $this->db->prepare("SELECT k.*,d.daire_kodu 
                                          FROM $this->table k 
                                          LEFT JOIN daireler d ON d.id = k.daire_id
                                          WHERE k.id = ?");
        $sql->execute([$id]);
        return $sql->fetch(PDO::FETCH_OBJ);
    }

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


    /** Gelen daire id'sine göre dairedeki kişileri döndürür
     * @param int $daire_id Daire ID'si.
     * @return array Dairedeki kişileri içeren bir dizi döner.
     * 
     */
    public function getKisilerByDaireId($daire_id)
    {
        $sql = $this->db->prepare(
                    "SELECT * FROM $this->table 
                            WHERE daire_id = ?
                            ORDER BY id DESC
                            ");
        $sql->execute([$daire_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }



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


    public function SiteKisileriJoin($site_id, $filter = null, $kisi_id = null)
    {
        if (!$site_id) return [];

        switch ($filter) {
            case 'acil':
                $sql = "
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
            ";

                if ($kisi_id) {
                    $sql .= " AND kisiler.id = :kisi_id";
                }
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':site_id', $site_id, PDO::PARAM_INT);

                if ($kisi_id) {
                    $stmt->bindParam(':kisi_id', $kisi_id, PDO::PARAM_INT);
                }
                break;

            case 'arac':
                $sql = 
               "SELECT 
                    kisiler.*, 
                    arac.id AS arac_id,
                    arac.plaka,
                    arac.marka_model
                FROM kisiler
                INNER JOIN bloklar ON kisiler.blok_id = bloklar.id
                INNER JOIN araclar arac ON kisiler.id = arac.kisi_id
                WHERE bloklar.site_id = :site_id
            ";

                if ($kisi_id) {
                    $sql .= " AND kisiler.id = :kisi_id";
                }

                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':site_id', $site_id, PDO::PARAM_INT);

                if ($kisi_id) {
                    $stmt->bindParam(':kisi_id', $kisi_id, PDO::PARAM_INT);
                }
                break;

            default:
                $stmt = $this->db->prepare(
        "SELECT  kisiler.*,
                    GROUP_CONCAT(arac.plaka SEPARATOR '<br>') AS plaka_listesi
                FROM kisiler
                LEFT JOIN bloklar ON kisiler.blok_id = bloklar.id
                LEFT JOIN araclar arac ON kisiler.id = arac.kisi_id
                LEFT JOIN daireler d ON kisiler.daire_id = d.id
                WHERE bloklar.site_id = :site_id
                GROUP BY kisiler.id");
                $stmt->bindParam(':site_id', $site_id, PDO::PARAM_INT);
        }

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
        $query = $this->db->prepare("SELECT * FROM kisiler WHERE daire_id = :daire_id");
        $query->execute(['daire_id' => $daire_id]);
        return $query->fetchAll(PDO::FETCH_OBJ);
    }
    public function SilinenDaireKisileri($daire_id)
    {
        $query = $this->db->prepare("SELECT * FROM silinen_kisiler WHERE daire_id = :daire_id");
        $query->execute(['daire_id' => $daire_id]);
        return $query->fetchAll(PDO::FETCH_OBJ);
    }
    
    
        /*Sitenin aktif ev sahiplerini getirir
     * @param int $site_id
     * @return array
     */
    public function SiteAktifEvSahipleri($site_id)
    {
        $query = $this->db->prepare("SELECT * FROM $this->view_site_aktif_evsahipleri WHERE site_id = ? ");
        $query->execute([$site_id]);
        return $query->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Tahsilat kaydı yapıldıktan sonra anlık olarak satırdaki veriyi güncellemek için kullanılır
     * @param int $kisi_id
     * @return string
     */

    public function TableRow($kisi_id)
    {


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
            <td class="text-end">' . Helper::formattedMoney($result->toplam_tahsilat) . '</td>
            <td class="text-end">' . Helper::formattedMoney($result->bakiye) . '</td>
            <td class="text-end"></td>
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




    // *************************************************************************************** */

    /**
     * Yüklenen bir Excel dosyasındaki daire bilgilerini işler ve veritabanına kaydeder.
     * Bu metot, "Benzersiz Daire Kodu"nu kullanarak daireleri bulur ve günceller/ekler.
     *
     * @param string $tmpFilePath Yüklenen dosyanın geçici yolu ($_FILES['file']['tmp_name']).
     * @param int $siteId İşlem yapılan sitenin ID'si.
     * @return array Başarılı ve hatalı işlemler hakkında bilgi içeren bir sonuç dizisi.
     */
    public function excelUpload(string $tmpFilePath, int $siteId): array
    {
        // Loglama servisini ve diğer modelleri hazırla
        $logger = \getLogger();
        $blokModel = new BloklarModel(); // Blok eklemek için gerekebilir
        $definesModel = new DefinesModel();
        $daireModel = new DairelerModel(); // Daire eklemek için gerekebilir

    
        // Sonuçları ve hataları toplayacağımız diziler
        $processedCount = 0;
        $errorRows = [];
        $skippedCount = 0; // Atlanan kayıt sayısı
    
        try {
            $spreadsheet = IOFactory::load($tmpFilePath);
            $worksheet = $spreadsheet->getActiveSheet();
            // toArray() yerine getRowIterator() kullanmak büyük dosyalarda daha az bellek tüketir.
            $rows = $worksheet->getRowIterator();
    
            // Başlık satırını oku ve sütun indekslerini haritala.
            if (!$rows->valid()) {
                // Eğer dosya tamamen boşsa, burada işlemi bitirebilirsiniz.
                return ['status' => 'error', 'message' => 'Yüklenen Excel dosyası boş veya okunamıyor.'];
            }
    
            $header = [];
            foreach ($rows->current()->getCellIterator() as $cell) {
                $header[$cell->getColumn()] = trim($cell->getValue() ?? ''); // Başlıklardaki boşlukları da temizle
            }
            $rows->next(); // Başlık satırını ATLA ve veri satırlarına geç
    
            // === 2. TÜM İŞLEMLERİ TEK BİR TRANSACTION İÇİNDE YAP ===
            $this->db->beginTransaction();
    
            // `foreach` yerine `while` döngüsü kullanarak iteratörün kontrolünü ele al.
            while ($rows->valid()) {
                $row = $rows->current(); // Mevcut satırı al
    
                $rowData = [];
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(FALSE); // Boş hücreleri de al
                foreach ($cellIterator as $cell) {
                    // Başlık haritasını kullanarak veriyi anahtar-değer şeklinde al
                    $columnHeader = $header[$cell->getColumn()] ?? null;
                    if ($columnHeader) {
                        $rowData[$columnHeader] = $cell->getValue();
                    }
                }
                
                // Satır tamamen boşsa atla
                if (count(array_filter($rowData)) == 0) {
                    $rows->next(); // Bir sonraki satıra geç
                    continue;
                }
                // Blok Adı*	Daire No*	Kimlik No*	IyelikTuru(Ev Sahibi/Kiracı)	Doğum Tarihi (gg.aa.yyyy)	Cinsiyet (Erkek/Kadın)	Uyeliği (Ev Sahibi/Kiracı)	Telefon	Eposta	Adres	Notlar	Satin Alma Tarihi	Giriş Tarihi	Çıkış Tarihi	Aktiflik Durumu

                // Verileri al ve temizle

                $blokAdi            = trim($rowData['Blok Adı*'] ?? '');
                $daireNo            = trim($rowData['Daire No*'] ?? '');
                $kimlikNo           = trim($rowData['Kimlik No*'] ?? '');
                $adiSoyadi          = trim($rowData['Adı Soyadı*'] ?? '');
                $uyelik_tipi        = trim($rowData['Uyeliği (Ev Sahibi/Kiracı)'] ?? '');
                $dogum_tarihi       = trim(Date::convertExcelDate($rowData['Doğum Tarihi (gg.aa.yyyy)']) ?? '');
                $cinsiyet           = trim($rowData['Cinsiyet (E/K)'] ?? '');
                $telefon            = trim($rowData['Telefon'] ?? '');
                $eposta             = $rowData['Eposta'] ?? null;
                $adres              = trim($rowData['Adres'] ?? '');
                $notlar             = trim($rowData['Notlar'] ?? '');
                $satin_alma_tarihi  = trim(Date::convertExcelDate($rowData['Satin Alma Tarihi']) ?? '');
                $giris_tarihi       = trim(Date::convertExcelDate($rowData['Giriş Tarihi']) ?? '');
                $cikis_tarihi       = trim(Date::convertExcelDate($rowData['Çıkış Tarihi']) ?? '');
                $aktif_mi           = trim($rowData['Aktiflik Durumu'] ?? '1'); // Varsayılan olarak aktif
    

                  // Eğer zorunlu alanlar boşsa, hatayı kaydet ve sonraki satıra geç
                  if (empty($blokAdi) || empty($daireNo)) {
                    $errorRows[] = [
                        'row_index' => $row->getRowIndex(),
                        'error_message' =>  "Satır {$row->getRowIndex()}: 'Blok Adı' ve 'Daire No' zorunludur.",
                        'data' => $rowData // SATIRIN TÜM VERİSİNİ EKLE
                    ];

                    $rows->next(); // Bir sonraki satıra geç
                    continue;
                }

               //Blok Adından blok ID'sini bul
                $blokId = $blokModel->findBlokBySiteAndName($siteId, $blokAdi)->id ?? null;

                // Eğer blok ID'si bulunamazsa, hata kaydet ve sonraki satıra geç
                if (!$blokId) {
                    $errorRows[] = [
                        'row_index' => $row->getRowIndex(),
                        'error_message' =>  "Satır {$row->getRowIndex()}: '{$blokAdi}' adında geçerli bir blok bulunamadı.",
                        'data' => $rowData // SATIRIN TÜM VERİSİNİ EKLE
                    ];
                 
                    $rows->next(); // Bir sonraki satıra geç
                    continue;
                }
    
                //Daire adından daire ID'sini bul
                $daire_id = $daireModel->findByApartmentNoandBlockId($daireNo, $blokId, $siteId) ?? null;
                
                // Eğer daire ID'si bulunamazsa, hata kaydet ve sonraki satıra geç
                if (!$daire_id) {
                    $errorRows[] = [
                        'row_index' => $row->getRowIndex(),
                        'error_message' =>  "Satır {$row->getRowIndex()}: '{$daireNo}' adında geçerli bir daire bulunamadı.",
                        'data' => $rowData // SATIRIN TÜM VERİSİNİ EKLE
                    ];
                    $rows->next(); // Bir sonraki satıra geç
                    continue;
                }
        
              
    
                // === 3. ANA MANTIK: TEK SORGULA, KARAR VER, İŞLEM YAP ===
                //Daha sonra tc Kimlik numarasına göre sorgulama yapılacak
                $kisi = null; // $this->findByColumn('kimlik_no', $kimlikNo) ?? null; // Kimlik numarasına göre kişiyi bul
    
                if ($kisi) {
                    // Daire zaten var, atla.
                    $skippedCount++;
                } else {
                    // Kişi yok, YENİ KAYIT OLUŞTURULACAK
                    
                      
                    // Veritabanına eklenecek veriyi hazırla
                    $daireData = [
                        'site_id'           => $siteId,
                        'blok_id'           => $blokId ?? 13,
                        'daire_id'          => $daire_id ?? null, // Daire ID'si boş olabilir, yeni daire ekleniyorsa null
                        'kimlik_no'         => $kimlikNo, // Bu eski daire_kodu sütununuz olabilir
                        'adi_soyadi'        => $adiSoyadi, // ID olarak kaydet, null olabilir
                        'dogum_tarihi'      => $dogum_tarihi,
                        'cinsiyet'          => $cinsiyet,
                        'uyelik_tipi'       => $uyelik_tipi,
                        'telefon'           => $telefon,
                        'eposta'            => $eposta, // Kullanım durumu: 'Dolu' ise 1, 'Boş' ise 0
                        'adres'             => $adres,
                        'satin_alma_tarihi' => $satin_alma_tarihi,
                        'giris_tarihi'      => $giris_tarihi,
                        'cikis_tarihi'      => $cikis_tarihi,
                        'aktif_mi'          => $aktif_mi,
                        'notlar'            => $notlar
                    ];
    
                    $this->saveWithAttr($daireData);
                    $processedCount++;
                }
    
                // Döngünün sonunda bir sonraki satıra MANUEL olarak geç.
                $rows->next();
            }
    
            // === 4. İŞLEMİ SONLANDIR ===
            $this->db->commit();
            $logger->info("Excel daire yükleme tamamlandı.", [
                'İşlenen kayıt sayısı' => $processedCount,
                'atlanan kayıt sayısı' => $skippedCount,
                'hatalı kayıt sayısı' => count($errorRows)
            ]);
            
            $message = "İşlem tamamlandı: {$processedCount} yeni kişi eklendi, {$skippedCount} kayıt zaten mevcut olduğu için atlandı.";
            if (!empty($errorRows)) {
                $message .= " " . count($errorRows) . " satırda hata oluştu.";
            }
    
            return [
                'status' => 'success',
                'message' => $message,
                'data' => [
                    'success_count' => $processedCount,
                    'skipped_count' => $skippedCount,
                    'error_rows' => $errorRows,
                ]
            ];
    
        } catch (\PDOException $e) {
            // Veritabanı hatası olursa, tüm işlemleri geri al
            $this->db->rollBack();
            $logger->error("Excel daire yükleme sırasında veritabanı hatası.", ['error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()];
    
        } catch (\Exception $e) {
            // Dosya okuma veya başka bir genel hata olursa
            // Not: `rollBack()` sadece transaction başlatıldıysa çalışır, aksi halde hata verir.
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $logger->error("Excel daire yükleme sırasında genel hata.", ['error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => 'İşlem sırasında bir hata oluştu: ' . $e->getMessage()];
        }
    }

    /* ***************************************************************************************/
 

    /**Gelen id'leri içeren bir kisiler dizisi döner
     * @param array $ids
     * @return array
     */
    public function getKisilerByIds(array $ids, $encrypt = false): array
    {
        if (empty($ids)) {
            return [];
        }

        //gelen id'lerin şifrelerini çözüp int'e çevir
        if ($encrypt) {
            $ids = array_map(function($id) {
                return (int) Security::decrypt($id);
            }, $ids);
        }
      

        // ID'leri virgülle ayır ve sorguyu hazırla
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT * FROM $this->table WHERE id IN ($placeholders)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($ids);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    

/** Daire kodu ve Adı Soyadından kişinin ID'sini bulur.
 * @param string $daire_kodu
 * @param string $adi_soyadi
 * 
 */
    public function findKisiIdByDaireKoduAndAdiSoyadi(string $daire_kodu, string $adi_soyadi)
    {
        $sql = $this->db->prepare("SELECT k.id 
                                        FROM kisiler k
                                        LEFT JOIN daireler d ON k.daire_id = d.id
                                        WHERE d.daire_kodu = ? AND k.adi_soyadi = ? 
                                        LIMIT 1");
        $sql->execute([$daire_kodu, $adi_soyadi]);
        $result = $sql->fetch(PDO::FETCH_OBJ);
        return $result ? $result->id : null;
    }

        /**
     * Belirtilen bir borçlandırma dönemiyle konaklama dönemi kesişen
     * tüm aktif kişileri (kiracı ve ev sahibi), daire ve blok bilgileriyle
     * birlikte tek bir sorguda getirir.
     *
     * @param int $site_id
     * @param string $borcBaslangicTarihi 'Y-m-d' formatında
     * @param string $borcBitisTarihi     'Y-m-d' formatında
     * @return array                      Bulunan kişilerin nesnelerinden oluşan bir dizi.
     */
    public function BorclandirilacakAktifKisileriGetir(int $site_id, string $borcBaslangicTarihi, string $borcBitisTarihi, $daireTipi = "Konut")
    {
       
        $sql = 
           "SELECT 
                k.id, k.adi_soyadi, k.uyelik_tipi, k.giris_tarihi, k.cikis_tarihi,
                d.id as daire_id, d.daire_kodu as daire_kodu,
                b.id as blok_id, b.blok_adi
            FROM kisiler k
            INNER JOIN daireler d ON k.daire_id = d.id
            INNER JOIN bloklar b ON d.blok_id = b.id
            INNER JOIN defines df ON d.daire_tipi = df.id
            WHERE 
                b.site_id = :site_id
                AND k.giris_tarihi <= :borc_bitis
                AND (
                    k.cikis_tarihi IS NULL
                    OR k.cikis_tarihi = '0000-00-00'
                    OR k.cikis_tarihi >= :borc_baslangic
                )
             and df.mulk_tipi = :daire_tipi   
            ORDER BY
                d.id, k.uyelik_tipi DESC";
    
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':site_id', $site_id, PDO::PARAM_INT);
        $stmt->bindParam(':borc_baslangic', $borcBaslangicTarihi, PDO::PARAM_STR);
        $stmt->bindParam(':borc_bitis', $borcBitisTarihi, PDO::PARAM_STR);
        $stmt->bindParam(':daire_tipi', $daireTipi, PDO::PARAM_STR);
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }


    /** Email Göndermek için kişileri getirir
     * @param int $site_id
     *  @return array
     */
    public function getKisilerForEmail(int $site_id)
    {
        $sql = $this->db->prepare("SELECT 
                                            id as value, adi_soyadi, eposta ,'' as avatar
                                          FROM $this->table 
                                            WHERE site_id = ? 
                                            AND eposta IS NOT NULL 
                                            AND eposta != '' ");
        $sql->execute([$site_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Belirli bir bloktaki aktif kişileri daire kodu ile birlikte getirir
     * @param int $blok_id Blok ID'si
     * @return array Kişi listesi (daire_kodu dahil)
     */
    public function getAktifKisilerByBlok(int $blok_id)
    {
        $sql = "SELECT k.*, d.daire_kodu 
                FROM kisiler k 
                LEFT JOIN daireler d ON d.id = k.daire_id 
                WHERE k.blok_id = :blok_id 
                  AND k.silinme_tarihi IS NULL
                ORDER BY d.daire_no, k.adi_soyadi";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':blok_id' => $blok_id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Tüm sitedeki aktif kişileri blok adı ve daire kodu ile birlikte getirir
     * @param int $site_id Site ID'si
     * @return array Kişi listesi (blok_adi ve daire_kodu dahil)
     */
    public function getAktifKisilerBySite(int $site_id)
    {
        $sql = "SELECT k.*, d.daire_kodu, b.blok_adi
                FROM kisiler k 
                LEFT JOIN daireler d ON d.id = k.daire_id 
                LEFT JOIN bloklar b ON b.id = k.blok_id
                WHERE k.site_id = :site_id 
                ORDER BY b.blok_adi, d.daire_no, k.adi_soyadi";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':site_id' => $site_id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

}
