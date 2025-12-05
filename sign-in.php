<?php
ob_start();
$page = "sign-in";


require_once __DIR__ . '/configs/bootstrap.php';

// Artık Controller'ları ve diğer sınıfları güvenle kullanabiliriz.
use App\Controllers\AuthController;
use Model\UserModel;
use App\Services\FlashMessageService;

$errors = [];
// Sadece POST isteği varsa kontrolcüyü çalıştır
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'choose_role') {
    $token = $_POST['token'] ?? '';
    $selectedId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $candidates = $_SESSION['role_select_candidates'] ?? [];
    $ids = array_map(function($c){ return (int)$c['id']; }, $candidates);
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
.modal-content{border-radius:14px;border:1px solid #e5e7eb;box-shadow:0 18px 36px rgba(17,24,39,.12)}
/* .modal-header{background:#111827;color:#fff;border-bottom:none;border-top-left-radius:14px;border-top-right-radius:14px} */
.modal-title{font-weight:700}
.role-list{display:block}
.role-item{display:flex;align-items:center;padding:12px;border:1px solid #e5e7eb;border-radius:12px;margin-bottom:10px;cursor:pointer;transition:box-shadow .15s ease,border-color .15s ease,background-color .15s ease}
.role-item:hover{box-shadow:0 8px 24px rgba(0,0,0,.06);border-color:#d1d5db;background-color:#f9fafb}
.role-item.selected{border-color:#4f46e5;background:#f5f7ff;box-shadow:0 8px 24px rgba(79,70,229,.16)}
.role-item .role-icon{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:#eef2ff;color:#4f46e5;margin-right:12px;font-size:18px}
.role-item .role-text{display:flex;flex-direction:column}
.role-item .role-name{font-weight:600}
.role-item .role-badge{font-size:12px;color:#64748b}
.role-item .favorite-toggle{margin-left:auto;border:none;background:transparent;color:#9ca3af;padding:6px;border-radius:8px}
.role-item .favorite-toggle:hover{color:#4f46e5;background:#eef2ff}
.role-item .favorite-toggle.active{color:#f59e0b}
#roleSelectSubmit[disabled]{opacity:.6;cursor:not-allowed}
.modal-footer{border-top:none}
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

                        <!-- ... (Formun geri kalanı aynı kalabilir) ... -->

                        <div class="mt-5">
                            <button type="submit" name="submitForm" class="btn btn-lg btn-primary w-100">Giriş</button>
                        </div>
                        <div class="mt-5 text-muted">
                        <span> Henüz hesabınız yok mu?</span>
                        <a href="/register.php" class="fw-bold">Hesap Oluştur</a>
                    </div>
                    </form>

                    <?php if (isset($_SESSION['role_select_candidates']) && is_array($_SESSION['role_select_candidates']) && count($_SESSION['role_select_candidates']) > 1): ?>
                    <div class="modal fade" id="roleSelectModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Giriş Rolünü Seçin</h5>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" action="sign-in.php<?php echo isset($_GET['returnUrl']) ? '?returnUrl=' . htmlspecialchars($_GET['returnUrl']) : ''; ?>" id="roleSelectForm">
                                        <input type="hidden" name="action" value="choose_role" />
                                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($_SESSION['role_select_csrf']); ?>" />
                                        <div class="role-list">
                                            <?php foreach ($_SESSION['role_select_candidates'] as $c): ?>
                                                <?php $rn = strtolower($c['role_name'] ?? ''); $icon = 'bi-person-badge'; if (strpos($rn,'sak') !== false) { $icon = 'bi-house-heart'; } elseif (strpos($rn,'yönet') !== false || strpos($rn,'admin') !== false) { $icon = 'bi-shield-check'; } ?>
                                                <label class="role-item" data-id="<?php echo (int)$c['id']; ?>">
                                                    <input class="form-check-input me-3" type="radio" name="user_id" value="<?php echo (int)$c['id']; ?>">
                                                    <span class="role-icon"><i class="bi <?php echo $icon; ?>"></i></span>
                                                    <span class="role-text">
                                                        <span class="role-name"><?php echo htmlspecialchars($c['full_name'] ?? ''); ?></span>
                                                        <span class="role-badge"><?php echo htmlspecialchars($c['role_name'] ?? ''); ?></span>
                                                    </span>
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
                        document.addEventListener('DOMContentLoaded', function () {
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

                                function applyFavoriteUI(){
                                    options.forEach(function(opt){
                                        var id = opt.getAttribute('data-id');
                                        var btn = opt.querySelector('.favorite-toggle');
                                        if (!btn) return;
                                        var isActive = btn.getAttribute('data-active') === '1';
                                        if (isActive) { btn.classList.add('active'); btn.querySelector('i').className = 'bi bi-star-fill'; }
                                        else { btn.classList.remove('active'); btn.querySelector('i').className = 'bi bi-star'; }
                                    });
                                }
                                function sortList(){
                                    var items = Array.prototype.slice.call(list.querySelectorAll('.role-item'));
                                    items.sort(function(a,b){
                                        var fa = a.querySelector('.favorite-toggle')?.getAttribute('data-active') === '1' ? 1 : 0;
                                        var fb = b.querySelector('.favorite-toggle')?.getAttribute('data-active') === '1' ? 1 : 0;
                                        if (fa !== fb) return fb - fa;
                                        var ua = parseInt(a.getAttribute('data-usage')||'0',10);
                                        var ub = parseInt(b.getAttribute('data-usage')||'0',10);
                                        if (!isNaN(ua) && !isNaN(ub) && ua !== ub) return ub - ua;
                                        var na = (a.querySelector('.role-name')?.textContent||'').toLowerCase();
                                        var nb = (b.querySelector('.role-name')?.textContent||'').toLowerCase();
                                        return na.localeCompare(nb);
                                    });
                                    items.forEach(function(it){ list.appendChild(it); });
                                    options = form.querySelectorAll('.role-item');
                                }

                                function selectDefaultTop(){
                                    var first = list.querySelector('.role-item');
                                    if (!first) return;
                                    options.forEach(function(o){ o.classList.remove('selected'); });
                                    first.classList.add('selected');
                                    var radio = first.querySelector('input[type="radio"]');
                                    if (radio) { radio.checked = true; submitBtn.removeAttribute('disabled'); }
                                }

                                options.forEach(function(opt){
                                    opt.addEventListener('click', function(){
                                        options.forEach(function(o){ o.classList.remove('selected'); });
                                        opt.classList.add('selected');
                                        var radio = opt.querySelector('input[type="radio"]');
                                        if (radio) { radio.checked = true; submitBtn.removeAttribute('disabled'); }
                                    });
                                    opt.addEventListener('dblclick', function(){ if (!submitBtn.hasAttribute('disabled')) { form.submit(); } });
                                    var favBtn = opt.querySelector('.favorite-toggle');
                                    if (favBtn) {
                                        favBtn.addEventListener('click', function(e){
                                            e.stopPropagation();
                                            var id = opt.getAttribute('data-id');
                                            var active = favBtn.getAttribute('data-active') === '1';
                                            var fd = new FormData();
                                            fd.append('action','toggle_favorite');
                                            fd.append('user_id', id);
                                            fd.append('token', '<?php echo isset($_SESSION['role_select_csrf']) ? htmlspecialchars($_SESSION['role_select_csrf']) : '' ?>');
                                            fd.append('fav', active ? '0' : '1');
                                            fetch('api/role-preferences.php', { method:'POST', body: fd })
                                                .then(function(r){ return r.json(); })
                                                .then(function(j){ if (j && j.ok){ favBtn.setAttribute('data-active', active ? '0' : '1'); applyFavoriteUI(); sortList(); } });
                                        });
                                    }
                                });
                                submitBtn.addEventListener('click', function(){
                                    var checked = form.querySelector('input[name="user_id"]:checked');
                                    if (checked) {
                                        var fd = new FormData();
                                        fd.append('action','inc_usage');
                                        fd.append('user_id', checked.value);
                                        fd.append('token', '<?php echo isset($_SESSION['role_select_csrf']) ? htmlspecialchars($_SESSION['role_select_csrf']) : '' ?>');
                                        fetch('api/role-preferences.php', { method:'POST', body: fd }).finally(function(){ form.submit(); });
                                    }
                                });
                                cancelBtn.addEventListener('click', function(){
                                    m.classList.remove('show');
                                    m.style.display = 'none';
                                    document.body.classList.remove('modal-open');
                                    var bd = document.querySelector('.modal-backdrop');
                                    if (bd) bd.remove();
                                });
                                document.addEventListener('keydown', function(e){
                                    if (e.key === 'Enter') {
                                        if (!submitBtn.hasAttribute('disabled')) {
                                            var checked = form.querySelector('input[name="user_id"]:checked');
                                            if (checked) {
                                                var fd = new FormData();
                                                fd.append('action','inc_usage');
                                                fd.append('user_id', checked.value);
                                                fd.append('token', '<?php echo isset($_SESSION['role_select_csrf']) ? htmlspecialchars($_SESSION['role_select_csrf']) : '' ?>');
                                                fetch('api/role-preferences.php', { method:'POST', body: fd }).finally(function(){ form.submit(); });
                                            }
                                        }
                                    }
                                });
                                options.forEach(function(opt){
                                    var id = opt.getAttribute('data-id');
                                    var favBtn = opt.querySelector('.favorite-toggle');
                                    if (favBtn) { favBtn.setAttribute('data-active','0'); }
                                    opt.setAttribute('data-usage','0');
                                });
                                fetch('api/role-preferences.php', { method:'POST', body: (function(){ var f=new FormData(); f.append('action','status'); f.append('token', '<?php echo isset($_SESSION['role_select_csrf']) ? htmlspecialchars($_SESSION['role_select_csrf']) : '' ?>'); return f; })() }).then(function(r){ return r.json(); }).then(function(j){
                                    if (j && j.data) {
                                        options.forEach(function(opt){
                                            var id = opt.getAttribute('data-id');
                                            var favBtn = opt.querySelector('.favorite-toggle');
                                            if (j.data[id]) {
                                                var d = j.data[id];
                                                if (favBtn) { favBtn.setAttribute('data-active', d.fav ? '1' : '0'); }
                                                opt.setAttribute('data-usage', String(d.cnt||0));
                                            }
                                        });
                                    }
                                }).catch(function(){ }).finally(function(){ applyFavoriteUI(); sortList(); selectDefaultTop(); });
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
