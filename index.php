<?php
/**
 * UYGULAMA GİRİŞ NOKTASI (ENTRY POINT)
 * Tüm web istekleri bu dosyadan geçer.
 */

// 1. Adım: Uygulamanın motorunu çalıştır.
// Bu dosya session'ı başlatır, autoloader'ı yükler, servisleri (logger vb.) kurar.
require_once __DIR__ . '/configs/bootstrap.php';

// 2. Adım: Kimlik doğrulama kontrolü.
// Bu fonksiyon, kullanıcı giriş yapmamışsa login sayfasına yönlendirir.
// (Bu fonksiyonu kendiniz oluşturabilirsiniz)

use App\Controllers\AuthController;
$authController = new AuthController();

//giriş ve demo süresi kontrolü
$authController->checkAuthentication();

// 3. Adım: Yönlendiriciyi (Router) çağır.
// Router, gelen URL'i analiz eder ve ilgili Controller'ı ve metodu belirler.
// $router = new App\Core\Router();
// $router->dispatch();

// Not: Yukarıdaki gibi bir yapı, projenizin "Front Controller" tasarım desenini
// uyguladığı anlamına gelir ve modern PHP'nin temelidir.

// Şimdilik, mevcut yapınızı daha güvenli hale getirelim:

// use Model\UserModel; // ve diğerleri

// // Gerekli verileri bir Controller'dan alıyor gibi düşünelim.
// // Bu kodlar normalde bir BaseController veya AppController içinde olurdu.
// $userModel = new UserModel();
// $user = $userModel->find($_SESSION['user']->id);

// if (!$user) {
//     // Auth servisi ile çıkış yap
//     // App\Services\AuthService::logout();
// }
// $_SESSION['user'] = $user; // Session'ı taze veri ile güncelle


// Görünüm (View) için gerekli değişkenleri hazırla
$page = $_GET['p'] ?? 'home';
$page = preg_replace('/[^a-zA-Z0-9\/\-]/', '', $page); // Güvenlik!
$pagePath = __DIR__ . "/pages/{$page}.php";
$viewToInclude = file_exists($pagePath) ? $pagePath : __DIR__ . "/pages/404.php";
// echo "Yüklenen sayfa: " . ($page); // Debug için
if (!file_exists($pagePath)) {
    http_response_code(404);
}

// ------------------- ARTIK HTML BAŞLAYABİLİR -------------------
?>
<!DOCTYPE html>
<html lang="tr">
    <?php include './partials/head.php'; ?>
<body>
    <?php include './partials/left-sidebar.php'; ?>
    <?php include './partials/header.php'; ?>

    <main class="nxl-container">
        <div class="nxl-content">
            <?php //echo "site_ id : " . $_SESSION["site_id"] ?>
            <?php include $viewToInclude; // Sayfayı dahil et ?>
        </div>
        <?php include './partials/footer.php'; ?>
    </main>

      <!--! ================================================================ !-->
    <!--! [End] Main Content !-->
    <!--! ================================================================ !-->
    <!--<< Footer Section Start >>-->
    <?php //include_once './partials/theme-customizer.php' ?> 
    <!--<< All JS Plugins >>-->
    <?php include_once './partials/homepage-script.php'; ?>

    <?php include_once "./partials/vendor-scripts.php" ?>

</body>
</html>