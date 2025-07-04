<?php


// Uygulama başlangıç ayarlarını ve servisleri yükle
require_once __DIR__ . '/configs/bootstrap.php';


if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    $returnUrl = urlencode($_SERVER["REQUEST_URI"]);
    if (!isset($_GET["p"])) {
        $returnUrl = urlencode("/index?p=home");
    }
    header("Location: sign-in.php?returnUrl={$returnUrl}");
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}




use App\Helper\Security;
use Model\UserModel;
use Model\MenuModel;
use Model\AuthsModel;

$menus = new MenuModel();
$User = new UserModel();
$perm = new AuthsModel();
$user = $User->find($_SESSION['user']->id) ?? null;


if (!$user) {
    $log_id = $_SESSION["log_id"];
    $Users->logoutLog($log_id);
    header("Location: sign-in.php");
    exit();
}

$_SESSION["user"] = $user;

// if ($_SESSION["user"]->parent_id != 0) {
//     $email = $_SESSION['user']->email ?? null;
//     $site_id = $_SESSION['site_id'];
//     $user = $User->getUserByEmailAndFirm($email, $site_id);
//     $_SESSION['user'] = $user;
// }

if ($user->user_type == 1) {
    $diff = 15 - date_diff(date_create($user->created_at), date_create(date("Y-m-d")))->format("%a");
    if ($diff <= 0) {
        header("Location: sign-in.php");
        exit();
    }
}
$page = isset($_GET["p"]) ? ($_GET["p"]) : "home";
$active_page = $page;
//$menu_name = $menus->getMenusByLink($page)->page_name ?? 'home';


?>
<!DOCTYPE html>
<html lang="zxx" >

<?php $title = 'YonApp - Apartman / Site Yönetim Sistemi' ?>
<?php include_once './partials/head.php' ?>

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
            <?php
            $page = isset($_GET["p"]) ? $_GET["p"] : "home/list";
                // echo "user token" . $user->session_token;
                // echo "session token : ".$_SESSION['csrf_token'];
            ; ?>

            <?php

            if (isset($_GET["p"]) && file_exists("pages/{$page}.php")) {
                include "pages/{$page}.php";
            } else if (!file_exists("pages/{$page}.php")) {

                include "pages/404.php";
            } else (
                    include "pages/home/list.php"
                );
            ?>
            <!-- [ Main Content ] end -->
        </div>
        <!--<< Footer Section Start >>-->
        <?php include_once './partials/footer.php' ?>

    </main>
    <!--! ================================================================ !-->
    <!--! [End] Main Content !-->
    <!--! ================================================================ !-->
    <!--<< Footer Section Start >>-->
     <?php include_once './partials/theme-customizer.php' ?> 
    <!--<< All JS Plugins >>-->
    <?php include_once './partials/homepage-script.php'; ?>

    <?php include_once "./partials/vendor-scripts.php" ?>



</body>

</html>