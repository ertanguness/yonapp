<?php
require_once '../../../vendor/autoload.php';

use App\Helper\Date;
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
    <div class="ms-auto">

        <div class="d-flex align-items-center justify-content-center">
            <a href="javascript:void(0)" class="d-flex me-1" data-alert-target="invoicSendMessage">
                <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" title="" data-bs-original-title="Send Invoice">
                    <i class="feather feather-send"></i>
                </div>
            </a>
            <a href="javascript:void(0)" class="d-flex me-1 printBTN">
                <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" title="" data-bs-original-title="Print Invoice" aria-label="Print Invoice"><i class="feather feather-printer"></i></div>
            </a>
            <a href="javascript:void(0)" class="d-flex me-1">
                <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" title="" data-bs-original-title="Add Payment" aria-label="Add Payment"><i class="feather feather-dollar-sign"></i></div>
            </a>
            <a href="javascript:void(0)" class="d-flex me-1 file-download">
                <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" title="" data-bs-original-title="Download Invoice" aria-label="Download Invoice"><i class="feather feather-download"></i></div>
            </a>
            <a href="invoice-create.html" class="d-flex me-1">
                <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" title="" data-bs-original-title="Edit Invoice">
                    <i class="feather feather-edit"></i>
                </div>
            </a>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
    </div>

</div>

<div class="modal-body">



    <div class="d-md-flex flex-wrap p-3 border-gray-5 text-center mb-3 mt-0">
        <div class="vr mx-4 text-gray-600 d-none d-md-flex"></div>
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

    </div>


    <div class="row">
        <div class="col-lg-6">
            <div class="card">
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
                                <span class="fs-12 text-muted"><?php echo "Son Ödeme : " . Date::dmY($borc->bitis_tarihi); ?></span>
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
            <div class="col-lg-6">
                <div class="card shadow-sm border-0">
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
                                    <span class="fs-12 text-muted"><?php echo "Ödeme Tarihi : " . Date::dmY($tahsilat->islem_tarihi); ?></span>
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