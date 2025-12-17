<?php
namespace Model;

use App\Helper\Helper;
use Model\Model;
use PDO;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class UserRolesModel extends Model
{
    protected $table = 'user_roles';



    public function __construct()
    {
        parent::__construct($this->table);
    }

    /**
     * Veri Sahibine id'sine göre kullanıcı gruplarını listelemek için gerekli verileri getirir.
     * Kayıt esnasında oluşturulan ana kullanıcı listelenmez
     * @return array
     */
    public function getUserGroups(): array
    {
        // Session güvenliği ve veri doğrulama
        $ownerID = isset($_SESSION["owner_id"]) ? (int) $_SESSION["owner_id"] : 0;

        // Kullanıcı ve rol kontrolü
        $user = $_SESSION["user"] ?? null;
        $superAdmin = ($user && isset($user->roles) && (int) $user->roles === 10);

        $params = [];

        if ($superAdmin) {
            // Süper admin ise sadece ana rolleri (main_role = 1) ve owner_id'si 0 veya NULL olanları getir
            $whereClause = "(ur.owner_id = :owner_id OR ur.owner_id IS NULL) AND ur.main_role = :main_role";
            $params['owner_id'] = 0;
            $params['main_role'] = 1;
        } else {
            // Normal kullanıcı ise sadece kendi owner_id'sine ait ve ana rol olmayanları (main_role = 0) getir
            $whereClause = "ur.owner_id = :owner_id AND ur.main_role = :main_role";
            $params['owner_id'] = $ownerID;
            $params['main_role'] = 0;
        }

        $sql = $this->db->prepare("SELECT 
                                            ur.id,
                                            ur.owner_id,
                                            ur.role_name,
                                            ur.description,

                                            -- toplam permission sayısı (sadece 1 kez hesaplanır)
                                            p.total_permission_count AS toplam_yetki_sayisi,

                                            -- bu role ait permission sayısı
                                            COUNT(urp.id) AS yetki_sayisi

                                        FROM user_roles ur

                                        -- Role -> Permission bağları
                                        LEFT JOIN user_role_permissions urp 
                                            ON ur.id = urp.role_id

                                        -- toplam permission sayısını tek seferde getiren alt tablo
                                        LEFT JOIN (
                                            SELECT COUNT(id) AS total_permission_count 
                                            FROM permissions
                                        ) p ON 1=1

                                        WHERE {$whereClause}
                                        GROUP BY ur.id
                                        ORDER BY ur.id DESC;");
        $sql->execute($params);

        return $sql->fetchAll(PDO::FETCH_OBJ) ?? [];
    }

    public function getGroupsOptions(): array
    {
        $groups = $this->getUserGroups();
        $options = [];

        // Add empty option first
        $options[0] = (object) [
            'id' => 0,
            'role_name' => 'Seçiniz'
        ];

        foreach ($groups as $group) {
            $options[$group->id] = (object) [
                'id' => $group->id,
                'role_name' => $group->role_name
            ];
        }

        return $options;
    }

    /** Bu Adla kaydedilmiş kullanıcı grubu var mı? */
    public function roleExists(string $roleName): bool
    {
        $ownerID = isset($_SESSION["owner_id"]) ? (int) $_SESSION["owner_id"] : 0;

        $sql = $this->db->prepare("SELECT COUNT(*) FROM user_roles WHERE owner_id = :owner_id AND role_name = :role_name");
        $sql->execute([
            'owner_id' => $ownerID,
            'role_name' => $roleName
        ]);

        return $sql->fetchColumn() > 0;
    }



}
?>