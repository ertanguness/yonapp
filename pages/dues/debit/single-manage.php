<?php

use App\Helper\Date;
use App\Helper\Aidat;
use App\Helper\Helper;
use App\Helper\Security;
use Model\KisilerModel;
use Model\BorclandirmaModel;
use Model\BorclandirmaDetayModel;
use App\Helper\Debit;
use App\Helper\KisiHelper;

use App\Helper\Error;
use App\Helper\Form;

use App\Services\Gate;
use Random\Engine\Secure;

$DebitHelper = new Debit();
$DueHelper = new Aidat();
$KisiModel = new KisilerModel();
$BorcModel = new BorclandirmaModel();
$BorcDetayModel = new BorclandirmaDetayModel();
$KisiHelper = new KisiHelper();



$borc_id = Security::decrypt($id ?? 0) ?? 0;
$borc_detay_id = Security::decrypt($detay_id ?? 0) ?? 0;


$borc = $BorcModel->find($borc_id);
$borc_detay = $BorcDetayModel->BorclandirmaDetayByID($borc_detay_id);

// if (!$borc_detay) {
//     header("Location: index?p=dues/debit/list");
//     exit;
// }

// 
$adi_soyadi = $borc_detay->adi_soyadi ?? '';
$kisi_id = $borc_detay->kisi_id ?? 0;
$borc_adi = $borc_detay->borc_adi ?? '';

$site_id = $_SESSION["site_id"];

$blocks = [];

$hedef_tipi = $borc->hedef_tipi ?? 'all'; // Hedef tipi, eğer borç detayında tanımlı değilse 'all' olarak varsayılır



//Gate::authorizeOrDie('debit_add');
?>

<div class="page-header">

    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Borç Ekle</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Borçlandırma</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">

            <a href="javascript:history.back()" type="button"
                class="btn btn-outline-secondary me-2" data-page="">
                <i class="feather-arrow-left me-2"></i>
                Listeye Dön
            </a>
            <button type="button" class="btn btn-primary" id="save_debit_single">
                <i class="feather-save  me-2"></i>
                Kaydet
            </button>
        </div>
    </div>
</div>

<div class="main-content">


    <div class="row">
        <div class="container-xl">
            <div class="col-xxl-12 single-note-item all-category note-important note-tasks">
                <div class="card card-body mb-4 stretch stretch-full">
                    <span class="side-stick"></span>
                    <h5 class="note-title text-truncate w-75 mb-1">
                        <?php echo $adi_soyadi . " - " .  $borc_adi ?>
                    </h5>

                    <div class="fs-12 mt-3">
                        <div class="hstack gap-2 text-muted mb-2">
                            <div class="avatar-text avatar-sm">
                                <i class="feather-calendar"></i>
                            </div>
                            <span class="text-truncate-1-line">
                                Dönemi :
                                <strong><?php echo Date::dmY($borc_detay->baslangic_tarihi) . "  -  " .  Date::dmY($borc_detay->bitis_tarihi)  ?>
                                </strong>
                            </span>
                        </div>
                        <div class="hstack gap-2 text-muted mb-2">
                            <div class="avatar-text avatar-sm">
                                <i class="feather-list"></i>
                            </div>
                            <span class="text-truncate-1-line">
                                Yapılan borçlandırmanın tutarını ve açıklamasını düzenleyebilirsiniz.
                            </span>
                        </div>


                    </div>

                </div>


            </div>
            <div class="card">

                <div class="card-body">
                    <form id="singleDebitForm" action="" method="POST">
                        <input type="hidden" class="form-control d-non" name="borc_detay_id" id="borc_detay_id"
                            value="<?php echo $detay_id ?? 0 ?>">
                        <input type="hidden" class="form-control d-non" name="borc_id" id="borc_id"
                            value="<?php echo $id ?? 0 ?>">
                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-2">
                                <label for="title" class="fw-semibold">Borç Başlığı:</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group flex-nowrap w-100">
                                    <div class="input-group-text"><i class="fas fa-file-invoice"></i></div>
                                    <?php echo $DueHelper->AidatTuruSelect("borc_baslik", $borc->borc_tipi_id ?? '', "disabled") ?>

                                </div>
                            </div>

                            <div class="col-lg-2">
                                <label for="tutar" class="fw-semibold">Tutar (₺) / Ceza Oranı (%):</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="fas fa-money-bill"></i></div>
                                    <input type="text" class="form-control money" name="tutar" id="tutar"
                                        value="<?php echo Helper::formattedMoney($borc_detay->tutar ?? 0) ?? ''; ?>"
                                        placeholder="Tutar giriniz" required>
                                </div>
                            </div>

                            <div class="col-lg-2">

                            </div>
                        </div>



                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-2">
                                <label for="hedef_kisi" class="fw-semibold">Kişi(ler):</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group flex-nowrap w-100">
                                    <div class="input-group-text"><i class="fas fa-users"></i></div>

                                    <?php echo $KisiHelper->KisiSelect("kisi_id", $kisi_id) ?>
                                </div>
                            </div>


                            <div class="col-lg-2">
                                <label for="title" class="fw-semibold">Borç Başlığı:</label>
                            </div>

                            <div class="col-lg-4">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="fas fa-percentage"></i></div>
                                    <input type="number" class="form-control" name="ceza_orani" id="ceza_orani"
                                        placeholder="Ceza oranı" step="0.01" min="0"
                                        value="<?php echo $borc_detay->ceza_orani ?? ''; ?>">
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
                            placeholder="Açıklama giriniz"><?php echo $borc_detay->aciklama ?? $borc->aciklama; ?></textarea>
                    </div>
                </div>
            </div>

            </form>
        </div>
    </div>
</div>
</div>
</div>