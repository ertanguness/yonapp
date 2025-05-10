<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10"> Bakım ve Arıza Takip </h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Bakım ve Arıza Takip</li>
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

                <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="repair/list">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </button>
                <button type="button" class="btn btn-primary" id="repair_kaydet">
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
    <?php

    $title = 'Yeni Bakım, Onarım ve Arıza Takip Ekleme';
    $text = "Bu sayfadan yeni bakım, onarım ve arıza takibi ekleyebilirsiniz.";

    require_once 'pages/components/alert.php'
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <form action="" id="repairForm">
                            <input type="hidden" id="repair_id" value="">
                            <div class="card-body repair-info">
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="talepNo" class="fw-semibold">Talep No:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-hashtag"></i></div>
                                            <input type="text" class="form-control" id="talepNo" placeholder="Veritabanından çekilip tanımlanacak" readonly>
                                        </div>
                                    </div>
                                </div>
                                <!-- Talep Bilgileri -->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="talepEden" class="fw-semibold">Talep Eden:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-user"></i></div>
                                            <input type="text" class="form-control" id="talepEden" placeholder="Talep Eden Kişi / Birim">
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <label for="talepTarihi" class="fw-semibold">Talep Tarihi:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                                            <input type="date" class="form-control" id="talepTarihi">
                                        </div>
                                    </div>
                                </div>

                                <!-- Kategori Seçimi -->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="kategori" class="fw-semibold">Kategori:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-list"></i></div>
                                            <select class="form-control" id="kategori">
                                                <option value="Bakım">Bakım</option>
                                                <option value="Onarım">Onarım</option>
                                                <option value="Arıza">Arıza</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <label for="state" class="fw-semibold">Bakım/Arıza Durumu:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-tasks"></i></div>
                                            <select class="form-control" id="state">
                                                <option value="0">Bekliyor</option>
                                                <option value="1">İşlemde</option>
                                                <option value="2">Tamamlandı</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Firma / Kişi Atama -->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="firmaKisi" class="fw-semibold">Atanan Firma / Kişi:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-building"></i></div>
                                            <input type="text" class="form-control" id="firmaKisi" placeholder="Firma veya Kişi Adı">
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <label for="atandiMi" class="fw-semibold">Atama Durumu:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-check-circle"></i></div>
                                            <select class="form-control" id="atandiMi">
                                                <option value="Evet">Evet</option>
                                                <option value="Hayır">Hayır</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>


                                <!-- Açıklama Alanı -->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="aciklama" class="fw-semibold">Açıklama:</label>
                                    </div>
                                    <div class="col-lg-10">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-info-circle"></i></div>
                                            <textarea class="form-control" id="aciklama" placeholder="Detayları Giriniz"></textarea>
                                        </div>
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