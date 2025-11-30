<?php

namespace Model;

use Model\Model;
use PDO;

use App\Helper\Security;

class UserModel extends Model
{
    protected $table = 'users';

    public function __construct()
    {
        parent::__construct($this->table);
    }


    public function getUserByEmail($email)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE email = ?");
        $sql->execute(array($email));
        return $sql->fetch(PDO::FETCH_OBJ) ?? null;
    }

    public function getUserByPhone($phone)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE phone = ?");
        $sql->execute([$phone]);
        return $sql->fetch(PDO::FETCH_OBJ) ?? null;
    }

    //Kullanıcı adı vey emailden kullanıcı kontrolü yapılır,true veya false döner
    public function checkUser($username)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE email_adresi = ? OR user_name = ?");
        $sql->execute(array($username, $username));
        return $sql->fetch(PDO::FETCH_OBJ) ?? null;
    }


    /**
     * Kullanıcıları listelemek için gerekli verileri getirir.
     * Kayıt esnasında oluşturulan ana kullanıcı listelenmez
     * @param int $owner_id Verinin sahibi ID'si(Session ID'si gibi)
     * @return array
    */
    public function getUsers($type = null): array
    {
        $ownerID = $_SESSION["owner_id"];
        $params = [
            'owner_id' => $ownerID,
            'main_user' => 0
        ];

        $sql = "SELECT u.*,r.role_name FROM $this->table u
                LEFT JOIN user_roles r ON u.roles = r.id
                WHERE u.owner_id = :owner_id
                AND r.guncellenebilir = :guncellenebilir
                AND u.is_main_user = :main_user";

        $params['guncellenebilir'] = 1;

        if ($type !== null) {
            $sql .= " AND u.roles = :roles";
            $params['roles'] = Security::decrypt($type);
        }

        $sql .= " ORDER BY u.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_OBJ) ?? [];
    }


    /** 
     * Kullanıcı id'sinden kullanıcının role id'sini döndürür.
     * @param int $userId Kullanıcı ID'si
     * @return int|null Kullanıcı rol ID'si veya null
     * @throws \Exception
     */
    public function getUserRoleID(int $userId): ?int
    {
        $sql = $this->db->prepare("SELECT roles FROM $this->table WHERE id = ?");
        $sql->execute([$userId]);
        $result = $sql->fetch(PDO::FETCH_OBJ);

        if ($result) {
            return (int)$result->roles;
        }

        return null; // Kullanıcı rolü bulunamadıysa null döner
    }


    
    //Kullanıcı girişinde bir token oluştur ve kullanıcıya kaydet
    public function setToken($id, $token)
    {
        //$token = bin2hex(random_bytes(32));
        $sql = $this->db->prepare("UPDATE $this->table SET session_token = ? WHERE id = ?");
        $sql->execute(array($token, $id));
        return $token;
    }

    public function getToken($token)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE token = ? ");
        $sql->execute(array($token));
        return $sql->fetch(PDO::FETCH_OBJ);
    }

    public function getUserByResetToken($token)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE reset_token = ? ");
        $sql->execute(array($token));
        return $sql->fetch(PDO::FETCH_OBJ);
    }

    public function roleName($id)
    {
        $sql = $this->db->prepare("SELECT * FROM user_roles WHERE id = ?");
        $sql->execute(array($id));
        $result = $sql->fetch(PDO::FETCH_OBJ);
        return $result->role_name ;
    }
    
    public function isEmailExists($email)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE email = ?");
        $sql->execute([$email]);
        return $sql->fetch(PDO::FETCH_OBJ);
    }

    //Giriş işlemleri kayıt altına alınıyor
    public function loginLog($user_id)
    {
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        $sql = $this->db->prepare("INSERT INTO login_logs (user_id, ip_address, user_agent) VALUES (?, ?, ?)");
        $sql->execute([$user_id, $ip_address, $user_agent]);
        return $this->db->lastInsertId();
    }

    //Çıkış işlemi yapıldığında log kaydı yapılır
    public function logoutLog($id)
    {
        $sql = $this->db->prepare("UPDATE login_logs SET logout_time = NOW() WHERE id = ?");
        $sql->execute([$id]);
    }

    //Token sorgulama


    public function updateUserPassword($email, $password)
    {
        $sql = $this->db->prepare("UPDATE $this->table SET password = ? WHERE email = ?");
        $sql->execute([$password, $email]);
    }

    //Activate Token kaydetme
    public function setActivateToken($data)
    {
        $this->saveWithAttr($data);
    }

    //Activate Token sorgulama
    public function checkToken($email)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE email = ?");
        $sql->execute([$email]);
        return $sql->fetch(PDO::FETCH_OBJ);
    }

    //Kullanıcıyı aktif etme
    public function ActivateUser($email)
    {
        $sql = $this->db->prepare("UPDATE $this->table SET status = 1 WHERE email = ?");
        $sql->execute([$email]);
        //eğer başarılı ise geriye değer döndür
        return $sql->rowCount();
    }

    //Kullanıcının seçtiği paketi getirme
    public function getSelectedPackage($user_id)
    {
        $sql = $this->db->prepare("SELECT p.package_id FROM users u
                                            LEFT JOIN mbeyazil_panel.users_packages p ON p.user_id= u.id WHERE user_id = ?");
        $sql->execute([$user_id]);
        return $sql->fetch(PDO::FETCH_OBJ);
    }


    /**
 * Belirtilen ID'ye sahip kullanıcıyı HTML tablo satırı olarak döndürür.
 *
 * @param int $id Kullanıcı ID'si
 * @return string HTML <tr> satırı
 */public function renderUserTableRow(int $id, $isNew = false): string
{
    $user = $this->find($id);

    if (!$user) {
        return '';
    }

    // Güvenli veri
    $enc_id    = htmlspecialchars(Security::encrypt($user->id), ENT_QUOTES, 'UTF-8');
    $userName  = htmlspecialchars($user->user_name, ENT_QUOTES, 'UTF-8');
    $fullName  = htmlspecialchars($user->adi_soyadi, ENT_QUOTES, 'UTF-8');
    $title     = htmlspecialchars($user->unvani, ENT_QUOTES, 'UTF-8');
    $email     = htmlspecialchars($user->email_adresi, ENT_QUOTES, 'UTF-8');
    $phone     = htmlspecialchars($user->telefon, ENT_QUOTES, 'UTF-8');
    $createdAt = htmlspecialchars($user->created_at, ENT_QUOTES, 'UTF-8');

    ob_start(); // 🔁 Output Buffer başlat
    ?>
    <?php if ($isNew ): ?>
    <tr data-id="<?= $enc_id ?>">
    <?php endif; ?>
        <td class="text-center">1</td>
        <td><?= $userName ?></td>
        <td><?= $fullName ?></td>
        <td class="text-center"><?= $title ?></td>
        <td class="text-center"><?= $email ?></td>
        <td class="text-center"><?= $phone ?></td>
        <td><?= $createdAt ?></td>
        <td class="text-center" style="width:5%">
            <div class="flex-shrink-0">
                <div class="dropdown align-self-start icon-demo-content">
                    <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bx bx-list-ul font-size-24 text-dark"></i>
                    </a>
                    <div class="dropdown-menu">
                        <a href="javascript:void(0)" class="dropdown-item kullanici-duzenle" data-id="<?= $enc_id ?>">
                            <span class="mdi mdi-account-edit font-size-18"></span> Düzenle
                        </a>
                        <a href="#" class="dropdown-item kullanici-sil" data-id="<?= $enc_id ?>" data-name="<?= $fullName ?>">
                            <span class="mdi mdi-delete font-size-18"></span> Sil
                        </a>
                    </div>
                </div>
            </div>
        </td>
    <?php if ($isNew): ?>
    </tr>
    <?php endif; ?>
    <?php
    return ob_get_clean(); // 🔚 HTML'yi döndür
}

public function getUser($id)
{
    $sql = $this->db->prepare("SELECT * FROM $this->table WHERE id = ?");
    $sql->execute(array($id));
    return $sql->fetch(PDO::FETCH_OBJ);
}
}
