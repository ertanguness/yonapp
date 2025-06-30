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
        $returnUrl = urlencode("/index?p=home");
    }
    header("Location: sign-in.php?returnUrl={$returnUrl}");
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// require_once "Database/db.php";
// require_once "Model/menus.php";
// require_once "Model/UserModel.php";
// require_once 'Model/Auths.php';
// require_once 'App/Helper/security.php';
// require_once "configs/functions.php";
require_once __DIR__ . '/vendor/autoload.php';

use App\Helper\Security;
use Model\UserModel;
use Model\MenuModel;
use Model\Auths;

$menus = new MenuModel();
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
    $site_id = $_SESSION['site_id'];
    $user = $User->getUserByEmailAndFirm($email, $site_id);
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
<?php include_once './partials/head.php' ?>

<!-- Datatables başlangıç istediğimiz tablonun classına datatables yazmak yeterli aktif olması için -->

<!-- 
<script>
    function initializeDataTables() {
        $(".datatables").each(function() {
            if (!$.fn.DataTable.isDataTable(this)) {
                $(this).DataTable({
                    language: {
                        url: "assets/js/tr.json" // Türkçe dil dosyasının yolu
                    },
                    columnDefs: [
                        { targets: "_all", className: "text-center" }, // Tüm sütun başlıklarını ve içeriklerini ortala
                        { targets: "_all", render: function(data, type, row) {
                            return `<div style="display: flex; justify-content: center; align-items: center;">${data}</div>`;
                        }} // Sembol içeren hücreleri de ortala
                    ]
                });
            }
        });
    }

    // Sayfa yüklendiğinde çalıştır
    $(document).ready(function() {
        initializeDataTables();
    });

    // Yeni DOM elemanları eklendiğinde DataTables başlat
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if ($(node).find(".datatables").length) {
                    initializeDataTables();
                }
            });
        });
    });

    // Tüm sayfa (body veya belirli bir alan) gözlemleniyor
    // observer.observe(document.body, {
    //     childList: true,
    //     subtree: true
    // });
</script>  -->



<!-- datatables bitiş -->

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
            <?php //include './partials/page-header.php' 
            ?>
            <!-- 
            <div class="main-content">
                <div class="row"> -->
            <?php
            $page = isset($_GET["p"]) ? $_GET["p"] : "home";

            // // PANEL İÇİN İSTİSNALI YÖNLENDİRME manuel giriş için 
            // if ($page === "panel/panel") {
            //     header("Location: /yonapp/pages/panel/panel.php");
            //     exit;
            // }

            if (isset($_GET["p"]) && file_exists("pages/{$page}.php")) {
                include "pages/{$page}.php";
            } else if (!file_exists("pages/{$page}.php")) {
                include "pages/404.php";
            } else {
                include "pages/home.php";
            }
            ?>
            <!-- </div>
            </div> -->
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