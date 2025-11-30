<?php
require_once __DIR__ . '/configs/bootstrap.php';

use App\Services\FlashMessageService;
use App\Helper\Security;
use Database\Db;

$uid = $_GET['uid'] ?? '';
$vid = $_GET['vid'] ?? '';
$maskedPhone = '';
try {
  $pdo = Db::getInstance()->connect();
  if (!empty($vid)) {
    $verifyId = Security::decrypt($vid);
    $stmt = $pdo->prepare('SELECT phone, code FROM user_phone_verifications WHERE id = ?');
    $stmt->execute([$verifyId]);
    $row = $stmt->fetch(PDO::FETCH_OBJ);
  } else {
    $userId = Security::decrypt($uid);
    $stmt = $pdo->prepare('SELECT phone, code FROM user_phone_verifications WHERE user_id = ? ORDER BY id DESC LIMIT 1');
    $stmt->execute([$userId]);
    $row = $stmt->fetch(PDO::FETCH_OBJ);
  }
  if ($row && !empty($row->phone)) {
    $p = $row->phone;
    $maskedPhone = preg_replace('/\d/', '*', substr($p, 0, max(0, strlen($p)-4))) . substr($p, -4);
  }
  $devCode = ($row->code ?? null);
} catch (\Throwable $e) {}
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$resendKey = 'resend_count_' . ($userId ?? '0');
$resendCount = $_SESSION[$resendKey] ?? 0;
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
            <input type="hidden" name="vid" value="<?= htmlspecialchars($vid) ?>">
            <div class="mb-2 text-muted text-center">Kodu şu numaraya gönderdik: <?= htmlspecialchars($maskedPhone) ?></div>
            <?php if ((isset($_ENV['APP_ENV']) && in_array(strtolower($_ENV['APP_ENV']), ['local','development'])) && !empty($devCode)): ?>
            <div class="mb-2 text-center"><small class="text-danger">Geliştirici modu: Kod <?= htmlspecialchars($devCode) ?></small></div>
            <?php endif; ?>
            <div class="mb-3 d-flex justify-content-center gap-2 otp-wrap">
              <input class="otp" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" required>
              <input class="otp" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" required>
              <input class="otp" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" required>
              <input class="otp" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" required>
              <input class="otp" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" required>
              <input class="otp" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" required>
              <input type="hidden" id="code" name="code">
            </div>
            <div class="mt-3">
              <button type="submit" class="btn btn-lg btn-success w-100">Doğrula</button>
            </div>
          </form>
          <div class="mt-3 text-center">
            <?php if ($resendCount < 3): ?>
            <form action="public/register-member-phone-verify-handler.php" method="POST" class="d-inline">
              <input type="hidden" name="action" value="resend_code">
              <input type="hidden" name="uid" value="<?= htmlspecialchars($uid) ?>">
              <input type="hidden" name="vid" value="<?= htmlspecialchars($vid) ?>">
              <button class="btn btn-link p-0">Kodu tekrar gönder (<?= 3 - (int)$resendCount ?>/3)</button>
            </form>
            <?php else: ?>
            <span class="text-muted">Tekrar gönderme sınırına ulaşıldı</span>
            <?php endif; ?>
          </div>
          <div class="mt-3 text-center">
            <a href="sign-in.php" class="text-muted">Giriş sayfasına dön</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
<?php include './partials/script.php' ?>
<style>
.otp-wrap{gap:.5rem}
.otp{width:42px;height:42px;text-align:center;font-size:18px;border:1px solid #dee2e6;border-radius:.5rem}
.otp:focus{outline:none;border-color:#0d6efd;box-shadow:0 0 0 .15rem rgba(13,110,253,.15)}
</style>
<script>
(function(){
  const inputs=[...document.querySelectorAll('.otp')];
  const hidden=document.getElementById('code');
  inputs.forEach((inp,i)=>{
    inp.addEventListener('input',()=>{
      inp.value=inp.value.replace(/\D/g,'').slice(0,1);
      if(inp.value&&inputs[i+1])inputs[i+1].focus();
      hidden.value=inputs.map(x=>x.value).join('');
    });
    inp.addEventListener('keydown',(e)=>{
      if(e.key==='Backspace'&&!inp.value&&inputs[i-1])inputs[i-1].focus();
    });
  });
})();
</script>
</body>
</html>