<?php

namespace Model;

use Model\Model;
use Model\DefinesModel;
use PhpOffice\PhpSpreadsheet\IOFactory;

use App\Helper\DefinesHelper;
use App\Helper\Security;
use PDO;

class DairelerModel extends Model
{
    protected $table = 'daireler';

    public function __construct()
    {
        parent::__construct($this->table);
    }

    // *************************************************************************************** */

    /**
     * Daire kodundan dairenin id'sini döndürür
     * @param string $daire_kodu
     * @return int|null
     */
    public function DaireId($daire_kodu)
    {
        $query = $this->db->prepare("SELECT id FROM $this->table WHERE daire_kodu = ?");
        $query->execute([$daire_kodu]);
        $result = $query->fetch(PDO::FETCH_OBJ);

        return $result ? $result->id : 0;  // Eğer sonuç varsa id'yi döndür, yoksa null döndür
    }

    // *************************************************************************************** */
    public function SitedekiDaireler($siteID)
    {
        $query = $this->db->prepare("SELECT * FROM {$this->table} WHERE site_id = ? ORDER BY blok_id ASC, daire_no ASC");
        $query->execute([$siteID]);
        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    // *************************************************************************************** */
    public function DaireVarmi($site_id, $block_id, $daire_no)
    {
        $query = $this->db->prepare("SELECT COUNT(*) FROM $this->table WHERE site_id = ? AND blok_id = ? AND daire_no = ?");
        $query->execute([$site_id, $block_id, $daire_no]);
        return $query->fetchColumn() > 0;
    }

    // *************************************************************************************** */
    public function DaireKoduVarMi($site_id, $block_id, $daire_kodu, $exclude_id = null)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} 
            WHERE site_id = ? AND blok_id = ? AND daire_kodu = ?";

        $params = [$site_id, $block_id, $daire_kodu];

        if ($exclude_id !== null) {
            $sql .= " AND id != ?";
            $params[] = $exclude_id;
        }

        $query = $this->db->prepare($sql);
        $query->execute($params);

        return $query->fetchColumn() > 0;
    }


    // *************************************************************************************** */
    public function BlokDaireleri($blok_id)
    {
        $sql = $this->db->prepare("SELECT id, daire_no FROM $this->table WHERE blok_id = ? order by daire_no ASC");
        $sql->execute([$blok_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);  // Her daire bir nesne olarak dönsün
    }

    // *************************************************************************************** */

    /**
     * Daire Adını döndürür
     * @param string $daire_id
     * @return string
     */
    public function DaireAdi($daire_id)
    {
        $sql = $this->db->prepare("Select daire_no from $this->table WHERE id = ?");
        $sql->execute([$daire_id]);
        return $sql->fetch(PDO::FETCH_OBJ);
    }

    // *************************************************************************************** */

    /**
     * Daire id'sinden Dairenin kodunu döndürür
     * @param mixed $daire_id
     * @return string
     */
    public function DaireKodu($daire_id)
    {
        $sql = $this->db->prepare("Select daire_kodu from $this->table WHERE id = ?");
        $sql->execute([$daire_id]);
        return $sql->fetch(PDO::FETCH_OBJ)->daire_kodu ?? '';
    }

    /**
     * site_id ve id alanlarına göre daire bilgilerini getirir
     * @param int $site_id
     * @param int $id
     * @return object|null
     */
    public function DaireBilgisi($site_id, $id)
    {
        $query = $this->db->prepare("SELECT * FROM {$this->table} WHERE site_id = ? AND id = ?");
        $query->execute([$site_id, $id]);
        return $query->fetch(PDO::FETCH_OBJ) ?: null;

        /**Daire tipine göre daireleri getirir
         * @param int $daire_tipi_id
         * @return array
         */
    }
    public function DaireTipineGoreDaireler($daire_tipi_id)
    {
        $query = $this->db->prepare("SELECT * FROM {$this->table} WHERE daire_tipi = ? ORDER BY blok_id ASC, daire_no ASC");
        $query->execute([$daire_tipi_id]);
        return $query->fetchAll(PDO::FETCH_OBJ);
    }


    public function getDairelerForTemplate(int $siteId, ?int $blokId = null): array
{
    $sql = "SELECT d.daire_kodu,d.daire_no,b.blok_adi FROM daireler d
            JOIN bloklar b ON d.blok_id = b.id
            WHERE b.site_id = ?";
    
    $params = [$siteId];

    if ($blokId !== null) {
        $sql .= " AND d.blok_id = ?";
        $params[] = $blokId;
    }
    
    $sql .= " ORDER BY d.id ASC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_OBJ);
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
                $blokAdi            = trim($rowData['Blok Adı*'] ?? '');
                $daireNo            = trim($rowData['Daire No*'] ?? '');
                $daireKoduBenzersiz = trim($rowData['Daire Kodu*'] ?? '');
                $daireTipi          = trim($rowData['Daire Tipi'] ?? '');
                $kat                = trim($rowData['Kat'] ?? '');
                $brutAlan           = trim($rowData['Brüt Alan*'] ?? '');
                $netAlan            = trim($rowData['Net Alan*'] ?? '');
                $arsaPayi           = trim($rowData['Arsa Payı*'] ?? '');
                $kullanimDurumu     = trim($rowData['Kullanım Durumu (Kullanımda, Boş)*'] ?? '');
                $aciklama           = trim($rowData['Açıklama'] ?? '');
    
                // ... diğer sütunlar ...
    
                // Eğer zorunlu alanlar boşsa, hatayı kaydet ve sonraki satıra geç
                if (empty($blokAdi) || empty($daireNo)) {
                    $errorRows[] = "Satır {$row->getRowIndex()}: 'Blok Adı' ve 'Daire No' zorunludur.";
                    $rows->next(); // Bir sonraki satıra geç
                    continue;
                }
    
                // Eğer benzersiz kod boşsa, otomatik oluştur
                if (empty($daireKoduBenzersiz)) {
                    $daireKoduBenzersiz = (str_replace(" Blok","",$blokAdi) . 'D' . $daireNo); // slugify gibi bir helper fonksiyon kullanın
                }
                
                // === 3. ANA MANTIK: TEK SORGULA, KARAR VER, İŞLEM YAP ===
                $daire = $this->findDaireByUniqueCode($daireKoduBenzersiz);
    
                if ($daire) {
                    // Daire zaten var, atla.
                    $skippedCount++;
                } else {
                    // Daire yok, YENİ KAYIT OLUŞTURULACAK
                    
                    // Bloğu bul veya oluştur
                    $blok = $blokModel->findBlokBySiteAndName($siteId, $blokAdi);
                    if (!$blok) {
                        $blokModel->saveWithAttr(['site_id' => $siteId, 'blok_adi' => $blokAdi]);
                        $blokId = $this->db->lastInsertId();
                        $logger->info("Excel: Yeni blok oluşturuldu.", ['blok_adi' => $blokAdi, 'site_id' => $siteId]);
                    } else {
                        $blokId = $blok->id;
                    }
    
                    // Daire tipi ID'sini bul
                    $daireTipiId = $definesModel->getApartmentTypeIdByName($siteId, DefinesHelper::TYPE_APARTMENT, $daireTipi);
                    if (!$daireTipiId && !empty($daireTipi)) { // Sadece daire tipi belirtilmişse hata ver
                        $errorRows[] = "Satır {$row->getRowIndex()}: '{$daireTipi}' adında geçerli bir daire tipi bulunamadı.";
                        $rows->next(); // Bir sonraki satıra geç
                        continue;
                    }
    
                    // Veritabanına eklenecek veriyi hazırla
                    $daireData = [
                        'site_id' => $siteId,
                        'blok_id' => $blokId,
                        'daire_no' => $daireNo,
                        'daire_kodu' => $daireKoduBenzersiz, // Bu eski daire_kodu sütununuz olabilir
                        'daire_tipi' => $daireTipiId, // ID olarak kaydet, null olabilir
                        'kat' => $kat,
                        'brut_alan' => $brutAlan,
                        'net_alan' => $netAlan,
                        'arsa_payi' => $arsaPayi,
                        'aktif_mi' => $kullanimDurumu == 'Dolu' ? 1 : 0, // Kullanım durumu: 'Dolu' ise 1, 'Boş' ise 0
                        'aciklama' => $aciklama,
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
            
            $message = "İşlem tamamlandı: {$processedCount} yeni daire eklendi, {$skippedCount} kayıt zaten mevcut olduğu için atlandı.";
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
 


    // *************************************************************************************** */

    /**
     * Benzersiz koda göre bir daire arar.
     * @param string $uniqueCode
     * @return object|null
     */
    public function findDaireByUniqueCode(string $uniqueCode): ?object
    {
        $stmt = $this->db->prepare("SELECT * FROM daireler WHERE daire_kodu = ? LIMIT 1");
        $stmt->execute([$uniqueCode]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ?: null;
    }

    // *************************************************************************************** */

    
}
