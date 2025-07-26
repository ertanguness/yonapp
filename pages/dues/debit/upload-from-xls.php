<?php

use App\Helper\Helper;
use App\Helper\Site;
use App\Helper\BlokHelper;
use App\Helper\Aidat;
use App\Helper\Security;
use App\Helper\Form;
use Model\BorclandirmaModel;
use Model\BorclandirmaDetayModel;
use Model\KisilerModel;
use Model\DueModel;


$siteHelper = new Site();
$blokHelper = new BlokHelper();
$DueHelper = new Aidat();
$DueModel = new DueModel();
$BorcModel = new BorclandirmaModel();
$KisiModel = new KisilerModel();
$BorcDetayModel = new BorclandirmaDetayModel();



$site = $siteHelper->getCurrentSite();
$site_adi = $site ? $site->site_adi : null;

$id = Security::decrypt($_GET['id']) ?? null;
$borc_detay_id = Security::decrypt($_GET["borc_detay_id"] ?? 0) ?? 0;


$borc = $BorcModel->find($id);
$borc_tanim = $DueModel->find($borc->borc_tipi_id ?? 0);

// echo "<pre>";
// print_r($borc_tanim);
// echo "</pre>";




$hedef_tipi = $borc->hedef_tipi ?? 'all'; // Hedef tipi, eğer borç detayında tanımlı değilse 'all' olarak varsayılır




//Kişi borçlandırma yapılmışsa borçlandırma detayından kişi id'lerini al 
switch ($hedef_tipi) {
    case 'all':
        //$hedef_kisi = $DebitHelper->getAllActiveUsers();
        break;
    case 'block':
        //$hedef_kisi = $DebitHelper->getActiveUsersByBlock($borc->block_id ?? 0);
        break;
    case 'apartment_type':
        //$hedef_kisi = $DebitHelper->getActiveUsersByApartmentType($borc->apartment_type_id ?? 0);
        break;
    case 'person':
        $kisiListesi = $KisiModel->SiteTumKisileri($_SESSION["site_id"]);
        //$optionsForSelect = array_column($kisiListesi, 'adi_soyadi', 'id');

        // Orijinal dizi üzerinde dönün
        foreach ($kisiListesi as $kisi) {
            // Yeni diziyi [id => "Adı Soyadı (Daire Kodu)"] formatında doldurun
            $optionsForSelect[$kisi->id] = $kisi->daire_kodu . ' | ' . $kisi->adi_soyadi . ' | ' . $kisi->uyelik_tipi;
        }

        if ($borc_detay_id != 0) {

            $seciliKisiler = $BorcDetayModel->getKisiIdsByBorcId($borc_id); // Borç detayında tanımlı hedef kişiler

            // Veri tek bir ID olsa bile, onu bir diziye koyun.

            // Eğer veriniz hiç olmayabilirse veya birden çok olabilirse, daha güvenli bir yapı:
            $seciliKisiIdleri = [];
            if (!empty($seciliKisiler)) {
                $seciliKisiIdleri = [(string)$seciliKisiler];
            }
        }
        // echo "<pre>";
        // print_r($kisiListesi);
        // echo "</pre>";



    default:
        $hedef_kisi = [];
}



?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Borçlar</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item"><a href="index?p=dues/debit/list">Borç Listesi</a></li>
            <li class="breadcrumb-item">Excelden Borç Yükle</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <a href="index?p=dues/debit/detail&id=<?php echo $_GET['id'] ?>" class="btn btn-outline-secondary">
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
    $title = "Borç Yükleme";
    $text = "*Bu sayfada Excel dosyasından toplu olarak borç yükleyebilirsiniz. ";
    $text .= "<br>*Lütfen aşağıdaki örnek şablona uygun bir dosya kullanınız.";
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
                        <div class="ms-0 ms-sm-3">
                                        <div class="text-dark fw-bold">
                                            <?php echo $borc_tanim->due_name ?>
                                        </div>
                                        <div class="mb-0 text-truncate-1-line">
                                            <?php echo $borc->aciklama ?>
                                        </div>
                                        <small class=" text-uppercase text-muted text-truncate-1-line">
                                            <?php echo $borc->baslangic_tarihi . " | " . $borc->bitis_tarihi ?>

                                        </small>
                                    </div>
                        </div>
                        <div class="card-body">
                        <form id="uploadDebitForm" action="pages/dues/debit/download-template.php"
                                        method="POST">

                                        <input type="text" class="form-control d-none" name="borc_id" id="borc_id"
                                            value="<?php echo $_GET["id"] ?? 0 ?>">

                                        <input type="text" class="form-control d-none" name="borc_baslik"
                                            id="borc_baslik" value="<?php echo $borc->borc_tipi_id ?? 0 ?>">




                                        <div class="row mb-4 align-items-center">


                                            <div class="col-lg-3">
                                                <label for="hedef_tipi" class="fw-semibold mb-1">Kime
                                                    Borçlandırılacak:</label>
                                                <div class="input-group flex-nowrap w-100">
                                                    <div class="input-group-text"><i class="fas fa-users"></i></div>
                                                    <?php

                                            ?>
                                                    <?php echo Helper::targetTypeSelect('hedef_tipi', $borc->hedef_tipi ?? "all"); ?>
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <label for="block_id" class="fw-semibold blok-sec-label mb-1">Blok
                                                    Seç:</label>
                                                <div class="input-group flex-nowrap w-100 blok-sec">
                                                    <div class="input-group-text"><i class="fas fa-building"></i></div>
                                                    <select class="form-control select2-single" name="block_id"
                                                        id="block_id" disabled>
                                                        <option value="">Seçiniz</option>
                                                        <?php foreach ($blocks as $block): ?>
                                                        <option value="<?= $block->id ?>"><?= $block->name ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="input-group flex-nowrap w-100 dairetipi-sec d-none">
                                                    <div class="input-group-text"><i class="fas fa-building"></i></div>

                                                    <?php echo Helper::getApartmentTypesSelect($site_id) ?>
                                                </div>
                                            </div>

                                            <div class="col-lg-3">
                                                <label for="hedef_kisi" class="fw-semibold mb-1">Kişi(ler):</label>
                                                <div class="input-group flex-nowrap w-100">
                                                    <div class="input-group-text"><i class="fas fa-user-friends"></i>
                                                    </div>
                                                    <!-- <select name="hedef_kisi[]" id="hedef_kisi" multiple class="form-control select2">
                                    </select> -->
                                                    <?php
                                            echo Form::Select2Multiple(
                                                'hedef_kisi[]',         // Form gönderildiğinde PHP'nin dizi olarak alması için name.
                                                $optionsForSelect ?? [],           // SEÇENEKLER: Veritabanından gelen [id => Ad Soyad] dizisi.
                                                $seciliKisiIdleri ?? [],      // SEÇİLİ OLANLAR: Seçili olacak kişi ID'lerini içeren bir DİZİ.
                                                'form-select select2 w-100', // CSS Sınıfı
                                                'hedef_kisi'            // JavaScript (Select2) için temiz bir ID.
                                            );
                                            ?>

                                                </div>

                                            </div>
                                            <div class="col-md-3">
                                                <label for="" class="fw-semibold mb-1">Örnek Excel Dosyası İndir</label>
                                                <a href="#" class="btn btn-outline-secondary" id="sablon-indir-linki">
                                                    <i class="feather-download me-2"></i>Örnek Excel Dosyası İndir</a>
                                            </div>

                                        </div>
                                    </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="container-xl">


                    <div class="row row-deck row-cards">
                        <div class="col-12">
                            <div class="card">
                                    <div class="card-body">
                                    

                                    <form action="" id="uploadForm">
                                        <div class="mb-3">
                                            <label for="peoples_file" class="form-label">Excel Dosyası</label>
                                            <input type="file" class="form-control" id="peoples_file"
                                                name="peoples_file" accept=".xls,.xlsx" required>
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

            $(document).on('click', '#sablon-indir-linki', function(e) {

                e.preventDefault();

                const hedef_tipi = $('#hedef_tipi option:selected').val();
                const hedef_kisi = $('#hedef_kisi').val();

                // alert ("Hedef Tipi: " + hedef_tipi + ", Hedef Kişi: " + hedef_kisi);

                if (hedef_tipi === 'person' && (hedef_kisi.length === 0)) {
                    swal.fire({
                        title: "Hata",
                        text: "Lütfen borçlandırma yapılacak kişileri seçiniz.",
                        icon: "warning",
                        confirmButtonText: "Tamam"
                    });
                    return;
                }



                var form = $('#uploadDebitForm');
                //form verilerini console'da göster
                console.log(form.serialize());


                // Form verilerini gönder
                form.submit();

            });


        })
        </script>