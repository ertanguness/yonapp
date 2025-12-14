<?php

require_once __DIR__ . '/configs/bootstrap.php';

use Model\PasswordModel;
use Model\UserModel;
use App\Services\FlashMessageService;

$PasswordModel = new PasswordModel();
$User = new UserModel();
$logger = \getLogger();

// Token kontrolü
$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$validToken = false;
$email = '';

if (!empty($token)) {
  $resetData = $PasswordModel->getPasswordReset($token);
  if ($resetData) {
    $validToken = true;
    $email = $resetData->email;
  }
}

// POST işlemi - Şifre sıfırlama
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $token = isset($_POST['token']) ? trim($_POST['token']) : '';
  $password = isset($_POST['password']) ? $_POST['password'] : '';
  $password_repeat = isset($_POST['password_repeat']) ? $_POST['password_repeat'] : '';

  // Token tekrar doğrula
  $resetData = $PasswordModel->getPasswordReset($token);

  if (!$resetData) {
    FlashMessageService::add('error', 'Hata!', 'Şifre sıfırlama bağlantısı geçersiz veya süresi dolmuş.', 'ikaz2.png');
  } elseif (empty($password)) {
    FlashMessageService::add('error', 'Hata!', 'Şifre alanı boş bırakılamaz.', 'ikaz2.png');
    $validToken = true;
    $email = $resetData->email;
  } elseif (strlen($password) < 8) {
    FlashMessageService::add('error', 'Hata!', 'Şifre en az 8 karakter uzunluğunda olmalıdır.', 'ikaz2.png');
    $validToken = true;
    $email = $resetData->email;
  } elseif ($password !== $password_repeat) {
    FlashMessageService::add('error', 'Hata!', 'Girilen şifreler eşleşmiyor.', 'ikaz2.png');
    $validToken = true;
    $email = $resetData->email;
  } else {
    // Şifreyi güncelle
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    
    $userUpdated = $User->saveWithAttr([
      "id" => $resetData->id,
      "password" => $hashedPassword
    ]);
    
    if ($userUpdated) {
      
      
      $PasswordModel->deletePasswordReset($token);
      $logger->info("Password reset successful for user: " . $resetData->email);

      FlashMessageService::add('success', 'Başarılı!', 'Şifreniz başarıyla değiştirildi. Yeni şifrenizle giriş yapabilirsiniz.', 'onay2.png');
      header('Location: /sign-in.php');
      exit;
    } else {
      // Güncelleme başarısız
      $errorMsg = 'Şifre güncellenemedi.';
      if ($updatedRows == 0) $errorMsg .= ' Kullanıcı bulunamadı.';
      if (!$verificationResult) $errorMsg .= ' Veritabanı doğrulaması başarısız.';
      
      FlashMessageService::add('error', 'Hata!', $errorMsg, 'ikaz2.png');
      $validToken = true;
      $email = $resetData->email;
    }
  }
}

?>

<!DOCTYPE html>
<html lang="tr">

<?php include __DIR__ . '/partials/head.php'; ?>

</html>

<body>
  <!--! ================================================================ !-->
  <!--! [Başlangıç] Ana İçerik !-->
  <!--! ================================================================ !-->
  <main class="auth-minimal-wrapper">
    <div class="auth-minimal-inner">
      <div class="minimal-card-wrapper">
        <div class="card mb-4 mt-5 mx-4 mx-sm-0 position-relative">


          <div class="card-body p-sm-5">
            <div class="text-center">
              <img src="assets/images/logo/logo.svg" style="max-width: 50%; height: auto;">
            </div>

            <?php
            include __DIR__ . '/partials/_flash_messages.php';
            unset($_SESSION['message']);
            ?>

            <?php if ($validToken): ?>
              <!-- Geçerli token - Şifre değiştirme formu göster -->
              <img src="assets/images/icons/reset1.png" class="img-fluid d-block mx-auto mb-3"
                style="width: 70px; height: 70px;">
              <h2 class="fs-20 fw-bolder mb-4 text-center">Şifre Sıfırlama</h2>

              <p class="fs-14 fw-medium text-muted">Lütfen yeni şifrenizi girin. Şifrenizin en az 8 karakter uzunluğunda
                olduğundan ve hem büyük hem de küçük harfler, rakamlar ve özel karakterler içerdiğinden emin olun.</p>
              <form action="reset-password.php" method="POST" class="w-100 mt-4 pt-2">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
                <div class="mb-4">
                  <input type="password" class="form-control" name="password" id="password" placeholder="Yeni Şifre"
                    required minlength="8">
                </div>
                <div class="mb-4">
                  <input type="password" class="form-control" name="password_repeat" id="password_repeat"
                    placeholder="Şifre tekrar" required minlength="8">
                </div>
                <div class="mb-4">
                  <label class="form-check">
                    <input type="checkbox" class="form-check-input" id="show-password" />
                    <span class="form-check-label">Şifreleri göster</span>
                  </label>
                </div>
                <div class="mt-5">
                  <button type="submit" class="btn btn-lg btn-primary w-100">Şifre Değiştir</button>
                </div>
              </form>
            <?php else: ?>
              <!-- Geçersiz veya süresi dolmuş token -->
              <div class="text-center">
                <img src="assets/images/icons/ikaz2.png" class="img-fluid d-block mx-auto mb-3"
                  style="width: 70px; height: 70px;">
                <h2 class="fs-20 fw-bolder mb-4">Geçersiz Bağlantı</h2>
                <p class="fs-14 fw-medium text-muted mb-4">
                  Şifre sıfırlama bağlantınız geçersiz veya süresi dolmuş.
                  Lütfen yeni bir şifre sıfırlama bağlantısı talep edin.
                </p>
                <div class="mt-4">
                  <a href="/forgot-password.php" class="btn btn-lg btn-primary w-100 mb-3">Yeni Bağlantı Talep Et</a>
                  <a href="/sign-in.php" class="btn btn-lg btn-outline-secondary w-100">Giriş Sayfasına Dön</a>
                </div>
              </div>
            <?php endif; ?>

          </div>
        </div>
      </div>
    </div>
  </main>
  <!--! ================================================================ !-->
  <!--! [Bitiş] Ana İçerik !-->
  <!--! ================================================================ !-->
  <?php include './partials/theme-customizer.php' ?>
  <!--<< Tüm JS Eklentileri >>-->
  <?php include './partials/script.php' ?>
  <script>
    //Şifreleri göster
    $(document).ready(function () {
      $('#show-password').click(function () {
        if ($(this).is(':checked')) {
          $('#password, #password_repeat').attr('type', 'text');
        } else {
          $('#password, #password_repeat').attr('type', 'password');
        }
      });
    });
  </script>
</body>

</html>