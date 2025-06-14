<?php

use App\Helper\Date;
use App\Helper\Due;
use App\Helper\Helper;
use App\Helper\Security;
use Model\BorclandirmaModel;
use App\Helper\Debit;



$DueHelper = new Due();
$Borc = new BorclandirmaModel();

$id = Security::decrypt(@$_GET["id"] ?? 0) ?? 0;
$borc = $Borc->find($id ) ?? null;

$DebitHelper = new Debit();

//içinde olduğumuz ayın ilk gününü alıyoruz
$baslangic_tarihi = Date::firstDay(
    Date::getMonth(),
    Date::getYear()

);
//içinde olduğumuz ayın son gününü alıyoruz
$bitis_tarihi = Date::lastDay(
    Date::getMonth(),
    Date::getYear()
);

?>


<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Borç Ekle</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Borçlandırma</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">

            <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="dues/debit/list">
                <i class="feather-arrow-left me-2"></i>
                Listeye Dön
            </button>
            <button type="button" class="btn btn-primary" id="save_debit">
                <i class="feather-save  me-2"></i>
                Kaydet
            </button>
        </div>
    </div>
</div>

<div class="main-content">
    <?php
    /* $title = $pageTitle;
 if ($pageTitle === 'Borç Ekle') {
      $text = "Borç Ekleme sayfasındasınız. Bu sayfada yeni bir borç ekleyebilirsiniz.";
 } else {
      $text = "Borç Güncelleme sayfasındasınız. Bu sayfada borç bilgilerini güncelleyebilirsiniz.";
 }
 require_once 'pages/components/alert.php'; */
    ?>
    <div class="row">
        <div class="container-xl">

            <div class="card">

                <div class="row p-4">
                    <div class="col-lg-12">

                        <div class="alert alert-dismissible d-flex alert-soft-teal-message" role="alert">
                            <div class="me-4 d-none d-md-block">
                                <i class="feather feather-alert-octagon fs-1"></i>
                            </div>
                            <div>
                                <p class="fw-bold mb-1 text-truncate-1-line alert-header">Borç Ekleme!</p>
                                <p class="fs-12 fw-medium text-truncate-1-line alert-description">
                                    Tüm Sakinler seçildiğinde, şu anda sitede oturan ev sahibi ve kiracılara
                                    borclandırma yapılacaktır.

                                </p>

                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form id="debitForm" action="" method="POST">
                        <input type="text" class="form-control d-none" name="borc_id" id="borc_id"
                            value="<?php echo $_GET["id"] ?? 0 ?>">
                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-2">
                                <label for="title" class="fw-semibold">Borç Başlığı:</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group flex-nowrap w-100">
                                    <div class="input-group-text"><i class="fas fa-file-invoice"></i></div>
                                    <?php echo $DueHelper->getDuesSelect("borc_baslik") ?>

                                </div>
                            </div>

                            <div class="col-lg-2">
                                <label for="tutar" class="fw-semibold">Tutar (₺):</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="fas fa-money-bill"></i></div>
                                    <input type="text" class="form-control money" name="tutar" id="tutar"
                                        value="<?php echo $borc->tutar ?? ''; ?>" placeholder="Tutar giriniz"
                                        required>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-2">
                                <label for="bitis_tarihi" class="fw-semibold">Dönemi:</label>
                            </div>
                            <div class="col-lg-2">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                                    <input type="text" class="form-control flatpickr" name="baslangic_tarihi" id="baslangic_tarihi"
                                        value="<?php echo Date::dmY($borc->baslangic_tarihi ?? $baslangic_tarihi); ?>"
                                        autocomplete="off" required>
                                </div>
                            </div>
                            <div class="col-lg-2">

                                <div class="input-group">
                                    <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                                    <input type="text" class="form-control flatpickr" name="bitis_tarihi" id="bitis_tarihi"
                                        value="<?php echo Date::dmY($borc->bitis_tarihi ?? $bitis_tarihi); ?>"
                                        autocomplete="off" required>
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <label for="ceza_orani" class="fw-semibold">Ceza Oranı (%):</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="fas fa-percentage"></i></div>
                                    <input type="number" class="form-control" name="ceza_orani" id="ceza_orani"
                                        placeholder="Ceza oranı" step="0.01" min="0">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-2">
                                <label for="hedef_tipi" class="fw-semibold">Kime Borçlandırılacak:</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group flex-nowrap w-100">
                                    <div class="input-group-text"><i class="fas fa-users"></i></div>
                                    <?php echo Helper::targetTypeSelect('hedef_tipi', $borc->hedef_tipi ?? "all"); ?>
                                </div>
                            </div>
                            <div class="col-lg-2 ">
                                <label for="block_id" class="fw-semibold blok-sec-label">Blok Seç:</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group flex-nowrap w-100 blok-sec">
                                    <div class="input-group-text"><i class="fas fa-building"></i></div>
                                    <select class="form-control select2-single" name="block_id" id="block_id" disabled>
                                        <option value="">Seçiniz</option>
                                        <?php foreach ($blocks as $block): ?>
                                        <option value="<?= $block->id ?>"><?= $block->name ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                              
                                <div class="input-group flex-nowrap w-100 dairetipi-sec d-none">
                                    <div class="input-group-text"><i class="fas fa-building"></i></div>
                                   
                                    <?php echo Helper::getApartmentTypesSelect($site_id) ?>
                                </div>
                            </div>

                        </div>
                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-2">
                                <label for="hedef_kisi" class="fw-semibold">Kişi(ler):</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group flex-nowrap w-100">
                                    <div class="input-group-text"><i class="fas fa-user-friends"></i></div>
                                    <select name="hedef_kisi" id="hedef_kisi" data-placeholder="Yours Placeholder"
                                        multiple disabled class="form-control select2"></select>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <label for="status" class="fw-semibold">Durum:</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-check form-switch form-switch-md">
                                    <input class="form-check-input" type="checkbox" id="status" name="status"
                                        value="Aktif" checked>
                                    <label class="form-check-label" for="status">Aktif</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-2">
                                <label for="aciklama" class="fw-semibold">Açıklama:</label>
                            </div>
                            <div class="col-lg-10">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="fas fa-info-circle"></i></div>
                                    <textarea class="form-control" name="aciklama" id="aciklama" rows="3"
                                        placeholder="Açıklama giriniz"><?php echo $borc->aciklama ?? ''; ?></textarea>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
