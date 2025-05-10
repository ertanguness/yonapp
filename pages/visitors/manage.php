<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10"> Güvenlik ve Ziyaretçi</h5>
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

                <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="visitors/list">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </button>
                <button type="button" class="btn btn-primary" id="securityVisitors">
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
            <form action="" id="visitorForm">
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-lg-2"><label class="fw-semibold">Ad Soyad:</label></div>
                        <div class="col-lg-4"><input type="text" class="form-control" name="full_name" placeholder="Ad Soyad Giriniz"></div>

                        <div class="col-lg-2"><label class="fw-semibold">Telefon:</label></div>
                        <div class="col-lg-4"><input type="text" class="form-control" name="phone" placeholder="Telefon Numarası"></div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-lg-2"><label class="fw-semibold">Ziyaret Edilen Kişi:</label></div>
                        <div class="col-lg-4"><input type="text" class="form-control" name="resident" placeholder="Daire Sahibi"></div>

                        <div class="col-lg-2"><label class="fw-semibold">Daire No:</label></div>
                        <div class="col-lg-4"><input type="text" class="form-control" name="apartment_no" placeholder="Daire No"></div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-lg-2"><label class="fw-semibold">Araç Plaka (Varsa):</label></div>
                        <div class="col-lg-4"><input type="text" class="form-control" name="car_plate" placeholder="Plaka Numarası"></div>

                        <div class="col-lg-2"><label class="fw-semibold">Giriş Tarihi:</label></div>
                        <div class="col-lg-4"><input type="date" class="form-control" name="entry_date"></div>

                    </div>
                    <div class="row mb-4">
                        <div class="col-lg-2"><label class="fw-semibold">Giriş Saati:</label></div>
                        <div class="col-lg-4"><input type="time" class="form-control" name="entry_time"></div>

                        <div class="col-lg-2"><label class="fw-semibold">Çıkış Saati:</label></div>
                        <div class="col-lg-4"><input type="time" class="form-control" name="exit_time"></div>

                    </div>
                </div>
            </form>
        </div>
    </div>
</div>