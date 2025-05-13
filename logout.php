<?php 
session_start();

require_once __DIR__ . '/vendor/autoload.php';
use Model\UserModel;

$Users = new UserModel();

$log_id= $_SESSION["log_id"];
$Users->logoutLog($log_id);
session_destroy();
header("Location: sign-in.php");

?>