
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Güvenlik ve Ziyaretçi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Ziyaretçi Yönetimi</li>
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
                <?php
                require_once 'pages/components/search.php';
                require_once 'pages/components/download.php'
                ?>

                <a href="#" class="btn btn-primary route-link" data-page="visitors/manage">
                    <i class="feather-plus me-2"></i>
                    <span>Yeni Ziyaretçi Ekle</span>
                </a>
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
    $title = "Ziyaretçi Listesi";
    $text = "Bu sayfada siteye giriş yapan ziyaretçileri görüntüleyebilir, yeni ziyaretçi ekleyebilir veya mevcut giriş kayıtlarını düzenleyebilirsiniz.";
    require_once 'pages/components/alert.php'; */
    ?>
    
    <div class="container-xl">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover datatables" id="visitorTable">
                    <thead>
                        <tr>
                            <th style="width:7%">Sıra</th>
                            <th style="width:20%">Ad Soyad</th>
                            <th style="width:15%">Telefon</th>
                            <th style="width:10%">Daire No</th>
                            <th style="width:15%">Giriş Saati</th>
                            <th style="width:15%">Çıkış Saati</th>
                            <th style="width:10%">Durum</th>
                            <th style="width:8%">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
