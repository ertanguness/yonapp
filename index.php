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

use App\Controllers\AuthController;
use App\Helper\Security;
use Random\Engine\Secure;

$authController = new AuthController();

//giriş ve demo süresi kontrolü
$authController->checkAuthentication();

/**site_id kontrolü */
Security::ensureSiteSelected("company-list.php");


// 3. Adım: ROTA TESPİTİ (SAYFANIN EN BAŞINDA)
// Rota tanımlarını ve Router nesnesini yükle
require_once __DIR__ . '/route.php'; 



//site_id boş ise company-list sayfasına yönlendir
if (empty($site_id)) {
    header('Location: /company-list.php');
    exit;
}

// Gelen URL'yi al
$url = $_GET['p'] ?? 'ana-sayfa';
//$url = rtrim($url, '/');
//if (empty($url)) $url = 'ana-sayfa';

// URL'yi çözümle ve eşleşen rota bilgilerini al
$resolvedRoute = $router->resolve($url);

// 3. SAYFA ADINI AL (SİHİR BURADA GERÇEKLEŞİYOR)
// Router artık hangi desenin eşleştiğini biliyor ve bize temiz halini veriyor.
$page = $router->getPageName() ?? '';


// // Görünüm (View) için gerekli değişkenleri hazırla
// $page = preg_replace('/[^a-zA-Z0-9\/\-]/', '', $page); // Güvenlik!
// $pagePath = __DIR__ . "/pages/{$page}.php";
// $viewToInclude = file_exists($pagePath) ? $pagePath : __DIR__ . "/pages/404.php";
// echo "Yüklenen sayfa: " . ($page); // Debug için
// if (!file_exists($pagePath)) {
//     http_response_code(404);
// }

// ------------------- ARTIK HTML BAŞLAYABİLİR -------------------
?>
<!DOCTYPE html>
<html lang="tr">
<?php include './partials/head.php'; ?>

<body>
    <?php include './partials/left-sidebar.php'; ?>
    <?php include './partials/header.php'; ?>
    
    <div id="preloader">
  <div class="ripple-loader"></div>
</div>


    <main class="nxl-container">
        <div class="nxl-content">
        <?php 
        echo "<!-- Yüklenen sayfa: " . ($page) . " -->"; // Debug için
            // 4. Adım: İÇERİK OLUŞTURMA
            // Rota tespitinde bulduğumuz callback'i parametreleriyle birlikte çalıştır.
            call_user_func_array($resolvedRoute['callback'], $resolvedRoute['params']);
            ?>
            
            <?php //include $viewToInclude; // Sayfayı dahil et ?>
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