<?php


// $host = "localhost";
// $user = "mbeyazil_root";
// $pass = "KT308WuD*ge+";
// $db = "mbeyazil_puantoryeni";
$host = "localhost";
$user = "root";
$pass = "";
$db = "yonapp";

try {
    $db = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     //echo "Connected successfully";
} catch (PDOException $e) {
    //echo "Connection failed: " . $e->getMessage();
}

date_default_timezone_set('Europe/Istanbul');