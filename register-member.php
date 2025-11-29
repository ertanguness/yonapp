<?php
require_once __DIR__ . '/configs/bootstrap.php';

use App\Helper\Security;
use App\Services\FlashMessageService;
use Model\KisilerModel;
use Model\UserModel;

$email = isset($_GET['email']) ? trim($_GET['email']) : '';
$kisiEnc = $_GET['kisi'] ?? '';
$fullName = '';

// Eğer kisi parametresi geldiyse, adı-soyadı ön-doldur
if (!empty($kisiEnc)) {
  try {
    $kisiId = (int) Security::decrypt($kisiEnc);
    if ($kisiId > 0) {
      $Kisiler = new KisilerModel();
      $kisi = $Kisiler->KisiBilgileri($kisiId);
      if ($kisi && !empty($kisi->adi_soyadi)) {
        $fullName = $kisi->adi_soyadi;
      }
    }
  } catch (\Throwable $e) {
    // sessiz geç, form boş kalsın
  }
}

// Eğer e-posta mevcut ise ve zaten kayıtlıysa, giriş sayfasına yönlendir
if (!empty($email)) {
  $User = new UserModel();
  if ($User->isEmailExists($email)) {
    FlashMessageService::add('error', 'Hata!', 'Bu e-posta ile daha önce kayıt olunmuş. Lütfen giriş yapınız.');
    header('Location: /sign-in.php?email=' . urlencode($email));
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="tr">
<?php $page = 'davetli-kayit'; include './partials/head.php'; ?>
<body>
<main class="auth-cover-wrapper">
  <div class="auth-cover-content-inner">
    <div class="auth-cover-content-wrapper">
      <div class="auth-img">
        <img src="assets/images/auth/auth-cover-register-bg.svg" alt="" class="img-fluid">
      </div>
    </div>
  </div>
  <div class="auth-cover-sidebar-inner">
    <div class="auth-cover-card-wrapper">
      <div class="auth-cover-card p-sm-5 ">
        <div class="text-center mb-5">
          <img src="assets/images/logo/logo.svg" style="max-width: 50%; height: auto;">
        </div>
        <h2 class="fs-20 fw-bolder mb-4">Davetli Üyelik Kaydı</h2>
        <?php include_once  'partials/_flash_messages.php'; ?>
        <ul class="nav nav-pills register-tabs" id="registerTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active d-flex align-items-center justify-content-center gap-2" id="email-tab" data-bs-toggle="tab" data-bs-target="#emailPane" type="button" role="tab" aria-controls="emailPane" aria-selected="true">
              <i class="bi bi-envelope"></i>
              <span>Email</span>
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link d-flex align-items-center justify-content-center gap-2" id="phone-tab" data-bs-toggle="tab" data-bs-target="#phonePane" type="button" role="tab" aria-controls="phonePane" aria-selected="false">
              <i class="bi bi-phone"></i>
              <span>Cep telefonu</span>
            </button>
          </li>
        </ul>
        <div class="tab-content mt-3" id="registerTabsContent">
          <div class="tab-pane fade show active" id="emailPane" role="tabpanel" aria-labelledby="email-tab">
            <form action="public/register-member-handler.php" method="POST" autocomplete="off" class="w-100 mt-3" id="emailRegisterForm">
              <input type="hidden" name="action" value="register_member_email">
              <input type="hidden" name="kisi" value="<?= htmlspecialchars($kisiEnc) ?>">
              <div class="mb-3">
                <input type="text" class="form-control" name="full_name" readonly id="full_name" placeholder="Adınız Soyadınız" value="<?= htmlspecialchars($fullName) ?>" required>
              </div>
              <div class="mb-3">
                <input type="email" class="form-control" name="email" id="email" value="<?= htmlspecialchars($email) ?>" placeholder="E-posta" <?= $email ? 'readonly' : '' ?> required>
              </div>
              <div class="mb-3 generate-pass">
                <div class="input-group field">
                  <input type="password" class="form-control password" id="password" name="password" placeholder="Şifre" required>
                  <div class="input-group-text border-start bg-gray-2 c-pointer show-pass" data-bs-toggle="tooltip" title="Şifre Göster"><i></i></div>
                </div>
              </div>
              <div class="mb-3">
                <input type="password" class="form-control" name="password2" id="password2" placeholder="Şifre (Tekrar)" required>
              </div>
              <div class="mt-2">
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="terms_of_service" id="terms_of_service" required>
                  <label class="form-check-label" for="terms_of_service">Üyelik koşullarını kabul ediyorum</label>
                </div>
              </div>
              <div class="mt-3">
                <button type="submit" class="btn btn-lg btn-primary w-100">E-posta ile Kayıt</button>
              </div>
            </form>
          </div>
          <div class="tab-pane fade" id="phonePane" role="tabpanel" aria-labelledby="phone-tab">
            <form action="public/register-member-handler.php" method="POST" autocomplete="off" class="w-100 mt-3" id="phoneRegisterForm">
              <input type="hidden" name="action" value="register_member_phone">
              <input type="hidden" name="kisi" value="<?= htmlspecialchars($kisiEnc) ?>">
              <div class="mb-3">
                <input type="text" class="form-control" name="full_name" id="full_name_phone" placeholder="Adınız Soyadınız" value="<?= htmlspecialchars($fullName) ?>" required>
              </div>
          <div class="row g-2 mb-3">
            <div class="col-4">
              <select class="form-select" name="country_code" id="country_code" required>
                <option value="+90" selected>+90 (TR)</option>
                <option value="+1">+1 (US)</option>
                <option value="+44">+44 (UK)</option>
                <option value="+49">+49 (DE)</option>
              </select>
            </div>
            <div class="col-8">
              <input type="tel" class="form-control" name="phone" id="phone" placeholder="5XX XXX XX XX" required>
            </div>
          </div>
          <div class="mb-3">
            <div class="input-group field">
              <input type="password" class="form-control password" id="password_phone" name="password" placeholder="Şifre" required>
              <div class="input-group-text border-start bg-gray-2 c-pointer show-pass" data-bs-toggle="tooltip" title="Şifre Göster"><i></i></div>
            </div>
          </div>
              <div class="mb-3">
                <input type="password" class="form-control" name="password2" id="password2_phone" placeholder="Şifre (Tekrar)" required>
              </div>
              <div class="mt-2">
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="terms_of_service" id="terms_of_service_phone" required>
                  <label class="form-check-label" for="terms_of_service_phone">Üyelik koşullarını kabul ediyorum</label>
                </div>
              </div>
              <div class="mt-3">
                <button type="submit" class="btn btn-lg btn-success w-100">Cep Telefonu ile Kayıt</button>
              </div>
            </form>
          </div>
        </div>
        <div class="mt-3 text-muted text-center">
          <p class="text-muted mb-0">Hesabınız var mı? <a href="sign-in.php" class="fw-bold">Giriş Yap</a></p>
        </div>
      </div>
    </div>
  </div>
</main>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const showPass = document.querySelector('.show-pass');
    const pass = document.getElementById('password');
    if (showPass && pass) {
      showPass.addEventListener('click', function(){
        pass.type = pass.type === 'password' ? 'text' : 'password';
      });
    }

    const toggles = document.querySelectorAll('.show-pass');
    toggles.forEach(function(t){
      t.addEventListener('click', function(){
        const input = this.parentElement.querySelector('input[type="password"], input[type="text"]');
        if (input) { input.type = input.type === 'password' ? 'text' : 'password'; }
      });
    });
  });
</script>
<style>
.register-tabs{display:flex;gap:.5rem;flex-wrap:nowrap;width:100%}
.register-tabs .nav-item{flex:0 0 50%;display:flex}
.register-tabs .nav-link{width:100%;border-radius:6px;padding:.9rem 1rem;background:#f8f9fa;color:#343a40;border:1px solid #e9ecef;transition:background .2s,transform .1s}
.register-tabs .nav-link:hover{background:#eef2f6}
.register-tabs .nav-link.active{background:#0d6efd;color:#fff;border-color:#0d6efd}
@media (max-width:576px){.register-tabs .nav-link{font-size:.95rem;padding:.7rem .8rem}}
</style>
<?php include './partials/script.php' ?>
<script>
  (function(){
    const emailForm = document.getElementById('emailRegisterForm');
    const phoneForm = document.getElementById('phoneRegisterForm');
    function validatePasswordPair(p1, p2){
      if (!p1 || !p2) return false;
      if (p1.value.length < 6) { alert('Şifre en az 6 karakter olmalı'); return false; }
      if (p1.value !== p2.value) { alert('Şifreler eşleşmiyor'); return false; }
      return true;
    }
    if (emailForm) {
      emailForm.addEventListener('submit', function(e){
        const p1 = document.getElementById('password');
        const p2 = document.getElementById('password2');
        if (!validatePasswordPair(p1, p2)) { e.preventDefault(); }
      });
    }
    if (phoneForm) {
      phoneForm.addEventListener('submit', function(e){
        const p1 = document.getElementById('password_phone');
        const p2 = document.getElementById('password2_phone');
        const cc = document.getElementById('country_code');
        const ph = document.getElementById('phone');
        const digits = (ph.value || '').replace(/\D+/g,'');
        if (digits.length < 8) { alert('Telefon numarasını doğru giriniz'); e.preventDefault(); return; }
        if (!validatePasswordPair(p1, p2)) { e.preventDefault(); }
      });
    }
  })();
</script>
</body>
</html>
