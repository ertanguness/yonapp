<!DOCTYPE html>
<html lang="tr">

</html>

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
                    <img src="assets/images/icons/onay2.png" class="img-fluid d-block mx-auto mb-3" style="width: 50px; height: 50px;">
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