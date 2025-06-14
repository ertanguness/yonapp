<?php 
require_once '../../../vendor/autoload.php';

use App\Helper\Security;
use App\Helper\Helper;

use Model\TahsilatModel;
use Model\BorclandirmaDetayModel;

use Model\KisilerModel;

$Kisi = new KisilerModel();
$BorcDetay = new BorclandirmaDetayModel();
$Tahsilat = new TahsilatModel();




$id = Security::decrypt($_GET['kisi_id']) ?? 0;
$kisi = $Kisi->find($id);

$finansalDurum = $BorcDetay->KisiFinansalDurum($id);
$bakiye_color = $finansalDurum->bakiye < 0 ? 'text-danger' : 'text-success';

$borclandirmalar = $BorcDetay->KisiBorclandirmalari($id);
$tahsilatlar = $Tahsilat->KisiTahsilatlari($id);


 ?>
<div class="modal-header">
    <h5 class="modal-title" id="modalTitleId"> <?php echo $kisi->adi_soyadi ?> Tahsilat Onay Detayları </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">



    <div class="d-md-flex flex-wrap p-3 border-gray-5 align-items-center justify-content-between mt-0">
        <div class="flex-fill mb-4 mb-md-0 pb-2 pb-md-0">
            <p class="fs-11 fw-semibold text-uppercase text-primary mb-2">Borç(TL)</p>
            <h2 class="fs-20 fw-bold mb-0"><?php echo Helper::formattedMoney(-$finansalDurum->toplam_borc); ?></h2>
        </div>
        <div class="vr mx-4 text-gray-600 d-none d-md-flex"></div>
        <div class="flex-fill mb-4 mb-md-0 pb-2 pb-md-0">
            <p class="fs-11 fw-semibold text-uppercase text-danger mb-2">Tahsilat(TL)</p>
            <h2 class="fs-20 fw-bold mb-0"><?php echo Helper::formattedMoney($finansalDurum->toplam_odeme); ?></h2>
        </div>
        <div class="vr mx-4 text-gray-600 d-none d-md-flex"></div>
        <div class="flex-fill">
            <p class="fs-11 fw-semibold text-uppercase mb-2">Bakiye(TL)</p> 
            <h2 class="fs-20 fw-bold mb-0 <?php echo $bakiye_color; ?>"><?php echo Helper::formattedMoney($finansalDurum->bakiye); ?></h2>
        </div>
        <div class="vr mx-4 text-gray-600 d-none d-md-flex"></div>
        <div class="flex-fill">
            <div class="fs-12 mt-3">
                <div class="hstack gap-2 text-muted mb-2">
                    <div class="avatar-text avatar-sm">
                        <i class="feather-phone-call"></i>
                    </div>
                    <span class="text-truncate-1-line">Excele Akar</span>
                </div>
                <div class="hstack gap-2 text-muted mb-2">
                    <div class="avatar-text avatar-sm">
                        <i class="feather-mail"></i>
                    </div>
                    <span class="text-truncate-1-line">Pdf'e Aktar</span>
                </div>
                <div class="hstack gap-2 text-muted mb-3">
                    <div class="avatar-text avatar-sm">
                        <i class="feather-map-pin"></i>
                    </div>
                    <span class="text-truncate-1-line">WhatsApp'tan Gönder</span>
                </div>

            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-xxl-6 col-lg-6">
            <div class="card stretch stretch-full">
                <div class="card-header">
                    <h5 class="card-title">Borçlar</h5>
                    <div class="card-header-action">
                        <div class="card-header-btn">
                           
                            <div data-bs-toggle="tooltip" title="" data-bs-original-title="Maximize/Minimize">
                                <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success"
                                    data-bs-toggle="expand"> </a>
                            </div>
                        </div>
                        <div class="dropdown">
                            <a href="javascript:void(0);" class="avatar-text avatar-sm" data-bs-toggle="dropdown"
                                data-bs-offset="25, 25">
                                <div data-bs-toggle="tooltip" title="" data-bs-original-title="Options">
                                    <i class="feather-more-vertical"></i>
                                </div>
                            </a>

                        </div>
                    </div>
                </div>
                <div class="card-body custom-card-action">
                    <!-- BORÇLANDIRMALAR BURADA -->
                    <?php foreach ($borclandirmalar as $borc): ?>
                    <div class="d-md-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div
                                class="avatar-text avatar-lg bg-soft-danger text-danger border-soft-danger rounded me-3">
                                <?php echo Helper::getInitials($borc->borc_adi); ?>
                            </div>
                            <div>
                                <a href="javascript:void(0);"><?php echo $borc->borc_adi; ?></a>
                                <p class="fs-12 text-muted mb-0"><?php echo $borc->aciklama; ?></p>
                            </div>
                        </div>
                        <div class="mt-2 mt-md-0 text-md-end mg-l-60 ms-md-0">
                            <a href="javascript:void(0);" class="fw-bold d-block"><?php echo Helper::formattedMoney($borc->tutar); ?></a>
                            <span class="fs-12 text-muted"><?php echo "Son Ödeme : " . $borc->bitis_tarihi; ?></span>
                        </div>
                    </div>
                    <hr class="border-dashed my-3">
                    <?php endforeach ?>
                    <!-- Kayıt yok ise  -->
                    <?php if (empty($borclandirmalar)): ?>
                    <div class="text-center text-muted">
                        <p>Kayıt Bulunamadı!!!</p>

                    <?php endif ?>


                </div>
            </div>
        </div>
        <div class="col-xxl-6 col-lg-6">
            <div class="card stretch stretch-full">
                <div class="card-header">
                    <h5 class="card-title">Ödemeler</h5>
                    <div class="card-header-action">
                        <div class="card-header-btn">

                            <div data-bs-toggle="tooltip" title="" data-bs-original-title="Maximize/Minimize">
                                <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success"
                                    data-bs-toggle="expand"> </a>
                            </div>
                        </div>
                        <div class="dropdown">
                            <a href="javascript:void(0);" class="avatar-text avatar-sm" data-bs-toggle="dropdown"
                                data-bs-offset="25, 25">
                                <div data-bs-toggle="tooltip" title="" data-bs-original-title="Options">
                                    <i class="feather-more-vertical"></i>
                                </div>
                            </a>
                            
                        </div>
                    </div>
                </div>
                <div class="card-body custom-card-action">
                <?php foreach ($tahsilatlar as $tahsilat): ?>
                    <div class="d-md-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div
                                class="avatar-text avatar-lg bg-soft-primary text-primary border-soft-primary rounded me-3">
                                <?php echo Helper::getInitials($tahsilat->tahsilat_tipi); ?>
                            </div>
                            <div>
                                <a href="javascript:void(0);"><?php echo $tahsilat->tahsilat_tipi; ?></a>
                                <p class="fs-12 text-muted mb-0"><?php echo $tahsilat->aciklama; ?></p>
                            </div>
                        </div>
                        <div class="mt-2 mt-md-0 text-md-end mg-l-60 ms-md-0">
                            <a href="javascript:void(0);" class="fw-bold d-block"><?php echo Helper::formattedMoney($tahsilat->tutar); ?></a>
                            <span class="fs-12 text-muted"><?php echo "Ödeme Tarihi : " . $tahsilat->islem_tarihi; ?></span>
                        </div>
                    </div>
                    <hr class="border-dashed my-3">
                    <?php endforeach ?>
                       <!-- Kayıt yok ise  -->
                       <?php if (empty($tahsilatlar)): ?>
                    <div class="text-center text-muted">
                        <p>Kayıt Bulunamadı!!!</p>

                    <?php endif ?>
                </div>
            </div>
        </div>

    </div>
</div>
