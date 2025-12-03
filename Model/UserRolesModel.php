<?php 
namespace Model;

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
     * @param int $owner_id Verinin sahibi ID'si(Session ID'si gibi)
     * @return array
     */
    public function getUserGroups(): array
    {
        $ownerID = $_SESSION["owner_id"];

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

                                        WHERE ur.owner_id = :owner_id
                                            -- AND ur.main_role != 1  -- Ana kullanıcı rolü hariç tutulur
                                        GROUP BY ur.id
                                        ORDER BY ur.id DESC;");
        $sql->execute([
            'owner_id' => $ownerID
        ]);

        return $sql->fetchAll(PDO::FETCH_OBJ) ?? [];
    }

    /**
     * Kullanıcı gruplarını seçenekler olarak döndürür.
     * @return array
     */
    public function getGroupsOptions(): array
    {
        $groups = $this->getUserGroups();
        $options = [];

        // Add empty option first
        $options[0] = (object)[
            'id' => 0,
            'role_name' => 'Seçiniz'
        ];

        foreach ($groups as $group) {
            $options[$group->id] = (object)[
                'id' => $group->id,
                'role_name' => $group->role_name
            ];
        }

        return $options;
    }

    /** Bu Adla kaydedilmiş kullanıcı grubu var mı? */
    public function roleExists(string $roleName): bool
    {
        $ownerID = $_SESSION["owner_id"];

        $sql = $this->db->prepare("SELECT COUNT(*) FROM user_roles WHERE owner_id = :owner_id AND role_name = :role_name");
        $sql->execute([
            'owner_id' => $ownerID,
            'role_name' => $roleName
        ]);

        return $sql->fetchColumn() > 0;
    }
}

?>