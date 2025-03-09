<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

define("ROOT", __DIR__);
date_default_timezone_set('Europe/Istanbul');

ob_start();
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    $returnUrl = urlencode($_SERVER["REQUEST_URI"]);
    if (!isset($_GET["p"])) {
        $returnUrl = urlencode("/index.php?p=home");
    }
    header("Location: sign-in.php?returnUrl={$returnUrl}");
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once "Database/db.php";
require_once "Model/menus.php";
require_once "Model/UserModel.php";
require_once 'Model/Auths.php';
require_once 'App/Helper/security.php';
require_once "configs/functions.php";


use App\Helper\Security;

$menus = new Menus();
$User = new UserModel();
$perm = new Auths();
$user = $User->find($_SESSION['user']->id) ?? null;

if (!$user) {
    $log_id = $_SESSION["log_id"];
    $Users->logoutLog($log_id);
    header("Location: sign-in.php");
    exit();
}

$_SESSION["user"] = $user;

if ($_SESSION["user"]->parent_id != 0) {
    $email = $_SESSION['user']->email ?? null;
    $firm_id = $_SESSION['firm_id'];
    $user = $User->getUserByEmailAndFirm($email, $firm_id);
    $_SESSION['user'] = $user;
}

if ($user->user_type == 1) {
    $diff = 15 - date_diff(date_create($user->created_at), date_create(date("Y-m-d")))->format("%a");
    if ($diff <= 0) {
        header("Location: sign-in.php");
        exit();
    }
}

$active_page = isset($_GET["p"]) ? $_GET["p"] : "home";
$menu_name = $menus->getMenusByLink($active_page);
?>
<!DOCTYPE html>
<html lang="zxx">

<?php $title = 'YonApp - Apartman / Site Yönetim Sistemi' ?>
<?php include './partials/head.php' ?>

<body>
    <!-- Left sidebar -->
    <?php include './partials/left-sidebar.php' ?>

    <!-- Header Section Start -->
    <?php include './partials/header.php' ?>
    <!--! ================================================================ !-->
    <!--! [Start] Main Content !-->
    <!--! ================================================================ !-->
    <main class="nxl-container">
        <div class="nxl-content">
            <?php //include './partials/page-header.php' ?>
<!-- 
            <div class="main-content">
                <div class="row"> -->
                    <?php
                    $page = isset($_GET["p"]) ? $_GET["p"] : "home";
                    // echo "user token" . $user->session_token;
                    // echo "session token : ".$_SESSION['csrf_token'];
                    ; ?>

                    <?php

                    if (isset($_GET["p"]) && file_exists("pages/{$page}.php")) {

                        include "pages/{$page}.php";
                    } else if (!file_exists("pages/{$page}.php")) {

                        include "pages/404.php";
                    } else
                        (
                            include "pages/home.php"
                        );
                    ?>
                <!-- </div>
            </div> -->
            <!-- [ Main Content ] end -->
        </div>
        <!--<< Footer Section Start >>-->
        <?php include './partials/footer.php' ?>
        <?php include "./partials/vendor-scripts.php" ?>

    </main>
    <!--! ================================================================ !-->
    <!--! [End] Main Content !-->
    <!--! ================================================================ !-->
    <!--<< Footer Section Start >>-->
    <?php include './partials/theme-customizer.php' ?>
    <!--<< All JS Plugins >>-->
    <?php include './partials/homepage-script.php'; ?>

</body>

</html>