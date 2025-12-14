<?php
ob_start();

$page = 'forgot-password';

define('ROOT', $_SERVER['DOCUMENT_ROOT']);
require_once ROOT . '/Database/require.php';
require_once ROOT . '/Model/UserModel.php';
require_once ROOT . '/Model/PasswordModel.php';

require_once __DIR__ . '/configs/bootstrap.php';

use App\Services\FlashMessageService;

$PasswordModel = new PasswordModel();
$Users = new UserModel();

$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $user = $email !== '' ? $Users->getUserByEmail($email) : null;

    if ($email === '') {
        FlashMessageService::add('error', 'Hata!', 'E-posta adresi boş bırakılamaz.', 'ikaz2.png');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        FlashMessageService::add('error', 'Hata!', 'Geçersiz e-posta adresi.', 'ikaz2.png');
    } elseif (!$user) {
        FlashMessageService::add('error', 'Hata!', 'Bu e-posta adresi ile kayıtlı bir hesap bulunamadı.', 'ikaz2.png');
    } else {
        // 1 saat geçerli token
        $token = bin2hex(random_bytes(32));

        // (Not: Link domain'i ortamınıza göre değişebilir; mevcut akışı bozmamak için aynı dosyayı hedefliyoruz.)
        $resetLink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
            . '://' . $_SERVER['HTTP_HOST']
            . '/reset-password.php?token=' . urlencode($token);

        // Token ve e-posta adresini veritabanına kaydetme
        $PasswordModel->setPasswordReset($email, $token);

        ob_start();
        include 'forgot-password-email.php';
        $content = ob_get_clean();

        try {
            require_once 'mail-settings.php';

            // Alıcılar
            $mail->setFrom('sifre@puantor.com.tr', 'Puantor.com.tr');
            $mail->addAddress($email);
            $mail->isHTML(true);

            $mail->Subject = 'Şifre Sıfırlama';
            $mail->Body = $content;
            $mail->AltBody = strip_tags($content);
            $mail->CharSet = 'UTF-8';

            // PNG dosyasını e-postaya ekleyin
            $mail->AddEmbeddedImage('static/png/lock.png', 'lock-icon');

            $mail->send();
            FlashMessageService::add('success', 'Başarılı!', 'Şifre sıfırlama bağlantısı e-posta adresinize gönderildi.', 'ikaz2.png');
        } catch (Exception $e) {
            FlashMessageService::add('error', 'Hata!', 'E-posta gönderilemedi. Lütfen daha sonra tekrar deneyin.', 'ikaz2.png');
        }
    }
}

include __DIR__ . '/partials/head.php';
?>
<!DOCTYPE html>
<html lang="tr">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<body>
    <main class="auth-cover-wrapper">
        <div class="auth-cover-content-inner">
            <div class="auth-hero-side">
                <img src="assets/images/auth/auth-bg.png" class="img-fluid">

                <div class="swiper testimonial-swiper">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <div class="quote-icon">“</div>
                            <h2>Topluluğunuzu akıllıca yönetin</h2>
                            <p>Tüm işlemlerinizi kolayca takip edin. YonApp ile kontrol artık parmaklarınızın ucunda!</p>
                        </div>
                        <div class="swiper-slide">
                            <div class="quote-icon">“</div>
                            <h2>Finansal şeffaflık sağlayın</h2>
                            <p>Aidat ve gider takibini kolaylaştırın, tüm sakinlerinizle anında paylaşın.</p>
                        </div>
                        <div class="swiper-slide">
                            <div class="quote-icon">“</div>
                            <h2>İletişimde kalın, güçlü kalın</h2>
                            <p>Duyuru ve anketlerle topluluğunuzla her an bağlantıda olun.</p>
                        </div>
                    </div>
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
                    include __DIR__ . '/partials/_flash_messages.php';
                    unset($_SESSION['message']);
                    ?>

                    <h2 class="fs-24 fw-bolder mb-4 text-center">Şifre Sıfırlama</h2>
                    <h4 class="fs-13 fw-bold">Kayıtlı e-posta adresinizi girin.</h4>

                    <form method="POST" action="forgot-password.php" class="w-100 mt-4 pt-2" autocomplete="off">
                        <div class="mb-3">
                            <input
                                type="email"
                                class="form-control"
                                id="email"
                                name="email"
                                value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
                                placeholder="E-posta adresiniz"
                                autocomplete="email"
                                required>
                        </div>

                        <div class="mt-5">
                            <button type="submit" class="btn btn-lg btn-primary w-100">Şifre Sıfırlama Linki Gönder</button>
                        </div>

                        <div class="mt-3 text-muted">
                            <a href="/sign-in.php" class="fw-bold">Giriş ekranına dön</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            try {
                const swiper = new Swiper('.testimonial-swiper', {
                    loop: true,
                    autoplay: {
                        delay: 5000,
                        disableOnInteraction: false,
                    },
                    effect: 'fade',
                    fadeEffect: {
                        crossFade: true
                    },
                    pagination: {
                        el: '.swiper-pagination',
                        clickable: true,
                    },
                });
            } catch (e) {}
        });
    </script>
</body>

</html>
<?php ob_end_flush(); ?>