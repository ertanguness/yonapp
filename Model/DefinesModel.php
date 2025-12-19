<?php

namespace Model;

use App\Helper\Helper;
use Model\Model;
use PDO;

class DefinesModel extends Model
{
    protected $table = 'defines';

    public function __construct()
    {
        parent::__construct($this->table);
    }

    const TYPE_DAIRE_TIPI = 3;

    const TYPE_GELIR_TIPI = 6;
    const TYPE_GIDER_TIPI = 7;


    /**Gelir Gider Tiplerini getirir
     * @return array
     */
    public function getGelirGiderTipleri(bool $groupByDefineName = false)
    {
            $site_id = $_SESSION['site_id']; // aktif site ID’sini alıyoruz
            $gelirTipi = self::TYPE_GELIR_TIPI;
            $giderTipi = self::TYPE_GIDER_TIPI;
            $groupSql = $groupByDefineName ? ' GROUP BY d.define_name,type ' : '';
            $sql = $this->db->prepare("SELECT d.*,
                                                case 
                                                    when d.type = :gelirTipi then 'Gelir'
                                                    when d.type = :giderTipi then 'Gider'
                                                    else 'Diğer'    
                                                end as type_name
                                            FROM $this->table d
                                            WHERE site_id = :site_id 
                                            AND type IN (:gelirTipi, :giderTipi)
                                            AND silinme_tarihi IS NULL 
                                            {$groupSql}
                                            ORDER BY define_name ASC");
            $sql->execute([
                ':site_id' => $site_id,
                ':gelirTipi' => $gelirTipi,
                ':giderTipi' => $giderTipi
            ]);
            return $sql->fetchAll(PDO::FETCH_OBJ);
    }


    /** Gelir veya gider kategorilerini getirir
     * @param mixed $type
     * @return array
     */
    public function getGelirGiderKategorileri($type, bool $groupByDefineName = false)
    {
        $site_id = $_SESSION['site_id']; // aktif site ID’sini alıyoruz
        $groupSql = $groupByDefineName ? ' GROUP BY define_name ' : '';
        $sql = $this->db->prepare("SELECT * FROM $this->table 
                                          WHERE site_id = ? AND type = ? 
                                          AND silinme_tarihi IS NULL 
                                          {$groupSql}
                                          ORDER BY define_name ASC");
        $sql->execute([
            $site_id,
            $type
        ]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }


    /** Gelir Gider Tipini getirir
     * @param mixed $id
     * @return object|null
     */
    public function getGelirGiderTipi($id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE id = ? AND silinme_tarihi IS NULL");
        $sql->execute([$id]);
        return $sql->fetch(PDO::FETCH_OBJ); // sadece bir satır bekleniyorsa
    }

    /**sitenin gelir grublarını select olarak al */
    public function getGelirGrubuSelect($name, $selected){

        $site_id = $_SESSION['site_id']; // aktif site ID’sini alıyoruz
        $sql = $this->db->prepare("SELECT id, define_name, islem_kodu FROM $this->table 
                                    WHERE type = :type 
                                    AND site_id = :site_id 
                                    AND silinme_tarihi IS NULL");
        $sql->execute([
            ':type' => self::TYPE_GELIR_TIPI,
            ':site_id' => $site_id
        ]);
        $options = '';

        while ($row = $sql->fetch(PDO::FETCH_ASSOC)) {
            $defineName = htmlspecialchars((string)($row['define_name'] ?? ''), ENT_QUOTES, 'UTF-8');
            $islemKodu = htmlspecialchars((string)($row['islem_kodu'] ?? ''), ENT_QUOTES, 'UTF-8');
            $isSelected = ((string)$selected === (string)($row['define_name'] ?? '')) ? 'selected' : '';

            $options .= "<option data-islem-kodu=\"{$islemKodu}\" value=\"{$defineName}\" {$isSelected}>{$defineName}</option>";
        }
        return "<select name=\"{$name}\" id=\"{$name}\" class=\"form-select select2\">{$options}</select>";
    }





    /** Gelir veya gider tiplerini select için getirir
     * @param mixed $type
     * @return string
     */
    public function getGelirGiderTipiSelect($name, int $type, $selected)
    {
        $tipler = $this->getGelirGiderTipleri(true);
        $options = '';
      
        foreach ($tipler as $tip) {
            if ($tip->type == $type) {
                $isSelected = ($tip->define_name == $selected) ? 'selected' : '';
                $options .= "<option value=\"{$tip->id}\" {$isSelected}>{$tip->define_name}</option>";
            }
     
        }
        
        return "<select name=\"{$name}\" id=\"{$name}\" class=\"form-select select2\">{$options}</select>";
    }


    /**
     * Gelir-gider kalemlerini (alt_tur) gelen siteId + type + define_name’e göre getirir.
     *
     * Not: Session’dan varsayılan site seçmek yerine siteId parametresi zorunlu tutulur.
     */
    public function getGelirGiderKalemleri(int $siteId, int $type, string $define_name): array
    {
        $sql = $this->db->prepare("SELECT d.*
                                          FROM $this->table d
                                          WHERE site_id = :site_id 
                                            AND type = :type 
                                            AND define_name = :define_name
                                            AND silinme_tarihi IS NULL
                                            AND alt_tur IS NOT NULL AND alt_tur != ''
                                          GROUP BY define_name, alt_tur
                                          ORDER BY alt_tur ASC");
        $sql->execute([
            ':site_id' => $siteId,
            ':type' => $type,
            ':define_name' => $define_name
        ]);
        return $sql->fetchAll(PDO::FETCH_OBJ);

    }

    public function daireTipiGetir($type)
    {
        $site_id = $_SESSION['site_id']; // aktif site ID’sini alıyoruz
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE site_id = ? and id = ?");
        $sql->execute([$site_id, $type]);
        return $sql->fetch(PDO::FETCH_OBJ); // sadece bir satır bekleniyorsa
    }

    public function isApartmentTypeNameExists($site_id, $name)
    {
        $sql = $this->db->prepare("SELECT COUNT(*) FROM defines WHERE site_id = :site_id AND define_name = :name");
        $sql->execute([':site_id' => $site_id, ':name' => $name]);
        return $sql->fetchColumn() > 0;
    }

    public function getAllByApartmentType($type)
    {
        $site_id = $_SESSION['site_id']; // aktif site ID’sini alıyoruz
        $sql = "SELECT * FROM defines WHERE type = :type AND site_id = :site_id ORDER BY define_name DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'type' => $type,
            'site_id' => $site_id
        ]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /* Belirli bir site ve tip için tanımları getirir.
     * @param int $siteId
     * @param int $type
     * @return array
     */
    public function getDefinesTypes($siteId, $type)
    {
        $sql = "SELECT * FROM defines WHERE site_id = :site_id AND type = :type ORDER BY define_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':site_id', $siteId, PDO::PARAM_INT);
        $stmt->bindParam(':type', $type, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }


    /**
     * Site+type+kategori için ana kategori define kaydı var mı?
     * Not: Aynı isimli birden fazla kayıt olabildiği için en küçük id'yi döndürür.
     */
    public function findGelirGiderKategoriId(int $siteId, int $type, string $kategoriAdi): ?int
    {
        $kategoriAdi = trim($kategoriAdi);
        if ($kategoriAdi === '') {
            return null;
        }

        $sql = $this->db->prepare("SELECT MIN(id) AS id
            FROM {$this->table}
            WHERE site_id = :site_id
              AND type = :type
              AND define_name = :define_name
              AND silinme_tarihi IS NULL
              AND (alt_tur IS NULL OR alt_tur = '')");
        $sql->execute([
            ':site_id' => $siteId,
            ':type' => $type,
            ':define_name' => $kategoriAdi,
        ]);
        $row = $sql->fetch(PDO::FETCH_OBJ);
        return !empty($row?->id) ? (int)$row->id : null;
    }

    /**
     * Site+type+kategori+alt_tur için alt kalem kaydı var mı?
     * Not: Aynı alt_tur tekrar ediyorsa en küçük id'yi döndürür.
     */
    public function findGelirGiderAltTurId(int $siteId, int $type, string $kategoriAdi, string $altTur): ?int
    {
        $kategoriAdi = trim($kategoriAdi);
        $altTur = trim($altTur);
        if ($kategoriAdi === '' || $altTur === '') {
            return null;
        }

        $sql = $this->db->prepare("SELECT MIN(id) AS id
            FROM {$this->table}
            WHERE site_id = :site_id
              AND type = :type
              AND define_name = :define_name
              AND alt_tur = :alt_tur
              AND silinme_tarihi IS NULL");
        $sql->execute([
            ':site_id' => $siteId,
            ':type' => $type,
            ':define_name' => $kategoriAdi,
            ':alt_tur' => $altTur,
        ]);
        $row = $sql->fetch(PDO::FETCH_OBJ);
        return !empty($row?->id) ? (int)$row->id : null;
    }

    /**
     * Gelir/Gider için kategori + alt_tur tanımlarını (yoksa) oluşturur.
     * - Kategori satırı: alt_tur NULL/''
     * - Alt tur satırı: alt_tur dolu
     */
    public function ensureGelirGiderDefines(int $siteId, int $type, string $kategoriAdi, ?string $altTur = null): array
    {
        $kategoriAdi = trim($kategoriAdi);
        $altTur = $altTur !== null ? trim($altTur) : null;

        if (!in_array($type, [self::TYPE_GELIR_TIPI, self::TYPE_GIDER_TIPI], true)) {
            throw new \InvalidArgumentException('Geçersiz gelir/gider type');
        }
        if ($siteId <= 0) {
            throw new \InvalidArgumentException('Geçersiz site_id');
        }
        if ($kategoriAdi === '') {
            throw new \InvalidArgumentException('Kategori boş olamaz');
        }

        $createdKategori = false;
        $createdAltTur = false;

        $kategoriId = $this->findGelirGiderKategoriId($siteId, $type, $kategoriAdi);
        if (!$kategoriId) {
            $stmt = $this->db->prepare("INSERT INTO {$this->table} (site_id, type, define_name, alt_tur, created_at)
                VALUES (:site_id, :type, :define_name, NULL, NOW())");
            $stmt->execute([
                ':site_id' => $siteId,
                ':type' => $type,
                ':define_name' => $kategoriAdi,
            ]);
            $createdKategori = true;
            $kategoriId = (int)$this->db->lastInsertId();
        }

        $altTurId = null;
        if ($altTur !== null && $altTur !== '') {
            $altTurId = $this->findGelirGiderAltTurId($siteId, $type, $kategoriAdi, $altTur);
            if (!$altTurId) {
                $stmt = $this->db->prepare("INSERT INTO {$this->table} (site_id, type, define_name, alt_tur, created_at)
                    VALUES (:site_id, :type, :define_name, :alt_tur, NOW())");
                $stmt->execute([
                    ':site_id' => $siteId,
                    ':type' => $type,
                    ':define_name' => $kategoriAdi,
                    ':alt_tur' => $altTur,
                ]);
                $createdAltTur = true;
                $altTurId = (int)$this->db->lastInsertId();
            }
        }

        return [
            'kategori_id' => $kategoriId,
            'alt_tur_id' => $altTurId,
            'created_kategori' => $createdKategori,
            'created_alt_tur' => $createdAltTur,
        ];
    }


    /**
     * Gelen Daire tipinden id'yi döndürürür.
     * @param mixed $site_id
     * @param mixed $type
     * @return int|null
     */
    public function getApartmentTypeIdByName($site_id, $type, $name,$mulk_tipi)
    {
        $sql = $this->db->prepare("SELECT id FROM defines 
                                          WHERE site_id = ? 
                                          AND type = ? 
                                          AND define_name = ? 
                                          AND mulk_tipi = ?
                                          LIMIT 1");
        $sql->execute([
            $site_id,
            $type,
            $name,
            $mulk_tipi
        ]);
        $result = $sql->fetch(PDO::FETCH_OBJ);

        return $result ? (int)$result->id : null; // Eğer sonuç varsa ID'yi döndür, yoksa null döndür
    }
}
