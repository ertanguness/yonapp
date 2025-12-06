<?php

namespace Model;

use Model\Model;
use PDO;

use App\Helper\Security;

class UserRegistirationModel extends Model
{
    protected $register_table = 'user_registration_methods';
    protected $verify_table = 'user_phone_verifications';

    public function __construct()
    {
        return parent::__construct($this->register_table);
    }
 
 
    public function userRegistiration($data)
    {
        $this->saveWithAttr($data);
    
    }


    /** Telefon doğrulama */
    public function verifyPhone($createdUserId, $verify)
    {
        $sql = 'UPDATE ' . $this->verify_table . ' 
                SET verified_at = NOW(), user_id = :userId 
                WHERE id = :verifyId';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':userId'   => $createdUserId,
            ':verifyId' => $verify->id
        ]);
    }

    public function getVerificationById($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM ' . $this->verify_table . ' WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return $row ?: null;
    }

    public function getLatestVerificationByUserId($userId)
    {
        $stmt = $this->db->prepare('SELECT * FROM ' . $this->verify_table . ' WHERE user_id = ? ORDER BY id DESC LIMIT 1');
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return $row ?: null;
    }

    public function updateVerificationCodeById($id, $code, $expiresAt)
    {
        $stmt = $this->db->prepare('UPDATE ' . $this->verify_table . ' SET code = ?, expires_at = ?, verified_at = NULL WHERE id = ?');
        $stmt->execute([$code, $expiresAt, $id]);
    }

    public function updateLatestVerificationCodeByUserId($userId, $code, $expiresAt)
    {
        $stmt = $this->db->prepare('UPDATE ' . $this->verify_table . ' SET code = ?, expires_at = ?, verified_at = NULL WHERE user_id = ? ORDER BY id DESC LIMIT 1');
        $stmt->execute([$code, $expiresAt, $userId]);
    }

}
