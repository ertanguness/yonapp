<?php

use App\Services\Gate;
use App\Helper\Helper;
use App\Helper\Security;
use Model\SikayetOneriModel;

//Gate::authorizeOrDie("talep_ekle_guncelle_sil");

$Model = new SikayetOneriModel();

$enc_id = $id ?? 0;
$id = Security::decrypt($id ?? 0);

$talep = $Model->find($id);

?>

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

<div class="main-content mb-5">
    <div class="row g-4">
        <div class="col-12 col-xl-6">
            <div class="card rounded-3">
                <div class="card-header d-flex align-items-center gap-2">
                    <h5 class="card-title mb-0">Şikayet / Öneri Oluştur</h5>
                    <a href="/sakin/taleplerim" class="btn btn-light ms-auto">Taleplerim</a>
                </div>
                <div class="card-body">
                    <form class="row g-3" id="formSikayetOneri">
                        <input type="hidden" id="id" name="id" value="<?php echo $enc_id; ?>">
                        <div class="col-12">
                            <label class="form-label">Tür</label>
                            <select class="form-select select2" id="inpType" name="inpType">
                                <option <?php echo ($talep->type ?? '') === 'Şikayet' ? 'selected' : ''; ?> value="Şikayet">Şikayet</option>
                                <option <?php echo ($talep->type ?? '') === 'Öneri' ? 'selected' : ''; ?> value="Öneri">Öneri</option>
                                <option <?php echo ($talep->type ?? '') === 'Talep' ? 'selected' : ''; ?> value="Talep">Talep</option>

                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Başlık</label>
                            <input type="text" class="form-control" id="inpTitle" name="inpTitle"
                                value="<?php echo $talep->title ?? ''; ?>" placeholder="Kısa başlık">
                        </div>
                        <div class="col-12">
                            <label class="form-label">İçerik</label>
                            <textarea class="form-control" id="inpContent" name="inpContent" rows="4" placeholder="Talep detayını yazın"><?php echo $talep->message ?? ''; ?></textarea>
                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-primary w-100" id="btnSubmit">
                                <i class="feather-send me-2"></i>Gönder
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6 mb-5">
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
                        <a href="/sakin/taleplerim" class="btn btn-light">Tüm Taleplerim</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/pages/site-sakini/js/sikayet-oneri.js"></script>