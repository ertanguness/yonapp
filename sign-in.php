<?php
ob_start();
define("ROOT", $_SERVER["DOCUMENT_ROOT"]);
require_once 'configs/require.php';
require_once 'Model/UserModel.php';
require_once 'App/Helper/security.php';
require_once 'Model/SettingsModel.php';
require_once 'App/Helper/date.php';
require_once 'Model/LoginLogsModel.php';

$Settings = new SettingsModel();
$User = new UserModel();

use App\Helper\Date;
use App\Helper\Security;

include './partials/head.php'
?>
<!DOCTYPE html>
<html lang="zxx">


<body>
    <!--! ================================================================ !-->
    <!--! [Start] Main Content !-->
    <!--! ================================================================ !-->
    <main class="auth-cover-wrapper">
    <div class="auth-cover-content-inner">
            <div style=" position: absolute; top: 20px; left: 20px;">
                <div style="font-weight: 700; font-size: 300%; text-align: center;">
                    Apartman Yönetiminde<br>Yeni Dönem!
                </div>
            </div>
            <div class="auth-cover-content-wrapper">
                <div class="auth-img">
                    <img src="assets/images/auth/auth-bg.png" class="img-fluid">
                </div>
            </div>
            <div style="position: absolute; bottom: 1%; left: 45%; transform: translate(-50%, -50%);">
                <div style="font-weight: 700; font-size: 180%; text-align: right;">
                    “Topluluğunuzu akıllıca yönetin<br>Tüm işlemlerinizi kolayca takip edin 
                    <br>YonApp ile kontrol artık parmaklarınızın ucunda!
                </div>
            </div>
        </div>

        <div class="auth-cover-sidebar-inner">

            <div class="auth-cover-card-wrapper">
                <div class="auth-cover-card p-sm-5 ">
                    <div class="wd-50 mb-5 sm-wd-50 mx-lg-4">
                        <img src="assets/images/yonapp-logo.svg" style="max-width: 650%; height: auto;">
                    </div>
                    <?php

                    if ($_POST && isset($_POST['submitForm'])) {
                        $email = $_POST['email'];
                        $password = $_POST['password'];

                        if (empty($email)) {
                            echo alertdanger('Email adresi boş bırakılamaz');
                        } elseif (empty($password)) {
                            echo alertdanger('Şifre boş bırakılamaz');
                        } else {
                            $user = $User->getUserByEmail($email);
                            if (!$user) {
                                echo alertdanger('Kullanıcı bulunamadı');
                            } else if (isset($user) && $user->status == 0) {
                                echo alertdanger('Hesabınız henüz aktif değil');
                            } else {
                                $verified = password_verify($password, $user->password);
                                $demo_date = $user->created_at;

                                if ($verified) {
                                    $days = Date::getDateDiff($demo_date);
                                    if ($days >= 15 && $user->user_type == 1) {
                                        echo alertdanger('Deneme süreniz dolmuştur. Lütfen iletişime geçiniz.');
                                    } else {
                                        $_SESSION['user'] = $user;
                                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                                        $_SESSION['full_name'] = $user->full_name;
                                        $_SESSION['user_role'] = $user->user_roles;
                                        $User->setToken($user->id, $_SESSION['csrf_token']);
                                        $_SESSION["log_id"] = $User->loginLog($user->id);

                                        $LoginLogs = new LoginLogsModel();
                                        $send_email_on_login = $Settings->getSettingIdByUserAndAction($user->id, "loginde_mail_gonder")->set_value ?? 0;
                                        if ($send_email_on_login == 1) {
                                            $email = $user->parent_id == 0 ? $user->email : $User->find($user->id);
                                            try {
                                                require_once "mail-settings.php";
                                                $body = 'Merhaba ' . $user->full_name . ',<br><br>
                Bu e-mail, hesabınıza giriş yapıldığını bildirmek amacıyla gönderilmiştir. 
                Kayıtlı mail adresiniz ile www.puantor.com.tr müşteri hesabınıza giriş yapılmıştır. <br><br>
                Giriş Zamanı: ' . date("Y-m-d H:i:s") . '<br>
                Giriş yapan IP Adresi: ' . $_SERVER['REMOTE_ADDR'] . '<br>
                Giriş yapan Kullanıcı: ' . $email . '<br>
                Eğer bu işlem bilginiz dışındaysa, lütfen en kısa sürede bizimle iletişime geçiniz: 0507 943 27 23<br><br>
                İyi Çalışmalar,<br><br>
                www.puantor.com.tr';
                                                $mail->setFrom('bilgi@yonapp.com.tr', 'YonApp');
                                                $mail->addAddress($email);
                                                $mail->isHTML(true);
                                                $mail->Subject = 'Hesabınıza giriş yapıldı';
                                                $mail->Body = $body;
                                                $mail->AltBody = strip_tags($body);
                                                $mail->CharSet = 'UTF-8';
                                                $mail->send();
                                            } catch (Exception $e) {
                                                echo "E-posta gönderilemedi. Hata: {$mail->ErrorInfo}";
                                            }
                                        }
                                        $returnUrl = isset($_GET['returnUrl']) && !empty($_GET['returnUrl']) ? urlencode($_GET['returnUrl']) : '';
                                        header("Location: company-list.php?returnUrl={$returnUrl}");
                                        exit();
                                    }
                                } else {
                                    echo alertdanger('Hatalı şifre veya email adresi');
                                }
                            }
                        }
                    }
                    ?>
                    <h2 class="fs-24 fw-bolder mb-4 text-center">Hoşgeldiniz!</h2>
                    <h4 class="fs-13 fw-bold ">Devam etmek için giriş yapın.</h4>

                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="w-100 mt-4 pt-2">

                        <div class="mb-3">
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $email ?? '' ?>" placeholder="Email Giriniz">
                        </div>
                        <div class="mb-3">
                            <input type="password" class="form-control pe-5" id="password" name="password" placeholder="Şifre Giriniz">

                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="rememberMe">
                                    <label class="custom-control-label c-pointer" for="rememberMe">Beni Hatırla</label>
                                </div>
                            </div>
                            <div>
                                <a href="auth-reset-cover.php" class="fs-13 text-muted  ">Şifremi Unuttum?</a>
                            </div>
                        </div>
                        <div class="mt-5">
                            <button type="submit" name="submitForm" class="btn btn-lg btn-primary w-100">Giriş</button>
                        </div>
                    </form>
                    <div class="mt-5 text-muted text-center">
                        <span> Hesabınız yok mu? </span>
                        <a href="register.php" class="fw-bold">Şimdi Kaydolun</a>
                    </div>

                </div>
            </div>
        </div>
    </main>
    <!--! ================================================================ !-->
    <!--! [End] Main Content !-->
    <!--! ================================================================ !-->
    <!--<< Footer Section Start >>-->
    <?php include './partials/theme-customizer.php' ?>
    <!--<< All JS Plugins >>-->
    <?php include './partials/script.php' ?>
    <?php include './partials/vendor-scripts.php' ?>
    <?php ob_end_flush(); ?>


</body>

</html>