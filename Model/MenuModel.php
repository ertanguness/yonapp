<?php

namespace Model;

use Model\Model; 
use Model\UserModel;
use PDO;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class MenuModel extends Model
{
    protected $table = 'menu';

    // Cache ayarları
    private string $baseCacheDir; // Ana cache dizini
    private int $cacheLifetime = 3600; // Saniye cinsinden (1 saat)

    private int $ownerId; // Mevcut kiracının (owner) ID'si
    private string $ownerSpecificCacheDir; // Kiracıya özel cache alt dizini

    public function __construct()
    {
        parent::__construct($this->table);

        // Doğru baseCacheDir tanımı:
        // __DIR__ şu anki dosyanın (MenuModel.php) dizinidir: C:\xampp\htdocs\cansen\admin\App\Model
        $this->baseCacheDir = dirname(__DIR__, 1) . '/cache';

        $this->ownerId = isset($_SESSION['owner_id']) ? (int)$_SESSION['owner_id'] : 0; // Kiracı ID'si, oturumdan alınır. Eğer oturumda yoksa 0 olarak ayarlanır.

        if ($this->ownerId !== 0) {
            // $this->baseCacheDir şimdi doğru yolu (C:\xampp\htdocs\cansen\admin/cache) göstermeli
            $this->ownerSpecificCacheDir = $this->baseCacheDir . '/tenant_' . $this->ownerId;

            if (!is_dir($this->ownerSpecificCacheDir)) {
                // Hata kontrolü eklenebilir (örneğin loglama)
                if (!mkdir($this->ownerSpecificCacheDir, 0775, true) && !is_dir($this->ownerSpecificCacheDir)) {
                    // Dizin oluşturulamadıysa logla veya bir istisna fırlat
                    error_log("Failed to create directory: " . $this->ownerSpecificCacheDir);
                    $this->ownerSpecificCacheDir = ''; // Hata durumunda cache dizinini geçersiz kıl
                }
            }
        }
        // ownerId 0 ise veya dizin oluşturulamadıysa $this->ownerSpecificCacheDir boş kalır.
    }
public function getHierarchicalMenuForRole(int $user_id): array
    {
        // Geliştirme sırasında cache'i temizlemek için bu satırı geçici olarak açabilirsiniz.
        // Production'da MUTLAKA kapalı olmalı!
         $this->clearAllMenuCachesForCurrentTenant();

        if (empty($this->ownerSpecificCacheDir) || !is_dir($this->ownerSpecificCacheDir)) {
            // Cache dizini yoksa veya geçersizse, cache'lemeden devam et.
            return $this->fetchAndBuildMenuFromDb($user_id);
        }

        $Users = new UserModel();
        $roleId = $Users->getUserRoleID($user_id);
        if ($roleId === 0 || $roleId === null) {
            return [];
        }

        $cacheFile = $this->ownerSpecificCacheDir . "/menu_role_{$roleId}.cache";

        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $this->cacheLifetime)) {
            $cachedData = @file_get_contents($cacheFile);
            if ($cachedData) {
                $unserializedData = @unserialize($cachedData);
                if ($unserializedData !== false) {
                    return $unserializedData; // Cache'den veriyi döndür
                }
                @unlink($cacheFile); // Bozuk cache dosyasını sil
            }
        }

        $menuData = $this->fetchAndBuildMenuFromDb($user_id, $roleId);

        if (is_writable($this->ownerSpecificCacheDir)) {
            @file_put_contents($cacheFile, serialize($menuData));
        }

        return $menuData;
    }

    /**
     * Veritabanından menüyü çeker ve hiyerarşik bir yapı oluşturur.
     * Bu fonksiyon, menu.permission_id'ye göre yetkilendirme yapar.
     */
    private function fetchAndBuildMenuFromDb(int $user_id, ?int $roleId = null): array
    {
        if ($roleId === null) {
            $Users = new UserModel();
            $roleId = $Users->getUserRoleID($user_id);
        }

        if (empty($roleId)) {
            return []; // Geçerli rol yoksa boş menü.
        }

        // --- 1. ADIM: Kullanıcının rolünün sahip olduğu TÜM izin ID'lerini al ---
        $stmt = $this->db->prepare("SELECT permission_id FROM user_role_permissions WHERE role_id = ?");
        $stmt->execute([$roleId]);
        $rolePermissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Eğer rolün hiçbir izni yoksa, sadece izin gerektirmeyen (herkese açık) menüleri gösterebilir.
        // Bu yüzden burada boş dizi döndürmüyoruz.

        // --- 2. ADIM: Gösterilecek menülerin ID'lerini bul ---
        // Koşullar:
        //   a) Menünün bir izni yoksa (permission_id IS NULL) -> Her zaman göster.
        //   b) Menünün izni varsa (permission_id), bu iznin kullanıcının rol izinleri ($rolePermissions) içinde olup olmadığını kontrol et.
        
        $sql = "SELECT id FROM {$this->table} WHERE is_active = 1 AND isMenu = 1 and (permission_id IS NULL OR permission_id = 0";

        if (!empty($rolePermissions)) {
            $placeholders = implode(',', array_fill(0, count($rolePermissions), '?'));
            $sql .= " OR permission_id IN ({$placeholders}))";
            $params = $rolePermissions;
        } else {
            $sql .= ")";
            $params = [];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $permittedMenuIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($permittedMenuIds)) {
            return []; // Hiçbir menüye izni yoksa boş dizi döndür.
        }

        // --- 3. ADIM: Üst menüleri de dahil et ---
        // Bu adım, kullanıcının sadece bir alt menüye izni olsa bile, o alt menünün üst başlıklarının da görünmesini sağlar.
        $allRequiredMenuIds = $this->fetchParentMenuIds($permittedMenuIds);
        
        if (empty($allRequiredMenuIds)) {
            return [];
        }

        // --- 4. ADIM: Gerekli tüm menülerin tam verilerini çek ---
        $placeholders = implode(',', array_fill(0, count($allRequiredMenuIds), '?'));
        $sql = "SELECT * FROM {$this->table}
                WHERE id IN ({$placeholders})
                ORDER BY group_order, menu_order"; // is_active kontrolü 2. adımda yapıldığı için burada tekrar gerekmez.
      
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($allRequiredMenuIds));
        $accessibleMenus = $stmt->fetchAll(PDO::FETCH_OBJ);

        // --- 5. ADIM: Düz listeyi hiyerarşik bir ağaca dönüştür ---
        return $this->buildMenuTree($accessibleMenus);
    }


    /**
     * Verilen menü ID'lerinin tüm üst (parent) menülerinin ID'lerini de bularak listeyi tamamlar.
     * Bu fonksiyon, N+1 problemine yol açmamak için döngüsel olarak çalışır.
     */
    private function fetchParentMenuIds(array $menuIds): array
    {
        if (empty($menuIds)) {
            return [];
        }
        $allIds = array_unique(array_values($menuIds));
        $idsToCheck = $allIds;

        while (!empty($idsToCheck)) {
            $placeholders = implode(',', array_fill(0, count($idsToCheck), '?'));
            $stmt = $this->db->prepare("SELECT DISTINCT parent_id FROM {$this->table} WHERE id IN ({$placeholders}) AND parent_id != 0");
            $stmt->execute($idsToCheck);
            $parentIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Henüz ana listemizde olmayan yeni üst menü ID'lerini bul
            $newParentIds = array_diff($parentIds, $allIds);

            if (empty($newParentIds)) {
                break; // Yeni eklenecek üst menü kalmadıysa döngüyü sonlandır.
            }

            // Yeni bulunan üst menüleri ana listeye ve bir sonraki turda kontrol edilecekler listesine ekle
            $allIds = array_merge($allIds, $newParentIds);
            $idsToCheck = array_values($newParentIds);
        }

        return $allIds;
    }

    // Mevcut kiracının TÜM menü önbelleklerini temizler
    public function clearAllMenuCachesForCurrentTenant(): void
    {
        if ($this->ownerId === 0 || empty($this->ownerSpecificCacheDir) || !is_dir($this->ownerSpecificCacheDir)) {
            return;
        }

        $pattern = $this->ownerSpecificCacheDir . '/menu_role_*.cache';
        $files = glob($pattern);

        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file); // Hata kontrolü eklenebilir
            }
        }
    }

    // Mevcut kiracının belirli bir rolünün menü önbelleğini temizler
    public function clearMenuCacheForRole(int $roleId): void
    {
        if ($this->ownerId === 0 || empty($this->ownerSpecificCacheDir) || !is_dir($this->ownerSpecificCacheDir)) {

            return;
        }

        $cacheFile = $this->ownerSpecificCacheDir . '/menu_role_' . $roleId . '.cache';
        if (is_file($cacheFile)) {
            @unlink($cacheFile);

            if (!@unlink($cacheFile)) {
                error_log("Failed to delete cache file: {$cacheFile}");
            }
        }
    }

    private function buildMenuTree(array $items): array
    {
        $structuredMenu = [];
        $itemsById = [];

        foreach ($items as $item) {
            $item->children = []; // Her öğeye çocuk dizisi ekle
            $itemsById[$item->id] = $item;
        }

        foreach ($items as $item) {
            if ($item->parent_id != 0 && isset($itemsById[$item->parent_id])) {
                $itemsById[$item->parent_id]->children[] = $itemsById[$item->id]; // Doğru referansla ekle
            } else {
                // Grup adına göre gruplandırma
                if (!isset($structuredMenu[$item->group_name])) {
                    $structuredMenu[$item->group_name] = [];
                }
                $structuredMenu[$item->group_name][] = $itemsById[$item->id];
            }
        }

        //echo Helper::dd($structuredMenu);
        return $structuredMenu;
    }
}
