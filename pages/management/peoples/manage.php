<?php

use Model\BloklarModel;
use App\Helper\Security;
use Model\SitelerModel;

$Siteler = new SitelerModel();
$Blocks = new BloklarModel();

$enc_id = $id ?? 0;
$id = Security::decrypt($id ?? 0) ?? 0;
$blocks = $Blocks->find($id  ?? null);

$site = $Siteler->SiteBilgileri($_SESSION['site_id'] ?? null);
?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Site Yönetim</S></h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="ana-sayfa">Ana Sayfa</a></li>
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
                <a href="/site-sakinleri" class="btn btn-outline-secondary route-link me-2">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </a>
                </button>
                <a href="/site-sakini-ekle" class="btn btn-success" class="dynamic-save-button">
                    <i class="feather-save me-2"></i>
                    Yeni Kayıt
                </a>
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
<div class="card-header p-0">
    <!-- Nav tabs -->
    <ul class="nav nav-tabs bg-white customers-nav-tabs"
        id="myTab" role="tablist">
        <li class="nav-item border-top" role="presentation">
            <a href="javascript:void(0);" class="nav-link"
                data-bs-toggle="tab" data-bs-target="#peopleInfoTab"
                role="tab">Genel Bilgiler</a>
        </li>
        <li class="nav-item border-top" role="presentation">
            <a href="javascript:void(0);" class="nav-link " data-bs-toggle="tab"
                data-bs-target="#girisbilgileri" role="tab">Program Giriş Bilgileri</a>
        </li>
        <li class="nav-item border-top" role="presentation">
            <a href="javascript:void(0);" class="nav-link " data-bs-toggle="tab"
                data-bs-target="#peopleCarInfoTab" role="tab">Araç Bilgileri</a>
        </li>
        <li class="nav-item border-top" role="presentation">
            <a href="javascript:void(0);" class="nav-link " data-bs-toggle="tab"
                data-bs-target="#peoplesEmergencyInfoTab" role="tab">Acil Durum Bilgileri</a>
        </li>
    </ul>
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
                            <input type="hidden" name="kisi_id" id="kisi_id" value='<?= Security::encrypt($id) ?? 0; ?>'>
                            <div class="card-body custom-card-action p-0">
                                <div class="card-body apartment-info">
                                    <div class="row mb-4 align-items-center">


                                        <div class="tab-content">
                                            <div class="tab-pane fade " id="peopleInfoTab" role="tabpanel">
                                                <?php
                                                require_once 'pages/management/peoples/content/PeopleInfoTab.php';
                                                ?>
                                            </div>
                                            <div class="tab-pane fade " id="girisbilgileri" role="tabpanel">
                                                <?php
                                                require_once 'pages/management/peoples/content/GirisBilgileri.php';
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
    document.addEventListener('DOMContentLoaded', function() {
        const saveButton = document.getElementById('savePeoples');
        const tabLinks = document.querySelectorAll('.nav-link');

        tabLinks.forEach(link => {
            link.addEventListener('shown.bs.tab', function(event) {
                const targetId = event.target.getAttribute('data-bs-target');

                if (targetId === '#peopleInfoTab') {
                    saveButton.id = 'save_peoples';
                    saveButton.innerHTML = '<i class="feather-save me-2"></i>Kaydet';
                } else if (targetId === '#peopleCarInfoTab') {
                    saveButton.id = 'ekle_araba';
                    saveButton.innerHTML = '<i class="feather-plus me-2"></i>Ekle';
                } else if (targetId === '#peoplesEmergencyInfoTab') {
                    saveButton.id = 'ekle_acildurum';
                    saveButton.innerHTML = '<i class="feather-plus me-2"></i>Ekle';
                }
            });
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('click', function(e) {
            if (e.target && e.target.id === 'ekle_araba') {
                const kisiId = document.getElementById('kisi_id').value;

                fetch(`pages/management/peoples/content/AracModal.php?kisi_id=${kisiId}`)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('modalContainer').innerHTML = html;
                        let aracModal = new bootstrap.Modal(document.getElementById('aracEkleModal'));
                        aracModal.show();
                    })
                    .catch(error => console.error('Modal yüklenirken hata oluştu:', error));
            }

            if (e.target && e.target.id === 'ekle_acildurum') {
                const kisiId = document.getElementById('kisi_id').value;
                fetch(`pages/management/peoples/content/AcilDurumModal.php?kisi_id=${kisiId}`)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('modalContainer').innerHTML = html;
                        let acilDurumModal = new bootstrap.Modal(document.getElementById('acilDurumEkleModal'));
                        acilDurumModal.show();
                    })
                    .catch(error => console.error('Acil Durum Modal yüklenirken hata oluştu:', error));
            }
        });
    });
</script>
<script src="/src/daire-kisi.js"></script>
<script src="/src/blok-daire.js"></script>
<!-- Sekmeyi PHP’den gelen parametreye göre aktif et -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // PHP'den gelen aktif sekme bilgisi
        const currentTab = "<?= $_GET['tab'] ?? '' ?>";

        // Sekme haritası
        const tabMap = {
            'general': '#peopleInfoTab',
            'car': '#peopleCarInfoTab',
            'emergency': '#peoplesEmergencyInfoTab'
        };

        // Varsayılan sekme
        let activeTabSelector = '#peopleInfoTab';
        if (currentTab && tabMap[currentTab]) {
            activeTabSelector = tabMap[currentTab];
        }

        // İlgili sekmeyi aktif et
        const triggerEl = document.querySelector(`a[data-bs-target="${activeTabSelector}"]`);
        if (triggerEl) {
            const tabTrigger = new bootstrap.Tab(triggerEl);
            tabTrigger.show();
        }
    });
</script>