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
        <form action="public/register-member-handler.php" method="POST" autocomplete="off" class="w-100 mt-4 pt-2">
          <input type="hidden" name="action" value="register_member">
          <input type="hidden" name="kisi" value="<?= htmlspecialchars($kisiEnc) ?>">
          <div class="mb-4">
            <input type="text" class="form-control" name="full_name" id="full_name" placeholder="Adınız Soyadınız" value="<?= htmlspecialchars($fullName) ?>" required>
          </div>
          <div class="mb-4">
            <input type="email" class="form-control" name="email" id="email" value="<?= htmlspecialchars($email) ?>" placeholder="E-posta" <?= $email ? 'readonly' : '' ?> required>
          </div>
          <div class="mb-4 generate-pass">
            <div class="input-group field">
              <input type="password" class="form-control password" id="password" name="password" placeholder="Şifre" required>
              <div class="input-group-text border-start bg-gray-2 c-pointer show-pass" data-bs-toggle="tooltip" title="Şifre Göster"><i></i></div>
            </div>
          </div>
          <div class="mb-4">
            <input type="password" class="form-control" name="password2" id="password2" placeholder="Şifre (Tekrar)" required>
          </div>
          <div class="mt-3">
            <div class="form-check">
              <input type="checkbox" class="form-check-input" name="terms_of_service" id="terms_of_service" required>
              <label class="form-check-label" for="terms_of_service">Üyelik koşullarını kabul ediyorum</label>
            </div>
          </div>
          <div class="mt-4">
            <button type="submit" class="btn btn-lg btn-primary w-100">Kaydı Tamamla</button>
          </div>
        </form>
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
  });
</script>
</body>
</html>
