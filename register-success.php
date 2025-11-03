<!DOCTYPE html>
<html lang="tr">

</html>


<?php 
$page = "register-success";
?>
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
                    <img src="assets/images/logo/logo.svg" style="max-width: 50%; height: auto;">
                        <h2 class="fs-32 fw-bolder mb-2 text-center text-success">Kayıt Başarılı</h2>
                        <p class="fs-16 fw-medium text-muted text-center">Kayıt işleminiz başarıyla tamamlandı. </br>
                            Eposta adresinize aktivasyon maili gönderildi. </br>
                            Aktivasyon işlemini tamamladıktan sonra giriş yapabilirsiniz</p>
                        <form action="sign-in.php" class="w-100 mt-4 pt-2">
                            <div class="mt-2">
                                <button type="submit" class="btn btn-lg btn-primary w-100">
                                    Giriş Sayfasına Git &nbsp;<i class="fas fa-arrow-right"></i>
                                </button>
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
</body>

</html>