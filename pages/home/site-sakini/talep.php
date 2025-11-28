<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Şikayet / Talep</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/sakin/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Talep</li>
        </ul>
    </div>
    </div>

<div class="main-content">
    <div class="row g-4">
        <div class="col-12 col-xl-6">
            <div class="card rounded-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Talep Oluştur</h5>
                </div>
                <div class="card-body">
                    <form class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Kategori</label>
                            <select class="form-select">
                                <option>Genel</option>
                                <option>Bakım</option>
                                <option>Güvenlik</option>
                                <option>Finans</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Aciliyet</label>
                            <select class="form-select">
                                <option>Düşük</option>
                                <option>Orta</option>
                                <option>Yüksek</option>
                                <option>Acil</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Açıklama</label>
                            <textarea class="form-control" rows="4" placeholder="Talep detayını yazın"></textarea>
                        </div>
                        <div class="col-12 d-flex align-items-center justify-content-between">
                            <label class="form-label mb-0">Fotoğraf</label>
                            <div>
                                <label class="btn btn-light">
                                    <i class="feather-image me-2"></i>Yükle
                                    <input type="file" class="d-none" accept="image/*" />
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-primary w-100">Gönder</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card rounded-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Talep Durumları</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex align-items-start gap-3 mb-3">
                            <div class="avatar-text avatar-md bg-soft-secondary text-secondary border-soft-secondary rounded">
                                <i class="feather-clock"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">Yeni</div>
                                <div class="fs-12 text-muted">Kayıt oluşturuldu</div>
                            </div>
                        </li>
                        <li class="d-flex align-items-start gap-3 mb-3">
                            <div class="avatar-text avatar-md bg-soft-info text-info border-soft-info rounded">
                                <i class="feather-refresh-ccw"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">İşleme Alındı</div>
                                <div class="fs-12 text-muted">Talep değerlendirmede</div>
                            </div>
                        </li>
                        <li class="d-flex align-items-start gap-3 mb-3">
                            <div class="avatar-text avatar-md bg-soft-success text-success border-soft-success rounded">
                                <i class="feather-check"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">Tamamlandı</div>
                                <div class="fs-12 text-muted">Talep çözümlendi</div>
                            </div>
                        </li>
                        <li class="d-flex align-items-start gap-3">
                            <div class="avatar-text avatar-md bg-soft-danger text-danger border-soft-danger rounded">
                                <i class="feather-x"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">Reddedildi</div>
                                <div class="fs-12 text-muted">Gerekçe bildirildi</div>
                            </div>
                        </li>
                    </ul>
                    <div class="mt-3">
                        <a href="/sakin/sikayet-oneri-listem" class="btn btn-light">Tüm Taleplerim</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>