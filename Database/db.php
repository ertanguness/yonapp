<?php
namespace Database;

use PDO;
use PDOException;

class Db {
    /**
     * Singleton instance'ını tutan statik özellik.
     * @var Db|null
     */
    private static ?Db $instance = null;
    
    /**
     * PDO bağlantı nesnesini tutar.
     * @var PDO
     */
    public PDO $connection;

    /**
     * Constructor'ı private yaparak dışarıdan "new Db()" ile
     * nesne oluşturulmasını engelliyoruz.
     */
    private function __construct() {

      // Ayarları .env dosyasından oku
            $host    = $_ENV['DB_HOST'];
            $dbname  = $_ENV['DB_DATABASE'];
            $user    = $_ENV['DB_USERNAME'];
            $pass    = $_ENV['DB_PASSWORD'];
            $charset = 'utf8mb4';
        
        //echo "Veritabanı bağlantısı kuruluyor: $host, $dbname\n"; // Debug için

        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Hataları exception olarak fırlat
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,       // Varsayılan fetch modunu obj yap
    
        ];

        try {
            $this->connection = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // .env'deki APP_DEBUG ayarını kontrol et
            $isDebug = $_ENV['APP_DEBUG'] ?? 'false';
        
            if ($isDebug === 'true') {
                // Geliştirme ortamındaysak, detaylı hata mesajı göstererek
                // sorunu hızlıca anlamamızı sağla.
                throw new PDOException($e->getMessage(), (int)$e->getCode());
            } else {
                // Canlı ortamdaysak, kullanıcıya genel bir mesaj göster
                // ve asıl hatayı sunucu loglarına yaz. Bu, loglama sistemimiz
                // çalışmadan önce olabilecek bir hata olduğu için PHP'nin
                // kendi error_log fonksiyonunu kullanmak en güvenlisidir.
                error_log("CRITICAL: Veritabanı bağlantısı kurulamadı! Hata: " . $e->getMessage());
                
                // Kullanıcıya hassas bilgi sızdırmadan genel bir hata sayfası göster.
                // header('HTTP/1.1 503 Service Unavailable');
                die("Sistemde geçici bir sorun oluştu. Lütfen daha sonra tekrar deneyin.");
            }
        }
    }

    /**
     * Sınıfın tek bir instance'ını oluşturan veya mevcut olanı döndüren statik metot.
     * @return Db
     */
    public static function getInstance(): Db
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Aktif PDO bağlantısını döndürür.
     * Artık doğrudan public olan $connection özelliğini kullanabiliriz
     * veya bu metodu koruyabiliriz.
     * @return PDO
     */
    public function connect(): PDO
    {
        return $this->connection;
    }

    // Dışarıdan klonlanmasını engelle
    private function __clone() {}

    // Dışarıdan unserialize edilmesini engelle
    public function __wakeup() {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    // Transaction metotları (aynı kalabilir)
    public function beginTransaction() { return $this->connection->beginTransaction(); }
    public function commit() { return $this->connection->commit(); }
    public function rollBack() { return $this->connection->rollBack(); }
}