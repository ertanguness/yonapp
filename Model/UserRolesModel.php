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

        $sql = $this->db->prepare("SELECT * 
                                   FROM $this->table 
                                   WHERE owner_id = :owner_id 
                                   AND main_role = :main_role
                                   ORDER BY id DESC");
        $sql->execute([
            'owner_id' => $ownerID,
            'main_role' => 0
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
}

?>