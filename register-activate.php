<?php
require_once "Database/require.php";
require_once "Model/UserModel.php";
require_once "App/Helper/security.php";

use App\Helper\Security;

$User = new UserModel();


function alertdanger($message, $type = "danger", $title = "Hata!")
{
    echo '<div class="alert alert-' . $type . ' bg-white text-start font-weight-600" role="alert">
            <div class="d-flex">
                <div>
                    <img src="assets/images/icons/ikaz2.png " alt="ikaz" style="width: 36px; height: 36px;">                    
                </div>
                    <div style="margin-left: 10px;">
                        <h4 class="alert-title">' . $title . '</h4>
                    <div class="text-secondary">' . $message . '</div>
                </div>
            </div>
        </div>';
}
?>

<!DOCTYPE html>
<html lang="tr">

<?php include './partials/head.php' ?>

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
                        <?php
                        $token_renegate = false;
                        if (isset($_POST["action"]) && $_POST["action"] == 'token_renegate') {
                            $email = $_POST["email"];
                            $user = $User->checkToken($email);
                            if (empty($user)) {
                                echo alertdanger("Kullanıcı Bulunamadı");
                            } else {
                                $token = (Security::encrypt(time() + 3600));

                                $data = [
                                    'id' => $user->id,
                                    'activate_token' => $token,
                                    'status' => 0
                                ];

                                $User->setActivateToken($data);
                                //Tekrar mail gönder

                                $activate_link = "http://yonapp.com.tr/register-activate.php?email=" . ($email) . "&token=" . $token;


                                //**********EPOSTA GÖNDERME ALANI */
                                // mail şablonunu dahil etme

                                ob_start();
                                include 'register-success-email.php';
                                $content = ob_get_clean();


                                try {
                                    //mail sınıfı ve ayarlarını dahil etme
                                    require_once "mail-settings.php";

                                    // Alıcılar
                                    $mail->setFrom('bilgi@yonapp.com.tr', 'Yonapp');
                                    $mail->addAddress($email);
                                    $mail->isHTML(true);

                                    // E-posta konusu ve içeriği
                                    $mail->Subject = 'Aktivasyon Bağlantısı';
                                    $mail->Body = $content;
                                    $mail->AltBody = strip_tags($content);
                                    //Karakter seti
                                    $mail->CharSet = 'UTF-8';

                                    // PNG dosyasını e-postaya ekleyin
                                    $mail->AddEmbeddedImage('assets/images/icons/activation.png', 'activation');

                                    $mail->send();
                                    echo alertdanger('Aktivasyon bağlantısı e-posta adresinize gönderildi.', "info", "Başarılı!");
                                } catch (Exception $e) {
                                    echo "E-posta gönderilemedi. Hata: {$mail->ErrorInfo}";
                                }
                                //**********EPOSTA GÖNDERME ALANI */


                                // echo alertdanger("Yeni Token Oluşturuldu ve Mail Gönderildi", "success", "Başarılı!");
                            }
                        } else {
                            $token = $_GET['token'];
                            $email = ($_GET['email']);
                            $user = $User->checkToken($email);
                            $token = (Security::decrypt($token));

                            if (empty($user)) {
                                echo alertdanger("Kullanıcı Bulunamadı");
                            } elseif ($token < time() || $user->activate_token != urlencode($_GET['token'])) {
                                echo alertdanger("Geçersiz Token!");
                                $token_renegate = true;
                                //Token boş ise mesaj ver
                            } elseif (empty($token)) {
                                echo alertdanger("Token bilgisi boş");
                            } elseif (empty($email)) {
                                echo alertdanger("Email bilgisi boş");
                            } elseif ($user->status == 1) {
                                echo alertdanger("Kullanıcı zaten aktif");
                            } else {
                                $User->ActivateUser($email);
                                echo alertdanger("Hesabınız başarı ile aktifleştirildi!", "success", "Başarılı!");
                            }
                        }

                        ?>
                        <?php if ($token_renegate == true) { ?>
                            <form action="register-activate.php" method="post">
                                <input type="hidden" name="email" value="<?php echo $email; ?>">
                                <input type="hidden" name="action" value="token_renegate">
                                <button type="submit" class="btn btn-lg btn-info w-100">
                                    Tekrar Token Oluştur
                                </button>
                            </form>
                        <?php } else {
                            echo '<a href="sign-in.php" class="btn btn-lg btn-primary w-100">
                                 Giriş Sayfasına Git
                              </a>';
                        } ?>

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
</body>

</html>