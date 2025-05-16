<?php
use App\Helper\Cities;
$cities = new Cities();

use Model\SitesModel;
use App\Helper\Security;
$Sites = new SitesModel();

$id = isset($_GET['id']) ? Security::decrypt($_GET['id']) : 0;
$company = $Sites->find($id  ?? null);

?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Yönetim</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Siteler</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Back</span>
                </a>
            </div>
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">

                <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="management/sites/list">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </button>
                <button type="button" class="btn btn-primary" id="save_sites">
                    <i class="feather-save  me-2"></i>
                    Kaydet
                </button>
            </div>
        </div>
        <div class="d-md-none d-flex align-items-center">
            <a href="javascript:void(0)" class="page-header-right-open-toggle">
                <i class="feather-align-right fs-20"></i>
            </a>
        </div>
    </div>
</div>
<div class="main-content">
    <?php
    /*
    $title = $pageTitle;
    if ($pageTitle === 'Yeni Site Ekle') {
        $text = "Yeni site tanımlayabilirsiniz.Site Bilgileri alanını doldurduktan sonra Blok Bilgileri ve Daire Bilgileri alanlarına İleri botonu ile geçebilirsiniz.";
    } else {
        $text = "Seçtiğiniz Siteye ait bilgileri güncelleyebilirsiniz.";
    }
    require_once 'pages/components/alert.php'
    */
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                    <form id="sitesForm" method="POST">
                            <div class="card-body custom-card-action p-0">
                                <div class="card-body">
                                    <div class="row mb-4 align-items-center">
                                    <input type="hidden" name="sites_id" id="sites_id" value="<?php echo $id ; ?>">

                                        <div class="card-header p-0">
                                            <!-- Nav tabs -->
                                            <ul class="nav nav-tabs flex-wrap w-100 text-center customers-nav-tabs"
                                                id="myTab" role="tablist">
                                                <li class="nav-item flex-fill border-top" role="presentation">
                                                    <a href="javascript:void(0);" class="nav-link active"
                                                        data-bs-toggle="tab" data-bs-target="#sitebilgileriTab"
                                                        role="tab">Site Bilgileri</a>
                                                </li>
                                                <li class="nav-item flex-fill border-top" role="presentation">
                                                    <a href="javascript:void(0);" class="nav-link disabled" data-bs-toggle="tab"
                                                        data-bs-target="#blokbilgileriTab" role="tab">Blok Bilgileri</a>
                                                </li>
                                                <li class="nav-item flex-fill border-top" role="presentation">
                                                    <a href="javascript:void(0);" class="nav-link disabled" data-bs-toggle="tab"
                                                        data-bs-target="#dairebilgileriTab" role="tab">Daire
                                                        Bilgileri</a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="tab-content">
                                            <div class="tab-pane fade show active" id="sitebilgileriTab"   role="tabpanel">
                                            <?php
                                                require_once 'pages/management/sites/content/SitesInformationPage.php';
                                                ?>
                                            </div>
                                            <div class="tab-pane fade" id="blokbilgileriTab" role="tabpanel">
                                                <?php
                                             //   require_once 'pages/management/blocks/content/BlocksNumberPage.php';
                                                ?>
                                            </div>
                                            <div class="tab-pane fade" id="dairebilgileriTab" role="tabpanel">
                                                <?php
                                             //   require_once 'pages/management/apartment/content/ApartmentInformation.php';
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Kaydet buton aktif pasiflik kontrolü : Sadece dairebilgileri sekmesi aktif olunca aktif olur -->
<!-- 
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const saveButton = document.getElementById("saveMySites");

        function updateButtonState() {
            const activeTab = document.querySelector(".tab-pane.fade.show.active");
            
            if (activeTab && activeTab.id === "dairebilgileriTab") {
                saveButton.removeAttribute("disabled"); // "Daire Bilgileri" sekmesinde aktif yap
            } else {
                saveButton.setAttribute("disabled", "disabled"); // Diğer sekmelerde pasif yap
            }
        }

        // **Sayfa yüklendiğinde** butonun durumunu ayarla
        updateButtonState();

        // **Sekme tıklamaları ile değişim olduğunda** güncelle
        document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener("shown.bs.tab", function () {
                updateButtonState();
            });
        });

        // **Blok bilgileri butonuna tıklanınca Daire Bilgileri sekmesine geçiş yap**
        document.getElementById("blocksTabButton").addEventListener("click", function(event) {
            event.preventDefault(); // Formun post edilmesini engelle

            var requiredFields = document.querySelectorAll(".card-body.blocks-info [required]");
            var allFilled = true;

            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    allFilled = false;
                    field.classList.add("is-invalid");
                } else {
                    field.classList.remove("is-invalid");
                }
            });

            if (allFilled) {
                var daireBilgileriTab = document.querySelector('[data-bs-target="#dairebilgileriTab"]');
                if (daireBilgileriTab) {
                    new bootstrap.Tab(daireBilgileriTab).show(); // Daire Bilgileri sekmesini aç
                    setTimeout(() => {
                        updateButtonState(); // Sekme değiştikten sonra butonu güncelle
                    }, 100);
                }
            } else {
                var toast = new bootstrap.Toast(document.getElementById('warningToast'));
                toast.show();
            }
        });

        // **Kullanıcı inputlara yazdıkça uyarıyı kaldır**
        document.querySelectorAll(".card-body.blocks-info [required]").forEach(function(field) {
            field.addEventListener("input", function() {
                if (field.value.trim()) {
                    field.classList.remove("is-invalid");
                }
            });
        });
    });
</script>
-->


