
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Aidat Listesi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Aidat Yönetimi</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <a href="#" class="btn btn-primary route-link" data-page="dues/dues-defines/manage">
                <i class="feather-plus me-2"></i>
                <span>Yeni Aidat Tanımla</span>
            </a>
        </div>
    </div>
</div>

<div class="main-content">
    <?php
    $title = "Aidat Yönetimi!";
    $text = "Tanımlanan aidatları listeleyebilir, düzenleyebilir veya silebilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>

    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover datatables" id="aidatTable">
                                    <thead>
                                        <tr>
                                            <th style="width:7%">#</th>
                                            <th>Blok</th>
                                            <th>Aidat Tutarı</th>
                                            <th>Başlangıç Tarihi</th>
                                            <th>Ödeme Süresi</th>
                                            <th>Durum</th>
                                            <th style="width:7%">İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        
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
