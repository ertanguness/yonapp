<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10"> Güvenlik ve Ziyaretçi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Güvenlik Yönetimi</li>
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

                <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="visitors/security/list">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </button>
                <button type="button" class="btn btn-primary" id="securityPersonelSave">
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
    <div class="container-xl">
        <div class="card">
            <div class="card-body blocks-info">
                <form action="" method="POST">
                    <input type="hidden" name="securityPersonel_id" value="">

                    <div class="row mb-4 align-items-center">
                        <div class="col-lg-2">
                            <label class="fw-semibold">Adı Soyadı:</label>
                        </div>
                        <div class="col-lg-4">
                            <div class="input-group">
                                <div class="input-group-text"><i class="fas fa-user"></i></div>
                                <input type="text" class="form-control" name="full_name" required>
                            </div>
                        </div>

                        <div class="col-lg-2">
                            <label class="fw-semibold">Telefon Numarası:</label>
                        </div>
                        <div class="col-lg-4">
                            <div class="input-group">
                                <div class="input-group-text"><i class="fas fa-phone"></i></div>
                                <input type="text" class="form-control" name="phone" required>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4 align-items-center">
                        <div class="col-lg-2">
                            <label class="fw-semibold">TC Kimlik No:</label>
                        </div>
                        <div class="col-lg-4">
                            <div class="input-group">
                                <div class="input-group-text"><i class="fas fa-id-card"></i></div>
                                <input type="text" class="form-control" name="tc_no" required>
                            </div>
                        </div>

                        <div class="col-lg-2">
                            <label class="fw-semibold">Görev Yeri:</label>
                        </div>
                        <div class="col-lg-4">
                        <div class="input-group flex-nowrap w-100">
                                <div class="input-group-text"><i class="fas fa-building"></i></div>
                                <select class="form-select select2 w-100" name="duty_location" required>
                                    <option value="">Görev Yeri Seçiniz</option>
                                    <option value="Ofis">Ofis</option>
                                    <option value="Saha">Saha</option>
                                    <option value="Depo">Depo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4 align-items-center">
                        <div class="col-lg-2">
                            <label class="fw-semibold">Vardiya:</label>
                        </div>
                        <div class="col-lg-4">
                        <div class="input-group flex-nowrap w-100">
                                <div class="input-group-text"><i class="fas fa-clock"></i></div>
                                <select class="form-select select2 w-100" name="shift" required>
                                    <option value="Gündüz">Gündüz</option>
                                    <option value="Gece">Gece</option>
                                    <option value="Vardiyalı">Vardiyalı</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-2">
                            <label class="fw-semibold">Durum:</label>
                        </div>
                        <div class="col-lg-4">
                        <div class="input-group flex-nowrap w-100">
                                <div class="input-group-text"><i class="fas fa-toggle-on"></i></div>
                                <select class="form-select select2 w-100" name="status" required>
                                    <option value="Aktif">Aktif</option>
                                    <option value="Pasif">Pasif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>