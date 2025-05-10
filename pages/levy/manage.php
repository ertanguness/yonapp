<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">İcra Takibi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">İcra Takibi</li>
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
                 <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="levy/list">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </button>
                <button type="button" class="btn btn-primary" id="saveLevy">
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
    <?php
    $title = "Yeni İcra Takibi Başlat";
    $text = "Yeni bir icra takibi başlatabilirsiniz.";
    require_once 'pages/components/alert.php'; 
    ?>
    
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <form method="post" action="icra-kaydet.php" id="icraTakipForm">
                            <div class="card-body custom-card-action p-0">
                                <div class="card-body personal-info">

                                    <!-- Daire / Kişi -->
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2">
                                            <label class="fw-semibold">Daire / Kişi:</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-user"></i></div>
                                                <select class="form-select" name="kisi_id" required>
                                                    <option value="">Seçiniz</option>
                                                    <!-- PHP ile kişiler doldurulacak -->
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-2">
                                            <label class="fw-semibold">Borç Tutarı:</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-dollar-sign"></i></div>
                                                <input type="text" name="borc_tutari" class="form-control" placeholder="Borç Tutarı" required>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Başlangıç Tarihi ve Faiz Oranı -->
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2">
                                            <label class="fw-semibold">Başlangıç Tarihi:</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-calendar"></i></div>
                                                <input type="date" name="baslangic_tarihi" class="form-control" required>
                                            </div>
                                        </div>

                                        <div class="col-lg-2">
                                            <label class="fw-semibold">Faiz Oranı (%):</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-percent"></i></div>
                                                <input type="number" name="faiz_orani" step="0.01" class="form-control" placeholder="Faiz Oranı">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- İcra Dairesi ve Dosya No -->
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2">
                                            <label class="fw-semibold">İcra Dairesi:</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-briefcase"></i></div>
                                                <input type="text" name="icra_dairesi" class="form-control" placeholder="İcra Dairesi">
                                            </div>
                                        </div>

                                        <div class="col-lg-2">
                                            <label class="fw-semibold">Dosya No:</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-hash"></i></div>
                                                <input type="text" name="dosya_no" class="form-control" placeholder="Dosya Numarası">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Açıklama -->
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2">
                                            <label class="fw-semibold">Açıklama:</label>
                                        </div>
                                        <div class="col-lg-10">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-file-text"></i></div>
                                                <textarea name="aciklama" class="form-control" rows="4" placeholder="Açıklama yazınız..."></textarea>
                                            </div>
                                        </div>
                                    </div>

                                </div> <!-- /.card-body personal-info -->
                            </div> <!-- /.card-body custom-card-action -->
                        </form>
                    </div> <!-- /.card -->
                </div> <!-- /.col-12 -->
            </div> <!-- /.row-cards -->
        </div> <!-- /.container-xl -->
    </div> <!-- /.row -->
</div> <!-- /.main-content -->
