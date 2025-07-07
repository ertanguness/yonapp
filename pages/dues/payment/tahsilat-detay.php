<?php
require_once dirname(__DIR__ ,levels: 3). '/configs/bootstrap.php';


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
                <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" title=""
                    data-bs-original-title="Send Invoice">
                    <i class="feather feather-send"></i>
                </div>
            </a>
            <a href="javascript:void(0)" class="d-flex me-1 printBTN">
                <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" title=""
                    data-bs-original-title="Print Invoice" aria-label="Print Invoice"><i
                        class="feather feather-printer"></i></div>
            </a>
            <a href="javascript:void(0)" class="d-flex me-1">
                <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" title=""
                    data-bs-original-title="Add Payment" aria-label="Add Payment"><i
                        class="feather feather-dollar-sign"></i></div>
            </a>
            <a href="javascript:void(0)" class="d-flex me-1 file-download">
                <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" title=""
                    data-bs-original-title="Download Invoice" aria-label="Download Invoice"><i
                        class="feather feather-download"></i></div>
            </a>
            <a href="invoice-create.html" class="d-flex me-1">
                <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" title=""
                    data-bs-original-title="Edit Invoice">
                    <i class="feather feather-edit"></i>
                </div>
            </a>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
    </div>

</div>
<style>
    /* .card-body {
        margin: 0;
        padding: 0;
    } */
</style>

<div class="modal-body ">



    <div class="row p-3 border-gray-5 mb-3 mt-0">

        <div class="col-xxl-4 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-text avatar-xl rounded bg-soft-danger text-danger border-soft-danger">
                                <i class="feather-user-minus"></i>
                            </div>
                            <a href="javascript:void(0);" class="fw-bold d-block">
                                <span class="d-block">BORÇ (TL)</span>
                                <span class="fs-24 fw-bolder d-block text-danger borc-etiket">
                                    <?php echo Helper::formattedMoney(-$finansalDurum->toplam_borc); ?>

                                </span>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-4 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-text avatar-xl rounded bg-soft-success text-success border-soft-success">
                                <i class="feather-user-check"></i>
                            </div>
                            <a href="javascript:void(0);" class="fw-bold d-block">
                                <span class="d-block">TAHSİLAT (TL)</span>
                                <span class="fs-24 fw-bolder d-block text-success tahsilat-etiket">
                                    <?php echo Helper::formattedMoney($finansalDurum->toplam_odeme); ?>
                                </span>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-4 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-text avatar-xl rounded bg-soft-primary text-primary border-soft-primary">
                                <i class="feather-users"></i>
                            </div>
                            <a href="javascript:void(0);" class="fw-bold d-block">
                                <span class="d-block">BAKİYE (TL)</span>
                                <span class="fs-24 fw-bolder d-block bakiye-etiket">
                                    <?php echo Helper::formattedMoney($finansalDurum->bakiye); ?>

                                </span>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-lg-6">

            <div class="card widget-tickets-content">
                <div class="card-header">
                    <h5 class="card-title">Borçlar</h5>
                    <div class="card-header-action">
                        <div class="card-header-btn">

                            <div data-bs-toggle="tooltip" title="" data-bs-original-title="Refresh">
                                <a href="#" class="avatar-text avatar-xs bg-warning" data-bs-toggle="refresh"> </a>
                            </div>
                            <div data-bs-toggle="tooltip" title="" data-bs-original-title="Maximize/Minimize">
                                <a href="#" class="avatar-text avatar-xs bg-success" data-bs-toggle="expand"> </a>
                            </div>
                        </div>
                        <div class="dropdown">
                            <a href="#" class="avatar-text avatar-sm" data-bs-toggle="dropdown" data-bs-offset="25, 25">
                                <div data-bs-toggle="tooltip" title="" data-bs-original-title="Options">
                                    <i class="feather-more-vertical"></i>
                                </div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="javascript:void(0);" class="dropdown-item"><i
                                        class="feather-at-sign"></i>New</a>
                                <a href="javascript:void(0);" class="dropdown-item"><i
                                        class="feather-calendar"></i>Event</a>
                                <a href="javascript:void(0);" class="dropdown-item"><i
                                        class="feather-bell"></i>Snoozed</a>
                                <a href="javascript:void(0);" class="dropdown-item"><i
                                        class="feather-trash-2"></i>Deleted</a>
                                <div class="dropdown-divider"></div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="overflow-auto tasks-items-wrapper" style="height: 340px;">
                    <div class="card-body custom-card-action p-0">
                        <div class="table-responsive tickets-items-wrapper">
                            <table class="table table-hover mb-0">
                                <tbody>
                                    <?php foreach ($borclandirmalar as $borc): ?>
                                        <tr>
                                            <td style="width:4%;">
                                                <div class="avatar-text bg-gray-100">
                                                    <a href="javascript:void(0);">
                                                        <?php echo Helper::getInitials($borc->borc_adi); ?>
                                                    </a>
                                                </div>

                                            </td>
                                            <td>
                                                <a href="javascript:void(0);"><?php echo $borc->borc_adi; ?> <span
                                                        class="fs-12 fw-normal text-muted">(20/02/2023)</span> </a>
                                                <p class="fs-12 text-muted text-truncate-1-line tickets-sort-desc">
                                                    <?php echo $borc->aciklama; ?>
                                                </p>
                                                <div class="tickets-list-action d-flex align-items-center gap-3">
                                                    <a href="javascript:void(0);">View</a>
                                                    <span>|</span>
                                                    <a href="javascript:void(0);">View public form</a>
                                                    <span>|</span>
                                                    <a href="javascript:void(0);">Edit</a>
                                                    <span>|</span>
                                                    <a href="javascript:void(0);" class="text-danger">Delete</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                <!-- Kayıt yok ise  -->
                <?php if (empty($borclandirmalar)): ?>
                    <div class="text-center text-muted">
                        <p>Kayıt Bulunamadı!!!</p>

                    </div>
                <?php endif ?>



            </div>
        </div>
        <div class="col-lg-6">

            <div class="card widget-tickets-content">
                <div class="card-header">
                    <h5 class="card-title">Ödemeler</h5>
                    <div class="card-header-action">
                        <div class="card-header-btn">

                            <div data-bs-toggle="tooltip" title="" data-bs-original-title="Refresh">
                                <a href="#" class="avatar-text avatar-xs bg-warning" data-bs-toggle="refresh"> </a>
                            </div>
                            <div data-bs-toggle="tooltip" title="" data-bs-original-title="Maximize/Minimize">
                                <a href="#" class="avatar-text avatar-xs bg-success" data-bs-toggle="expand"> </a>
                            </div>
                        </div>
                        <div class="dropdown">
                            <a href="#" class="avatar-text avatar-sm" data-bs-toggle="dropdown" data-bs-offset="25, 25">
                                <div data-bs-toggle="tooltip" title="" data-bs-original-title="Options">
                                    <i class="feather-more-vertical"></i>
                                </div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="javascript:void(0);" class="dropdown-item"><i
                                        class="feather-at-sign"></i>New</a>
                                <a href="javascript:void(0);" class="dropdown-item"><i
                                        class="feather-calendar"></i>Event</a>
                                <a href="javascript:void(0);" class="dropdown-item"><i
                                        class="feather-bell"></i>Snoozed</a>
                                <a href="javascript:void(0);" class="dropdown-item"><i
                                        class="feather-trash-2"></i>Deleted</a>
                                <div class="dropdown-divider"></div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="overflow-auto tasks-items-wrapper" style="height: 340px;">
                    <div class="card-body custom-card-action p-0">
                        <div class="table-responsive tickets-items-wrapper">
                            <table class="table table-hover mb-0">
                                <tbody>
                                    <?php foreach ($tahsilatlar as $tahsilat):
                                        $enc_id = Security::encrypt($tahsilat->id);
                                    ?>
                                        <tr class="cursor-pointer">
                                            <td style="width:4%;">
                                                <div class="avatar-text bg-gray-100">
                                                    <a href="javascript:void(0);">
                                                        <?php echo Helper::getInitials($tahsilat->tahsilat_tipi); ?>
                                                    </a>
                                                </div>

                                            </td>
                                            <td style="width:60%;">
                                                <a href="javascript:void(0);"><?php echo $tahsilat->tahsilat_tipi; ?></a>
                                                <p class="fs-12 text-muted text-truncate-1-line tickets-sort-desc">
                                                    <?php echo !empty($tahsilat->aciklama) ? $tahsilat->aciklama : "&nbsp;"; ?>
                                                </p>
                                                <div class="tickets-list-action d-flex align-items-center gap-3">
                                                    <a href="javascript:void(0);">Düzenle</a>
                                                    <span>|</span>
                                                    <a href="javascript:void(0);" data-id="<?php echo $enc_id ?>"
                                                        class="text-danger tahsilat-sil">Sil</a>
                                                </div>
                                            </td>
                                            <td class="text-end" style="width: 35%;">
                                                <a href="javascript:void(0);"
                                                    class="fw-bold d-block"><?php echo Helper::formattedMoney($tahsilat->tutar); ?></a>
                                                <span
                                                    class="fs-12 text-muted"><?php echo "Ödeme Tarihi : " . Date::dmY($tahsilat->islem_tarihi); ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                <!-- Kayıt yok ise  -->
                <?php if (empty($tahsilatlar)): ?>
                    <div class="text-center text-muted">
                        <p>Kayıt Bulunamadı!!!</p>

                    </div>
                <?php endif ?>



            </div>
        </div>

    </div>
</div>
