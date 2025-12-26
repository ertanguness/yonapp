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

class KasaHareketModel extends Model
{
    protected $table = "kasa_hareketleri";

    protected $view = "view_kasa_hareketleri";

    public function __construct()
    {
        parent::__construct($this->table);
    }

    /**
     * Site ID'ye göre aylık gelir ve gider toplamlarını view tablosundan çeker.
     * @param int $site_id
     * @param int $year
     * @return array [month => ['gelir' => float, 'gider' => float]]
     */
    public function getMonthlySummaryBySiteId(int $site_id, int $year): array
    {
        $query = "SELECT 
                    MONTH(kh.islem_tarihi) as ay,
                    SUM(CASE WHEN kh.islem_tipi = 'Gelir' THEN kh.tutar ELSE 0 END) as toplam_gelir,
                    SUM(CASE WHEN kh.islem_tipi = 'Gider' THEN ABS(kh.tutar) ELSE 0 END) as toplam_gider
                  FROM {$this->view} kh
                  WHERE kh.kasa_id IN (SELECT id FROM kasa WHERE site_id = :site_id)
                  AND YEAR(kh.islem_tarihi) = :year
                  AND kh.silinme_tarihi IS NULL
                  GROUP BY MONTH(kh.islem_tarihi)
                  ORDER BY ay ASC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':site_id', $site_id, \PDO::PARAM_INT);
        $stmt->bindParam(':year', $year, \PDO::PARAM_INT);
        $stmt->execute();
        
        $results = $stmt->fetchAll(\PDO::FETCH_OBJ);
        
        // Sonucu ay bazlı anahtarlarla diziye çevir
        $monthlyData = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyData[$m] = ['gelir' => 0.0, 'gider' => 0.0];
        }
        
        foreach ($results as $row) {
            $m = (int)$row->ay;
            if (isset($monthlyData[$m])) {
                $monthlyData[$m]['gelir'] = (float)$row->toplam_gelir;
                $monthlyData[$m]['gider'] = (float)$row->toplam_gider;
            }
        }
        
        return $monthlyData;
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
        string $orderDir = 'desc',
        array $columnFilters = []
    ): array {
        // Güvenlik: Sadece izin verilen kolonlara sıralama
        $allowedColumns = ['islem_tarihi', 'islem_tipi', 'tutar', 'kategori', 'alt_tur', 'adi_soyadi', 'daire_kodu', 'makbuz_no'];
        if (!in_array($orderColumn, $allowedColumns)) {
            $orderColumn = 'islem_tarihi';
        }

        $orderDir = strtolower($orderDir) === 'asc' ? 'ASC' : 'DESC';

        $query = "SELECT kh.*, k.adi_soyadi AS adi_soyadi, d.daire_kodu AS daire_kodu 
                  FROM {$this->view} kh
                  LEFT JOIN kisiler k ON kh.kisi_id = k.id
                  LEFT JOIN daireler d ON k.daire_id = d.id
                  WHERE kh.kasa_id = :kasa_id 
                  AND kh.silinme_tarihi IS NULL 
                  AND kh.tutar != 0";

        // Global arama (metin + sayı + tarih)
        $searchNum = null; $searchDateYmd = null; $searchLike = null;
        if (!empty($searchValue)) {
            $searchLike = "%{$searchValue}%";
            $num = preg_replace('/[^0-9.,-]/', '', (string)$searchValue);
            if ($num !== '') {
                $num = str_replace('.', '', $num);
                $num = str_replace(',', '.', $num);
                if (is_numeric($num)) { $searchNum = (float)$num; }
            }
            $m = [];
            if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', trim((string)$searchValue), $m)) {
                $searchDateYmd = $m[3] . '-' . $m[2] . '-' . $m[1];
            }

            $ors = [
                "k.adi_soyadi LIKE :search",
                "d.daire_kodu LIKE :search",
                "kh.aciklama LIKE :search",
                "kh.islem_tipi LIKE :search",
                "kh.kategori LIKE :search",
                "kh.alt_tur LIKE :search",
                "kh.makbuz_no LIKE :search"
            ];
            if ($searchNum !== null) {
                $ors[] = "kh.tutar = :searchNum";
                $ors[] = "kh.yuruyen_bakiye = :searchNum";
            }
            if ($searchDateYmd !== null) {
                $ors[] = "DATE(kh.islem_tarihi) = :searchDate";
            }
            $query .= " AND (" . implode(' OR ', $ors) . ")";
        }

        $bindings = [];
        if (!empty($columnFilters)) {
            if (!empty($columnFilters['islem_tarihi'])) {
                $f = $columnFilters['islem_tarihi'];
                if (is_array($f) && !empty($f['val'])) {
                    $m = [];
                    preg_match('/(\d{2})\.(\d{2})\.(\d{4})/', (string)$f['val'], $m);
                    if (!empty($m)) {
                        $ymd = $m[3] . '-' . $m[2] . '-' . $m[1];
                        $op = ($f['op'] ?? 'on');
                        if ($op === 'after') { $query .= " AND DATE(kh.islem_tarihi) > :f_date"; }
                        elseif ($op === 'before') { $query .= " AND DATE(kh.islem_tarihi) < :f_date"; }
                        elseif ($op === 'not_on') { $query .= " AND DATE(kh.islem_tarihi) <> :f_date"; }
                        else { $query .= " AND DATE(kh.islem_tarihi) = :f_date"; }
                        $bindings[':f_date'] = $ymd;
                    }
                } else {
                    $query .= " AND DATE_FORMAT(kh.islem_tarihi, '%d.%m.%Y %H:%i') LIKE :f_date";
                    $bindings[':f_date'] = '%' . (is_array($f) ? ($f['val'] ?? '') : $f) . '%';
                }
            }
            if (!empty($columnFilters['islem_tipi'])) {
                $f = $columnFilters['islem_tipi'];
                $val = is_array($f) ? ($f['val'] ?? '') : $f;
                $op = is_array($f) ? ($f['op'] ?? 'contains') : 'contains';
                $valLower = strtolower((string)$val);

                if ($op === 'none' || $op === 'yok') {
                    // no filter
                } elseif ($op === 'starts') {
                    $query .= " AND LOWER(kh.islem_tipi) LIKE :f_islem";
                    $bindings[':f_islem'] = $valLower . '%';
                } elseif ($op === 'ends') {
                    $query .= " AND LOWER(kh.islem_tipi) LIKE :f_islem";
                    $bindings[':f_islem'] = '%' . $valLower;
                } elseif ($op === 'equals') {
                    $query .= " AND LOWER(kh.islem_tipi) = :f_islem";
                    $bindings[':f_islem'] = $valLower;
                } elseif ($op === 'not_equals') {
                    $query .= " AND (LOWER(kh.islem_tipi) <> :f_islem OR kh.islem_tipi IS NULL OR kh.islem_tipi = '')";
                    $bindings[':f_islem'] = $valLower;
                } elseif ($op === 'not_contains') {
                    $query .= " AND (LOWER(kh.islem_tipi) NOT LIKE :f_islem OR kh.islem_tipi IS NULL OR kh.islem_tipi = '')";
                    $bindings[':f_islem'] = '%' . $valLower . '%';
                } elseif ($op === 'empty' || $op === 'is_empty') {
                    $query .= " AND (kh.islem_tipi IS NULL OR kh.islem_tipi = '')";
                } else {
                    // contains (default)
                    $query .= " AND LOWER(kh.islem_tipi) LIKE :f_islem";
                    $bindings[':f_islem'] = '%' . $valLower . '%';
                }
            }
            if (!empty($columnFilters['daire_kodu'])) {
                $f = $columnFilters['daire_kodu'];
                $val = is_array($f) ? ($f['val'] ?? '') : $f;
                $op = is_array($f) ? ($f['op'] ?? 'contains') : 'contains';
                if ($op === 'none' || $op === 'yok') { }
                elseif ($op === 'starts') { $query .= " AND d.daire_kodu LIKE :f_daire"; $bindings[':f_daire'] = $val . '%'; }
                elseif ($op === 'ends') { $query .= " AND d.daire_kodu LIKE :f_daire"; $bindings[':f_daire'] = '%' . $val; }
                elseif ($op === 'equals') { $query .= " AND d.daire_kodu = :f_daire"; $bindings[':f_daire'] = $val; }
                elseif ($op === 'not_equals') { $query .= " AND (d.daire_kodu <> :f_daire OR d.daire_kodu IS NULL OR d.daire_kodu = '')"; $bindings[':f_daire'] = $val; }
                elseif ($op === 'not_contains') { $query .= " AND (d.daire_kodu NOT LIKE :f_daire OR d.daire_kodu IS NULL OR d.daire_kodu = '')"; $bindings[':f_daire'] = '%' . $val . '%'; }
                elseif ($op === 'empty' || $op === 'is_empty') { $query .= " AND (d.daire_kodu IS NULL OR d.daire_kodu = '')"; }
                else { $query .= " AND d.daire_kodu LIKE :f_daire"; $bindings[':f_daire'] = '%' . $val . '%'; }
            }
            if (!empty($columnFilters['adi_soyadi'])) {
                $f = $columnFilters['adi_soyadi'];
                $val = is_array($f) ? ($f['val'] ?? '') : $f;
                $op = is_array($f) ? ($f['op'] ?? 'contains') : 'contains';
                if ($op === 'none' || $op === 'yok') { }
                elseif ($op === 'starts') { $query .= " AND k.adi_soyadi LIKE :f_hesap"; $bindings[':f_hesap'] = $val . '%'; }
                elseif ($op === 'ends') { $query .= " AND k.adi_soyadi LIKE :f_hesap"; $bindings[':f_hesap'] = '%' . $val; }
                elseif ($op === 'equals') { $query .= " AND k.adi_soyadi = :f_hesap"; $bindings[':f_hesap'] = $val; }
                elseif ($op === 'not_equals') { $query .= " AND (k.adi_soyadi <> :f_hesap OR k.adi_soyadi IS NULL OR k.adi_soyadi = '')"; $bindings[':f_hesap'] = $val; }
                elseif ($op === 'not_contains') { $query .= " AND (k.adi_soyadi NOT LIKE :f_hesap OR k.adi_soyadi IS NULL OR k.adi_soyadi = '')"; $bindings[':f_hesap'] = '%' . $val . '%'; }
                elseif ($op === 'empty' || $op === 'is_empty') { $query .= " AND (k.adi_soyadi IS NULL OR k.adi_soyadi = '')"; }
                else { $query .= " AND k.adi_soyadi LIKE :f_hesap"; $bindings[':f_hesap'] = '%' . $val . '%'; }
            }
            if (!empty($columnFilters['tutar'])) {
                $f = $columnFilters['tutar'];
                $raw = is_array($f) ? ($f['val'] ?? '') : $f;
                $val = preg_replace('/[^0-9.,-]/', '', (string)$raw);
                $val = str_replace('.', '', $val);
                $val = str_replace(',', '.', $val);
                $op = is_array($f) ? ($f['op'] ?? 'equals') : 'equals';
                if ($val !== '' && $op !== 'none' && $op !== 'yok') {
                    if ($op === 'contains') {
                        // Numeric contains: compare formatted string representation (TR style) to allow partial match.
                        // 1) kh.tutar -> replace '.' with ',' (decimal)
                        // 2) remove thousands separator '.' already absent in numeric
                        // 3) compare against user input normalized to ',' decimal
                        $q = str_replace('.', ',', (string)$val);
                        $query .= " AND REPLACE(CAST(kh.tutar AS CHAR), '.', ',') LIKE :f_tutar_like";
                        $bindings[':f_tutar_like'] = '%' . $q . '%';
                    } else {
                        $cmp = '=';
                        if ($op === 'gt') $cmp = '>';
                        elseif ($op === 'gte') $cmp = '>=';
                        elseif ($op === 'lt') $cmp = '<';
                        elseif ($op === 'lte') $cmp = '<=';
                        elseif ($op === 'not_equals') $cmp = '<>';
                        $query .= " AND kh.tutar {$cmp} :f_tutar";
                        $bindings[':f_tutar'] = (float)$val;
                    }
                }
            }
            if (!empty($columnFilters['yuruyen_bakiye'])) {
                $f = $columnFilters['yuruyen_bakiye'];
                $raw = is_array($f) ? ($f['val'] ?? '') : $f;
                $valb = preg_replace('/[^0-9.,-]/', '', (string)$raw);
                $valb = str_replace('.', '', $valb);
                $valb = str_replace(',', '.', $valb);
                $op = is_array($f) ? ($f['op'] ?? 'equals') : 'equals';
                if ($valb !== '' && $op !== 'none' && $op !== 'yok') {
                    if ($op === 'contains') {
                        $q = str_replace('.', ',', (string)$valb);
                        $query .= " AND REPLACE(CAST(kh.yuruyen_bakiye AS CHAR), '.', ',') LIKE :f_bakiye_like";
                        $bindings[':f_bakiye_like'] = '%' . $q . '%';
                    } else {
                        $cmp = '=';
                        if ($op === 'gt') $cmp = '>';
                        elseif ($op === 'gte') $cmp = '>=';
                        elseif ($op === 'lt') $cmp = '<';
                        elseif ($op === 'lte') $cmp = '<=';
                        elseif ($op === 'not_equals') $cmp = '<>';
                        $query .= " AND kh.yuruyen_bakiye {$cmp} :f_bakiye";
                        $bindings[':f_bakiye'] = (float)$valb;
                    }
                }
            }
            if (!empty($columnFilters['kategori'])) {
                $f = $columnFilters['kategori'];
                $val = is_array($f) ? ($f['val'] ?? '') : $f;
                $op = is_array($f) ? ($f['op'] ?? 'contains') : 'contains';
                if ($op === 'none' || $op === 'yok') { }
                elseif ($op === 'starts') { $query .= " AND kh.kategori LIKE :f_kategori"; $bindings[':f_kategori'] = $val . '%'; }
                elseif ($op === 'ends') { $query .= " AND kh.kategori LIKE :f_kategori"; $bindings[':f_kategori'] = '%' . $val; }
                elseif ($op === 'equals') { $query .= " AND kh.kategori = :f_kategori"; $bindings[':f_kategori'] = $val; }
                elseif ($op === 'not_equals') { $query .= " AND (kh.kategori <> :f_kategori OR kh.kategori IS NULL OR kh.kategori = '')"; $bindings[':f_kategori'] = $val; }
                elseif ($op === 'not_contains') { $query .= " AND (kh.kategori NOT LIKE :f_kategori OR kh.kategori IS NULL OR kh.kategori = '')"; $bindings[':f_kategori'] = '%' . $val . '%'; }
                elseif ($op === 'empty' || $op === 'is_empty') { $query .= " AND (kh.kategori IS NULL OR kh.kategori = '')"; }
                else { $query .= " AND kh.kategori LIKE :f_kategori"; $bindings[':f_kategori'] = '%' . $val . '%'; }
            }
            if (!empty($columnFilters['alt_tur'])) {
                $f = $columnFilters['alt_tur'];
                $val = is_array($f) ? ($f['val'] ?? '') : $f;
                $op = is_array($f) ? ($f['op'] ?? 'contains') : 'contains';
                if ($op === 'none' || $op === 'yok') { }
                elseif ($op === 'starts') { $query .= " AND kh.alt_tur LIKE :f_alt_tur"; $bindings[':f_alt_tur'] = $val . '%'; }
                elseif ($op === 'ends') { $query .= " AND kh.alt_tur LIKE :f_alt_tur"; $bindings[':f_alt_tur'] = '%' . $val; }
                elseif ($op === 'equals') { $query .= " AND kh.alt_tur = :f_alt_tur"; $bindings[':f_alt_tur'] = $val; }
                elseif ($op === 'not_equals') { $query .= " AND (kh.alt_tur <> :f_alt_tur OR kh.alt_tur IS NULL OR kh.alt_tur = '')"; $bindings[':f_alt_tur'] = $val; }
                elseif ($op === 'not_contains') { $query .= " AND (kh.alt_tur NOT LIKE :f_alt_tur OR kh.alt_tur IS NULL OR kh.alt_tur = '')"; $bindings[':f_alt_tur'] = '%' . $val . '%'; }
                elseif ($op === 'empty' || $op === 'is_empty') { $query .= " AND (kh.alt_tur IS NULL OR kh.alt_tur = '')"; }
                else { $query .= " AND kh.alt_tur LIKE :f_alt_tur"; $bindings[':f_alt_tur'] = '%' . $val . '%'; }
            }
            if (!empty($columnFilters['makbuz_no'])) {
                $f = $columnFilters['makbuz_no'];
                $val = is_array($f) ? ($f['val'] ?? '') : $f;
                $op = is_array($f) ? ($f['op'] ?? 'contains') : 'contains';
                if ($op === 'none' || $op === 'yok') { }
                elseif ($op === 'starts') { $query .= " AND kh.makbuz_no LIKE :f_makbuz"; $bindings[':f_makbuz'] = $val . '%'; }
                elseif ($op === 'ends') { $query .= " AND kh.makbuz_no LIKE :f_makbuz"; $bindings[':f_makbuz'] = '%' . $val; }
                elseif ($op === 'equals') { $query .= " AND kh.makbuz_no = :f_makbuz"; $bindings[':f_makbuz'] = $val; }
                elseif ($op === 'not_equals') { $query .= " AND (kh.makbuz_no <> :f_makbuz OR kh.makbuz_no IS NULL OR kh.makbuz_no = '')"; $bindings[':f_makbuz'] = $val; }
                elseif ($op === 'not_contains') { $query .= " AND (kh.makbuz_no NOT LIKE :f_makbuz OR kh.makbuz_no IS NULL OR kh.makbuz_no = '')"; $bindings[':f_makbuz'] = '%' . $val . '%'; }
                elseif ($op === 'empty' || $op === 'is_empty') { $query .= " AND (kh.makbuz_no IS NULL OR kh.makbuz_no = '')"; }
                else { $query .= " AND kh.makbuz_no LIKE :f_makbuz"; $bindings[':f_makbuz'] = '%' . $val . '%'; }
            }
            if (!empty($columnFilters['aciklama'])) {
                $f = $columnFilters['aciklama'];
                $val = is_array($f) ? ($f['val'] ?? '') : $f;
                $op = is_array($f) ? ($f['op'] ?? 'contains') : 'contains';
                if ($op === 'none' || $op === 'yok') { }
                elseif ($op === 'starts') { $query .= " AND kh.aciklama LIKE :f_aciklama"; $bindings[':f_aciklama'] = $val . '%'; }
                elseif ($op === 'ends') { $query .= " AND kh.aciklama LIKE :f_aciklama"; $bindings[':f_aciklama'] = '%' . $val; }
                elseif ($op === 'equals') { $query .= " AND kh.aciklama = :f_aciklama"; $bindings[':f_aciklama'] = $val; }
                elseif ($op === 'not_equals') { $query .= " AND (kh.aciklama <> :f_aciklama OR kh.aciklama IS NULL OR kh.aciklama = '')"; $bindings[':f_aciklama'] = $val; }
                elseif ($op === 'not_contains') { $query .= " AND (kh.aciklama NOT LIKE :f_aciklama OR kh.aciklama IS NULL OR kh.aciklama = '')"; $bindings[':f_aciklama'] = '%' . $val . '%'; }
                elseif ($op === 'empty' || $op === 'is_empty') { $query .= " AND (kh.aciklama IS NULL OR kh.aciklama = '')"; }
                else { $query .= " AND kh.aciklama LIKE :f_aciklama"; $bindings[':f_aciklama'] = '%' . $val . '%'; }
            }
        }

        $query .= " ORDER BY kh.{$orderColumn} {$orderDir}, kh.id DESC
                    LIMIT :start, :length";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':kasa_id', $kasa_id, \PDO::PARAM_INT);
        $stmt->bindParam(':start', $start, \PDO::PARAM_INT);
        $stmt->bindParam(':length', $length, \PDO::PARAM_INT);

        if (!empty($searchValue)) {
            $stmt->bindParam(':search', $searchLike, \PDO::PARAM_STR);
            if ($searchNum !== null) { $stmt->bindValue(':searchNum', $searchNum, \PDO::PARAM_STR); }
            if ($searchDateYmd !== null) { $stmt->bindValue(':searchDate', $searchDateYmd, \PDO::PARAM_STR); }
        }
        foreach ($bindings as $key => $val) {
            if (is_float($val) || is_int($val)) {
                $stmt->bindValue($key, $val, \PDO::PARAM_STR);
            } else {
                $stmt->bindValue($key, $val, \PDO::PARAM_STR);
            }
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
    public function getKasaHareketleriCount(int $kasa_id, string $searchValue = '', array $columnFilters = []): int
    {
        $query = "SELECT COUNT(*) as total
                  FROM {$this->view} kh
                  LEFT JOIN kisiler k ON kh.kisi_id = k.id
                  LEFT JOIN daireler d ON k.daire_id = d.id
                  WHERE kh.kasa_id = :kasa_id 
                  AND kh.silinme_tarihi IS NULL 
                  AND kh.tutar != 0";

        // Arama filtresi
        $searchNumC = null; $searchDateYmdC = null; $searchLikeC = null;
        if (!empty($searchValue)) {
            $searchLikeC = "%{$searchValue}%";
            $num = preg_replace('/[^0-9.,-]/', '', (string)$searchValue);
            if ($num !== '') { $num = str_replace('.', '', $num); $num = str_replace(',', '.', $num); if (is_numeric($num)) { $searchNumC = (float)$num; } }
            $m = [];
            if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', trim((string)$searchValue), $m)) { $searchDateYmdC = $m[3] . '-' . $m[2] . '-' . $m[1]; }

            $ors = [
                "k.adi_soyadi LIKE :search",
                "d.daire_kodu LIKE :search",
                "kh.aciklama LIKE :search",
                "kh.islem_tipi LIKE :search",
                "kh.kategori LIKE :search",
                "kh.alt_tur LIKE :search",
                "kh.makbuz_no LIKE :search"
            ];
            if ($searchNumC !== null) { $ors[] = "kh.tutar = :searchNum"; $ors[] = "kh.yuruyen_bakiye = :searchNum"; }
            if ($searchDateYmdC !== null) { $ors[] = "DATE(kh.islem_tarihi) = :searchDate"; }
            $query .= " AND (" . implode(' OR ', $ors) . ")";
        }

        $bindings = [];
        if (!empty($columnFilters)) {
            if (!empty($columnFilters['islem_tarihi'])) {
                $f = $columnFilters['islem_tarihi'];
                if (is_array($f) && !empty($f['val'])) {
                    $m = [];
                    preg_match('/(\d{2})\.(\d{2})\.(\d{4})/', (string)$f['val'], $m);
                    if (!empty($m)) {
                        $ymd = $m[3] . '-' . $m[2] . '-' . $m[1];
                        $op = ($f['op'] ?? 'on');
                        if ($op === 'after') { $query .= " AND DATE(kh.islem_tarihi) > :f_date"; }
                        elseif ($op === 'before') { $query .= " AND DATE(kh.islem_tarihi) < :f_date"; }
                        elseif ($op === 'not_on') { $query .= " AND DATE(kh.islem_tarihi) <> :f_date"; }
                        else { $query .= " AND DATE(kh.islem_tarihi) = :f_date"; }
                        $bindings[':f_date'] = $ymd;
                    }
                } else {
                    $query .= " AND DATE_FORMAT(kh.islem_tarihi, '%d.%m.%Y %H:%i') LIKE :f_date";
                    $bindings[':f_date'] = '%' . (is_array($f) ? ($f['val'] ?? '') : $f) . '%';
                }
            }
            if (!empty($columnFilters['islem_tipi'])) {
                $f = $columnFilters['islem_tipi'];
                $val = is_array($f) ? ($f['val'] ?? '') : $f;
                $op = is_array($f) ? ($f['op'] ?? 'contains') : 'contains';
                $valLower = strtolower((string)$val);

                if ($op === 'none' || $op === 'yok') {
                    // no filter
                } elseif ($op === 'starts') {
                    $query .= " AND LOWER(kh.islem_tipi) LIKE :f_islem";
                    $bindings[':f_islem'] = $valLower . '%';
                } elseif ($op === 'ends') {
                    $query .= " AND LOWER(kh.islem_tipi) LIKE :f_islem";
                    $bindings[':f_islem'] = '%' . $valLower;
                } elseif ($op === 'equals') {
                    $query .= " AND LOWER(kh.islem_tipi) = :f_islem";
                    $bindings[':f_islem'] = $valLower;
                } elseif ($op === 'not_equals') {
                    $query .= " AND (LOWER(kh.islem_tipi) <> :f_islem OR kh.islem_tipi IS NULL OR kh.islem_tipi = '')";
                    $bindings[':f_islem'] = $valLower;
                } elseif ($op === 'not_contains') {
                    $query .= " AND (LOWER(kh.islem_tipi) NOT LIKE :f_islem OR kh.islem_tipi IS NULL OR kh.islem_tipi = '')";
                    $bindings[':f_islem'] = '%' . $valLower . '%';
                } elseif ($op === 'empty' || $op === 'is_empty') {
                    $query .= " AND (kh.islem_tipi IS NULL OR kh.islem_tipi = '')";
                } else {
                    // contains (default)
                    $query .= " AND LOWER(kh.islem_tipi) LIKE :f_islem";
                    $bindings[':f_islem'] = '%' . $valLower . '%';
                }
            }
            if (!empty($columnFilters['daire_kodu'])) {
                $f = $columnFilters['daire_kodu'];
                $val = is_array($f) ? ($f['val'] ?? '') : $f;
                $op = is_array($f) ? ($f['op'] ?? 'contains') : 'contains';
                if ($op === 'none' || $op === 'yok') { }
                elseif ($op === 'starts') { $query .= " AND d.daire_kodu LIKE :f_daire"; $bindings[':f_daire'] = $val . '%'; }
                elseif ($op === 'ends') { $query .= " AND d.daire_kodu LIKE :f_daire"; $bindings[':f_daire'] = '%' . $val; }
                elseif ($op === 'equals') { $query .= " AND d.daire_kodu = :f_daire"; $bindings[':f_daire'] = $val; }
                elseif ($op === 'not_equals') { $query .= " AND (d.daire_kodu <> :f_daire OR d.daire_kodu IS NULL OR d.daire_kodu = '')"; $bindings[':f_daire'] = $val; }
                elseif ($op === 'not_contains') { $query .= " AND (d.daire_kodu NOT LIKE :f_daire OR d.daire_kodu IS NULL OR d.daire_kodu = '')"; $bindings[':f_daire'] = '%' . $val . '%'; }
                elseif ($op === 'empty' || $op === 'is_empty') { $query .= " AND (d.daire_kodu IS NULL OR d.daire_kodu = '')"; }
                else { $query .= " AND d.daire_kodu LIKE :f_daire"; $bindings[':f_daire'] = '%' . $val . '%'; }
            }
            if (!empty($columnFilters['adi_soyadi'])) {
                $f = $columnFilters['adi_soyadi'];
                $val = is_array($f) ? ($f['val'] ?? '') : $f;
                $op = is_array($f) ? ($f['op'] ?? 'contains') : 'contains';
                if ($op === 'none' || $op === 'yok') { }
                elseif ($op === 'starts') { $query .= " AND k.adi_soyadi LIKE :f_hesap"; $bindings[':f_hesap'] = $val . '%'; }
                elseif ($op === 'ends') { $query .= " AND k.adi_soyadi LIKE :f_hesap"; $bindings[':f_hesap'] = '%' . $val; }
                elseif ($op === 'equals') { $query .= " AND k.adi_soyadi = :f_hesap"; $bindings[':f_hesap'] = $val; }
                elseif ($op === 'not_equals') { $query .= " AND (k.adi_soyadi <> :f_hesap OR k.adi_soyadi IS NULL OR k.adi_soyadi = '')"; $bindings[':f_hesap'] = $val; }
                elseif ($op === 'not_contains') { $query .= " AND (k.adi_soyadi NOT LIKE :f_hesap OR k.adi_soyadi IS NULL OR k.adi_soyadi = '')"; $bindings[':f_hesap'] = '%' . $val . '%'; }
                elseif ($op === 'empty' || $op === 'is_empty') { $query .= " AND (k.adi_soyadi IS NULL OR k.adi_soyadi = '')"; }
                else { $query .= " AND k.adi_soyadi LIKE :f_hesap"; $bindings[':f_hesap'] = '%' . $val . '%'; }
            }
            if (!empty($columnFilters['tutar'])) {
                $f = $columnFilters['tutar'];
                $raw = is_array($f) ? ($f['val'] ?? '') : $f;
                $val = preg_replace('/[^0-9.,-]/', '', (string)$raw);
                $val = str_replace('.', '', $val);
                $val = str_replace(',', '.', $val);
                $op = is_array($f) ? ($f['op'] ?? 'equals') : 'equals';
                if ($val !== '' && $op !== 'none' && $op !== 'yok') {
                    if ($op === 'contains') {
                        $q = str_replace('.', ',', (string)$val);
                        $query .= " AND REPLACE(CAST(kh.tutar AS CHAR), '.', ',') LIKE :f_tutar_like";
                        $bindings[':f_tutar_like'] = '%' . $q . '%';
                    } else {
                        $cmp = '=';
                        if ($op === 'gt') $cmp = '>';
                        elseif ($op === 'gte') $cmp = '>=';
                        elseif ($op === 'lt') $cmp = '<';
                        elseif ($op === 'lte') $cmp = '<=';
                        elseif ($op === 'not_equals') $cmp = '<>';
                        $query .= " AND kh.tutar {$cmp} :f_tutar";
                        $bindings[':f_tutar'] = (float)$val;
                    }
                }
            }
            if (!empty($columnFilters['yuruyen_bakiye'])) {
                $f = $columnFilters['yuruyen_bakiye'];
                $raw = is_array($f) ? ($f['val'] ?? '') : $f;
                $valb = preg_replace('/[^0-9.,-]/', '', (string)$raw);
                $valb = str_replace('.', '', $valb);
                $valb = str_replace(',', '.', $valb);
                $op = is_array($f) ? ($f['op'] ?? 'equals') : 'equals';
                if ($valb !== '' && $op !== 'none' && $op !== 'yok') {
                    if ($op === 'contains') {
                        $q = str_replace('.', ',', (string)$valb);
                        $query .= " AND REPLACE(CAST(kh.yuruyen_bakiye AS CHAR), '.', ',') LIKE :f_bakiye_like";
                        $bindings[':f_bakiye_like'] = '%' . $q . '%';
                    } else {
                        $cmp = '=';
                        if ($op === 'gt') $cmp = '>';
                        elseif ($op === 'gte') $cmp = '>=';
                        elseif ($op === 'lt') $cmp = '<';
                        elseif ($op === 'lte') $cmp = '<=';
                        elseif ($op === 'not_equals') $cmp = '<>';
                        $query .= " AND kh.yuruyen_bakiye {$cmp} :f_bakiye";
                        $bindings[':f_bakiye'] = (float)$valb;
                    }
                }
            }
            if (!empty($columnFilters['kategori'])) {
                $f = $columnFilters['kategori'];
                $val = is_array($f) ? ($f['val'] ?? '') : $f;
                $op = is_array($f) ? ($f['op'] ?? 'contains') : 'contains';
                if ($op === 'none' || $op === 'yok') { }
                elseif ($op === 'starts') { $query .= " AND kh.kategori LIKE :f_kategori"; $bindings[':f_kategori'] = $val . '%'; }
                elseif ($op === 'ends') { $query .= " AND kh.kategori LIKE :f_kategori"; $bindings[':f_kategori'] = '%' . $val; }
                elseif ($op === 'equals') { $query .= " AND kh.kategori = :f_kategori"; $bindings[':f_kategori'] = $val; }
                elseif ($op === 'not_equals') { $query .= " AND (kh.kategori <> :f_kategori OR kh.kategori IS NULL OR kh.kategori = '')"; $bindings[':f_kategori'] = $val; }
                elseif ($op === 'not_contains') { $query .= " AND (kh.kategori NOT LIKE :f_kategori OR kh.kategori IS NULL OR kh.kategori = '')"; $bindings[':f_kategori'] = '%' . $val . '%'; }
                elseif ($op === 'empty' || $op === 'is_empty') { $query .= " AND (kh.kategori IS NULL OR kh.kategori = '')"; }
                else { $query .= " AND kh.kategori LIKE :f_kategori"; $bindings[':f_kategori'] = '%' . $val . '%'; }
            }
            if (!empty($columnFilters['alt_tur'])) {
                $f = $columnFilters['alt_tur'];
                $val = is_array($f) ? ($f['val'] ?? '') : $f;
                $op = is_array($f) ? ($f['op'] ?? 'contains') : 'contains';
                if ($op === 'none' || $op === 'yok') { }
                elseif ($op === 'starts') { $query .= " AND kh.alt_tur LIKE :f_alt_tur"; $bindings[':f_alt_tur'] = $val . '%'; }
                elseif ($op === 'ends') { $query .= " AND kh.alt_tur LIKE :f_alt_tur"; $bindings[':f_alt_tur'] = '%' . $val; }
                elseif ($op === 'equals') { $query .= " AND kh.alt_tur = :f_alt_tur"; $bindings[':f_alt_tur'] = $val; }
                elseif ($op === 'not_equals') { $query .= " AND (kh.alt_tur <> :f_alt_tur OR kh.alt_tur IS NULL OR kh.alt_tur = '')"; $bindings[':f_alt_tur'] = $val; }
                elseif ($op === 'not_contains') { $query .= " AND (kh.alt_tur NOT LIKE :f_alt_tur OR kh.alt_tur IS NULL OR kh.alt_tur = '')"; $bindings[':f_alt_tur'] = '%' . $val . '%'; }
                elseif ($op === 'empty' || $op === 'is_empty') { $query .= " AND (kh.alt_tur IS NULL OR kh.alt_tur = '')"; }
                else { $query .= " AND kh.alt_tur LIKE :f_alt_tur"; $bindings[':f_alt_tur'] = '%' . $val . '%'; }
            }
            if (!empty($columnFilters['makbuz_no'])) {
                $f = $columnFilters['makbuz_no'];
                $val = is_array($f) ? ($f['val'] ?? '') : $f;
                $op = is_array($f) ? ($f['op'] ?? 'contains') : 'contains';
                if ($op === 'none' || $op === 'yok') { }
                elseif ($op === 'starts') { $query .= " AND kh.makbuz_no LIKE :f_makbuz"; $bindings[':f_makbuz'] = $val . '%'; }
                elseif ($op === 'ends') { $query .= " AND kh.makbuz_no LIKE :f_makbuz"; $bindings[':f_makbuz'] = '%' . $val; }
                elseif ($op === 'equals') { $query .= " AND kh.makbuz_no = :f_makbuz"; $bindings[':f_makbuz'] = $val; }
                elseif ($op === 'not_equals') { $query .= " AND (kh.makbuz_no <> :f_makbuz OR kh.makbuz_no IS NULL OR kh.makbuz_no = '')"; $bindings[':f_makbuz'] = $val; }
                elseif ($op === 'not_contains') { $query .= " AND (kh.makbuz_no NOT LIKE :f_makbuz OR kh.makbuz_no IS NULL OR kh.makbuz_no = '')"; $bindings[':f_makbuz'] = '%' . $val . '%'; }
                elseif ($op === 'empty' || $op === 'is_empty') { $query .= " AND (kh.makbuz_no IS NULL OR kh.makbuz_no = '')"; }
                else { $query .= " AND kh.makbuz_no LIKE :f_makbuz"; $bindings[':f_makbuz'] = '%' . $val . '%'; }
            }
            if (!empty($columnFilters['aciklama'])) {
                $f = $columnFilters['aciklama'];
                $val = is_array($f) ? ($f['val'] ?? '') : $f;
                $op = is_array($f) ? ($f['op'] ?? 'contains') : 'contains';
                if ($op === 'none' || $op === 'yok') { }
                elseif ($op === 'starts') { $query .= " AND kh.aciklama LIKE :f_aciklama"; $bindings[':f_aciklama'] = $val . '%'; }
                elseif ($op === 'ends') { $query .= " AND kh.aciklama LIKE :f_aciklama"; $bindings[':f_aciklama'] = '%' . $val; }
                elseif ($op === 'equals') { $query .= " AND kh.aciklama = :f_aciklama"; $bindings[':f_aciklama'] = $val; }
                elseif ($op === 'not_equals') { $query .= " AND (kh.aciklama <> :f_aciklama OR kh.aciklama IS NULL OR kh.aciklama = '')"; $bindings[':f_aciklama'] = $val; }
                elseif ($op === 'not_contains') { $query .= " AND (kh.aciklama NOT LIKE :f_aciklama OR kh.aciklama IS NULL OR kh.aciklama = '')"; $bindings[':f_aciklama'] = '%' . $val . '%'; }
                elseif ($op === 'empty' || $op === 'is_empty') { $query .= " AND (kh.aciklama IS NULL OR kh.aciklama = '')"; }
                else { $query .= " AND kh.aciklama LIKE :f_aciklama"; $bindings[':f_aciklama'] = '%' . $val . '%'; }
            }
        }

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':kasa_id', $kasa_id, \PDO::PARAM_INT);

        if (!empty($searchValue)) {
            $stmt->bindParam(':search', $searchLikeC, \PDO::PARAM_STR);
            if ($searchNumC !== null) { $stmt->bindValue(':searchNum', $searchNumC, \PDO::PARAM_STR); }
            if ($searchDateYmdC !== null) { $stmt->bindValue(':searchDate', $searchDateYmdC, \PDO::PARAM_STR); }
        }
        foreach ($bindings as $key => $val) {
            if (is_float($val) || is_int($val)) {
                $stmt->bindValue($key, $val, \PDO::PARAM_STR);
            } else {
                $stmt->bindValue($key, $val, \PDO::PARAM_STR);
            }
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
    public function excelUpload(string $tmpFilePath, int $siteId, int $kasaId = 0, bool $createMissingDefines = false): array
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

    // Kasa id öncelikle parametre ile alınır, yoksa varsayılan kasa kullanılır
    $kasa_id = $kasaId ?: ($KasaModel->varsayilanKasa()->id ?? 0);

    // Eğer defines ekleme isteniyorsa model kullanımı için örnekle
    $definesModel = new DefinesModel();

    $createdKategoriCount = 0;
    $createdAltTurCount = 0;


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

                // Yeni şablon kolonları:
                // Tarih*, Tutar*, Gelir/Gider*, Kategori, Alt Kategori, Açıklama, Referans Kod

                $tarihRaw = $rowData['Tarih*'] ?? null;
                if (is_string($tarihRaw)) {
                    // Tarih içindeki / karakterlerini . yap (parse için)
                    $tarihRaw = str_replace(['/'], '.', $tarihRaw);
                }
                $tarih          = trim((string)Date::convertExcelDate($tarihRaw, 'Y-m-d H:i:s'));

                $logger->info("Parsed Tarih: ", [$tarih]);

                
                $tutarRaw       = $rowData['Tutar*'] ?? '';
                $islemTipiRaw   = trim((string)($rowData['Gelir/Gider*'] ?? ''));
                $kategoriAdi    = trim((string)($rowData['Kategori'] ?? ''));
                $altTur         = trim((string)($rowData['Alt Kategori'] ?? ''));
                $aciklama       = trim((string)($rowData['Açıklama'] ?? ''));
                $refKod         = trim((string)($rowData['Referans Kod'] ?? ''));

                $logger->info("Satır Verileri", [$tarih, $tutarRaw, $islemTipiRaw, $kategoriAdi, $altTur, $aciklama, $refKod]);

                // Zorunlular: Tarih*, Tutar*, Gelir/Gider*
                if (empty($tarih) || trim((string)$tutarRaw) === '' || empty($islemTipiRaw)) {
                    $errorRows[] = [
                        'row_index' => $row->getRowIndex(),
                        'error_message' =>  "Satır {$row->getRowIndex()}: 'Tarih', 'Tutar' ve 'Gelir/Gider' zorunludur.",
                        'data' => $rowData
                    ];
                    $rows->next();
                    continue;
                }

                // İşlem tipini normalize et
                $tipLower = mb_strtolower($islemTipiRaw, 'UTF-8');
                if ($tipLower === 'gelir') {
                    $islemTipi = 'Gelir';
                } elseif ($tipLower === 'gider') {
                    $islemTipi = 'Gider';
                } else {
                    $errorRows[] = [
                        'row_index' => $row->getRowIndex(),
                        'error_message' =>  "Satır {$row->getRowIndex()}: 'Gelir/Gider*' alanı Gelir veya Gider olmalıdır.",
                        'data' => $rowData
                    ];
                    $rows->next();
                    continue;
                }

                // Tutarı sayıya çevir
                if (is_numeric($tutarRaw)) {
                    $tutar = (float)$tutarRaw;
                } else {
                    $tutar = (float)Helper::formattedMoneyToNumber((string)$tutarRaw);
                }

                if (!is_numeric($tutar) || (float)$tutar == 0.0) {
                    $errorRows[] = [
                        'row_index' => $row->getRowIndex(),
                        'error_message' =>  "Satır {$row->getRowIndex()}: 'Tutar*' geçersiz.",
                        'data' => $rowData
                    ];
                    $rows->next();
                    continue;
                }

                // Gider ise negatife çek
                if ($islemTipi === 'Gider') {
                    $tutar = -abs((float)$tutar);
                } else {
                    $tutar = abs((float)$tutar);
                }


                // Yeni şablonda kişi/daire yok.
                $kisi_id = 0;

                // Eğer createMissingDefines flag aktif ise defines tablosunu güncelle
                if ($createMissingDefines && $kategoriAdi !== '') {
                    $logger->info("Eksik tanım kontrolü yapılıyor: {$createMissingDefines} - {$islemTipi} - {$kategoriAdi} / {$altTur}");
                    $defineType = ($islemTipi === 'Gelir') ? DefinesModel::TYPE_GELIR_TIPI : DefinesModel::TYPE_GIDER_TIPI;
                    $res = $definesModel->ensureGelirGiderDefines($siteId, $defineType, $kategoriAdi, $altTur );
                    if (!empty($res['created_kategori'])) $createdKategoriCount++;
                    if (!empty($res['created_alt_tur'])) $createdAltTurCount++;
                }

           
                // Referans kodu ile dupe kontrolü: aynı site+kasa içinde varsa eklemiyoruz
                if ($refKod !== '') {
                    $stmtDupe = $this->db->prepare("SELECT 1 FROM kasa_hareketleri 
                                                           WHERE site_id = :site_id 
                                                           AND kasa_id = :kasa_id 
                                                           AND ref_kod = :ref_kodu 
                                                           AND tutar = :tutar
                                                           AND silinme_tarihi IS NULL LIMIT 1");
                    $stmtDupe->execute([
                        ':site_id' => $siteId,
                        ':kasa_id' => $kasa_id,
                        ':ref_kodu' => $refKod,
                        ':tutar' => $tutar,
                    ]);
                    if ($stmtDupe->fetchColumn()) {
                        $skippedCount++;
                        $errorRows[] = [
                            'row_index' => $row->getRowIndex(),
                            'error_message' => "Satır {$row->getRowIndex()}: Referans Kod '{$refKod}' zaten kayıtlı olduğu için eklenmedi",
                            'data' => $rowData,
                        ];
                        $rows->next();
                        continue;
                    }
                }

                $payload = [
                    'id' => 0,
                    'site_id' => $siteId,
                    'kasa_id' => $kasa_id,
                    'islem_tarihi' => $tarih,
                    'islem_tipi' => $islemTipi,
                    'kategori' => $kategoriAdi,
                    'alt_tur' => $altTur,
                    'ref_kod' => $refKod,
                    'tutar' => (float)$tutar,
                    'kisi_id' => $kisi_id,
                    'aciklama' => $aciklama,
                    'guncellenebilir' => 1,
                ];

                try {
                    $this->saveWithAttr($payload);
                    $processedCount++;
                } catch (\Exception $e) {
                    $errorRows[] = [
                        'row_index' => $row->getRowIndex(),
                        'error_message' => "Satır {$row->getRowIndex()}: Kayıt hatası - " . $e->getMessage(),
                        'data' => $rowData,
                    ];
                }

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

            $message = "İşlem tamamlandı: <br>{$processedCount} yeni kayıt eklendi.";
            if (!empty($errorRows)) {
                $message .= " " . count($errorRows) . " satırda hata oluştu.";
            }

            /** Kategori eklendiyse mesaja ekle */
            if ($createdKategoriCount > 0) {
                $message .= "<br> {$createdKategoriCount} yeni kategori eklendi.";
            }

            /** Alt tür eklendiyse mesaja ekle */
            if ($createdAltTurCount > 0) {
                $message .= "<br> {$createdAltTurCount} yeni alt tür eklendi.";
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
