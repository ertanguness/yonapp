<?php
require_once "db.php";

use Database\Db;

$dbInstance = Db::getInstance(); // Singleton pattern ile Db örneğini al
$db = $dbInstance->connect(); // Veritabanı bağlantısını alıyoruz.
session_start(); // Session'ı başlatıyoruz.
// $user_id = $_SESSION['user_id']; // Session'dan user_id'yi alıyoruz.