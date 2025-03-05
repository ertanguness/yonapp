
<!DOCTYPE html>
<html lang="tr">

<?php include './partials/head.php'?>

<body>
    <!--! ================================================================ !-->
    <!--! [Start] Main Content !-->
    <!--! ================================================================ !-->
    <main class="auth-minimal-wrapper">
        <div class="auth-minimal-inner">
            <div class="minimal-card-wrapper">
                <div class="card mb-4 mt-1 mx-4 mx-sm-0 position-relative">
                    <div class="wd-100 bg-white p-3 rounded-circle shadow-lg position-absolute translate-middle top-0 start-50">
                        <img src="assets/images/yonapp-logo.jpg" alt="" class="img-fluid">
                    </div>
                    <div class="card-body p-sm-5 text-center">
                        <h2 class="fw-bolder mb-4" style="font-size: 120px">4<span class="text-danger">0</span>4</h2>
                        <h4 class="fw-bold mb-2">Sayfa bulunamadı</h4>
                        <p class="fs-12 fw-medium text-muted">Üzgünüz, aradığınız sayfa bulunamadı. Lütfen URL'yi kontrol edin veya sitemizde farklı bir sayfayı deneyin.</p>
                        <div class="mt-5">
                            <a onclick="goBack()" class="btn btn-light-brand w-100">
                              <i class="fas fa-arrow-left"></i>&nbsp; Geri Dön
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
  <script>
function goBack() {
  window.history.back();
}
</script>
</body>

</html>