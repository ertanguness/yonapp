<?php


namespace Model;

use App\Helper\Security;
use PDO;


class Model 
{
    protected $table;
    protected $primaryKey = 'id';
    protected $attributes = [];
    protected $isNew = true;

    protected $db;


    public function __construct($table = null)
    {
        $this->table = $table ?: $this->getTableName();
        $this->db = \getDbConnection();
    }

    protected function getTableName()
    {
        $className = get_called_class();
        $parts = explode('\\', $className);
        $className = end($parts);
        return strtolower($className) . 's';
    }

    public function all()
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    public function find($id, $encrypt = false)
    {
        if ($encrypt) {
            $id = Security::decrypt($id);
        }

        if (!$id) {
            return false;
        }

        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE $this->primaryKey = ?");
        $sql->execute(array($id));
        return $sql->fetch(PDO::FETCH_OBJ) ?? false;
    }
   

    public function save()
    {
        if ($this->isNew) {
            return $this->insert();
        } else {
            $this->update();
        }
    }

    public function saveWithAttr($data)
    {
        $this->attributes = $data;
        if (isset($data['id']) && $data['id'] > 0) {
            $this->update();
        } else {
            return $this->insert();
        }
    }

    protected function insert()
    {
        $columns = implode(', ', array_keys($this->attributes));
        $values = ':' . implode(', :', array_keys($this->attributes));
        $sql = $this->db->prepare("INSERT INTO $this->table ($columns) VALUES ($values)");

        foreach ($this->attributes as $key => $value) {
            $sql->bindValue(":$key", $value);
        }

        $sql->execute();

        $this->isNew = false;
        $this->attributes[$this->primaryKey] = $this->db->lastInsertId();

        return Security::encrypt($this->attributes[$this->primaryKey]);
    }

    protected function update()
    {
        $setClause = '';

        if ($this->find($this->attributes[$this->primaryKey]) === false) {
            throw new \Exception('Kayıt bulunamadı.' . $this->attributes[$this->primaryKey]);
        }

        foreach ($this->attributes as $key => $value) {
            $setClause .= "$key = :$key, ";
        }
        $setClause = rtrim($setClause, ', ');

        $sql = $this->db->prepare("UPDATE $this->table SET $setClause WHERE $this->primaryKey = :$this->primaryKey");

        $sql->bindParam(":$this->primaryKey", $this->attributes[$this->primaryKey], PDO::PARAM_INT);

        foreach ($this->attributes as $key => $value) {
            $sql->bindValue(":$key", $value);
        }

        $sql->execute();

        // if ($sql->rowCount() === 0) {
        //     throw new Exception("Kayıt güncellenemedi.");
        // }
    }

    public function reload()
    {
        if (!$this->isNew) {
            $sql = $this->db->prepare("SELECT * FROM $this->table WHERE $this->primaryKey = ?");
            $sql->execute(array($this->attributes[$this->primaryKey]));
            $data = $sql->fetch(PDO::FETCH_OBJ);
        }
    }

    public function delete($id)
    {

        $id = Security::decrypt($id);
        $sql = $this->db->prepare("DELETE FROM $this->table WHERE $this->primaryKey = ?");
        $sql->execute(array($id));

        if ($sql->rowCount() === 0) {
            return new \Exception('Kayıt bulunamadı veya silinemedi.');
        }
        return true;
    }

    //Soft delete
    public function softDelete($id)
    {
        $id = Security::decrypt($id);
        $sql = $this->db->prepare("UPDATE $this->table SET deleted_at = NOW() WHERE $this->primaryKey = ?");
        $sql->execute(array($id));

        if ($sql->rowCount() === 0) {
            return new \Exception('Kayıt bulunamadı veya silinemedi.');
        }
        return true;
    }
}
