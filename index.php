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

// /**site_id kontrolü */
// Security::ensureSiteSelected("company-list.php");


// 3. Adım: ROTA TESPİTİ (SAYFANIN EN BAŞINDA)
// Rota tanımlarını ve Router nesnesini yükle
require_once __DIR__ . '/route.php'; 





// Gelen URL'yi al
$url = $_GET['p'] ?? 'ana-sayfa';
//$url = rtrim($url, '/');
//if (empty($url)) $url = 'ana-sayfa';

// URL'yi çözümle ve eşleşen rota bilgilerini al
$resolvedRoute = $router->resolve($url);

// 3. SAYFA ADINI AL (SİHİR BURADA GERÇEKLEŞİYOR)
// Router artık hangi desenin eşleştiğini biliyor ve bize temiz halini veriyor.
$page = $router->getPageName() ?? '';
$skipPagesForSiteCheck = ['site-ekle','siteler','site-duzenle','sign-in','kayit-ol','logout','forgot-password','reset-password'];
if (!in_array($page, $skipPagesForSiteCheck, true)) {
    Security::ensureSiteSelected('/site-ekle');
}

// Seçim bağlamını header'dan önce güncelle
if (isset($_GET['clear_context'])) {
    unset($_SESSION['selected_apartment_id'], $_SESSION['selected_person_id']);
}
if (isset($_GET['daire_id'])) {
    $_SESSION['selected_apartment_id'] = (int)$_GET['daire_id'];
    if (!isset($_GET['kisi_id'])) {
        unset($_SESSION['selected_person_id']);
    }
}
if (isset($_GET['kisi_id'])) {
    $_SESSION['selected_person_id'] = (int)$_GET['kisi_id'];
    if (isset($_GET['daire_id'])) {
        $_SESSION['selected_apartment_id'] = (int)$_GET['daire_id'];
    }
}

// PDF benzeri özel sayfalar: layout'u basmadan doğrudan çıktıyı üret
if (preg_match('/-pdf$/', $page)) {
    call_user_func_array($resolvedRoute['callback'], $resolvedRoute['params']);
    exit;
}



// ------------------- ARTIK HTML BAŞLAYABİLİR -------------------
?>
<!DOCTYPE html>
<html lang="tr">
<?php include './partials/head.php'; ?>

<body data-page="<?= $page ?>">

    <?php 
    //Eğer site sakini ise bunu 
    include './partials/left-sidebar.php'; ?>
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

    <div id="onboarding-checklist-root"></div>

    <div class="modal fade-scale" id="composeMail" tabindex="-1" aria-labelledby="composeMail" aria-hidden="true" data-bs-dismiss="ou">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content">
            </div>
        </div>
    </div>

    <div class="modal fade" id="SendMessage" tabindex="-1" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content sms-gonder-modal">
            </div>
        </div>
    </div>

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
