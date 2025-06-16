<?php

use Model\BlockModel;
use App\Helper\Security;
use Model\SitelerModel;

$Siteler = new SitelerModel();
$Blocks = new BlockModel();

$id = isset($_GET['id']) ? Security::decrypt($_GET['id']) : 0;
$blocks = $Blocks->find($id  ?? null);

$site = $Siteler->SiteAdi($_SESSION['site_id'] ?? null);
?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Site Yönetim</S></h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Site Sakinleri</li>
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

                <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="management/peoples/list">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </button>
                <button type="button" class="btn btn-primary" id="savePeoples" class="dynamic-save-button">
                    <i class="feather-save me-2"></i>
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
    <?php /*
    $title = $pageTitle;
    if ($pageTitle === 'Yeni Site Sakini Ekle') {
        $text = "Yeni Site Sakini Ekleme sayfasındasınız. Bu sayfada yeni bir site sakini ekleyebilirsiniz.";
    } else {
        $text = "Site Sakini Güncelleme sayfasındasınız. Bu sayfada site sakini bilgilerini güncelleyebilirsiniz.";
    }
    require_once 'pages/components/alert.php' */
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <form action='' id='peoplesForm'>
                            <div class="card-body custom-card-action p-0">
                                <div class="card-body apartment-info">
                                    <div class="row mb-4 align-items-center">
                                        <input type="hidden" name="kisi_id" id="kisi_id" value="<?php echo $id; ?>">

                                        <div class="card-header p-0">
                                            <!-- Nav tabs -->
                                            <ul class="nav nav-tabs flex-wrap w-100 text-center customers-nav-tabs"
                                                id="myTab" role="tablist">
                                                <li class="nav-item flex-fill border-top" role="presentation">
                                                    <a href="javascript:void(0);" class="nav-link"
                                                        data-bs-toggle="tab" data-bs-target="#peopleInfoTab"
                                                        role="tab">Genel Bilgiler</a>
                                                </li>
                                                <li class="nav-item flex-fill border-top" role="presentation">
                                                    <a href="javascript:void(0);" class="nav-link " data-bs-toggle="tab"
                                                        data-bs-target="#peopleCarInfoTab" role="tab">Araç Bilgileri</a>
                                                </li>
                                                <li class="nav-item flex-fill border-top" role="presentation">
                                                    <a href="javascript:void(0);" class="nav-link " data-bs-toggle="tab"
                                                        data-bs-target="#peoplesEmergencyInfoTab" role="tab">Acil Durum Bilgileri</a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="tab-content">
                                            <div class="tab-pane fade " id="peopleInfoTab" role="tabpanel">
                                                <?php
                                                require_once 'pages/management/peoples/content/PeopleInfoTab.php';
                                                ?>
                                            </div>
                                            <div class="tab-pane fade " id="peopleCarInfoTab" role="tabpanel">
                                                <?php
                                                require_once 'pages/management/peoples/content/PeopleCarInfoTab.php';
                                                ?>
                                            </div>
                                            <div class="tab-pane fade " id="peoplesEmergencyInfoTab" role="tabpanel">
                                                <?php
                                                require_once 'pages/management/peoples/content/PeoplesEmergencyInfoTab.php';
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
<div id="modalContainer"></div>

<!-- Tab sekmesine göre Kaydet buton değişimi -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const saveButton = document.getElementById('savePeoples');
    const tabLinks = document.querySelectorAll('.nav-link');

    tabLinks.forEach(link => {
        link.addEventListener('shown.bs.tab', function (event) {
            const targetId = event.target.getAttribute('data-bs-target');

            if (targetId === '#peopleInfoTab') {
                saveButton.id = 'save_peoples';
                saveButton.innerHTML = '<i class="feather-save me-2"></i>Kaydet';
            } 
            else if (targetId === '#peopleCarInfoTab') {
                saveButton.id = 'ekle_araba';
                saveButton.innerHTML = '<i class="feather-plus me-2"></i>Ekle';
            } 
            else if (targetId === '#peoplesEmergencyInfoTab') {
                saveButton.id = 'ekle_acildurum';
                saveButton.innerHTML = '<i class="feather-plus me-2"></i>Ekle';
            }
        });
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('click', function (e) {
        if (e.target && e.target.id === 'ekle_araba') {
            Pace.restart();
            fetch('pages/management/peoples/content/AracModal.php')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('modalContainer').innerHTML = html;
                    let aracModal = new bootstrap.Modal(document.getElementById('aracEkleModal'));
                    aracModal.show();
                    $(".select2").select2( {
                        dropdownParent: $('#aracEkleModal'),
                    } );
                })
                .catch(error => console.error('Modal yüklenirken hata oluştu:', error));
        }
      
        if (e.target && e.target.id === 'ekle_acildurum') {
            Pace.restart();
            fetch('pages/management/peoples/content/AcilDurumModal.php')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('modalContainer').innerHTML = html;
                    let acilDurumModal = new bootstrap.Modal(document.getElementById('acilDurumEkleModal'));
                    acilDurumModal.show();
                    $(".select2").select2( {
                        dropdownParent: $('#acilDurumEkleModal'),
                    } );
                })
                .catch(error => console.error('Acil Durum Modal yüklenirken hata oluştu:', error));
        }
    });
});
</script>
<script src="src/daire-kisi.js"></script>
<script src="src/blok-daire.js"></script>
<!-- Araç ve acil durum sekmesinin butonla aktif edilmesi -->
<script> 
    document.addEventListener("DOMContentLoaded", function () {
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');

    const tabMap = {
        'general': '#peopleInfoTab',
        'car': '#peopleCarInfoTab',
        'emergency': '#peoplesEmergencyInfoTab'
    };

    let activeTabSelector = '#peopleInfoTab'; // default
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


