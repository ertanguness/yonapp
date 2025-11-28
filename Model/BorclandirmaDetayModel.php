<?php


namespace Model;

use Model\Model;
use Model\BloklarModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Helper\Date;
use App\Helper\Security;
use Model\KisilerModel;

use PDO;

class BorclandirmaDetayModel extends Model
{
    protected $table = "borclandirma_detayi";

    protected $view_table = "view_borclandirma_detay_raporu"; // Görünüm tablosu, eğer varsa

    public function __construct()
    {
        parent::__construct($this->table);
    }



    /**
     * Borçlandırma tipi blok olan kayıtların gruplanmış blok id'lerini döndürür
     * @param int $borclandirma_id
     * @return array
     * @throws \Exception
     */
    public function BorclandirilmisBloklar($borclandirma_id)
    {
        $query = "SELECT DISTINCT blok_id FROM {$this->table} WHERE borclandirma_id = :borclandirma_id AND hedef_tipi = 'block'";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':borclandirma_id', $borclandirma_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }


    //******************************************************************** */

    /**Borclandırma Detayını getirir
     * @param int $borclandirma_id
     * @return array
     */
    public function BorclandirmaDetay($borclandirma_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->view_table WHERE borclandirma_id = ? ");
        $sql->execute([$borclandirma_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    /**Borçlandirma detayını id bazında getirir
     * @param int $id
     * @return object|null
     */
    public function BorclandirmaDetayByID($id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->view_table WHERE id = ? ");
        $sql->execute([$id]);
        return $sql->fetch(PDO::FETCH_OBJ);
    }



    /**
     * Borçlandırılmış Blokların isimlerini getirir
     * @param int $borclandirma_id
     *  @return string
     */
    public function BorclandirilmisBlokIsimleri($borclandirma_id)
    {
        $Bloklar = new BloklarModel();
        $boclandirilmis_bloklar = $this->BorclandirilmisBloklar($borclandirma_id);
        $blokIsimleri = [];
        foreach ($boclandirilmis_bloklar as $blok) {
            $blokIsimleri[] = $Bloklar->BlokAdi($blok->blok_id);
        }
        return implode(', ', $blokIsimleri); // Blok isimlerini virgülle ayırarak döndürür

    }



    //******************************************************************************
    /**
     * Borçlandırılmış Daire Tiperini isimlerini getirir
     * @return string
     */
    public function BorclandirilmisDaireTipleri($borclandirma_id)
    {
        $query = "SELECT 
                    bd.daire_id,
                    df.id,
                    df.define_name
                  FROM borclandirma_detayi bd 
                  LEFT JOIN daireler d ON d.id = bd.daire_id
                  LEFT JOIN defines df ON df.id = d.daire_tipi
                  WHERE hedef_tipi = ?
                  AND borclandirma_id = ?
                  GROUP BY define_name";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'dairetipi', // hedef_tipi olarak 'apartment' kullanılıyor
            $borclandirma_id
        ]);
        $daireler = $stmt->fetchAll(PDO::FETCH_OBJ);
        $daire_tipleri = [];

        foreach ($daireler as $daire) {
            $daire_tipleri[] = $daire->define_name;
        };
        return implode(", ", $daire_tipleri);
    }
    //******************************************************************************

    /**
     * Toplam Borcandirma Tutarını getirir
     * @param int $borclandirma_id
     * @return float
     */
    public function ToplamBorclandirmaTutar($borclandirma_id)
    {
        $sql = $this->db->prepare("SELECT SUM(tutar) AS toplam_borc FROM $this->table WHERE borclandirma_id = ? AND silinme_tarihi IS NULL");
        $sql->execute([$borclandirma_id]);
        $result = $sql->fetch(PDO::FETCH_OBJ);
        return $result ? (float)$result->toplam_borc : 0.0; // Eğer sonuç varsa toplam borcu döndür, yoksa 0 döndür
    }


    // Borçlandırma detaylarını borç ID'sine göre getirir
    public function KisiBorclandirmalari($kisi_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE kisi_id = ? AND silinme_tarihi IS NULL");
        $sql->execute([$kisi_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }



    /**
     * Kişilerin borç listenini gruplanmış olarak getirir
     * 
     * @return array
     */
    public function gruplanmisBorcListesi($borclandirma_id = 0)
    {
        $sql = $this->db->prepare("SELECT
                        bd.id as borc_id,
                        k.adi_soyadi AS kisi_adi,
                        bd.borc_adi,
                        b.blok_adi AS blok_adi,
                        bd.baslangic_tarihi,
                        bd.bitis_tarihi,
                        bd.ceza_orani,
                        bd.aciklama,
                        SUM(tutar) AS toplam_borc,
                        COUNT(*) AS borc_sayisi,
                        GROUP_CONCAT(DISTINCT borc_adi SEPARATOR ', ') AS borc_turleri
                    FROM
                        $this->table bd
                        LEFT JOIN kisiler k ON k.id = bd.kisi_id 
                        LEFT JOIN bloklar b ON b.id = k.blok_id
                    WHERE
                        bd.borclandirma_id = :borclandirma_id AND
                        bd.silinme_tarihi IS NULL  -- Silinmemiş kayıtlar
                    GROUP BY
                        k.id, b.id
                    ORDER BY toplam_borc DESC;");
        $sql->execute(
            [
                ':borclandirma_id' => $borclandirma_id // Burada borclandirma_id'yi 0 olarak ayarlıyoruz, çünkü tüm borçları listelemek istiyoruz
            ]
        );
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Belirli bir kişinin toplam borçlarını getirir
     * @param int $kisi_id
     * @return float
     */
    public function KisiToplamBorc($kisi_id)
    {
        $sql = $this->db->prepare("SELECT SUM(tutar) AS toplam_borc FROM $this->table WHERE kisi_id = ? AND silinme_tarihi IS NULL");
        $sql->execute([$kisi_id]);
        $result = $sql->fetch(PDO::FETCH_OBJ);
        return $result ? (float)$result->toplam_borc : 0.0; // Eğer sonuç varsa toplam borcu döndür, yoksa 0 döndür
    }


    /**
     * Kişinin finansal durumunu özetler (toplam borç, toplam ödeme, bakiye)
     * @param int $kisi_id
     * @return object
     */
    public function KisiFinansalDurum($kisi_id)
    {
        // $this->table'ı doğrudan sorguya eklemek için
        $borcTablosu = $this->table;

        $sql = $this->db->prepare("
                            SELECT
                                borc.toplam_borc,
                                odeme.toplam_odeme,
                                odeme.toplam_odeme - borc.toplam_borc AS bakiye
                            FROM
                                (SELECT COALESCE(SUM(tutar), 0) AS toplam_borc FROM {$borcTablosu} WHERE kisi_id = :kisi_id_borc) AS borc,
                                (SELECT COALESCE(SUM(tutar), 0) AS toplam_odeme 
                                    FROM tahsilatlar 
                                    WHERE kisi_id = :kisi_id_odeme
                                    AND silinme_tarihi IS NULL
                                ) AS odeme;
                            ");

        $sql->execute([
            ':kisi_id_borc' => $kisi_id,
            ':kisi_id_odeme' => $kisi_id
        ]);
        return $sql->fetch(PDO::FETCH_OBJ);
    }

    // Model/BorcDetayModel.php

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

        $sql = "SELECT SUM(tutar) as toplam FROM {$this->table} WHERE id IN ({$placeholders})";

        try {
            $stmt = $this->db->prepare($sql);
            // execute() metoduna ID dizisini doğrudan ver
            $stmt->execute($idler);
            // fetchColumn() tek bir sütunun değerini döndürür
            $toplam = $stmt->fetchColumn();
            return (float)$toplam; // Sonucu float'a çevirerek döndür
        } catch (\PDOException $e) {
            // Hata loglama
            error_log("Toplam tutar alınırken hata: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Belirtilen bir kişiye ait, henüz tamamen ödenmemiş borçları getirir.
     * Sadece anaparası 0'dan büyük olan borçları dikkate alır.
     * Borçları, ödeme önceliğine göre (en eski son ödeme tarihli olan önce) sıralar.
     *
     * @param int $kisiId Borçları getirilecek kişinin ID'si.
     * @return array Kişinin ödenmemiş borçlarının bir dizisi (nesne olarak).
     */
    public function getOdenmemisBorclarByKisi(int $kisiId): array
    {
        // SQL sorgumuzu hazırlıyoruz.
        // 1. Sadece belirli bir kisi_id'ye ait olanları seçiyoruz.
        // 2. Sadece anaparası (kalan_tutar) 0'dan büyük olanları seçiyoruz. 
        //    (Kuruş farkları için 0.009 gibi küçük bir değerden büyük olması daha güvenli olabilir)
        // 3. silinme_tarihi IS NULL koşulu ile soft-delete edilmiş borçları dışarıda bırakıyoruz.
        // 4. ORDER BY ile borçları sıralıyoruz:
        //    - Önce son_odeme_tarihi en eski olanlar (en acil borçlar).
        //    - Eğer son ödeme tarihleri aynıysa, ID'si küçük olan (yani daha önce oluşturulmuş olan) önce gelir.
        //      Bu, tutarlılık için önemlidir.

        $sql = "SELECT 
                    id,
                    borc_adi,
                    kisi_id, -- Kişi ID'si, hangi kişiye ait olduğunu gösterir.
                    kalan_borc,
                    kalan_gecikme_zammi, -- Bu kolon da mevcutsa almak faydalı olabilir.
                    son_odeme_tarihi,
                    ceza_orani
                FROM 
                    borclandirma_detayi
                WHERE 
                    kisi_id = :kisi_id 
                    AND kalan_borc > 0.00
                    AND silinme_tarihi IS NULL
                ORDER BY 
                    son_odeme_tarihi ASC, id ASC";

        try {
            // Sorguyu hazırla
            $stmt = $this->db->prepare($sql);

            // Parametreleri bağla
            $stmt->bindParam(':kisi_id', $kisiId, PDO::PARAM_INT);

            // Sorguyu çalıştır
            $stmt->execute();

            // Tüm sonuçları nesne dizisi olarak döndür
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            // Hata durumunda, hatayı loglayabilir ve boş bir dizi döndürebiliriz.
            // Bu, uygulamanın çökmesini engeller.
            getLogger()->error("Ödenmemiş borçlar getirilirken veritabanı hatası: " . $e->getMessage());

            // veya hatayı yukarıya fırlatabiliriz, bu daha iyi bir pratik olabilir
            throw new \Exception("Ödenmemiş borçlar getirilirken bir veritabanı hatası oluştu.");

            // return []; // Alternatif olarak boş dizi döndür
        }
    }

    /** Kolonddaki değeri gelen değerle toplayarak artrırır
     * @param int $id
     * @param string $column
     * @param float $amount
     * @return bool
     */
    public function increaseColumnValue(int $id, string $column, float $amount): bool
    {
        $query = "UPDATE {$this->table} SET {$column} = {$column} + :amount WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }



    /*Borc İd'sine göre borçlandirilan kisi id'si gelir
        * @param int $borc_id
        * @return int|null
        */
    public function getKisiIdsByBorcId(int $borclandirma_id): ?int
    {
        $sql = $this->db->prepare("SELECT kisi_id FROM {$this->table} WHERE borclandirma_id = ?");
        $sql->execute([$borclandirma_id]);
        return ($result = $sql->fetch(PDO::FETCH_OBJ)) ? (int)$result->kisi_id : null;
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
    public function excelUpload(string $tmpFilePath, int $siteId, $postData): array
    {

        // Logger'ı başlat
        $logger = getLogger();
        // $logger->info("Excel daire yükleme işlemi başlatıldı.", [
        //     'site_id' => $siteId,
        //     'file_path' => $tmpFilePath
        // ]);


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

                // Verileri al ve temizle
                $kisiID             = trim($rowData['Kişi ID*'] ?? '');
                $ceza_orani         = trim($rowData['Ceza Oranı %'] ?? '0'); // Ceza oranı, boşsa 0 olarak ayarla
                $aciklama           = trim($rowData['Açıklama'] ?? $postData['aciklama']);

                $kisi = new KisilerModel();
                //Kişiyi bul
                $kisi = $kisi->find($kisiID);
                // Eğer zorunlu alanlar boşsa, hatayı kaydet ve sonraki satıra geç
                if (empty($kisi)) {
                    $errorRows[] = [
                        'row_index' => $row->getRowIndex(),
                        'error_message' =>  "Satır {$row->getRowIndex()}: 'Kişi ID' zorunludur.",
                        'data' => $rowData // SATIRIN TÜM VERİSİNİ EKLE
                    ];

                    $rows->next(); // Bir sonraki satıra geç
                    continue;
                }

                $daireId = $kisi->daire_id; // Kişinin daire ID'sini al
                $blokId = $kisi->blok_id; // Kişinin blok ID'sini al



                // === 3. ANA MANTIK: TEK SORGULA, KARAR VER, İŞLEM YAP ===
                // Veritabanına eklenecek veriyi hazırla
                $borcData = [
                    'borclandirma_id'   => $postData['borc_id'], // Borçlandırma ID'si
                    'blok_id'           => $blokId,
                    'daire_id'          => $daireId, // Daire ID'si boş olabilir, yeni daire ekleniyorsa null
                    'borc_adi'          => $postData['borc_adi'], // Borç adı
                    'kisi_id'           => $kisiID,
                    'tutar'             => (float)(abs($rowData['Tutar']) ?? 0.0), // Tutarı float olarak al
                    'hedef_tipi'        => $postData['hedef_tipi'], // Hedef tipi (blok, daire, kişi)
                    'baslangic_tarihi'  => $postData['baslangic_tarihi'],
                    'bitis_tarihi'      => $postData['bitis_tarihi'],
                    'son_odeme_tarihi'  => $postData['bitis_tarihi'] ?? null, // Son ödeme tarihi
                    'ceza_orani'        => (float)$ceza_orani, // Ceza oranını float olarak al
                    'aciklama'          => $aciklama, // Açıklama
                ];

                $this->saveWithAttr($borcData);
                $processedCount++;

                // Döngünün sonunda bir sonraki satıra MANUEL olarak geç.
                $rows->next();
            }

            // === 4. İŞLEMİ SONLANDIR ===
            $this->db->commit();
            $logger->info("Excel borç yükleme tamamlandı.", [
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

    /**
     * Borçlandırma Detaylarını sil
     * @param $borclandirma_id
     * @return void
     */
    public function BorclandirmaDetaylariniSil($borclandirma_id): void
    {
        // Gelen ID’yi decrypt et; bozuk / geçersiz ise hata fırlat
        $borclandirma_id = Security::decrypt($borclandirma_id);
        if ($borclandirma_id === false) {
            throw new \InvalidArgumentException('Geçersiz borçlandırma ID’si.');
        }

        // Soft-delete: silinme_tarihi ve silen kullanıcıyı güncelle
        $sql = $this->db->prepare("
            UPDATE {$this->table}
            SET silinme_tarihi = NOW(),
                silen_kullanici = :user_id
            WHERE borclandirma_id = :borc_id
        ");
        $sql->execute([
            ':user_id' => $_SESSION['user']->id,
            ':borc_id' => $borclandirma_id
        ]);
    }

    /* ***************************************************************************************/


    /* Borc detaylarini getirir */
    public function borc_detaylari_param($borc_idleri)
    {
        $sql = $this->db->prepare("CALL borc_detaylari_param(?)");
        $sql->execute([$borc_idleri]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Birden fazla borçlandırma için detayları tek sorguda getirir (N+1 query çözümü)
     * @param array $borclandirma_ids Borçlandırma ID'leri dizisi
     * @return array Borçlandırma ID'sine göre gruplanmış detaylar
     */
    public function getBatchDetails(array $borclandirma_ids): array
    {
        if (empty($borclandirma_ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($borclandirma_ids), '?'));
        
        // Blok ve daire tipi detaylarını tek sorguda çek
        $sql = "SELECT 
                    bd.borclandirma_id,
                    bd.hedef_tipi,
                    CASE 
                        WHEN bd.hedef_tipi = 'block' THEN GROUP_CONCAT(DISTINCT b.blok_adi SEPARATOR ', ')
                        WHEN bd.hedef_tipi = 'dairetipi' THEN GROUP_CONCAT(DISTINCT df.define_name SEPARATOR ', ')
                        ELSE NULL
                    END as detay
                FROM {$this->table} bd
                LEFT JOIN bloklar b ON b.id = bd.blok_id AND bd.hedef_tipi = 'block'
                LEFT JOIN daireler d ON d.id = bd.daire_id AND bd.hedef_tipi = 'dairetipi'
                LEFT JOIN defines df ON df.id = d.daire_tipi AND bd.hedef_tipi = 'dairetipi'
                WHERE bd.borclandirma_id IN ({$placeholders})
                  AND bd.silinme_tarihi IS NULL
                GROUP BY bd.borclandirma_id, bd.hedef_tipi";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($borclandirma_ids);
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        // Borçlandırma ID'sine göre grupla
        $grouped = [];
        foreach ($results as $row) {
            $grouped[$row->borclandirma_id] = $row->detay ?? '';
        }
        
        return $grouped;
    }

    /**
     * Toplu borç detayı ekleme (batch insert)
     * @param array $records Eklenecek kayıtlar dizisi
     * @return int Eklenen kayıt sayısı
     */
    public function batchInsert(array $records): int
    {
        if (empty($records)) {
            return 0;
        }

        try {
            // Sütun isimlerini ilk kayıttan al
            $columns = array_keys($records[0]);
            $columnList = implode(', ', $columns);
            
            // Her kayıt için placeholder oluştur
            $placeholders = [];
            $values = [];
            
            foreach ($records as $record) {
                $rowPlaceholders = [];
                foreach ($columns as $column) {
                    $rowPlaceholders[] = '?';
                    $values[] = $record[$column] ?? null;
                }
                $placeholders[] = '(' . implode(', ', $rowPlaceholders) . ')';
            }
            
            $sql = "INSERT INTO {$this->table} ({$columnList}) VALUES " . implode(', ', $placeholders);
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
            
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            getLogger()->error("Batch insert hatası: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Kişinin belirli bir ay aralığındaki borçlandırmalarını getirir
     * @param int $kisi_id Kişi ID
     * @param string $baslangic_tarihi Ayın başlangıç tarihi
     * @param string $bitis_tarihi Ayın bitiş tarihi
     * @return array Borçlandırma kayıtları
     */
    public function getAylikBorclandirma(int $kisi_id, string $baslangic_tarihi, string $bitis_tarihi): array
    {
        $sql = "SELECT id, tutar
                FROM {$this->table}
                WHERE kisi_id = :kisi_id
                  AND baslangic_tarihi >= :baslangic_tarihi
                  AND bitis_tarihi <= :bitis_tarihi
                  AND silinme_tarihi IS NULL
                ORDER BY baslangic_tarihi ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':kisi_id' => $kisi_id,
            ':baslangic_tarihi' => $baslangic_tarihi,
            ':bitis_tarihi' => $bitis_tarihi
        ]);
        
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
}
