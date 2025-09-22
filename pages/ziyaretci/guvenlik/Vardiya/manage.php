<?php

use App\Helper\Security;

use Model\GuvenlikGorevYeriModel;
use Model\GuvenlikVardiyaModel;
use App\Helper\Date;

$GorevYerleri = new GuvenlikGorevYeriModel();
$Vardiyalar = new GuvenlikVardiyaModel();

$id = isset($_GET['id']) ? Security::decrypt($_GET['id']) : 0;
$gorevYeri = $GorevYerleri->GorevYerleri();
$vardiya = $Vardiyalar->VardiyaBilgileri($id);
?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Güvenlik ve Ziyaretçi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Vardiya Ekle</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="ziyaretci/guvenlik/Vardiya/list">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </button>
                <button type="button" class="btn btn-primary" id="vardiyaKaydet">
                    <i class="feather-save me-2"></i>
                    Kaydet
                </button>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <form action="" id="VardiyaForm">
                            <div class="card-body custom-card-action p-0">
                                <div class="card-body personal-info">
                                    <div class="row mb-4 align-items-center">
                                        <!-- HIDDEN FIELDS -->
                                        <input type="hidden" name="vardiya_id" id="vardiya_id" value="<?php echo $_GET['id'] ?? 0; ?>">

                                        <!-- Görev Yeri -->
                                        <div class="col-lg-2">
                                            <label for="gorev_yeri_id" class="fw-semibold">
                                                <i class="feather-map-pin me-1"></i> Görev Yeri:
                                            </label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group flex-nowrap w-100">
                                                <div class="input-group-text"><i class="feather-map-pin"></i></div>
                                                <select class="form-select select2 w-100" id="gorev_yeri_id" name="gorev_yeri_id" >
                                                    <option value="">Seçiniz</option>
                                                    <?php if (!empty($gorevYeri)) : ?>
                                                        <?php foreach ($gorevYeri as $item) : ?>
                                                            <option value="<?php echo $item->id; ?>"
                                                                <?php echo (isset($vardiya->gorev_yeri_id) && $vardiya->gorev_yeri_id == $item->id) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($item->ad); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>

                                            </div>
                                        </div>

                                        <!-- Vardiya Adı -->
                                        <div class="col-lg-2">
                                            <label for="vardiya_adi" class="fw-semibold">
                                                <i class="feather-tag me-1"></i> Vardiya Adı:
                                            </label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-clock"></i></div>
                                                <input type="text" class="form-control" id="vardiya_adi" name="vardiya_adi"
                                                    value="<?php echo $vardiya->vardiya_adi ?? '' ?>" >
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4 align-items-center">
                                        <!-- Başlangıç -->
                                        <div class="col-lg-2">
                                            <label for="vardiya_baslangic" class="fw-semibold">
                                                <i class="feather-play me-1"></i> Başlangıç:
                                            </label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-play"></i></div>
                                                <input type="time" class="form-control" id="vardiya_baslangic" name="vardiya_baslangic"
                                                    value="<?php echo $vardiya->vardiya_baslangic ?? '' ?>" >
                                            </div>
                                        </div>

                                        <!-- Bitiş -->
                                        <div class="col-lg-2">
                                            <label for="vardiya_bitis" class="fw-semibold">
                                                <i class="feather-stop-circle me-1"></i> Bitiş:
                                            </label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-stop-circle"></i></div>
                                                <input type="time" class="form-control" id="vardiya_bitis" name="vardiya_bitis"
                                                    value="<?php echo $vardiya->vardiya_bitis ?? '' ?>" >
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4 align-items-center">
                                        <!-- Açıklama -->
                                        <div class="col-lg-2">
                                            <label for="aciklama" class="fw-semibold">
                                                <i class="feather-info me-1"></i> Açıklama:
                                            </label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-info"></i></div>
                                                <textarea class="form-control" id="aciklama" name="aciklama" rows="2"><?php echo $vardiya->aciklama ?? '' ?></textarea>
                                            </div>
                                        </div>

                                        <!-- Durum -->
                                        <div class="col-lg-2">
                                            <label for="aktif" class="fw-semibold">
                                                <i class="feather-check-circle me-1"></i> Durum:
                                            </label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group flex-nowrap w-100">
                                                <div class="input-group-text"><i class="feather-check-circle"></i></div>
                                                <select class="form-select select2 w-100" id="durum" name="durum">
                                                    <option value="1" <?php echo (isset($vardiya->durum) && $vardiya->durum == 1) ? 'selected' : '' ?>>Aktif</option>
                                                    <option value="0" <?php echo (isset($vardiya->durum) && $vardiya->durum == 0) ? 'selected' : '' ?>>Pasif</option>
                                                </select>
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