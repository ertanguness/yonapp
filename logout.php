<?php 
require_once __DIR__ . '/configs/bootstrap.php';

use App\Controllers\AuthController;
$authController = new AuthController();

$authController->logout();

?>