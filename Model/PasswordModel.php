<?php

namespace Model;

use Model\Model;
use PDO;

class PasswordModel extends Model
{
    protected $table = 'password_resets';

    public function __construct()
    {
        parent::__construct($this->table);
    }

    public function setPasswordReset($email, $token)
    {
        $sql = $this->db->prepare("INSERT INTO $this->table (email, token) VALUES (?, ?)");
        return $sql->execute(array($email, $token));
    }

    public function getPasswordReset($token)
    {
        $sql = $this->db->prepare("SELECT email FROM $this->table WHERE token = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        $sql->execute(array($token));
        return $sql->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Kullanılmış token'ı veritabanından siler
     * @param string $token Silinecek token
     * @return bool
     */
    public function deletePasswordReset($token)
    {
        $sql = $this->db->prepare("DELETE FROM $this->table WHERE token = ?");
        return $sql->execute(array($token));
    }

    /**
     * Belirli bir e-posta adresine ait tüm eski token'ları siler
     * @param string $email E-posta adresi
     * @return bool
     */
    public function deleteOldTokensByEmail($email)
    {
        $sql = $this->db->prepare("DELETE FROM $this->table WHERE email = ?");
        return $sql->execute(array($email));
    }

}