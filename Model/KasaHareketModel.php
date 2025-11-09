<?php 

namespace Model;

use PDO;
use Model\Model;
use App\Helper\Date;
use Model\KasaModel;
use App\Helper\Helper;
use App\Helper\Security;
use Model\TahsilatModel;


use Model\KisiKredileriModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class KasaHareketModel extends Model{
    protected $table = "kasa_hareketleri";

    protected $view = "view_kasa_hareketleri";

    public function __construct()
    {
        parent::__construct($this->table);
    }


    /**Kaynak tablo ve kaynek_id alanına göre kayıtları sil 
     * @param string $kaynak_tablo
     * @param int $kaynak_id
     * @return bool
     */
    public function SilKaynakTabloKaynakId($kaynak_tablo, $kaynak_id)
    {
        $query = "DELETE FROM {$this->table} WHERE kaynak_tablo = :kaynak_tablo AND kaynak_id = :kaynak_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':kaynak_tablo', $kaynak_tablo, \PDO::PARAM_STR);
        $stmt->bindParam(':kaynak_id', $kaynak_id, \PDO::PARAM_INT);
        return $stmt->execute();
    }


    /** Kasa hareketlerini getirir.
     * @param int $kasa_id
     * @return array
     * @throws \Exception
     */
    public function getKasaHareketleri($kasa_id)
    {
        $query = "SELECT kh.*, k.adi_soyadi AS adi_soyadi, d.daire_kodu AS daire_kodu FROM $this->view kh
                    LEFT JOIN kisiler k ON kh.kisi_id = k.id
                    LEFT JOIN daireler d ON k.daire_id = d.id
                    WHERE kh.kasa_id = :kasa_id 
                    AND kh.tutar != 0
                    ORDER BY kh.islem_tarihi desc, kh.id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':kasa_id', $kasa_id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    /** Tür ve tarih aralığına göre kasa hareketlerini getirir.
     * @param int $kasa_id
     * @param string $baslangic_tarihi
     * @param string $bitis_tarihi
     * @param string $hareket_yonu 'Gelir', 'Gider' veya '' (tümü)
     * @return array
    */
    public function getKasaHareketleriByDateRange($kasa_id, $baslangic_tarihi, $bitis_tarihi, $hareket_yonu = '')
{
    // Tarih aralıklarını günün tümünü kapsayacak şekilde normalize et
    // Sadece tarih (YYYY-MM-DD) verilmişse başlangıcı 00:00:00, bitişi 23:59:59 yap
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', trim((string)$baslangic_tarihi))) {
        $baslangic_tarihi = $baslangic_tarihi . ' 00:00:00';
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', trim((string)$bitis_tarihi))) {
        $bitis_tarihi = $bitis_tarihi . ' 23:59:59';
    }

    $query = "SELECT kh.*, k.adi_soyadi AS adi_soyadi, d.daire_kodu AS daire_kodu 
              FROM $this->table kh
              LEFT JOIN kisiler k ON kh.kisi_id = k.id
              LEFT JOIN daireler d ON k.daire_id = d.id
              WHERE kh.kasa_id = :kasa_id 
              AND kh.silinme_tarihi IS NULL 
              AND kh.tutar != 0
              AND kh.islem_tarihi BETWEEN :baslangic_tarihi AND :bitis_tarihi";

    // Hareket yönü filtresini normalize et
    $hareketYonuNormalized = strtolower((string)$hareket_yonu);

    // Hareket yönü filtresi varsa ekle
    if (in_array($hareketYonuNormalized, ['gelir', 'gider'], true)) {
        $query .= " AND LOWER(kh.islem_tipi) = :hareket_yonu";
    }
    
    $query .= " ORDER BY kh.islem_tarihi DESC, kh.id DESC";
    
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':kasa_id', $kasa_id, \PDO::PARAM_INT);
    $stmt->bindParam(':baslangic_tarihi', $baslangic_tarihi, \PDO::PARAM_STR);
    $stmt->bindParam(':bitis_tarihi', $bitis_tarihi, \PDO::PARAM_STR);

    if (in_array($hareketYonuNormalized, ['gelir', 'gider'], true)) {
        $stmt->bindValue(':hareket_yonu', $hareketYonuNormalized, \PDO::PARAM_STR);
    }
    
    $stmt->execute();
    return $stmt->fetchAll(\PDO::FETCH_OBJ);
}



    /** Banka hesap hareketlerini tarih aralığı ve yöne göre filtreler.
     * @param int $kasa_id Banka (kasa) ID
     * @param string $baslangic_tarihi Başlangıç tarihi (Y-m-d H:i:s)
     * @param string $bitis_tarihi Bitiş tarihi (Y-m-d H:i:s)
     * @param string $hareket_yonu İşlem tipi filtresi: 'Gelir', 'Gider' veya '' (tümü)
     * @return array
     */
    public function getBankaHareketleri($kasa_id, $baslangic_tarihi, $bitis_tarihi, $hareket_yonu = '')
    {
        // Tarih aralıklarını günün tümünü kapsayacak şekilde normalize et
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', trim((string)$baslangic_tarihi))) {
            $baslangic_tarihi = $baslangic_tarihi . ' 00:00:00';
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', trim((string)$bitis_tarihi))) {
            $bitis_tarihi = $bitis_tarihi . ' 23:59:59';
        }

        $query = "SELECT kh.*, k.adi_soyadi AS adi_soyadi, d.daire_kodu AS daire_kodu 
                  FROM $this->table kh
                  LEFT JOIN kisiler k ON kh.kisi_id = k.id
                  LEFT JOIN daireler d ON k.daire_id = d.id
                  WHERE kh.kasa_id = :kasa_id 
                  AND kh.silinme_tarihi IS NULL 
                  AND kh.tutar != 0
                  AND kh.islem_tarihi BETWEEN :baslangic_tarihi AND :bitis_tarihi";
        
        // Hareket yönü filtresi varsa ekle
        if ($hareket_yonu && in_array($hareket_yonu, ['Gelir', 'Gider'])) {
            $query .= " AND kh.islem_tipi = :hareket_yonu";
        }
        
        $query .= " ORDER BY kh.islem_tarihi DESC, kh.id DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':kasa_id', $kasa_id, \PDO::PARAM_INT);
        $stmt->bindParam(':baslangic_tarihi', $baslangic_tarihi, \PDO::PARAM_STR);
        $stmt->bindParam(':bitis_tarihi', $bitis_tarihi, \PDO::PARAM_STR);
        
        if ($hareket_yonu && in_array($hareket_yonu, ['Gelir', 'Gider'])) {
            $stmt->bindParam(':hareket_yonu', $hareket_yonu, \PDO::PARAM_STR);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * Kasa hareketlerini sayfalanmış olarak getirir (DataTables için optimize edilmiş)
     * @param int $kasa_id
     * @param int $start Başlangıç index'i (offset)
     * @param int $length Sayfa başına kayıt sayısı
     * @param string $searchValue Arama terimi
     * @param string $orderColumn Sıralama kolonu
     * @param string $orderDir Sıralama yönü (asc/desc)
     * @return array
     */
    public function getKasaHareketleriPaginated(
        int $kasa_id, 
        int $start = 0, 
        int $length = 50,
        string $searchValue = '',
        string $orderColumn = 'islem_tarihi',
        string $orderDir = 'desc'
    ): array {
        // Güvenlik: Sadece izin verilen kolonlara sıralama
        $allowedColumns = ['islem_tarihi', 'islem_tipi', 'tutar', 'kategori', 'adi_soyadi', 'daire_kodu'];
        if (!in_array($orderColumn, $allowedColumns)) {
            $orderColumn = 'islem_tarihi';
        }
        
        $orderDir = strtolower($orderDir) === 'asc' ? 'ASC' : 'DESC';
        
        $query = "SELECT kh.*, k.adi_soyadi AS adi_soyadi, d.daire_kodu AS daire_kodu 
                  FROM {$this->table} kh
                  LEFT JOIN kisiler k ON kh.kisi_id = k.id
                  LEFT JOIN daireler d ON k.daire_id = d.id
                  WHERE kh.kasa_id = :kasa_id 
                  AND kh.silinme_tarihi IS NULL 
                  AND kh.tutar != 0";
        
        // Arama filtresi
        if (!empty($searchValue)) {
            $query .= " AND (
                k.adi_soyadi LIKE :search 
                OR d.daire_kodu LIKE :search 
                OR kh.aciklama LIKE :search
                OR kh.islem_tipi LIKE :search
            )";
        }
        
        $query .= " ORDER BY kh.{$orderColumn} {$orderDir}, kh.id DESC
                    LIMIT :start, :length";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':kasa_id', $kasa_id, \PDO::PARAM_INT);
        $stmt->bindParam(':start', $start, \PDO::PARAM_INT);
        $stmt->bindParam(':length', $length, \PDO::PARAM_INT);
        
        if (!empty($searchValue)) {
            $searchParam = "%{$searchValue}%";
            $stmt->bindParam(':search', $searchParam, \PDO::PARAM_STR);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * Kasa hareketlerinin toplam sayısını döndürür (pagination için)
     * @param int $kasa_id
     * @param string $searchValue Arama terimi
     * @return int
     */
    public function getKasaHareketleriCount(int $kasa_id, string $searchValue = ''): int
    {
        $query = "SELECT COUNT(*) as total
                  FROM {$this->table} kh
                  LEFT JOIN kisiler k ON kh.kisi_id = k.id
                  LEFT JOIN daireler d ON k.daire_id = d.id
                  WHERE kh.kasa_id = :kasa_id 
                  AND kh.silinme_tarihi IS NULL 
                  AND kh.tutar != 0";
        
        // Arama filtresi
        if (!empty($searchValue)) {
            $query .= " AND (
                k.adi_soyadi LIKE :search 
                OR d.daire_kodu LIKE :search 
                OR kh.aciklama LIKE :search
                OR kh.islem_tipi LIKE :search
            )";
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':kasa_id', $kasa_id, \PDO::PARAM_INT);
        
        if (!empty($searchValue)) {
            $searchParam = "%{$searchValue}%";
            $stmt->bindParam(':search', $searchParam, \PDO::PARAM_STR);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_OBJ);
        
        return (int)($result->total ?? 0);
    }


    /** Kasa Hareketini view tablsoundan getir
     * @param int $id
     * @return object|null
     */
    public function findFromView($id): ?object
    {
        $id = Security::decrypt($id);
        $query = "SELECT * FROM {$this->view} WHERE id = :id AND silinme_tarihi IS NULL LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_OBJ);
        return $result ?: null;
    }


    
    /**
     * Yüklenen bir Excel dosyasındaki tutar bilgilerini işler ve veritabanına kaydeder.
     *
     * @param string $tmpFilePath Yüklenen dosyanın geçici yolu ($_FILES['file']['tmp_name']).
     * @param int $siteId İşlem yapılan sitenin ID'si.
     * @return array Başarılı ve hatalı işlemler hakkında bilgi içeren bir sonuç dizisi.
     */
    public function excelUpload(string $tmpFilePath, int $siteId): array
    {
        // Loglama servisini ve diğer modelleri hazırla
        $logger = \getLogger();
        $KisiModel = new KisilerModel();
        $KasaModel = new KasaModel();
        $TahsilatModel = new TahsilatModel();
        $KisiKredileriModel = new KisiKredileriModel();
    
        // Sonuçları ve hataları toplayacağımız diziler
        $processedCount = 0;
        $errorRows = [];
        $skippedCount = 0; // Atlanan kayıt sayısı
    


        //   id;site_id;kasa_id;islem_tarihi;islem_tipi;tahsilat_id;kisi_id;tutar;para_birimi;kategori;aciklama;silinme_tarihi;silen_kullanici;kaynak_tablo;kaynak_id;kayit_yapan;created_at;guncellenebilir;updated_at
        
        $kasa_id = $KasaModel->varsayilanKasa()->id ?? 0;


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
    

            $logger->info("Excel dosyası başarıyla yüklendi ve işleme alınıyor.", ['file' => $tmpFilePath]);

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

                $logger->info("İşlenen satır numarası: " . $row->getRowIndex());
    
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
                
                // Tarih*	Tutar*	Kategori(Gelir/Gider)*	DaireKodu	Adı Soyadı	Açıklama

                // Verileri al ve temizle
                //Tarih içindeki /- karakterlerini - ile değiştir
                $rowData['Tarih*'] = str_replace(['/'], '.', $rowData['Tarih*']);
                $rowData['Tarih*'] = str_replace(['-'], ' ', $rowData['Tarih*']);
                $tarih            = trim(Date::convertExcelDate($rowData['Tarih*'], 'Y-m-d H:i:s'));
                $tutar            = trim($rowData['Tutar*'] ?? '');
                $kategori         = trim($rowData['Kategori(Gelir/Gider)*'] ?? '');
                $daireKodu        = trim($rowData['DaireKodu'] ?? '');
                $hesapAdi         = trim($rowData['Adı Soyadı'] ?? '');  
                $aciklama         = trim($rowData['Açıklama'] ?? '');
    
                // ... diğer sütunlar ...
                $logger->info("Satır Verileri", [$tarih, $tutar, $kategori, $daireKodu, $hesapAdi, $aciklama]);
    
                // Eğer zorunlu alanlar boşsa, hatayı kaydet ve sonraki satıra geç
                if (empty($tarih) || empty($tutar) || empty($kategori)) {
                    $errorRows[] = [
                        'row_index' => $row->getRowIndex(),
                        'error_message' =>  "Satır {$row->getRowIndex()}: 'Tarih', 'Tutar' ve 'Kategori' zorunludur.",
                        'data' => $rowData // SATIRIN TÜM VERİSİNİ EKLE
                    ];

                    $rows->next(); // Bir sonraki satıra geç
                    continue;
                }
    
                //Daire kodu ve hesap Adından kişi id'yi bul
                
                if (empty($daireKodu) && empty($hesapAdi)) {
                        $kisi_id = $KisiModel->findKisiIdByDaireKoduAndAdiSoyadi( $daireKodu, $hesapAdi);

                }          
                    // Veritabanına eklenecek veriyi hazırla
                    $data = [
                        'id' => 0,
                        'site_id' => $siteId,
                        'kasa_id' => $kasa_id,
                        'islem_tarihi' => $tarih,
                        'tutar' => floatval($tutar),
                        'islem_tipi' => $kategori,
                        'kisi_id' => $kisi_id ?? 0,
                        'aciklama' => $aciklama,
                    ];
    
                    $this->saveWithAttr($data);


                    // Kişi id mevcutsa, ve gelen tutar pozitif ise Tahsilat ve Kredi kaydı oluştur
                    if (!empty($kisi_id) && floatval($tutar) > 0) {
                        $logger->info("Kişi ID {$kisi_id} için tahsilat ve kredi kaydı oluşturuluyor.", ['tutar' => $tutar]);
                    }


                    $processedCount++;
                    
                    // Döngünün sonunda bir sonraki satıra MANUEL olarak geç.
                    $rows->next();
                }

    
            // === 4. İŞLEMİ SONLANDIR ===
            $this->db->commit();
            $logger->info("Excel yükleme tamamlandı.", [
                'İşlenen kayıt sayısı' => $processedCount,
                'atlanan kayıt sayısı' => $skippedCount,
                'hatalı kayıt sayısı' => count($errorRows)
            ]);
            
            $message = "İşlem tamamlandı: {$processedCount} yeni kayıt eklendi.";
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
            $logger->error("Gelir-Gider Excel yükleme sırasında veritabanı hatası.", ['error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()];
    
        } catch (\Exception $e) {
            // Dosya okuma veya başka bir genel hata olursa
            // Not: `rollBack()` sadece transaction başlatıldıysa çalışır, aksi halde hata verir.
            $this->db->rollBack();
            $logger->error("Gelir-Gider Excel yükleme sırasında genel hata.", ['error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => 'İşlem sırasında bir hata oluştu: ' . $e->getMessage()];
        }
    }




}