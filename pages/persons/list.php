<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Personeller</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Personeller Takip</li>
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
                require_once 'pages/components/download.php';
                ?>
                <a href="#" class="btn btn-primary route-link" data-page="persons/manage">
                    <i class="feather-plus me-2"></i>
                    <span>Yeni Personel Ekle</span>
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
    <?php
    $title = "Personel Listesi";
    $text = "Sistemde kayıtlı personelleri görüntüleyebilir, yeni personel ekleyebilir, düzenleyebilir veya silebilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover datatables" id="personnelList">
                                    <thead>
                                        <tr class="text-center">
                                            <th>Sıra</th>
                                            <th>Adı Soyadı</th>
                                            <th>Pozisyon</th>
                                            <th>Telefon</th>
                                            <th>E-Posta</th>
                                            <th>Durumu</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                          <!-- Dinamik Veri Çekilecek -->
                                        <!-- İşlem  
                                        <div class="hstack gap-2 ">
                                            <a href="javascript:void(0);" class="avatar-text avatar-md">
                                                <i class="feather-eye"></i>
                                            </a>
                                            <a href="javascript:void(0);" class="avatar-text avatar-md">
                                                <i class="feather-edit"></i>
                                            </a>
                                            <a href="javascript:void(0);" class="avatar-text avatar-md">
                                                <i class="feather-trash-2"></i>
                                            </a> 
                                        </div> -->
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
