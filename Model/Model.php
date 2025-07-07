<?php


namespace Model;

use App\Helper\Security;
use Override;
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


    /** Finds records where a specific column matches a value.
     * @param string $column The column to search in.
     * @param mixed $value The value to match against the column.
     */
    public function findWhereIn($column, $values, $sorting ="column asc")
    {
        if (empty($values)) {
            return [];
        }

        $placeholders = rtrim(str_repeat('?, ', count($values)), ', ');
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE $column IN ($placeholders) ORDER BY $sorting");
        $sql->execute($values);
        return $sql->fetchAll(PDO::FETCH_OBJ);
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

/**
 * Bir kaydı ID'sine göre günceller.
 * Bu metod, üst sınıftaki (parent) `update` metodunu override eder.
 * Güncellemeden önce kaydın varlığını kontrol eder ve verileri hazırlar.
 *
 * @param int|string $id Güncellenecek kaydın şifrelenmiş veya normal ID'si
 * @param array $data Güncellenecek verileri içeren anahtar-değer dizisi
 * @return bool Güncelleme işleminin sonucu (genellikle true/false)
 * @throws \Exception Kayıt bulunamazsa veya güncelleme başarısız olursa
 */
public function updateSingle($id, $data)
{
    // 1. ID'yi deşifre et (Eğer şifreli geliyorsa)
   // $decryptedId = Security::decrypt($id);

    // 2. Güncelleme öncesi kaydın varlığını kontrol et
    // Bu, gereksiz veritabanı sorgularını önler ve hata yönetimini iyileştirir.
    // find() metodunun zaten modelin niteliklerini ($this->attributes) doldurduğunu varsayıyoruz.
    $record = $this->find($id);
    if ($record === false) {
        // Kayıt bulunamadıysa, bir istisna fırlatarak işlemi durdur.
        throw new \Exception("Güncellenmek istenen kayıt bulunamadı. ID: " . $id);
    }

    // 3. Modelin niteliklerini (attributes) yeni güncelleme verileriyle birleştir/ayarla
    // Gelen veriyi mevcut niteliklerin üzerine yazıyoruz.
    $this->attributes = array_merge($this->attributes, $data);

    // Birincil anahtarın doğru ayarlandığından emin olalım.
    // find() bunu zaten yapmış olmalı, ama bu bir güvencedir.
    $this->attributes[$this->primaryKey] = $id;

    // 4. Üst sınıfın orijinal update metodunu çağırarak asıl veritabanı işlemini gerçekleştir
    // DİKKAT: $this->update() yerine parent::update() kullanılmalıdır!
    // parent::update() metodu, $this->attributes dizisindeki verileri kullanarak
    // "UPDATE tablo SET ... WHERE id=..." sorgusunu çalıştıracaktır.
    return $this->update(); 
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
