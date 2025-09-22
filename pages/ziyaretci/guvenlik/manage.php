<?php

use App\Helper\Security;
use Model\GuvenlikGorevYeriModel;
use Model\GuvenlikPersonelModel;
use Model\GuvenlikVardiyaModel;
use Model\GuvenlikModel;
use App\Helper\Date;

$id = isset($_GET['id']) ? Security::decrypt($_GET['id']) : 0;

$GorevYerleri = new GuvenlikGorevYeriModel();
$Personeller = new GuvenlikPersonelModel();
$Vardiyalar  = new GuvenlikVardiyaModel();
$GuvenlikGorevler = new GuvenlikModel();

$gorevYeri = $GorevYerleri->GorevYerleri();
$personel  = $Personeller->Personeller();
$vardiya   = $Vardiyalar->Vardiyalar();
$guvenlik  = $GuvenlikGorevler->GuvenlikVardiyaBilgileri($id);

$bugun = date('Y-m-d');
?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Güvenlik ve Ziyaretçi</h5>
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
                <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="ziyaretci/guvenlik/list">
                    <i class="feather-arrow-left me-2"></i>Listeye Dön
                </button>
                <button type="button" class="btn btn-primary" id="guvenlik_kaydet">
                    <i class="feather-save me-2"></i>Kaydet
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
            <form id="guvenlikForm">
                <input type="hidden" name="guvenlik_id" id="guvenlik_id" value="<?= $_GET['id'] ?? 0 ?>">

                <div class="card-body security-info">

                    <!-- Personel ve Görev Yeri -->
                    <div class="row mb-4 align-items-center">
                        <div class="col-lg-2">
                            <label class="fw-semibold">Personel:</label>
                        </div>
                        <div class="col-lg-4">
                            <div class="input-group flex-nowrap w-100">
                                <div class="input-group-text"><i class="fas fa-user-shield"></i></div>
                                <select class="form-select select2 w-100" name="personel" id="personel">
                                    <option value="">Personel Seçiniz</option>
                                    <?php foreach ($personel as $p): ?>
                                        <option value="<?= $p->id ?>"
                                            <?= (!empty($guvenlik->personel_id) && $guvenlik->personel_id == $p->id) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p->adi_soyadi) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-2">
                            <label class="fw-semibold">Görev Yeri:</label>
                        </div>
                        <div class="col-lg-4">
                            <div class="input-group flex-nowrap w-100">
                                <div class="input-group-text"><i class="fas fa-building"></i></div>
                                <select class="form-select select2 w-100" name="gorev_yeri" id="gorev_yeri">
                                    <option value="">Görev Yeri Seçiniz</option>
                                    <?php foreach ($gorevYeri as $g): ?>
                                        <option value="<?= $g->id ?>"
                                            <?= (!empty($guvenlik->gorev_yeri_id) && $guvenlik->gorev_yeri_id == $g->id) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($g->ad) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Görev Başlangıç / Bitiş Tarihi -->
                    <div class="row mb-4 align-items-center">
                        <div class="col-lg-2">
                            <label class="fw-semibold">Görev Başlangıç Tarihi:</label>
                        </div>
                        <div class="col-lg-4">
                            <div class="input-group">
                                <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                                <input type="text" class="form-control flatpickr" name="gorev_baslangic" id="gorev_baslangic"
                                    value="<?= !empty($guvenlik->baslama_tarihi) ? Date::dmY($guvenlik->baslama_tarihi) : Date::dmY($bugun); ?>"
                                     >
                            </div>
                        </div>

                    <div class="col-lg-2">
                        <label class="fw-semibold">Görev Bitiş Tarihi:</label>
                    </div>
                    <div class="col-lg-4">
                        <div class="input-group">
                            <div class="input-group-text"><i class="fas fa-calendar-check"></i></div>
                            <input type="text" class="form-control flatpickr" name="gorev_bitis" id="gorev_bitis"
                            value="<?= !empty($guvenlik->bitis_tarihi) ? Date::dmY($guvenlik->bitis_tarihi) : '' ?>">
                            </div>
                    </div>
                </div>

                <!-- Vardiya ve Durum -->
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-2">
                        <label class="fw-semibold">Vardiya:</label>
                    </div>
                    <div class="col-lg-4">
                        <div class="input-group flex-nowrap w-100">
                            <div class="input-group-text"><i class="fas fa-clock"></i></div>
                            <select class="form-select select2 w-100" name="vardiya" id="vardiya">
                                <option value="">Vardiya Seçiniz</option>
                                <?php foreach ($vardiya as $v): ?>
                                    <option value="<?= $v->id ?>"
                                        <?= (!empty($guvenlik->vardiya_id) && $guvenlik->vardiya_id == $v->id) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($v->vardiya_adi . ' (' . $v->vardiya_baslangic . ' - ' . $v->vardiya_bitis . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-2">
                        <label class="fw-semibold">Durum:</label>
                    </div>
                    <div class="col-lg-4">
                        <div class="input-group flex-nowrap w-100">
                            <div class="input-group-text"><i class="fas fa-toggle-on"></i></div>
                            <select class="form-select select2 w-100" name="durum" id="durum">
                                <option value="1" <?= (isset($guvenlik->aciklama_durum) && $guvenlik->aciklama_durum == 1) ? 'selected' : '' ?>>Aktif</option>
                                <option value="0" <?= (isset($guvenlik->aciklama_durum) && $guvenlik->aciklama_durum == 0) ? 'selected' : '' ?>>Pasif</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Açıklama -->
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-2">
                        <label class="fw-semibold">Açıklama:</label>
                    </div>
                    <div class="col-lg-10">
                        <div class="input-group flex-nowrap w-100">
                            <div class="input-group-text"><i class="fas fa-comment-dots"></i></div>
                            <textarea class="form-control" name="aciklama" id="aciklama" rows="3" placeholder="Açıklama giriniz"><?= $guvenlik->aciklama ?? '' ?></textarea>
                        </div>
                    </div>
                </div>

        </div>
        </form>
    </div>
</div>
</div>