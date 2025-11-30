<?php


namespace Model;

use App\Helper\Security;
use Model\SSPModel;
use Override;
use PDO;


class Model extends SSPModel
{
    protected $table;
    protected $primaryKey = 'id';
    protected $attributes = [];
    protected $isNew = true;

    protected $db;


    public function __construct($table = null)
    {
        $this->table = $table ?: ($this->table ?? $this->getTableName());
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

    /**
     * Tablodaki toplam kayıt sayısını döndürür
     * @return int Kayıt sayısı
     */
    public function count()
    {
        $sql = $this->db->prepare("SELECT COUNT(*) as total FROM $this->table");
        $sql->execute();
        $result = $sql->fetch(PDO::FETCH_OBJ);
        return $result->total ?? 0;
    }

    /**
     * Belirtilen koşula göre kayıt sayısını döndürür
     * @param string $column Kolon adı
     * @param mixed $value Kolon değeri
     * @return int Kayıt sayısı
     */
    public function countWhere($column, $value)
    {
        $sql = $this->db->prepare("SELECT COUNT(*) as total FROM $this->table WHERE $column = ?");
        $sql->execute([$value]);
        $result = $sql->fetch(PDO::FETCH_OBJ);
        return $result->total ?? 0;
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

    /**
     * Birden fazla koşula göre tablodan kayıtları bulur ve getirir.
     *
     * Örnek Kullanım:
     * $kosullar = ['kisi_id' => 12, 'kullanildi_mi' => 0];
     * $krediler = $KisiKredi->findWhere($kosullar);
     *
     * @param array $conditions  Sorgu koşullarını içeren bir anahtar=>değer dizisi.
     *                           Örn: ['sutun_adi' => 'deger', 'baska_sutun' => 1]
     * @param string $orderBy    Sıralama için kullanılacak sütun ve yön. Örn: "id DESC"
     * @param int|null $limit    Getirilecek maksimum kayıt sayısı.
     * @return array             Bulunan kayıtların nesnelerinden oluşan bir dizi.
     */
    public function findWhere(array $conditions, string $orderBy = null, int $limit = null): array
    {
        // Temel SQL sorgusunu oluştur.
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";

        // Koşul dizisindeki her bir eleman için WHERE ifadesini dinamik olarak oluştur.
        foreach ($conditions as $column => $value) {
            // Güvenlik: Sütun adının geçerli bir ad olduğundan emin ol (isteğe bağlı ama önerilir).
            // Bu, '`' karakteri kullanarak SQL injection'a karşı ek bir katman sağlar.
            $sql .= " AND `{$column}` = :{$column}";
        }
        
        // Sıralama ifadesi ekle
        if ($orderBy) {
            // Not: ORDER BY sütunları doğrudan bind edilemez, bu yüzden
            // bu değeri doğrudan kullanıcıdan almamak en güvenlisidir.
            $sql .= " ORDER BY {$orderBy}";
        }

        // Limit ifadesi ekle
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }

        try {
            // Sorguyu hazırla
            $stmt = $this->db->prepare($sql);

            // Değerleri güvenli bir şekilde sorguya bağla (bindParam/bindValue).
            foreach ($conditions as $column => $value) {
                $stmt->bindValue(":{$column}", $value);
            }

            // Sorguyu çalıştır
            $stmt->execute();

            // Sonuçları bu sınıfın nesneleri olarak döndür.
            // Bu, sonuçların $kredi->kisi_id gibi kullanılmasını sağlar.
            return $stmt->fetchAll(PDO::FETCH_OBJ);

        } catch (\PDOException $e) {
            // Hata yönetimi: Hataları loglayabilir veya bir istisna fırlatabilirsiniz.
            // error_log("Sorgu hatası: " . $e->getMessage());
            return []; // Hata durumunda boş bir dizi döndür.
        }
    }
    

    public function findAll(array $conditions, string $orderBy = null, int $limit = null): array
    {
        return $this->findWhere($conditions, $orderBy, $limit);
    }



    /** Finds records where a specific column matches a value.
     * @param string $column The column to search in.
     * @param mixed $value The value to match against the column.
     */
    public function findWhereIn($column, $values, $sorting = "column asc")
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
        $attrs = $this->attributes;
        $pk = $this->primaryKey;
        if (array_key_exists($pk, $attrs)) {
            $v = $attrs[$pk];
            if ($v === null || $v === '' || $v === 0 || $v === '0') {
                unset($attrs[$pk]);
            }
        }

        $columns = implode(', ', array_keys($attrs));
        $values = ':' . implode(', :', array_keys($attrs));
        $sql = $this->db->prepare("INSERT INTO $this->table ($columns) VALUES ($values)");

        foreach ($attrs as $key => $value) {
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

    /*Herhangi bir kolona göre güncelleme işlemi
        * @param string $column Güncellenecek kaydın hangi kolona göre güncelleneceği
        * @param mixed $value Güncellenecek kaydın değeri
        * @param array $data Güncellenecek veriler
        * @return bool|Exception
    */
    public function updateWhere($column, $value, $data)
    {
        // 1. Güncelleme verilerini hazırla
        $setClause = '';
        foreach ($data as $key => $val) {
            $setClause .= "$key = :$key, ";
        }
        $setClause = rtrim($setClause, ', ');

        // 2. Sorguyu hazırla
        $sql = $this->db->prepare("UPDATE $this->table SET $setClause WHERE $column = :value");

        // 3. Bind işlemi
        foreach ($data as $key => $val) {
            $sql->bindValue(":$key", $val);
        }
        $sql->bindValue(':value', $value);

        // 4. Sorguyu çalıştır
        if ($sql->execute()) {
            return true;
        } else {
            return new \Exception('Güncelleme işlemi başarısız.');
        }
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
        // if (!$id) {
        //     throw new \Exception('Geçersiz ID.');
        // }

        $record =  $this->find($id); // Kayıt var mı kontrol et

        if (!$record) {
            throw new \Exception('Kayıt bulunamadı veya silinemedi.' . $id);
        }



        $sql = $this->db->prepare("DELETE FROM $this->table WHERE $this->primaryKey = ?");
        $sql->execute(array($id));

        if ($sql->rowCount() === 0) {
            return new \Exception('Kayıt bulunamadı veya silinemedi.');
        }
        return true;
    }

    /**Kolona göre silme işlemi
     * @param string $column Silinecek kaydın hangi kolona göre silineceği
     * @param mixed $value Silinecek kaydın değeri
     * @return bool|Exception
     */
    public function deleteByColumn($column, $value)
    {
        $sql = $this->db->prepare("DELETE FROM $this->table WHERE $column = ?");
        $sql->execute(array($value));

        if ($sql->rowCount() === 0) {
            return new \Exception('Kayıt bulunamadı veya silinemedi.');
        }
        return true;
    }

    //Soft delete
    public function softDelete($id, $silen_kullanici_id = null)
    {
        //$id = Security::decrypt($id);
        if($silen_kullanici_id === null){
            $silen_kullanici_id = $_SESSION['user']->id ?? null;
        }
        $sql = $this->db->prepare("UPDATE $this->table 
                                            SET silinme_tarihi = NOW() , silen_kullanici = ?  
                                            WHERE $this->primaryKey = ? ");
        $sql->execute(array( $silen_kullanici_id, $id));

        if ($sql->rowCount() === 0) {
            return new \Exception('Kayıt bulunamadı veya silinemedi.');
        }
        return true;
    }

    /**Kolona göre Soft delete işlemi
     * @param string $column Silinecek kaydın hangi kolona göre silineceği
     * @param mixed $value Silinecek kaydın değeri
     * @param int $silen_kullanici_id  Silen kullanıcının ID'si
     *  @return bool|Exception
     */
    public function softDeleteByColumn($column, $value, $silen_kullanici_id = null)
    {

        if($silen_kullanici_id === null){
            $silen_kullanici_id = $_SESSION['user']->id ?? null;
        }

        if (!$silen_kullanici_id) {
            return new \Exception('Silen kullanıcı ID\'si belirtilmedi.');
        }

        if(!$value){
            return new \Exception('Silinecek değer belirtilmedi.');
        }


        $sql = $this->db->prepare("UPDATE $this->table 
                                          SET silinme_tarihi = NOW(), silen_kullanici = ? 
                                          WHERE $column = ?");
        $sql->execute(array($silen_kullanici_id, $value));

        if ($sql->rowCount() === 0) {
            throw new \Exception('Kayıt bulunamadı veya silinemedi.' . $this->table . ' - ' . $column . ' = ' . $value);
        }
        return true;
    }


    

    //     return true;
    // }
    public function backupDelete($id, $table, $primaryKey = 'id')
    {
        $id = Security::decrypt($id);
        $backupTable = 'silinen_' . $table;
        // 1. Kaydı al
        $stmt = $this->db->prepare("SELECT * FROM $table WHERE $primaryKey = ? LIMIT 1");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return new \Exception('Kayıt bulunamadı.');
        }
        // 2. aktif_mi ve kullanim_durumu varsa sıfırla
        if (array_key_exists('aktif_mi', $data)) {
            $data['aktif_mi'] = 0;
        }
        if (array_key_exists('kullanim_durumu', $data)) {
            $data['kullanim_durumu'] = 0;
        }
        // 3. silinme_tarihi yoksa ekle
        if (!array_key_exists('silinme_tarihi', $data)) {
            $data['silinme_tarihi'] = date('Y-m-d H:i:s');
        }
        // 4. Alanları ve değerleri hazırla
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ':' . $col, $columns);
        // 5. Sorguyu hazırla
        $sql = "INSERT INTO $backupTable (" . implode(', ', $columns) . ")
            VALUES (" . implode(', ', $placeholders) . ")";
        $insertStmt = $this->db->prepare($sql);

        // 6. Bind işlemi
        foreach ($data as $key => $value) {
            $insertStmt->bindValue(':' . $key, $value);
        }

        $insertStmt->execute();

        // 7. Orijinal kaydı sil
        $deleteStmt = $this->db->prepare("DELETE FROM $table WHERE $primaryKey = ?");
        $deleteStmt->execute([$id]);

        if ($deleteStmt->rowCount() === 0) {
            return new \Exception('Kayıt silinemedi.');
        }

        return true;
    }



// Mevcut BaseModel'e ekle:

/**
 * DataTables server-side processing için veri döndürür
 */
public function dataTablesResponse($request, $columns) 
{
    $bindings = array();
    
    // Filtreleme
    $where = $this->buildDataTablesWhere($request, $columns, $bindings);
    
    // Sıralama
    $order = $this->buildDataTablesOrder($request, $columns);
    
    // Sayfalama
    $limit = $this->buildDataTablesLimit($request);
    
    // Ana veri sorgusu
    $sql = "SELECT * FROM {$this->table} {$where} {$order} {$limit}";
    $stmt = $this->db->prepare($sql);
    $stmt->execute($bindings);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatter'ları uygula
    $data = $this->applyDataTablesFormatters($data, $columns);
    
    // Toplam kayıt sayısı
    $totalRecords = $this->getTotalRecords();
    $filteredRecords = $this->getFilteredRecords($where, $bindings);
    
    return [
        "draw" => intval($request['draw'] ?? 0),
        "recordsTotal" => $totalRecords,
        "recordsFiltered" => $filteredRecords,
        "data" => array_values($data)
    ];
}

private function applyDataTablesFormatters($data, $columns) 
{
    foreach ($data as $rowIndex => &$row) {
        foreach ($columns as $column) {
            if (isset($column['formatter']) && is_callable($column['formatter'])) {
                $field = $column['db'];
                if (isset($row[$field])) {
                    $row[$field] = $column['formatter']($row[$field], $row);
                }
            }
        }
    }
    return $data;
}


private function buildDataTablesWhere($request, $columns, &$bindings) 
{
    $where = '';
    $conditions = [];
    
    // Global arama
    if (isset($request['search']['value']) && !empty($request['search']['value'])) {
        $searchValue = $request['search']['value'];
        $searchConditions = [];
        
        foreach ($columns as $column) {
            if (isset($column['db']) && !empty($column['db'])) {
                $searchConditions[] = "`{$column['db']}` LIKE ?";
                $bindings[] = "%{$searchValue}%";
            }
        }
        
        if (!empty($searchConditions)) {
            $conditions[] = '(' . implode(' OR ', $searchConditions) . ')';
        }
    }
    
    // Kolon bazlı arama
    if (isset($request['columns'])) {
        foreach ($request['columns'] as $i => $requestColumn) {
            if (isset($requestColumn['search']['value']) && !empty($requestColumn['search']['value'])) {
                $column = $columns[$i] ?? null;
                if ($column && isset($column['db'])) {
                    $conditions[] = "`{$column['db']}` LIKE ?";
                    $bindings[] = "%{$requestColumn['search']['value']}%";
                }
            }
        }
    }
    
    if (!empty($conditions)) {
        $where = 'WHERE ' . implode(' AND ', $conditions);
    }
    
    return $where;
}

private function buildDataTablesOrder($request, $columns) 
{
    $order = '';
    
    if (isset($request['order']) && !empty($request['order'])) {
        $orderBy = [];
        
        foreach ($request['order'] as $orderItem) {
            $columnIndex = $orderItem['column'];
            $direction = $orderItem['dir'] === 'desc' ? 'DESC' : 'ASC';
            
            if (isset($columns[$columnIndex]['db'])) {
                $orderBy[] = "`{$columns[$columnIndex]['db']}` {$direction}";
            }
        }
        
        if (!empty($orderBy)) {
            $order = 'ORDER BY ' . implode(', ', $orderBy);
        }
    }
    
    return $order;
}

private function buildDataTablesLimit($request) 
{
    $limit = '';
    
    if (isset($request['start']) && isset($request['length']) && $request['length'] != -1) {
        $start = intval($request['start']);
        $length = intval($request['length']);
        $limit = "LIMIT {$start}, {$length}";
    }
    
    return $limit;
}

private function getTotalRecords() 
{
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table}");
    $stmt->execute();
    return $stmt->fetchColumn();
}

private function getFilteredRecords($where, $bindings) 
{
    $sql = "SELECT COUNT(*) FROM {$this->table} {$where}";
    $stmt = $this->db->prepare($sql);
    $stmt->execute($bindings);
    return $stmt->fetchColumn();
}

/**
 * SSP sınıfını kullanarak DataTables response'u oluşturur
 */
public function getSSPResponse($request, $columns, $whereResult = null, $whereAll = null, $customTable = null, $primaryKey = null)
{
    // Mevcut DB bağlantısını SSP için uygun formata çevir
    $conn = $this->db;
    $table = $customTable ?? $this->table;
    $pk = $primaryKey ?? $this->primaryKey;
    
    if ($whereResult || $whereAll) {
        return parent::complex($request, $conn, $table, $pk, $columns, $whereResult, $whereAll);
    } else {
        return parent::simple($request, $conn, $table, $pk, $columns);
    }
}

    /**
     * DataTables server-side processing (convenience wrapper)
     * - Uses the current model's table and primary key
     * - Accepts the standard DataTables columns array (with db and dt mappings)
     * - Optionally returns JSON string directly
     *
     * @param array $request  Typically $_GET or $_POST from DataTables
     * @param array $columns  [ [ 'db' => 'db_col', 'dt' => 0, 'formatter' => callable|null ], ... ]
     * @param string|array|null $whereResult Additional filtering applied only to data rows
     * @param string|array|null $whereAll    Filtering applied to all queries (restrict data scope)
     * @param bool $asJson When true returns json_encode'd string, else returns array
     * @param string|null $customTable Optional custom table/JOIN string
     * @param string|null $primaryKey Optional qualified primary key (e.g., 'kh.id' for JOINs)
     * @return string|array
     */
    public function serverProcessing(
        array $request,
        array $columns,
        $whereResult = null,
        $whereAll = null,
        bool $asJson = true,
        $customTable = null,
        $primaryKey = null
    ) {
        $response = $this->getSSPResponse($request, $columns, $whereResult, $whereAll, $customTable, $primaryKey);
        return $asJson ? json_encode($response) : $response;
    }

    /**
     * snake_case alias for serverProcessing to match DataTables docs naming.
     * @see serverProcessing
     */
    public function server_processing(
        array $request,
        array $columns,
        $whereResult = null,
        $whereAll = null,
        bool $asJson = true,
        $customTable = null,
        $primaryKey = null
    ) {
        return $this->serverProcessing($request, $columns, $whereResult, $whereAll, $asJson, $customTable, $primaryKey);
    }


}
