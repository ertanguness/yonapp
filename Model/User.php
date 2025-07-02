<?php

use App\InterFaces\LoggerInterface;
use Model\Model;
use PDO;

class UserModel extends Model
{
    protected $table = 'users';
    private LoggerInterface $logger;
    public function __construct()
    {
        parent::__construct($this->table);
        $this->logger = $GLOBALS['logger'] ;
    }


    
    public function allByFirms($firm_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE firm_id = :firm_id");
        $sql->execute(['firm_id' => $firm_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }
    public function getUserByEmailandPassword($email, $password)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE email = ? AND password = ?");
        $sql->execute(array($email, $password));
        return $sql->fetch(PDO::FETCH_OBJ);
    }

    // is there a user with this email and firm_id
    public function getUserByEmailandFirm($email, $firm_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE email = ? AND firm_id = ?");
        $sql->execute(array($email, $firm_id));
        return $sql->fetch(PDO::FETCH_OBJ);
    }


    public function getUser($id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE id = ?");
        $sql->execute(array($id));
        return $sql->fetch(PDO::FETCH_OBJ);
    }

    public function getUserByEmail($email)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE email = ? ");
        $sql->execute(array($email));
        return $sql->fetch(PDO::FETCH_OBJ);
    }

    function getUsersByFirm($firm_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE firm_id = ? ");
        $sql->execute(array($firm_id));
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    public function getUserByToken($token)
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


 

    public function saveUser($data)
    {
        $this->attributes = $data;
        $this->isNew = true;
        if (isset($data["id"]) && $data["id"] > 0) {
            $this->isNew = false;
        }
        return parent::save();
    }

    public function roleName($id)
    {
        $sql = $this->db->prepare("SELECT * FROM userroles WHERE id = ?");
        $sql->execute(array($id));
        $result = $sql->fetch(PDO::FETCH_OBJ);
        return $result->roleName ?? "Bilinmiyor";
    }


}
