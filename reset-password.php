<?php
require_once "Database/require.php";
require_once "Model/UserModel.php";
require_once "Model/PasswordModel.php";
include './partials/head.php';

$PasswordModel = new PasswordModel();

$User = new UserModel();

function alertdanger($message, $type = "danger", $title = "Hata!")
{
  echo '<div class="alert alert-' . $type . ' bg-white text-start font-weight-600" role="alert">
            <div class="d-flex">
                <div>
                    
                    <img src="assets/images/icons/ikaz2.png " alt="ikaz" style="width: 36px; height: 36px;">                    
                </div>
                    <div>
                        <h4 class="alert-title">' . $title . '</h4>
                    <div class="text-secondary">' . $message . '</div>
                </div>
            </div>
        </div>';
}

?>

<!DOCTYPE html>
<html lang="tr">

</html>

<body>
  <!--! ================================================================ !-->
  <!--! [Başlangıç] Ana İçerik !-->
  <!--! ================================================================ !-->
  <main class="auth-minimal-wrapper">
    <div class="auth-minimal-inner">
      <div class="minimal-card-wrapper">
        <div class="card mb-4 mt-5 mx-4 mx-sm-0 position-relative">
          <div class="wd-100 bg-white p-3 rounded-circle shadow-lg position-absolute translate-middle top-0 start-50">
            <img src="assets/images/yonapp-logo.jpg" alt="" class="img-fluid">
          </div>


          <div class="card-body p-sm-5">
            <img src="assets/images/icons/reset1.png" class="img-fluid d-block mx-auto mb-3" style="width: 70px; height: 70px;">
            <h2 class="fs-20 fw-bolder mb-4 text-center">Şifre Sıfırlama</h2>

            <p class="fs-14 fw-medium text-muted">Lütfen yeni şifrenizi girin. Şifrenizin en az 8 karakter uzunluğunda
              olduğundan ve hem büyük hem de küçük harfler, rakamlar ve özel karakterler içerdiğinden emin olun.</p>
            <form action="#" class="w-100 mt-4 pt-2">
              <div class="mb-4">
                <input type="password" class="form-control" name="password" value="<?php echo $password ?? ''; ?>"
                  placeholder="Yeni Şifre">
              </div>
              <div class="mb-4">
                <input type="password" class="form-control" name="password_repeat"
                  value="<?php echo $password_repeat ?? ''; ?>" placeholder="Şifre tekrar">
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
    $(document).ready(function() {
      $('#show-password').click(function() {
        if ($(this).is(':checked')) {
          $('input[type="password"]').attr('type', 'text');
        } else {
          $('input[type="text"]').attr('type', 'password');
        }
      });
    });
  </script>
</body>

</html>