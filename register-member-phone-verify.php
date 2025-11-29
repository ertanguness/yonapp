<?php
require_once __DIR__ . '/configs/bootstrap.php';

use App\Services\FlashMessageService;
use App\Helper\Security;

$uid = $_GET['uid'] ?? '';
?>
<!DOCTYPE html>
<html lang="tr">
<?php $page = 'phone-verify'; include './partials/head.php'; ?>
<body>
<main class="auth-minimal-wrapper">
  <div class="auth-minimal-inner">
    <div class="minimal-card-wrapper">
      <div class="card mb-4 mt-5 mx-4 mx-sm-0 position-relative">
        <div class="card-body p-sm-5">
          <div class="text-center mb-5">
            <img src="assets/images/logo/logo.svg" style="max-width: 50%; height: auto;">
          </div>
          <h2 class="fs-20 fw-bolder mb-3 text-center">Telefon Doğrulama</h2>
          <?php include_once  'partials/_flash_messages.php'; ?>
          <form action="public/register-member-phone-verify-handler.php" method="POST" class="w-100 mt-3">
            <input type="hidden" name="action" value="verify_phone">
            <input type="hidden" name="uid" value="<?= htmlspecialchars($uid) ?>">
            <div class="mb-3">
              <label for="code" class="form-label">SMS ile gönderilen 6 haneli kod</label>
              <input type="text" class="form-control" id="code" name="code" maxlength="6" pattern="\\d{6}" placeholder="123456" required>
            </div>
            <div class="mt-3">
              <button type="submit" class="btn btn-lg btn-success w-100">Doğrula</button>
            </div>
          </form>
          <div class="mt-3 text-center">
            <a href="register-member.php" class="text-muted">Kayıt sayfasına dön</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
<?php include './partials/script.php' ?>
</body>
</html>