<?php

namespace Model;

use Model\Model;
use PDO;

use App\Helper\Security;

class UserModel extends Model
{
    protected $table = 'users';

    const SUPER_ADMIN = 10;

    public function __construct()
    {
        parent::__construct($this->table);
    }


    /** tüm kullanıcıları rol adı ve site adıyla beraber getirir */
    public function allUser(){
        $sql = $this->db->prepare("SELECT u.*, r.role_name, s.site_adi FROM $this->table u
                                        LEFT JOIN user_roles r ON u.roles = r.id
                                        LEFT JOIN kisiler k ON u.kisi_id = k.id
                                        LEFT JOIN siteler s ON k.site_id = s.id
                                        ORDER BY u.id DESC");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_OBJ) ?? [];
        
    }

    public function getUserByEmail($email)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE LOWER(email) = LOWER(?)");
        $sql->execute(array($email));
        return $sql->fetch(PDO::FETCH_OBJ) ?? null;
    }

    /** email kontrolü-kisi_id ve roles ile beraber kontrol  
     * @param string $email Kullanıcı email adresi
     * @param int $kisi_id Kullanıcı ID'si(opsiyonel, varsayılan olarak 0)
     * @param int $roles Kullanıcı rolü(varsayılan olarak 3=>site sakini)
     * @return object|null
     */
    public function getUserByEmailWithID($email, $kisi_id = 0, $roles = 3)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table 
                                          WHERE email = ? AND kisi_id = ? and roles = ?");
        $sql->execute(array($email, $kisi_id, $roles));
        return $sql->fetch(PDO::FETCH_OBJ) ?? null;
    }

    public function getUserByPhone($phone)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE phone = ?");
        $sql->execute([$phone]);
        return $sql->fetch(PDO::FETCH_OBJ) ?? null;
    }

    public function getAccountsByEmailOrPhone(?string $email, ?string $phone): array
    {
        $params = [];
        $clauses = [];
        if ($email) {
            $clauses[] = 'LOWER(u.email) = LOWER(:email)';
            $params['email'] = $email;
        }
        if ($phone) {
            $digits = preg_replace('/\D+/', '', $phone);
            $digits = $digits ?? '';
            // Normalize to common Turkish representations
            // Use the last 10 digits as base mobile number, and compare flexible variants
            $last10 = strlen($digits) >= 10 ? substr($digits, -10) : $digits;
            $cand0 = '0' . $last10;
            $cand90 = '90' . $last10;
            $candp = '+90' . $last10;
            $params['p_exact'] = $digits;
            $params['p_last10'] = $last10;
            $params['p_cand0'] = $cand0;
            $params['p_cand90'] = $cand90;
            $params['p_candp'] = $candp;
            // Strip non-digits from DB phone and compare on multiple flexible options
            $normalizedDbPhone = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(u.phone,' ',''),'(',''),')',''),'-',''),'+',''),'.','')";
            $clauses[] = "(
                $normalizedDbPhone = :p_exact
                OR RIGHT($normalizedDbPhone, 10) = :p_last10
                OR $normalizedDbPhone = :p_cand0
                OR $normalizedDbPhone = :p_cand90
                OR $normalizedDbPhone = :p_candp
            )";
        }
        if (empty($clauses)) {
            return [];
        }
        $sql = 'SELECT u.*, r.role_name 
                FROM ' . $this->table . ' u 
                LEFT JOIN user_roles r ON u.roles = r.id 
                WHERE (' . implode(' OR ', $clauses) . ')
                  AND (u.status = 1 OR u.roles = 3)
                ORDER BY IFNULL(u.login_favorite,0) DESC, IFNULL(u.login_usage_count,0) DESC, u.is_main_user DESC, u.id ASC';
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ) ?? [];
    }

    public function ensureLoginPreferenceColumns(): void
    {
        try {
            $checkFav = $this->db->query("SHOW COLUMNS FROM $this->table LIKE 'login_favorite'");
            $checkUse = $this->db->query("SHOW COLUMNS FROM $this->table LIKE 'login_usage_count'");
            $hasFav = $checkFav && $checkFav->fetch(PDO::FETCH_ASSOC);
            $hasUse = $checkUse && $checkUse->fetch(PDO::FETCH_ASSOC);
            if (!$hasFav) {
                $this->db->exec("ALTER TABLE $this->table ADD COLUMN login_favorite TINYINT(1) NOT NULL DEFAULT 0");
            }
            if (!$hasUse) {
                $this->db->exec("ALTER TABLE $this->table ADD COLUMN login_usage_count INT NOT NULL DEFAULT 0");
            }
        } catch (\Exception $e) {
            // Sessiz geç, login akışını engelleme
        }
    }

    public function ensureOnboardingCompletedColumn(): void
    {
        try {
            $chk = $this->db->query("SHOW COLUMNS FROM $this->table LIKE 'onboarding_completed'");
            $has = $chk && $chk->fetch(PDO::FETCH_ASSOC);
            if (!$has) {
                $this->db->exec("ALTER TABLE $this->table ADD COLUMN onboarding_completed TINYINT(1) NOT NULL DEFAULT 0");
            }
        } catch (\Exception $e) {
        }
    }

    public function getOnboardingCompleted(int $userId): int
    {
        $stmt = $this->db->prepare("SELECT IFNULL(onboarding_completed,0) AS oc FROM $this->table WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return (int) ($row->oc ?? 0);
    }

    public function setOnboardingCompleted(int $userId, int $val = 1): bool
    {
        $stmt = $this->db->prepare("UPDATE $this->table SET onboarding_completed = :v WHERE id = :id");
        return $stmt->execute(['v' => $val, 'id' => $userId]);
    }

    public function setLoginFavorite(int $userId, int $favorite): bool
    {
        $stmt = $this->db->prepare("UPDATE $this->table SET login_favorite = :fav WHERE id = :id");
        return $stmt->execute(['fav' => $favorite, 'id' => $userId]);
    }

    public function incLoginUsage(int $userId): bool
    {
        $stmt = $this->db->prepare("UPDATE $this->table SET login_usage_count = IFNULL(login_usage_count,0) + 1 WHERE id = :id");
        return $stmt->execute(['id' => $userId]);
    }

    public function getLoginPreferenceStatus(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("SELECT id, IFNULL(login_favorite,0) AS fav, IFNULL(login_usage_count,0) AS cnt FROM $this->table WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
        $out = [];
        foreach ($rows as $r) {
            $out[(int) $r['id']] = ['fav' => (int) $r['fav'], 'cnt' => (int) $r['cnt']];
        }
        return $out;
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
            return (int) $result->roles;
        }

        return null; // Kullanıcı rolü bulunamadıysa null döner
    }

   


    /**Kullanıcı süper admin mi */
    public static function isSuperAdmin(): bool
    {

         /**Kullanıcı modelini başlat */
        //$UserModel::isSuperAdmin();

        /**Session kontrolü */
        if (!isset($_SESSION["user_id"])) {
            return false;
        }


        /**id'yi session'dan al */
        $userId = $_SESSION["user_id"];

        $instance = new self();
        $sql = $instance->db->prepare("SELECT roles FROM " . $instance->table . " WHERE id = ?");
        $sql->execute([$userId]);
        $result = $sql->fetch(PDO::FETCH_OBJ);

        if ($result) {
            return (int) $result->roles === self::SUPER_ADMIN;
        }

        return false;
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
        return $result->role_name;
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
        // Email karşılaştırması büyük/küçük harf duyarsız yapıldı
        $sql = $this->db->prepare("UPDATE $this->table SET password = ? WHERE LOWER(email) = LOWER(?)");
        $sql->execute([$password, $email]);
        return $sql->rowCount(); // Kaç satır güncellendi
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
     */
    public function renderUserTableRow(int $id, $isNew = false): string
    {
        $user = $this->find($id);

        if (!$user) {
            return '';
        }

        // Güvenli veri
        $enc_id = htmlspecialchars(Security::encrypt($user->id), ENT_QUOTES, 'UTF-8');
        $userName = htmlspecialchars($user->user_name, ENT_QUOTES, 'UTF-8');
        $fullName = htmlspecialchars($user->adi_soyadi, ENT_QUOTES, 'UTF-8');
        $title = htmlspecialchars($user->unvani, ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($user->email_adresi, ENT_QUOTES, 'UTF-8');
        $phone = htmlspecialchars($user->telefon, ENT_QUOTES, 'UTF-8');
        $createdAt = htmlspecialchars($user->created_at, ENT_QUOTES, 'UTF-8');

        ob_start(); // 🔁 Output Buffer başlat
        ?>
        <?php if ($isNew): ?>
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
                        <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false">
                            <i class="bx bx-list-ul font-size-24 text-dark"></i>
                        </a>
                        <div class="dropdown-menu">
                            <a href="javascript:void(0)" class="dropdown-item kullanici-duzenle" data-id="<?= $enc_id ?>">
                                <span class="mdi mdi-account-edit font-size-18"></span> Düzenle
                            </a>
                            <a href="#" class="dropdown-item kullanici-sil" data-id="<?= $enc_id ?>"
                                data-name="<?= $fullName ?>">
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
