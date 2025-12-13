<?php
require_once "db.php";

use Database\Db;

$dbInstance = Db::getInstance(); // Singleton pattern ile Db örneğini al
$db = $dbInstance->connect(); // Veritabanı bağlantısını alıyoruz.

require_once __DIR__ . '/../configs/session-config.php';

// $user_id = $_SESSION['user_id']; // Session'dan user_id'yi alıyoruz.