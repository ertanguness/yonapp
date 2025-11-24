<?php

use App\Helper\Security;
use Model\BloklarModel;
use Model\DefinesModel;
use Model\DairelerModel;
use App\Helper\DefinesHelper;
use App\Services\FlashMessageService;

$Block = new BloklarModel();
$daireModel = new DairelerModel();
$definesModel = new DefinesModel();
$DefinesHelper = new DefinesHelper();


$enc_id = $id ?? 0;
$id =  Security::decrypt($id ?? 0);
$site_id = $_SESSION['site_id'] ?? 0;


$blocks = $Block->SiteBloklari($site_id);
$daire = $daireModel->DaireBilgisi($site_id, $id ?? 0);
$apartmentTypes = $definesModel->getDefinesTypes($site_id, 3);


/** Hiç apartman tipi yoksa uyarı ver */
if (empty($apartmentTypes)) {
    FlashMessageService::add(
                       'warning', 
                      'Bu site için apartman tipi bulunmamaktadır. Lütfen apartman tipini ekleyin.', 
                    'apartman-tipi-bulunamadi');
    
}






?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Tanımlamalar</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Daireler</li>
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

                <a href="/site-daireleri" type="button" class="btn btn-outline-secondary route-link me-2">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </a>
                <button type="button" class="btn btn-primary" id="save_apartment">
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
<ul class="nav nav-tabs flex-wrap w-100 text-center customers-nav-tabs bg-white"
                                                id="myTab" role="tablist">
                                                <li class="nav-item border-top" role="presentation">
                                                    <a href="javascript:void(0);" class="nav-link"
                                                        data-bs-toggle="tab" data-bs-target="#apartmentInfoTab"
                                                        role="tab">Daire Bilgileri</a>
                                                </li>
                                                <?php if ($id && $id != 0): ?>
                                                    <li class="nav-item border-top" role="presentation">
                                                        <a href="javascript:void(0);" class="nav-link" data-bs-toggle="tab"
                                                            data-bs-target="#apartmentPeopleInfoTab" role="tab">Daire Kişi Bilgileri</a>
                                                    </li>
                                                <?php endif; ?>

                                            </ul>
<div class="main-content">

    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Daire Bilgileri Sayfası</h5>
                        </div>
                        <!-- <div class="row px-4 pt-4 pb-0">
                            <div class="col-lg-12">
                                <div class="alert alert-dismissible d-flex alert-soft-teal-message" role="alert">
                                    <div class="me-4 d-none d-md-block">
                                        <i class="feather feather-alert-octagon fs-1"></i>
                                    </div>
                                    <div>
                                        <p class="fw-bold mb-1 text-truncate-1-line alert-header">Daire Tanımla!</p>
                                        <p class="fs-12 fw-medium  alert-description">
                                            <strong>Daire kodu,</strong> blok seçilip daire numarası girildikten sonra otomatik oluşmaktadır.<br>
                                            <strong>Kullanım durumu alanı </strong>ise ilgili alanın boş mu, aktif olarak kullanımda mı olduğunu belirtmektedir. &nbsp;&nbsp; 
                                           <strong>Aidattan muaf alanı</strong>  ise ilgili alanın aidattan muaf olup olmadığını belirtir.
                                        </p>

                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                        <form action='' id='apartmentForm'>
                            <input type="hidden" name="apartment_id" id="apartment_id" value="<?php echo $enc_id ?? 0; ?>">

                            <div class="card-body custom-card-action p-0">
                                <div class="card-body apartment-info">
                                    <div class="row mb-4 align-items-center">
                                  
                                        <div class="tab-content">
                                            <div class="tab-pane fade " id="apartmentInfoTab" role="tabpanel">
                                                <?php
                                                require_once 'pages/management/apartment/content/ApartmentInformation.php';
                                                ?>
                                            </div>
                                            <?php if ($id && $id != 0): ?>
                                                <div class="tab-pane fade" id="apartmentPeopleInfoTab" role="tabpanel">
                                                    <?php
                                                    require_once 'pages/management/apartment/content/ApartmentPeopleInformation.php';
                                                    ?>
                                                </div>
                                            <?php endif; ?>


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


<script>
    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');

        const tabMap = {
            'general': '#apartmentInfoTab',
            'peoples': '#apartmentPeopleInfoTab',
        };

        let activeTabSelector = '#apartmentInfoTab'; // default
        if (tab && tabMap[tab]) {
            activeTabSelector = tabMap[tab];
        }

        // İlgili sekmenin başlığını seçelim (nav-link)
        const triggerEl = document.querySelector(`a[data-bs-target="${activeTabSelector}"]`);
        if (triggerEl) {
            // Bootstrap tab instance yarat ve göster
            const tabTrigger = new bootstrap.Tab(triggerEl);
            tabTrigger.show();
        }
    });
</script>

<!-- JavaScript dosyalarını ekle -->
<script src="/pages/management/apartment/apartment.js"></script>