<?php 

use App\Helper\Site;
use App\Helper\BlokHelper;
use App\Helper\Security;

$siteHelper = new Site();
$blokHelper = new BlokHelper();


$site = $siteHelper->getCurrentSite();
$site_adi = $site ? $site->site_adi : null;
?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Kişiler</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item"><a href="index?p=dues/payment/list">Kişi Listesi</a></li>
            <li class="breadcrumb-item">Excelden Kişi Yükle</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <a href="index?p=dues/payment/list" class="btn btn-outline-secondary">
                    <i class="feather-arrow-left me-2"></i>Listeye Dön
                </a>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <?php
    $title = "Kişi Yükleme";
    $text = "*Bu sayfada Excel dosyasından toplu olarak daire kişi bilgilerini yükleyebilirsiniz. ";
    $text .= "<br>*Lütfen aşağıdaki örnek şablona uygun bir dosya kullanınız.";
    $text .=  "<br>*Dosyada olması gereken alanlar: Blok*, Daire*,Tc Kimlik No,Adı Soyadı*,Telefon*,İyelik Türü(Ev Sahibi,Kiracı),Satın Alma Tarihi(Ev Sahibi İse),
    Giriş Tarihi,Cinsiyet, Doğum Tarihi, Eposta Adresi";
    $text .= "<br><strong></strong> <a href='files\payment-upload-from-xls.xlsx' target='_blank'>Örnek Excel Dosyası İndir</a>";
    require_once 'pages/components/alert.php';
    ?>
    <div class="col-lg-12 upload-info d-none">

        <div class="alert alert-dismissible d-flex alert-soft-teal-message" role="alert">
            <div class="me-4 d-none d-md-block">
                <i class="feather feather-alert-octagon fs-1"></i>
            </div>
            <div>
                <p class="fw-bold mb-1 text-truncate-1-line alert-header">Bilgi!</p>
                <div class="fs-12 fw-medium text-truncate-1-line alert-description">
                    <!-- İçerik dinamik olarak güncellenecek -->
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="container-xl">


            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Excel Dosyası Yükle</h4>

                        </div>
                        <div class="card-body">
                            <form action="" method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="site_name" class="form-label">Site Adı</label>
                                            <input type="text" class="form-control" id="site_name" name="site_name"
                                                value="<?= htmlspecialchars($site_adi) ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="block_name" class="form-label">Blok Adı</label>
                                            <?php echo ($blokHelper->blokSelect("blok_id")) ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="sablon-indir-linki" class="form-label">Örnek Excel Dosyası
                                            İndir</label>
                                        <a href="pages/management/peoples/download-template.php" class="btn btn-outline-secondary" id="sablon-indir-linki">
                                            <i class="feather-download me-2"></i>Örnek Excel Dosyası İndir</a>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="peoples_file" class="form-label">Excel Dosyası</label>
                                    <input type="file" class="form-control" id="peoples_file" name="peoples_file"
                                        accept=".xls,.xlsx" required>
                                </div>
                                <div class="d-flex justify-content-start">
                                    <!-- Temizle Butonu -->
                                    <button type="reset" class="btn btn-secondary" id="clear_peoples_file">
                                        <i class="feather-x me-2"></i>Temizle
                                    </button>
                                    <button type="submit" class="btn btn-primary ms-2" id="upload_peoples_file">
                                        <i class="feather-upload me-2"></i>Yükle
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include "pages/components/dottie-preloader.php" ?>

<script>
$(document).ready(function() {

    const downloadLink = document.getElementById('sablon-indir-linki');
    $('#blok_id').change(function() {
        const blokId = $(this).val();
        const downloadUrl =""
        if (blokId !== 'all') {
            // Blok ID'sine göre dinamik olarak dosya indirme linkini oluştur
            dowloadUrl = `pages/management/peoples/download-template.php?blok_id=${blokId}`;
        } else {
            // Eğer blok seçilmemişse, varsayılan bir link kullan
            dowloadUrl = 'pages/management/peoples/download-template.php';
        }
        downloadLink.setAttribute('href', dowloadUrl);


    })
})
</script>