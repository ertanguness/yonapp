<?php
require_once "App/Helper/Security.php";

use App\Helper\Security;

require_once __DIR__ . '/configs/session-config.php';

$page = $_GET["p"];


$user_id = $_SESSION['user']->id;
$user_role = $_SESSION['user']->user_roles;
//get ile gelen firm_id değeri sessiona atanır
$site_id = Security::decrypt($_GET['site_id']);
unset($_SESSION['kasa_id']);
if ($site_id == null) {
    include_once "pages/unauthorized.php";
}
$_SESSION['site_id'] = $site_id;

header("Location: $page");
