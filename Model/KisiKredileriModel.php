<?php 

namespace Model;

use App\Helper\Security;
use Model\Model;
use PDO;


class KisiKredileriModel extends Model
{
    protected $table = "kisi_kredileri";

    public function __construct()
    {
        parent::__construct($this->table);
    }

    /**
     * Kisi kredilerini getirir
     * @param int $kisi_id
     * @return array
     */
    public function getKisiKredileri($kisi_id)
    {
        $sql = $this->db->prepare("SELECT sum(tutar) as toplam_kredi 
                                          FROM $this->table 
                                          WHERE kisi_id = ?
                                          AND kullanildi_mi = ?");
        $sql->execute([$kisi_id,0]);
        return $sql->fetch(PDO::FETCH_OBJ)->toplam_kredi ?? 0;
    }

    /**Tahsilat id'sine göre kredileri getirir
     * @param int $tahsilat_id
     * @return array
     */
    public function getKredilerByTahsilatId($tahsilat_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE tahsilat_id = ?");
        $sql->execute([$tahsilat_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }


    /**
     * Bir kişinin kullanabileceği toplam net kredi/alacak bakiyesini döndürür.
     * Bu, toplam alacaklarından toplam kullanılanları çıkararak bulunur.
     *
     * @param int $kisiId
     * @return float Kişinin net kullanılabilir kredi tutarı.
     */
    public function getKullanilabilirKredi(int $kisiId): float
    {
        // Toplam alacak (tutar) ile toplam kullanılan tutar arasındaki farkı alırız.
        $sql = "SELECT SUM(tutar - kullanilan_tutar) as net_kredi 
                FROM {$this->table} 
                WHERE kisi_id = :kisi_id AND silinme_tarihi IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':kisi_id' => $kisiId]);
        
        // fetchColumn() tek bir değer döndürmek için idealdir.
        $netKredi = $stmt->fetchColumn();

        // Eğer kişinin hiç kredisi yoksa sonuç NULL olabilir, bu yüzden 0'a çeviriyoruz.
        return (float)($netKredi ?? 0.0);
    }

    /**
     * Bir kişinin kredisini belirtilen tutarda kullanır (harcar).
     * En eski krediden başlayarak harcama yapar (FIFO - İlk Giren İlk Çıkar mantığı).
     *
     * @param int $kisiId Kredisi kullanılacak kişi.
     * @param float $kullanilacakTutar Harcanacak toplam tutar.
     * @param string $aciklama Bu harcamanın neden yapıldığına dair açıklama.
     * @return bool İşlemin başarılı olup olmadığı.
     * @throws \Exception
     */
    public function kullanKredi(int $kisiId, float $kullanilacakTutar, string $aciklama): bool
    {
        if ($kullanilacakTutar <= 0) {
            return true; // Kullanılacak tutar yoksa, işlem başarılı sayılır.
        }
        
        // 1. Kişinin kullanılabilir tüm kredilerini en eskiden yeniye doğru al.
        $sql = "SELECT id, tutar, kullanilan_tutar 
                FROM {$this->table} 
                WHERE kisi_id = :kisi_id 
                  AND tutar > kullanilan_tutar -- Sadece kısmen veya hiç kullanılmamış krediler
                  AND silinme_tarihi IS NULL
                ORDER BY created_at ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':kisi_id' => $kisiId]);
        $krediler = $stmt->fetchAll(PDO::FETCH_OBJ);

        $harcanacakPara = $kullanilacakTutar;

        // 2. Kredileri döngüye alarak harcamayı yap.
        foreach ($krediler as $kredi) {
            if ($harcanacakPara <= 0) break; // Harcanacak para bittiyse döngüden çık.

            $buKredidenKullanilabilir = $kredi->tutar - $kredi->kullanilan_tutar;
            $buKredidenHarcanan = min($harcanacakPara, $buKredidenKullanilabilir);

            // 'kullanilan_tutar' kolonunu güncelle
            $yeniKullanilan = $kredi->kullanilan_tutar + $buKredidenHarcanan;
            
            $updateSql = "UPDATE {$this->table} SET kullanilan_tutar = :yeni_kullanilan WHERE id = :id";
            $updateStmt = $this->db->prepare($updateSql);
            $islemBasarili = $updateStmt->execute([
                ':yeni_kullanilan' => $yeniKullanilan,
                ':id' => $kredi->id
            ]);

            if (!$islemBasarili) {
                // Eğer bir güncelleme başarısız olursa, işlemi durdur ve hata fırlat.
                // Transaction bunu geri alacaktır.
                throw new \Exception("Kredi (ID: {$kredi->id}) güncellenirken bir hata oluştu.");
            }
            
            // Kredi kullanımını loglamak için ayrı bir tabloya kayıt atılabilir (isteğe bağlı ama önerilir).
            // ornegin: kredi_kullanim_detaylari (kredi_id, harcanan_tutar, borc_detay_id, aciklama)
            // $this->logKrediKullanimi($kredi->id, $buKredidenHarcanan, $aciklama);

            $harcanacakPara -= $buKredidenHarcanan;
        }

        // Eğer döngü bittiğinde hala harcanacak para kaldıysa, bu kişinin yeterli kredisi yok demektir.
        // Bu durum, getKullanilabilirKredi kontrolü sayesinde normalde oluşmamalıdır,
        // ama bir güvenlik kontrolü olarak önemlidir.
        if ($harcanacakPara > 0.01) { // Kuruş farkları için tolerans
            throw new \Exception("İşlem hatası: Kişinin yeterli kredisi bulunmuyor. Eksik Tutar: " . $harcanacakPara);
        }

        return true;
    }


    /**Borç Güncellemeden dolayı oluşan krediyi silmek için
     * @param int $borc_detay_id
     * @return bool
     */
    public function deleteKrediByBorcDetayId(int $borc_detay_id): bool
    {
        // Borç detayına bağlı kredileri siler
        $sql = "DELETE FROM {$this->table} WHERE borc_detay_id = :borc_detay_id";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([':borc_detay_id' => $borc_detay_id]);
    }



    /*Tahsilata göre kullanılan krediyi getirir
     * @param int $tahsilat_id
     * @return float
     */
    public function getKullanilanKrediByTahsilatId(int $tahsilat_id): float
    {
        $sql = "SELECT SUM(kullanilan_tutar) as toplam_kullanilan_kredi
                FROM {$this->table} 
                WHERE tahsilat_id = :tahsilat_id AND silinme_tarihi IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tahsilat_id' => $tahsilat_id]);
        
        return (float)($stmt->fetchColumn() ?? 0.0);
    }

}
