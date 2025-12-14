<?php \App\Services\Gate::authorizeOrDie('survey_admin_page'); ?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Yeni Anket Oluştur</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Anket Yönetimi</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="d-flex align-items-center gap-2">
            <a href="/anket-listesi" class="btn btn-outline-secondary route-link me-2">
                <i class="feather-arrow-left me-2"></i> Listeye Dön
            </a>
            <button type="button" class="btn btn-primary" id="saveSurvey">
                <i class="feather-send me-2"></i> Kaydet
            </button>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="row">
        <div class="container-xl mb-5">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <form id="pollForm">
                            <div class="card-body">
                                <!-- Anket Başlığı -->
                                <div class="mb-4">
                                    <label for="pollTitle" class="form-label fw-semibold">Anket Başlığı</label>
                                    <input type="text" class="form-control" id="pollTitle" name="title" placeholder="Anket başlığını giriniz..." required>
                                </div>

                                <!-- Açıklama (opsiyonel) -->
                                <div class="mb-4">
                                    <label for="pollDescription" class="form-label fw-semibold">Açıklama (Opsiyonel)</label>
                                    <textarea class="form-control" id="pollDescription" name="description" rows="3" placeholder="Açıklama giriniz..."></textarea>
                                </div>

                                <!-- Seçenekler -->
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Oylama Seçenekleri</label>
                                    <div id="optionsWrapper">
                                        <div class="input-group mb-2">
                                            <input type="text" name="options[]" class="form-control" placeholder="Seçenek 1" required>
                                            <button type="button" class="btn btn-outline-danger removeOption">Sil</button>
                                        </div>
                                        <div class="input-group mb-2">
                                            <input type="text" name="options[]" class="form-control" placeholder="Seçenek 2" required>
                                            <button type="button" class="btn btn-outline-danger removeOption">Sil</button>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary mt-2" id="addOption">+ Seçenek Ekle</button>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">

                                        <!-- Başlangıç Tarihi -->
                                        <div class="mb-4">
                                            <label for="pollStartDate" class="form-label fw-semibold">Başlangıç Tarihi</label>
                                            <input type="date" class="form-control flatpickr"
                                                autocomplete="off" id="pollStartDate" name="start_date">
                                        </div>
                                    </div>
                                    <div class="col-md-6">

                                        <!-- Bitiş Tarihi -->
                                        <div class="mb-4">
                                            <label for="pollEndDate" class="form-label fw-semibold">Bitiş Tarihi</label>
                                            <input type="date" class="form-control flatpickr"
                                                autocomplete="off" id="pollEndDate" name="end_date" required>
                                        </div>
                                    </div>


                                </div>

                                <!-- Durum -->
                                <div class="mb-4">
                                    <label for="pollStatus" class="form-label fw-semibold">Durum</label>
                                    <select class="form-select" id="pollStatus" name="status">
                                        <option value="Taslak">Taslak</option>
                                        <option value="Aktif" selected>Aktif</option>
                                        <option value="Pasif">Pasif</option>
                                    </select>
                                </div>

                                <!-- Yayınla Butonu -->

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/pages/duyuru-talep/admin/js/anket.js"></script>
<script>
    $(function() {
        window.SurveyUI && window.SurveyUI.initManage();
    });
</script>