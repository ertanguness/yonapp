<?php

require_once __DIR__ . '/configs/bootstrap.php';

use App\Helper\Helper;
use Model\UserModel;
use Model\PasswordModel;
use App\Services\FlashMessageService;
use App\Services\MailGonderService;

$PasswordModel = new PasswordModel();
$Users = new UserModel();

$page = 'forgot-password';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $user = $email !== '' ? $Users->getUserByEmail($email) : null;

//Helper::dd($user);

    if ($email === '') {
        FlashMessageService::add('error', 'Hata!', 'E-posta adresi boş bırakılamaz.', 'ikaz2.png');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        FlashMessageService::add('error', 'Hata!', 'Geçersiz e-posta adresi.', 'ikaz2.png');
    } elseif (!$user) {
        FlashMessageService::add('error', 'Hata!', 'Bu e-posta adresi ile kayıtlı bir hesap bulunamadı.', 'ikaz2.png');
    } else {
        // 1 saat geçerli token
        try {
        $token = bin2hex(random_bytes(32));

        // (Not: Link domain'i ortamınıza göre değişebilir; mevcut akışı bozmamak için aynı dosyayı hedefliyoruz.)
        $resetLink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
            . '://' . $_SERVER['HTTP_HOST']
            . '/reset-password.php?token=' . urlencode($token);

        // Token ve e-posta adresini veritabanına kaydetme
        $PasswordModel->setPasswordReset($email, $token);


$mailBody = '
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Şifre Sıfırlama</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;padding:30px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;">
                    
                    <!-- HEADER -->
                    <tr>
                        <td style="background:#2563eb;color:#ffffff;padding:20px 30px;">
                            <h2 style="margin:0;font-size:22px;">Şifre Sıfırlama Talebi</h2>
                        </td>
                    </tr>

                    <!-- CONTENT -->
                    <tr>
                        <td style="padding:30px;color:#374151;font-size:14px;line-height:1.6;">
                            <p>Merhaba,</p>

                            <p>
                                Hesabınız için bir <strong>şifre sıfırlama talebi</strong> oluşturuldu.
                                Aşağıdaki butona tıklayarak yeni şifrenizi belirleyebilirsiniz.
                            </p>

                            <p style="text-align:center;margin:30px 0;">
                                <a href="'.$resetLink.'" 
                                   style="background:#2563eb;color:#ffffff;
                                          padding:12px 26px;text-decoration:none;
                                          border-radius:6px;font-weight:bold;
                                          display:inline-block;">
                                    Şifremi Sıfırla
                                </a>
                            </p>

                            <p>
                                Bu bağlantı <strong>güvenlik nedeniyle sınırlı süre</strong> için geçerlidir.
                                Eğer bu işlemi siz başlatmadıysanız, bu e-postayı dikkate almayınız.
                            </p>

                            <hr style="border:none;border-top:1px solid #e5e7eb;margin:25px 0;">

                            <p style="font-size:12px;color:#6b7280;">
                                Buton çalışmazsa aşağıdaki bağlantıyı tarayıcınıza yapıştırabilirsiniz:
                            </p>
                            <p style="font-size:12px;word-break:break-all;color:#2563eb;">
                                '.$resetLink.'
                            </p>
                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td style="background:#f9fafb;padding:15px 30px;
                                   font-size:12px;color:#6b7280;text-align:center;">
                            © '.date('Y').' YonApp | Tüm hakları saklıdır
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
';






            if(MailGonderService::gonder([$email], 'Şifre Sıfırlama', $mailBody)) {
                FlashMessageService::add('success', 'Başarılı!', 'Şifre sıfırlama bağlantısı e-posta adresinize gönderildi.', 'onay2.png');
                header('Location: /sign-in.php');
                exit;
            }
        } catch (Exception $e) {
            FlashMessageService::add('error', 'Hata!', 'E-posta gönderilemedi. Lütfen daha sonra tekrar deneyin.', 'ikaz2.png');
        }
    }
}

include __DIR__ . '/partials/head.php'; ?>

<!DOCTYPE html>
<html lang="tr">

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