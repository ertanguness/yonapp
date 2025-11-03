<?php
require_once __DIR__ . '/configs/bootstrap.php';

use App\Controllers\RegisterActivateController;

$page = "register-activate";
// Flash mesajlar ve iş mantığı
$token_renegate = RegisterActivateController::handleActivation($_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET);
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
                    <div class="card-body p-sm-5 text-center">
                        <div class="text-center mb-5">
                            <img src="assets/images/logo/logo.svg" style="max-width: 50%; height: auto;">
                        </div>
                        <h2 class="fs-20 fw-bolder mb-4">Hesap Aktivasyonu</h2>
                        <?php
                        include_once 'partials/_flash_messages.php';
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