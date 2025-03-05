<?php
define("ROOT", $_SERVER["DOCUMENT_ROOT"]);
require_once ROOT . '/Database/require.php';
require_once ROOT . '/Model/UserModel.php';
require_once ROOT . '/Model/PasswordModel.php';
include './partials/head.php';
$PasswordModel = new PasswordModel();
$Users = new UserModel();


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
          <?php

          if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $email = $_POST['email'];
            $user = $Users->getUserByEmail($email);
            // E-posta adresi kontrolü
            if (empty($email)) {
              echo alertdanger('Email adresi boş bırakılamaz');
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
              echo alertdanger('Geçersiz e-posta adresi');
            } elseif (!$user) {
              echo alertdanger('Bu e-posta adresi ile kayıtlı bir hesap bulunamadı.');
            } else {

              //1 saat geçerli olan token oluşturma
              $token = bin2hex(random_bytes(32));
              $resetLink = "http://puantor.com.tr/reset-password.php?token=" . $token;

              // Token ve e-posta adresini veritabanına kaydetme
              $PasswordModel->setPasswordReset($email, $token);

              ob_start();
              include 'forgot-password-email.php';
              $content = ob_get_clean();


              try {

                require_once "mail-settings.php";

                // Alıcılar
                $mail->setFrom('sifre@puantor.com.tr', 'Puantor.com.tr');
                $mail->addAddress($email);
                $mail->isHTML(true);

                $mail->Subject = 'Şifre Sıfırlama';
                $mail->Body = $content;
                $mail->AltBody = strip_tags($content);
                //Karakter seti
                $mail->CharSet = 'UTF-8';

                // PNG dosyasını e-postaya ekleyin
                $mail->AddEmbeddedImage('static/png/lock.png', 'lock-icon');

                $mail->send();
                echo alertdanger('Şifre sıfırlama bağlantısı e-posta adresinize gönderildi.', "info", "Başarılı!");
              } catch (Exception $e) {
                echo "E-posta gönderilemedi. Hata: {$mail->ErrorInfo}";
              }
            }
          }

          ?>

          <div class="card-body p-sm-5">
            <img src="assets/images/icons/reset1.png" class="img-fluid d-block mx-auto mb-3" style="width: 70px; height: 70px;">
            <h2 class="fs-20 fw-bolder mb-4 text-center">Şifre Sıfırlama</h2>

            <p class="fs-14 fw-medium text-muted">Email adresini girin. Şifre sıfırlama kayıtlı mail adresinize gönderilecektir.</p>
            <form action="forgot-password.php" method="post" autocomplete="off" novalidate="" class="w-100 mt-4 pt-2">
              <div class="mb-4">
              <input type="email" name="email" class="form-control" value="<?php echo $email ?? ''; ?>"
              placeholder="Email adresiniz">
              </div>
  
              <div class="mt-5">
                <button type="submit" class="btn btn-lg btn-primary w-100">Şifre Gönder</button>
              </div>
              <div class=" text-center mt-5">
              <a href="sign-in.php">Giriş ekranına gitmek için tıklayınız.</a> 
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
  <script>
    setTimeout(function () {
      $('.alert-danger, .alert-info').each(function () {
        $(this).fadeOut(500, function () {
          $(this).remove();
        });
      });
    }, 8000);
  </script>
</body>

</html>