<?php
ob_start();
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);
define("ROOT", __DIR__);
date_default_timezone_set('Europe/Istanbul');
// Gerekli sınıfları dahil et
// Projenin kök dizinini tanımla
define('PROJECT_ROOT', dirname(__DIR__));
// Composer Autoloader'ı dahil et
require_once PROJECT_ROOT . '/vendor/autoload.php';


use App\InterFaces\LoggerInterface;
use App\Services\FileLogger;
use App\Services\DatabaseLogger;
use Database\Db;


// --- .env DEĞİŞKENLERİNİ YÜKLEME ---
$dotenv = Dotenv\Dotenv::createImmutable(PROJECT_ROOT);
$dotenv->load();

// --- MERKEZİ VERİTABANI BAĞLANTISI ---
/**
 * Proje genelinde kullanılacak tekil PDO bağlantı nesnesini döndürür.
 * Bu fonksiyon, Db sınıfının Singleton yapısını kullanarak her zaman
 * aynı bağlantı nesnesini döndürür.
 * @return PDO
 */
function getDbConnection(): PDO
{
    // Singleton Db sınıfından PDO bağlantısını al
    return Db::getInstance()->connect();
}


// --- LOGLAMA SERVİSİNİN KURULUMU ---

/**
 * Proje genelinde kullanılacak loglama servisini döndürür.
 * Hangi logger'ın kullanılacağını merkezi bir yerden yönetir.
 * @return LoggerInterface
 */
function getLogger(): LoggerInterface
{
    // --- DEĞİŞİKLİK 1: Loglama tipini 'database' olarak ayarlıyoruz ---
    $loggerType = 'database'; // 'file' veya 'database' olabilir.

    // Singleton Pattern: Logger'ı her seferinde yeniden oluşturmak yerine,
    // bir kere oluşturup tekrar tekrar aynı nesneyi kullanmak için.
    static $loggerInstance = null;

    if ($loggerInstance === null) {
        
        // --- DEĞİŞİKLİK 2: 'database' koşulunu birincil hale getiriyoruz ---
        if ($loggerType === 'database') {
            try {
                // Veritabanı bağlantısını merkezi fonksiyondan alıyoruz.
                $pdo_connection = getDbConnection(); 
                $loggerInstance = new DatabaseLogger($pdo_connection);
            } catch (\PDOException $e) {
                // EĞER VERİTABANI BAĞLANTISI KURULAMAZSA KRİTİK HATA!
                // Bu durumda, sistemin çökmesini engellemek için dosyaya loglamaya geri dön (fallback).
                // Bu, veritabanı çöktüğünde bile "veritabanı çöktü" logunu tutabilmemizi sağlar.
                error_log("CRITICAL: Database connection for logger failed. Falling back to FileLogger. DB Error: " . $e->getMessage());
                
                $logFile = PROJECT_ROOT . '/logs/critical_errors.log';
                $loggerInstance = new FileLogger($logFile);
            }
        } 
        else { // Varsayılan veya 'file' seçiliyse
            $logFile = PROJECT_ROOT . '/logs/' . date('Y-m-d') . '.log';
            $loggerInstance = new FileLogger($logFile);
        }
    }
    
    return $loggerInstance;
}