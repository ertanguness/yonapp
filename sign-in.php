<?php
ob_start();
$page = "sign-in";


require_once __DIR__ . '/configs/bootstrap.php';

// Artık Controller'ları ve diğer sınıfları güvenle kullanabiliriz.
use App\Controllers\AuthController;

$errors = [];
// Sadece POST isteği varsa kontrolcüyü çalıştır
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitForm'])) {
    $authController = new AuthController();
   
    $authController->handleLoginRequest();
}

// Hatalı giriş sonrası e-posta alanını dolu tutmak için
$oldEmail = $_SESSION['old_form_input']['email'] ?? '';
// Okuduktan sonra session'ı temizle
unset($_SESSION['old_form_input']);

// HTML'i başlatalım
include './partials/head.php';
?>
<!DOCTYPE html>
<html lang="tr">
<!-- <head> içine CSS ve Swiper stilleri (Aynı kalıyor) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<style>
.auth-hero-side {
    position: relative;
    /* ... */
}

/* Swiper Konteyneri */
.testimonial-swiper {
    position: absolute;
    bottom: 8%;
    left: 10%;
    right: 10%;
    z-index: 10;
    text-align: center;
    color: #334155;
    padding-bottom: 30px;
}

.testimonial-swiper .quote-icon {
    font-size: 4rem;
    font-family: 'Source Sans Pro', serif;
    color: #4f46e5;
    line-height: 1;
    opacity: 0.3;
}

.testimonial-swiper h2 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-top: -1rem;
    margin-bottom: 0.75rem;
}

.testimonial-swiper p {
    font-size: 1rem;
    line-height: 1.6;
    max-width: 500px;
    margin: 0 auto;
}

/* Swiper pagination noktalarını özelleştirme */
.swiper-pagination-bullet {
    background-color: rgba(79, 70, 229, 0.5);
    opacity: 1;
}

.swiper-pagination-bullet-active {
    background-color: #4f46e5;
}
</style>

<body>
    <main class="auth-cover-wrapper">
        <div class="auth-cover-content-inner">
            <div style=" position: absolute; top: 20px; left: 20px;">
                <!-- <div style="font-weight: 700; font-size: 300%; text-align: center;">
                    Apartman Yönetiminde<br>Yeni Dönem!
                </div> -->
            </div>
            <!-- Sol Tarafı Temsil Eden Ana Konteyner -->
            <div class="auth-hero-side">

                <!-- Arka plandaki ana görseliniz -->
                <img src="assets/images/auth/auth-bg.png" class="img-fluid">

                <!-- YENİ SWIPER SLIDER ALANI -->
                <div class="swiper testimonial-swiper">
                    <div class="swiper-wrapper">
                        <!-- Slide 1 -->
                        <div class="swiper-slide">
                            <div class="quote-icon">“</div>
                            <h2>Topluluğunuzu akıllıca yönetin</h2>
                            <p>Tüm işlemlerinizi kolayca takip edin. YonApp ile kontrol artık parmaklarınızın ucunda!
                            </p>
                        </div>
                        <!-- Slide 2 -->
                        <div class="swiper-slide">
                            <div class="quote-icon">“</div>
                            <h2>Finansal şeffaflık sağlayın</h2>
                            <p>Aidat ve gider takibini kolaylaştırın, tüm sakinlerinizle anında paylaşın.</p>
                        </div>
                        <!-- Slide 3 -->
                        <div class="swiper-slide">
                            <div class="quote-icon">“</div>
                            <h2>İletişimde kalın, güçlü kalın</h2>
                            <p>Duyuru ve anketlerle topluluğunuzla her an bağlantıda olun.</p>
                        </div>
                    </div>
                    <!-- Navigasyon Noktaları -->
                    <div class="swiper-pagination"></div>
                </div>

            </div>
        </div>



        <div class="auth-cover-sidebar-inner">
            <div class="auth-cover-card-wrapper">
                <div class="auth-cover-card p-sm-5">
                    <div class="text-center mb-5">
                        <img src="assets/images/logo/logo.svg" style="max-width: 50%; height: auto;">
                    </div>

                     
                    <?php
                   // --- TEK SATIRDA FLASH MESAJLARI GÖSTERME ---
                   include __DIR__ . '/partials/_flash_messages.php';
                   unset($_SESSION['message']);
                    ?>
                    
                    <h2 class="fs-24 fw-bolder mb-4 text-center">Hoşgeldiniz!</h2>
                    <h4 class="fs-13 fw-bold">Devam etmek için giriş yapın.</h4>

                    <!-- Form action'ı boş bırakmak en güvenlisidir. Güvenli returnUrl yönetimi eklendi. -->
                    <form method="POST"
                        action="sign-in.php<?php echo isset($_GET['returnUrl']) ? '?returnUrl=' . htmlspecialchars($_GET['returnUrl']) : ''; ?>"
                        class="w-100 mt-4 pt-2">
                        <div class="mb-3">
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                                placeholder="E-posta Giriniz" required>
                        </div>
                        <div class="mb-3">
                            <input type="password" class="form-control pe-5" id="password" name="password"
                                placeholder="Şifre Giriniz" required>
                        </div>

                        <!-- ... (Formun geri kalanı aynı kalabilir) ... -->

                        <div class="mt-5">
                            <button type="submit" name="submitForm" class="btn btn-lg btn-primary w-100">Giriş</button>
                        </div>
                        <div class="mt-5 text-muted">
                        <span> Henüz hesabınız yok mu?</span>
                        <a href="/register.php" class="fw-bold">Hesap Oluştur</a>
                    </div>
                    </form>

                    <!-- ... (Formun alt kısmı aynı kalabilir) ... -->
                </div>
            </div>
        </div>
    </main>

    <!-- ... (Tüm JS scriptleriniz ve Swiper JS kodunuz burada, değişmedi) ... -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const swiper = new Swiper('.testimonial-swiper', {
            // Döngüsel olmasını sağlar
            loop: true,

            // Otomatik oynatma
            autoplay: {
                delay: 5000,
                disableOnInteraction: false, // Kullanıcı etkileşiminden sonra durmasın
            },

            // Geçiş efekti (fade daha şık durur)
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },

            // Navigasyon noktaları
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
        });
    });
    </script>
</body>

</html>
<?php ob_end_flush(); ?>