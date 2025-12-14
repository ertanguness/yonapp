<?php

namespace Model;

//Model klasoru altında bulunan BaseModel sınıfını dahil ediyoruz
use PDO;
use Model\Model;
use App\Services\Gate;

class SitelerModel extends Model
{
    protected $table = "siteler";

    public function __construct()
    {
        parent::__construct($this->table);
    }
    /**
     * Giriş yapan Kullanıcının sitelerini getirir
     * @return array
     */
    public function Sitelerim()
    {
        $user = $_SESSION['user'];

        // Varsayılan: ana kullanıcının kendi siteleri
        $ownerId = $user->id;

        /**
         * Alt kullanıcı ise:
         *  - owner_id üzerinden ana kullanıcıyı bul
         *  - siteler_ids alanı doluysa, sadece o ID'lerdeki siteleri getir
         */
        $isSubUser = !empty($user->owner_id) && $user->owner_id > 0;

        if ($isSubUser) {
            $ownerId = $user->owner_id;

            // siteler_ids JSON/string alanını güvenli şekilde diziye çevir
            $siteIds = [];
            if (!empty($user->siteler_ids)) {
                if (is_string($user->siteler_ids)) {
                    $decoded = json_decode($user->siteler_ids, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $siteIds = $decoded;
                    }
                } elseif (is_array($user->siteler_ids)) {
                    $siteIds = $user->siteler_ids;
                }
            }

            // Eğer alt kullanıcının atanmış site listesi varsa, sadece o siteleri getir
            if (!empty($siteIds)) {
                // Sadece sayısal ID'leri filtrele
                $siteIds = array_values(array_filter($siteIds, static function ($v) {
                    return $v !== null && $v !== '' && is_numeric($v);
                }));

                if (!empty($siteIds)) {
                    $placeholders = implode(',', array_fill(0, count($siteIds), '?'));
                    $sql = $this->db->prepare("SELECT * FROM $this->table 
                                                WHERE user_id = ?
                                                  AND silinme_tarihi IS NULL
                                                  AND id IN ($placeholders)
                                                ORDER BY favori_mi DESC, click_count DESC, aktif_mi DESC, site_adi ASC");

                    $params = array_merge([$ownerId], $siteIds);
                    $sql->execute($params);
                    return $sql->fetchAll(PDO::FETCH_OBJ);
                }
            }
        }

        // Alt kullanıcı değilse ya da siteler_ids boşsa: tüm siteleri getir
        $sql = $this->db->prepare("SELECT * FROM $this->table 
                                            WHERE user_id = ? 
                                              AND silinme_tarihi IS NULL
                                            ORDER BY favori_mi DESC, click_count DESC, aktif_mi DESC, site_adi ASC");
        $sql->execute([$ownerId]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }


  

    public function SiteBilgileri($id)
    {
        $query = $this->db->prepare("SELECT * FROM $this->table WHERE id = ?");
        $query->execute([$id]);
        $result = $query->fetch(PDO::FETCH_OBJ);
        return $result;
    }

    public function getFavorites()
    {
        $user_id = $_SESSION['user']->id;
        $isSubUser = $_SESSION['user']->owner_id > 0 ? true : false;
        if ($isSubUser) {
            $user_id = $_SESSION['user']->owner_id;
        }
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE user_id = ? AND favori_mi = 1 ORDER BY click_count DESC, site_adi ASC");
        $sql->execute([$user_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    public function getAllWithOwners()
    {
        $sql = $this->db->prepare("
            SELECT s.*, u.full_name AS owner_name, u.phone AS owner_phone, u.email AS owner_email
            FROM $this->table s
            LEFT JOIN users u ON s.yonetici_id = u.id
            WHERE s.silinme_tarihi IS NULL
            ORDER BY s.favori_mi DESC, s.click_count DESC, s.aktif_mi DESC, s.site_adi ASC
        ");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    public function getAllWithCreatorsAndCounts()
    {
        $sql = $this->db->prepare("
            SELECT s.*,
                   CASE WHEN cu.full_name IS NULL OR cu.full_name = '' THEN cu.email ELSE cu.full_name END AS creator_name,
                   cu.phone AS creator_phone,
                   cu.email AS creator_email,
                   (
                     SELECT COUNT(*) 
                     FROM {$this->table} sc 
                     WHERE COALESCE(NULLIF(sc.olusturan_kullanici,0), sc.user_id) = COALESCE(NULLIF(s.olusturan_kullanici,0), s.user_id)
                       AND sc.silinme_tarihi IS NULL
                       AND sc.aktif_mi = 1
                   ) AS creator_site_count
            FROM {$this->table} s
            LEFT JOIN users cu ON cu.id = s.user_id
            WHERE s.silinme_tarihi IS NULL
            ORDER BY s.favori_mi DESC, s.click_count DESC, s.aktif_mi DESC, s.site_adi ASC
        ");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    public function incrementClickCount($id)
    {
        $stmt = $this->db->prepare("UPDATE $this->table SET click_count = click_count + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getCreatorSitesWithApartmentCount(int $creatorId): array
    {
        $sql = $this->db->prepare("
            SELECT s.id,
                   s.site_adi,
                   s.il,
                   s.ilce,
                   s.telefon,
                   s.eposta,
                   s.tam_adres,
                   s.kayit_tarihi,
                   s.aktif_mi,
                   (SELECT COUNT(*) FROM daireler d WHERE d.site_id = s.id) AS apartment_count
            FROM {$this->table} s
            WHERE s.user_id = ?
              AND s.silinme_tarihi IS NULL
              AND s.aktif_mi = 1
            ORDER BY s.kayit_tarihi DESC, s.site_adi ASC
        ");
        $sql->execute([$creatorId]);
        return $sql->fetchAll(PDO::FETCH_OBJ) ?: [];
    }

    public function getCreatorsSummary(): array
    {
        $sql = $this->db->prepare("
            SELECT 
                u.id AS user_id,
                CASE WHEN u.full_name IS NULL OR u.full_name = '' THEN u.email ELSE u.full_name END AS creator_name,
                u.phone AS creator_phone,
                u.email AS creator_email,
                COUNT(s.id) AS site_count
            FROM users u
            JOIN {$this->table} s 
              ON s.user_id = u.id 
             AND s.silinme_tarihi IS NULL 
             AND s.aktif_mi = 1
            GROUP BY u.id, u.full_name, u.email, u.phone
            ORDER BY site_count DESC, creator_name ASC
        ");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_OBJ) ?: [];
    }

    public function setFavorite($id, $isFavorite)
    {
        $stmt = $this->db->prepare("UPDATE $this->table SET favori_mi = ? WHERE id = ?");
        return $stmt->execute([intval($isFavorite) ? 1 : 0, $id]);
    }

    public function siteSonID()
    {
        $query = $this->db->query("SHOW TABLE STATUS LIKE '$this->table'");
        $result = $query->fetch(PDO::FETCH_OBJ);
        return $result ? $result->Auto_increment : null;
    }
}
