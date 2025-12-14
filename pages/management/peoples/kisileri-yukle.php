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
            <li class="breadcrumb-item"><a href="ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item"><a href="site-sakinleri">Site Sakinleri</a></li>
            <li class="breadcrumb-item">Excelden Kişi Yükle</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <a href="site-sakinleri" class="btn btn-outline-secondary">
                    <i class="feather-arrow-left me-2"></i>Listeye Dön
                </a>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <?php
    // --- TEK SATIRDA FLASH MESAJLARI GÖSTERME ---
    include_once  'partials/_flash_messages.php';
    ?>


    <?php
    $title = "Kişi Yükleme";
    $text = "*Bu sayfada Excel dosyasından toplu olarak daire kişi bilgilerini yükleyebilirsiniz. ";
    $text .= "<br>*Lütfen aşağıdaki örnek şablona uygun bir dosya kullanınız.";
    $text .=  "<br>*Dosyada olması gereken alanlar: <br>" ;
     $text .=   "Sıra,Site Adı*,Blok Adı,Daire Kodu*,Mülk Tipi*,Daire Tipi*,Adı Soyadı*,Telefon*,Giriş Tarihi,Kimlik No,Doğum Tarihi (gg.aa.yyyy),Cinsiyet (Erkek/Kadın),Uyeliği (Ev Sahibi/Kiracı),Eposta,Adres,Notlar,Satin Alma Tarihi,Çıkış Tarihi,Aktiflik Durumu";
    $text .= "<br><strong></strong> <a href='files\kisi_yukleme_sablon.xlsx' target='_blank'>Örnek Excel Dosyası İndir</a>";
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
                               
                                <input type="hidden" id="upload_action" value="excel_upload_peoples_resolve">
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
