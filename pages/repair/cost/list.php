<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Maliyet ve Faturalandırma</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Maliyet Takip</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Geri</span>
                </a>
            </div>
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <?php
                require_once 'pages/components/download.php';
                ?>
                <a href="#" class="btn btn-primary route-link" data-page="repair/cost/manage">
                    <i class="feather-plus me-2"></i>
                    <span>Yeni İşlem Ekle</span>
                </a>
            </div>

        </div>
    </div>
</div>

<div class="main-content">
    <?php
    $title = "Maliyet ve Faturalandırma";
    $text = "Bu modülde bakım işlemlerinin maliyetlerini ve faturalarını takip edebilirsiniz. 
             Burada işlem yapabilmeniz için Bakım veya Arıza kaydı oluşturmuş olmanız gerekmektedir.";
    require_once 'pages/components/alert.php';
    ?>

    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover datatables" id="financeList">
                                    <thead>
                                        <tr class="text-center">
                                            <th>Sıra</th>
                                            <th>Bakım Türü</th>
                                            <th>Bakım Yeri</th>
                                            <th>Toplam Maliyet (₺)</th>
                                            <th>Ödenen Tutar (₺)</th>
                                            <th>Kalan Borç (₺)</th>
                                            <th>Fatura Durumu</th>
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

                    <!-- Fatura Listesi -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title">Fatura Listesi</h5>
                        </div>
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover datatables" id="invoiceList">
                                    <thead>
                                        <tr class="text-center">
                                            <th>Fatura No</th>
                                            <th>Bakım Türü</th>
                                            <th>Fatura Tutarı (₺)</th>
                                            <th>Ödeme Durumu</th>
                                            <th>Fatura Tarihi</th>
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
                                            </a> -->
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