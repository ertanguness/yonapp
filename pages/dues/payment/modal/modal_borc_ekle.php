<?php
require_once dirname(__DIR__, levels: 4) . '/configs/bootstrap.php';

use App\Helper\Date;
use App\Helper\Security;
use App\Services\Gate;
use Model\KisilerModel;
use App\Helper\Borclandirma;

use Model\BorclandirmaDetayModel;
$KisiModel = new KisilerModel();
$BorclandirmaHelper = new Borclandirma();

$BorclandirmaDetayModel = new BorclandirmaDetayModel();

//Gate::authorizeOrDie('kisiye_borc_ekle','',false); // Borç ekleme yetkisi kontrolü

$kisi_id = Security::decrypt($_GET['kisi_id'] ?? 0);
$borc_detay_id = Security::decrypt($_GET['borc_detay_id'] ?? 0);


$kisi = $KisiModel->getKisiByDaireId($kisi_id);

if(!$kisi){
    die('Kişi bulunamadı.');
};

$borc_detay = $BorclandirmaDetayModel->find($borc_detay_id);

if(!$borc_detay && $borc_detay_id){
    die('Borç detayı bulunamadı.');
};
$borc_id = $borc_detay->borclandirma_id ?? null;

?>

<div class="modal-header">
    <h5 class="modal-title" id="modalTitleId">Borç Ekle</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <div class="row">

        <div class="col-xxl-4 col-xl-6 col-lg-4 col-sm-6 single-note-item all-category note-important note-tasks">
            <div class="card card-body mb-4 stretch stretch-full">
                <span class="side-stick"></span>
                <h5 class="note-title text-truncate w-75 mb-1" data-noteheading="Book a Ticket for Movie"><?php echo $kisi->adi_soyadi ?> <i class="point bi bi-circle-fill ms-1 fs-7"></i></h5>
                <p class="fs-11 text-muted"><?php echo $kisi->daire_kodu ?></p>
                <div class="note-content flex-grow-1">
                    <p class="text-muted note-inner-content text-truncate-3-line" data-notecontent="">
                        Tanımlamış olduğunuz borçlardan ilgili kişiye borçlandırma yapabilirsiniz.
                    </p>
                </div>
                <div class="note-content flex-grow-1">
                    <div class="ps-3 border-start border-3 border-primary rounded">
                        <div class="d-flex flex-column">
                            <a href="javascript:void(0);" class="fw-semibold text-truncate-1-line">Borç Bilgileri</a>
                            <a href="javascript:void(0);" class="fs-12 fw-medium text-muted borc-baslangic">Başlangıç Tarihi : <?php echo Date::dmY($borc_detay->baslangic_tarihi ?? ''); ?></a>
                            <a href="javascript:void(0);" class="fs-12 fw-medium text-muted borc-bitis">Bitiş Tarihi : <?php echo Date::dmY($borc_detay->bitis_tarihi ?? ''); ?></a>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <span class="avatar-text avatar-sm"><i class="feather-star favourite-note"></i></span>
                    <span class="avatar-text avatar-sm"><i class="feather-trash-2 remove-note"></i></span>

                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card widget-tickets-content">

                <div class="card-body custom-card-action">
                    <div class="notes-box">
                        <div class="notes-content">
                            <form action="javascript:void(0);" id="borcEkleForm" method="post" class="needs-validation" novalidate>
                                <input type="text" name="kisi_id" value="<?= Security::encrypt($kisi_id) ?>" hidden>
                                <input type="text" name="borc_detay_id" value="<?= Security::encrypt($borc_detay_id) ?>" hidden>

                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label fw-bold">Borç Türü</label>
                                         <div class="input-group flex-nowrap w-100">
                                    <div class="input-group-text"><i class="fas fa-file-invoice"></i></div>
                                            <?php echo $BorclandirmaHelper->BorclandirmaTuruSelect('borclandirmalar', $borc_id); ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">

                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">Tutar</label>
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="feather-credit-card"></i></div>
                                            <input type="text" class="form-control money" name="borc_tutar" id="borc_tutar" value="<?= $borc_detay->tutar ?? '' ?>"
                                                placeholder="₺ 0,00" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">Tarih</label>
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="feather-calendar"></i></div>
                                            <input type="text" class="form-control flatpickr" 
                                                name="borc_islem_tarihi" id="borc_islem_tarihi" autocomplete="off"
                                                value="<?= Date::dmYHIS($borc_detay->borclandirma_tarihi ?? Date::now())  ?>">
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label">Açıklama</label>
                                        <div class="input-group flex-nowrap mb-3">
                                            <div class="input-group-text">
                                                <i class="feather-file-text"></i>
                                            </div>
                                            <textarea id="borc_aciklama" name="borc_aciklama"
                                                class="form-control" placeholder="Açıklama giriniz.(Referans No vb."
                                                rows="5"><?= $borc_detay->aciklama ?? '' ?></textarea>

                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
            <button type="button" class="btn btn-primary" id="borcEkleBtn">Borç Ekle</button>
        </div>


    </div>
</div>