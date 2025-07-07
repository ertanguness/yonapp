<?php

namespace Model;

use Model\Model;
use App\Helper\Helper;
use PDO;

class PermissionsModel extends Model
{
    protected $table = 'permissions';

    public function __construct()
    {
        parent::__construct($this->table);
    }

    /**
     * Veritabanındaki tüm izinleri alır ve istenen gruplanmış/hiyerarşik yapıya dönüştürür.
     * Bu ana metot, tüm süreci yönetir.
     *
     * @return array
     */
    public function getGroupedPermissions(): array
    {


        // 1. Adım: Tüm izinleri veritabanından tek bir sorguyla al.
        $flatPermissions = $this->fetchAllPermissionsFromDb();

        if (empty($flatPermissions)) {
            return [];
        }

        // 2. Adım: Alınan düz veriyi hiyerarşik yapıya dönüştür.
        $groupedPermissions = [];
        $groupIndexMap = []; // Hangi grubun hangi index'te olduğunu tutan performans haritası
        $nextGroupId = 1;

        // İyileştirilmiş $iconMap
        $iconMap = [
            'Ana Sayfa'                         => 'home',
            'Üye Yönetimi'                      => 'users',
            'Aidat/Tahsilat Yönetimi'           => 'git-branch',
            'Finans Yönetimi'                   => 'credit-card',
            'Site Yönetimi'                     => 'server',
            'default'                           => 'settings' // Veya 'grid', 'circle' gibi genel bir ikon
        ];

        foreach ($flatPermissions as $permission) {
            $groupName = !empty($permission->group_name) ? $permission->group_name : 'Genel İzinler';

            // Eğer bu grup daha önce işlenmediyse, ana grup yapısını oluştur.
            if (!isset($groupIndexMap[$groupName])) {
                $groupIndexMap[$groupName] = count($groupedPermissions); // Yeni grubun index'ini kaydet
                $groupedPermissions[] = [
                    'id'      => $nextGroupId++,
                    'name'    => $groupName,
                    'icon'    => $iconMap[$groupName] ?? $iconMap['default'],
                    'group'   => $this->slugify($groupName),
                    'permissions' => []
                ];
            }

            // Mevcut izni, doğru grubun 'permissions' dizisine ekle.
            $index = $groupIndexMap[$groupName];
            $groupedPermissions[$index]['permissions'][] = [
                'id'          => (int)$permission->id,
                'name'        => $permission->title,
                'description' => $permission->description ?? '',
                'level'       => (int)($permission->permission_level ?? 1), // Veritabanından gelen değeri kullan, yoksa 1 olsun
                'required'    => (int)($permission->is_required ?? 0)      // Veritabanından gelen değeri kullan, yoksa 0 olsun
            ];
        }

        return $groupedPermissions;
    }

    /**
     * Veritabanından tüm izinleri tek bir sorguyla çeker.
     * Gruplama mantığının doğru çalışması için group_name'e göre sıralamak önemlidir.
     * @return array
     */
    private function fetchAllPermissionsFromDb(): array
    {
        $sql = "SELECT id, title, description, group_name, permission_level, is_required 
                FROM {$this->table}
                ORDER BY group_name, id";
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Bir metni URL dostu bir "slug" formatına dönüştürür.
     * @param string $text
     * @return string
     */
    private function slugify(string $text): string
    {
        $text = str_replace(
            ['ı', 'ğ', 'ü', 'ş', 'ö', 'ç', 'İ', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç'],
            ['i', 'g', 'u', 's', 'o', 'c', 'i', 'g', 'u', 's', 'o', 'c'],
            $text
        );
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = strtolower($text);
        if (empty($text)) {
            return 'n-a';
        }
        return $text;
    }

    /**
     * Sayfa adından sayfa ID'sini alır. (Mevcut metodunuz)
     * @param string $pageName Sayfa adı
     * @return int|null
     */
    public function getPageIDByName($pageName): ?int
    {
        $sql = $this->db->prepare("SELECT id FROM $this->table WHERE name = ?");
        $sql->execute([$pageName]);
        $result = $sql->fetchColumn();
        return $result !== false ? (int)$result : null;
    }



    
    /**
     * Bir kullanıcının ID'sine göre, rolü üzerinden sahip olduğu tüm izinlerin adlarını
     * içeren düz bir dizi döndürür.
     * 
     * @param int $userId
     * @return array Örnek: ['kullanici_listele', 'kullanici_ekle', 'rapor_goruntule']
     */
    public function getPermissionsForUser(int $userId): array
    {
        // 1. Kullanıcının rol ID'sini al.
        // Eğer getUserRoleID metodu zaten varsa, onu kullanın.
        // Yoksa aşağıdaki gibi bir sorgu yazılabilir.
        $stmt = $this->db->prepare("SELECT roles FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $roleId = $stmt->fetchColumn();

        if (empty($roleId)) {
            return []; // Rolü olmayan kullanıcının izni yoktur.
        }

        // 2. Rol ID'sine göre tüm izin adlarını çek.
        // Gerekli tablolar: user_role_permissions (ara tablo), permissions (izinlerin adları)
        $sql = "SELECT p.auth_name
            FROM user_role_permissions urp
            JOIN permissions p ON urp.permission_id = p.id
            WHERE urp.role_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roleId]);

        // fetchAll(PDO::FETCH_COLUMN) sadece 'permission_name' sütununu içeren
        // ['izin1', 'izin2', ...] şeklinde düz bir dizi döndürür.
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
