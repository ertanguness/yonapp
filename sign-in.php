<?php
ob_start();

$page = "sign-in";


require_once __DIR__ . '/configs/bootstrap.php';

// Artık Controller'ları ve diğer sınıfları güvenle kullanabiliriz.
use App\Controllers\AuthController;
use Model\UserModel;
use App\Services\FlashMessageService;

unset($_SESSION['user']);


$errors = [];
// Sadece POST isteği varsa kontrolcüyü çalıştır
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'choose_role') {
    $token = $_POST['token'] ?? '';
    $selectedId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $candidates = $_SESSION['role_select_candidates'] ?? [];
    $ids = array_map(function ($c) {
        return (int)$c['id'];
    }, $candidates);
    if (!empty($_SESSION['role_select_csrf']) && hash_equals($_SESSION['role_select_csrf'], $token) && in_array($selectedId, $ids, true)) {
        $userModel = new UserModel();
        $user = $userModel->getUser($selectedId);
        unset($_SESSION['role_select_candidates'], $_SESSION['role_select_csrf']);
        if (isset($_SESSION['role_select_returnUrl'])) {
            $_GET['returnUrl'] = $_SESSION['role_select_returnUrl'];
            unset($_SESSION['role_select_returnUrl']);
        }
        AuthController::performLogin($user);
    } else {
        FlashMessageService::add('error', 'Seçim geçersiz', 'Rol seçimi doğrulanamadı.', 'ikaz2.png');
        header("Location: /sign-in.php");
        exit();
    }
}

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

    .modal-backdrop {
        background: radial-gradient(90% 140% at 50% 50%, rgba(17, 24, 39, .65) 0%, rgba(2, 6, 23, .85) 100%);
        backdrop-filter: blur(6px);
    }

    .modal-content.role-select {
        border-radius: 24px;
        border: 1px solid rgba(255, 255, 255, .14);
        background: linear-gradient(180deg, rgba(255, 255, 255, .16), rgba(255, 255, 255, .10));
        backdrop-filter: blur(20px) saturate(140%);
        box-shadow: 0 30px 60px rgba(0, 0, 0, .35), inset 0 1px 0 rgba(255, 255, 255, .06);
        color: #e5e7eb;
    }

    .role-modal-header {
        border-bottom: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        padding: 24px 24px 6px;
        background: transparent
    }

    .role-avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .14);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .24)
    }

    .role-avatar i {
        font-size: 32px
    }

    .modal-title.role-title {
        font-weight: 800;
        color: #fff
    }

    .role-subtitle {
        font-size: 13px;
        color: #cbd5e1
    }

    .role-list {
        display: block;
        margin-top: 6px
    }

    .role-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 14px;
        border: 1px solid rgba(255, 255, 255, .18);
        background: rgba(255, 255, 255, .10);
        border-radius: 18px;
        margin-bottom: 12px;
        cursor: pointer;
        transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease, background-color .15s ease
    }

    .role-item:hover {
        transform: translateY(-1px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, .24);
        border-color: rgba(255, 255, 255, .28);
        background: rgba(255, 255, 255, .14)
    }

    .role-item.selected {
        border-color: #3b82f6;
        background: rgba(59, 130, 246, .18);
        box-shadow: 0 24px 48px rgba(59, 130, 246, .35)
    }

    .role-item .form-check-input {
        position: absolute;
        opacity: 0;
        pointer-events: none
    }

    .role-item .role-icon {
        position: relative;
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, .18);
        color: #60a5fa;
        margin-right: 2px;
        font-size: 20px;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .24)
    }

    .role-item.selected .role-icon::after {
        content: '\F26A';
        /* bi-check-circle */
        font-family: 'bootstrap-icons';
        position: absolute;
        right: -6px;
        bottom: -6px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #3b82f6;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        box-shadow: 0 8px 20px rgba(59, 130, 246, .45)
    }

    .role-item .role-text {
        display: flex;
        flex-direction: column
    }

    .role-item .role-name {
        font-weight: 700;
        color: #fff
    }

    .role-item .role-badge {
        font-size: 12px;
        color: #e2e8f0
    }

    .role-item .role-go {
        margin-left: auto;
        border: none;
        background: rgba(255, 255, 255, .14);
        color: #fff;
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background .15s ease, transform .15s ease
    }

    .role-item .role-go:hover {
        background: #3b82f6;
        transform: translateX(1px)
    }

    .role-item .favorite-toggle {
        margin-left: 8px;
        border: none;
        background: transparent;
        color: #cbd5e1;
        padding: 6px;
        border-radius: 10px
    }

    .role-item .favorite-toggle:hover {
        color: #60a5fa;
        background: rgba(96, 165, 250, .12)
    }

    .role-item .favorite-toggle.active {
        color: #f59e0b
    }

    #roleSelectSubmit[disabled] {
        opacity: .6;
        cursor: not-allowed
    }

    .modal-footer {
        border-top: none;
        padding: 18px 24px
    }

    .modal-footer .btn {
        border-radius: 14px;
        padding: 10px 16px
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
                            <input type="text" class="form-control" id="email" name="email"
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                                placeholder="E-posta veya telefon giriniz" required>
                        </div>
                        <div class="mb-3">
                            <input type="password" class="form-control pe-5" id="password" name="password"
                                placeholder="Şifre Giriniz" required>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="custom-control custom-checkbox ms-1">
                                    <input type="checkbox" class="custom-control-input" id="rememberMe">
                                    <label class="custom-control-label c-pointer" for="rememberMe">Beni Hatırla</label>
                                </div>
                            </div>
                            <div>
                                <a href="/forgot-password.php" class="fs-11 text-primary">Şifremi Unuttum?</a>
                            </div>
                        </div>

                        <!-- ... (Formun geri kalanı aynı kalabilir) ... -->

                        <div class="mt-5">
                            <button type="submit" name="submitForm" class="btn btn-lg btn-primary w-100">Giriş</button>
                        </div>

                        
                        <div class="mt-3 text-muted">
                            <span> Henüz hesabınız yok mu?</span>
                            <a href="/register.php" class="fw-bold">Hesap Oluştur</a>

                        </div>

                    </form>

                    <?php if (isset($_SESSION['role_select_candidates']) && is_array($_SESSION['role_select_candidates']) && count($_SESSION['role_select_candidates']) > 1): ?>
                        <div class="modal fade" id="roleSelectModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content role-select">
                                    <div class="modal-header role-modal-header">
                                        <div class="role-avatar"><i class="bi bi-person"></i></div>
                                        <h5 class="modal-title role-title">Giriş Rolünü Seçin</h5>
                                        <div class="role-subtitle">Giriş yapmak istediğiniz rolü seçin.</div>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST" action="sign-in.php<?php echo isset($_GET['returnUrl']) ? '?returnUrl=' . htmlspecialchars($_GET['returnUrl']) : ''; ?>" id="roleSelectForm">
                                            <input type="hidden" name="action" value="choose_role" />
                                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_SESSION['role_select_csrf']); ?>" />
                                            <div class="role-list">
                                                <?php foreach ($_SESSION['role_select_candidates'] as $c): ?>
                                                    <?php $rn = strtolower($c['role_name'] ?? '');
                                                    $icon = 'bi-person-badge';
                                                    if (strpos($rn, 'sak') !== false) {
                                                        $icon = 'bi-house-heart';
                                                    } elseif (strpos($rn, 'yönet') !== false || strpos($rn, 'admin') !== false) {
                                                        $icon = 'bi-shield-check';
                                                    } ?>
                                                    <label class="role-item" data-id="<?php echo (int)$c['id']; ?>">
                                                        <input class="form-check-input me-3" type="radio" name="user_id" value="<?php echo (int)$c['id']; ?>">
                                                        <span class="role-icon"><i class="bi <?php echo $icon; ?>"></i></span>
                                                        <span class="role-text">
                                                            <span class="role-name"><?php echo htmlspecialchars($c['full_name'] ?? ''); ?></span>
                                                            <span class="role-badge"><?php echo htmlspecialchars($c['role_name'] ?? ''); ?></span>
                                                        </span>
                                                        <button type="button" class="role-go" aria-label="Seç">
                                                            <i class="bi bi-arrow-right"></i>
                                                        </button>
                                                        <button type="button" class="favorite-toggle" aria-label="Favori">
                                                            <i class="bi bi-star"></i>
                                                        </button>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" id="roleSelectCancel">İptal</button>
                                        <button type="button" class="btn btn-primary" id="roleSelectSubmit" disabled>Devam Et</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                var m = document.getElementById('roleSelectModal');
                                if (m) {
                                    m.classList.add('show');
                                    m.style.display = 'block';
                                    document.body.classList.add('modal-open');
                                    var backdrop = document.createElement('div');
                                    backdrop.className = 'modal-backdrop fade show';
                                    document.body.appendChild(backdrop);
                                    var submitBtn = document.getElementById('roleSelectSubmit');
                                    var cancelBtn = document.getElementById('roleSelectCancel');
                                    var form = document.getElementById('roleSelectForm');
                                    var options = form.querySelectorAll('.role-item');
                                    var list = form.querySelector('.role-list');

                                    function applyFavoriteUI() {
                                        options.forEach(function(opt) {
                                            var id = opt.getAttribute('data-id');
                                            var btn = opt.querySelector('.favorite-toggle');
                                            if (!btn) return;
                                            var isActive = btn.getAttribute('data-active') === '1';
                                            if (isActive) {
                                                btn.classList.add('active');
                                                btn.querySelector('i').className = 'bi bi-star-fill';
                                            } else {
                                                btn.classList.remove('active');
                                                btn.querySelector('i').className = 'bi bi-star';
                                            }
                                        });
                                    }

                                    function sortList() {
                                        var items = Array.prototype.slice.call(list.querySelectorAll('.role-item'));
                                        items.sort(function(a, b) {
                                            var fa = a.querySelector('.favorite-toggle')?.getAttribute('data-active') === '1' ? 1 : 0;
                                            var fb = b.querySelector('.favorite-toggle')?.getAttribute('data-active') === '1' ? 1 : 0;
                                            if (fa !== fb) return fb - fa;
                                            var ua = parseInt(a.getAttribute('data-usage') || '0', 10);
                                            var ub = parseInt(b.getAttribute('data-usage') || '0', 10);
                                            if (!isNaN(ua) && !isNaN(ub) && ua !== ub) return ub - ua;
                                            var na = (a.querySelector('.role-name')?.textContent || '').toLowerCase();
                                            var nb = (b.querySelector('.role-name')?.textContent || '').toLowerCase();
                                            return na.localeCompare(nb);
                                        });
                                        items.forEach(function(it) {
                                            list.appendChild(it);
                                        });
                                        options = form.querySelectorAll('.role-item');
                                    }

                                    function selectDefaultTop() {
                                        var first = list.querySelector('.role-item');
                                        if (!first) return;
                                        options.forEach(function(o) {
                                            o.classList.remove('selected');
                                        });
                                        first.classList.add('selected');
                                        var radio = first.querySelector('input[type="radio"]');
                                        if (radio) {
                                            radio.checked = true;
                                            submitBtn.removeAttribute('disabled');
                                        }
                                    }

                                    options.forEach(function(opt) {
                                        opt.addEventListener('click', function() {
                                            options.forEach(function(o) {
                                                o.classList.remove('selected');
                                            });
                                            opt.classList.add('selected');
                                            var radio = opt.querySelector('input[type="radio"]');
                                            if (radio) {
                                                radio.checked = true;
                                                submitBtn.removeAttribute('disabled');
                                            }
                                        });
                                        opt.addEventListener('dblclick', function() {
                                            if (!submitBtn.hasAttribute('disabled')) {
                                                form.submit();
                                            }
                                        });
                                        var favBtn = opt.querySelector('.favorite-toggle');
                                        if (favBtn) {
                                            favBtn.addEventListener('click', function(e) {
                                                e.stopPropagation();
                                                var id = opt.getAttribute('data-id');
                                                var active = favBtn.getAttribute('data-active') === '1';
                                                var fd = new FormData();
                                                fd.append('action', 'toggle_favorite');
                                                fd.append('user_id', id);
                                                fd.append('token', '<?php echo isset($_SESSION['role_select_csrf']) ? htmlspecialchars($_SESSION['role_select_csrf']) : '' ?>');
                                                fd.append('fav', active ? '0' : '1');
                                                fetch('api/role-preferences.php', {
                                                        method: 'POST',
                                                        body: fd
                                                    })
                                                    .then(function(r) {
                                                        return r.json();
                                                    })
                                                    .then(function(j) {
                                                        if (j && j.ok) {
                                                            favBtn.setAttribute('data-active', active ? '0' : '1');
                                                            applyFavoriteUI();
                                                            sortList();
                                                        }
                                                    });
                                            });
                                        }
                                        var goBtn = opt.querySelector('.role-go');
                                        if (goBtn) {
                                            goBtn.addEventListener('click', function(e) {
                                                e.stopPropagation();
                                                options.forEach(function(o) {
                                                    o.classList.remove('selected');
                                                });
                                                opt.classList.add('selected');
                                                var radio = opt.querySelector('input[type="radio"]');
                                                if (radio) {
                                                    radio.checked = true;
                                                    submitBtn.removeAttribute('disabled');
                                                }
                                                if (!submitBtn.hasAttribute('disabled')) {
                                                    var checked = form.querySelector('input[name="user_id"]:checked');
                                                    if (checked) {
                                                        var fd = new FormData();
                                                        fd.append('action', 'inc_usage');
                                                        fd.append('user_id', checked.value);
                                                        fd.append('token', '<?php echo isset($_SESSION['role_select_csrf']) ? htmlspecialchars($_SESSION['role_select_csrf']) : '' ?>');
                                                        fetch('api/role-preferences.php', {
                                                            method: 'POST',
                                                            body: fd
                                                        }).finally(function() {
                                                            form.submit();
                                                        });
                                                    }
                                                }
                                            });
                                        }
                                    });
                                    submitBtn.addEventListener('click', function() {
                                        var checked = form.querySelector('input[name="user_id"]:checked');
                                        if (checked) {
                                            var fd = new FormData();
                                            fd.append('action', 'inc_usage');
                                            fd.append('user_id', checked.value);
                                            fd.append('token', '<?php echo isset($_SESSION['role_select_csrf']) ? htmlspecialchars($_SESSION['role_select_csrf']) : '' ?>');
                                            fetch('api/role-preferences.php', {
                                                method: 'POST',
                                                body: fd
                                            }).finally(function() {
                                                form.submit();
                                            });
                                        }
                                    });
                                    cancelBtn.addEventListener('click', function() {
                                        m.classList.remove('show');
                                        m.style.display = 'none';
                                        document.body.classList.remove('modal-open');
                                        var bd = document.querySelector('.modal-backdrop');
                                        if (bd) bd.remove();
                                    });
                                    document.addEventListener('keydown', function(e) {
                                        if (e.key === 'Enter') {
                                            if (!submitBtn.hasAttribute('disabled')) {
                                                var checked = form.querySelector('input[name="user_id"]:checked');
                                                if (checked) {
                                                    var fd = new FormData();
                                                    fd.append('action', 'inc_usage');
                                                    fd.append('user_id', checked.value);
                                                    fd.append('token', '<?php echo isset($_SESSION['role_select_csrf']) ? htmlspecialchars($_SESSION['role_select_csrf']) : '' ?>');
                                                    fetch('api/role-preferences.php', {
                                                        method: 'POST',
                                                        body: fd
                                                    }).finally(function() {
                                                        form.submit();
                                                    });
                                                }
                                            }
                                        }
                                    });
                                    options.forEach(function(opt) {
                                        var id = opt.getAttribute('data-id');
                                        var favBtn = opt.querySelector('.favorite-toggle');
                                        if (favBtn) {
                                            favBtn.setAttribute('data-active', '0');
                                        }
                                        opt.setAttribute('data-usage', '0');
                                    });
                                    fetch('api/role-preferences.php', {
                                        method: 'POST',
                                        body: (function() {
                                            var f = new FormData();
                                            f.append('action', 'status');
                                            f.append('token', '<?php echo isset($_SESSION['role_select_csrf']) ? htmlspecialchars($_SESSION['role_select_csrf']) : '' ?>');
                                            return f;
                                        })()
                                    }).then(function(r) {
                                        return r.json();
                                    }).then(function(j) {
                                        if (j && j.data) {
                                            options.forEach(function(opt) {
                                                var id = opt.getAttribute('data-id');
                                                var favBtn = opt.querySelector('.favorite-toggle');
                                                if (j.data[id]) {
                                                    var d = j.data[id];
                                                    if (favBtn) {
                                                        favBtn.setAttribute('data-active', d.fav ? '1' : '0');
                                                    }
                                                    opt.setAttribute('data-usage', String(d.cnt || 0));
                                                }
                                            });
                                        }
                                    }).catch(function() {}).finally(function() {
                                        applyFavoriteUI();
                                        sortList();
                                        selectDefaultTop();
                                    });
                                }
                            });
                        </script>
                    <?php endif; ?>
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