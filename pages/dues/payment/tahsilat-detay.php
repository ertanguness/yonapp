<?php
require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';


use App\Helper\Date;
use App\Helper\Security;
use App\Helper\Helper;

use Model\TahsilatModel;
use Model\BorclandirmaDetayModel;
use Model\FinansalRaporModel;

use Model\KisilerModel;
use Random\Engine\Secure;

$Kisi = new KisilerModel();
$BorcDetay = new BorclandirmaDetayModel();
$Tahsilat = new TahsilatModel();
$FinansalRapor = new FinansalRaporModel();




$id = Security::decrypt($_GET['kisi_id']) ?? 0;
$kisi = $Kisi->find($id);



$finansalDurum = $FinansalRapor->KisiFinansalDurum($id);
$bakiye_color = $finansalDurum->bakiye < 0 ? 'text-danger' : 'text-success';

//$borclandirmalar = $BorcDetay->KisiBorclandirmalari($id);

$kisi_borclar = $FinansalRapor->getKisiBorclar($id);
$tahsilatlar = $Tahsilat->KisiTahsilatlariWithDetails($id);


?>
<div class="modal-header">
    <h5 class="modal-title" id="modalTitleId"> <?php echo $kisi->adi_soyadi ?> Tahsilat Onay Detayları </h5>
    <div class="ms-auto">

        <div class="d-flex align-items-center justify-content-center">
            <a href="javascript:void(0)" class="d-flex me-1 mesaj-gonder" data-alert-target="SendMessage"
                data-id="<?php echo $kisi->id; ?>">
                <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" title=""
                    data-bs-original-title="Mesaj Gönder">
                    <i class="feather feather-send"></i>
                </div>
            </a>
            <a href="javascript:void(0)" class="d-flex me-1 printBTN">
                <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" title=""
                    data-bs-original-title="Print Invoice" aria-label="Print Invoice"><i
                        class="feather feather-printer"></i></div>
            </a>

            <a href="/pages/dues/payment/export/kisi_borc_tahsilat.php?kisi_id=<?php echo $kisi->id; ?>" class="d-flex me-1 file-download">
                <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" title=""
                    data-bs-original-title="Download Invoice" aria-label="Download Invoice">
                  <i class="fa-solid fa-file-pdf"></i>
                    </div>
            </a>
            <a href="/pages/dues/payment/export/kisi_borc_tahsilat.php?kisi_id=<?php echo $kisi->id; ?>&format=xlsx" class="d-flex me-1 file-download">
                <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" title=""
                    data-bs-original-title="Download Invoice" aria-label="Download Invoice">
<i class="fa-regular fa-file-excel"></i>
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
                                    <?php echo Helper::formattedMoney($finansalDurum->toplam_tahsilat); ?>
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

                        </div>
                    </div>
                </div>
                <div class="overflow-auto tasks-items-wrapper" style="height: 340px;">
                    <div class="card-body custom-card-action p-0">
                        <div class="table-responsive tickets-items-wrapper">
                            <table class="table table-hover mb-0">
                                <tbody>
                                    <?php foreach ($kisi_borclar as $borc): ?>
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
                                                        class="fs-12 fw-normal text-muted">
                                                        <?php echo $borc->daire_kodu; ?> </span>
                                                    </span> </a>
                                                <p class="fs-12 text-muted text-truncate-1-line tickets-sort-desc">
                                                    <?php echo $borc->aciklama; ?>
                                                </p>
                                                <div class="tickets-list-action d-flex align-items-center gap-3">

                                                    <a href="javascript:void(0);" data-id="<?php echo Security::encrypt($borc->id); ?>" class="text-danger borc-sil">Sil</a>
                                                </div>
                                            </td>
                                            <TD>
                                                <div class="mt-2 mt-md-0 text-md-end mg-l-60 ms-md-0">
                                                    <a href="javascript:void(0);" class="fw-bold d-block">
                                                        <?php echo Helper::formattedMoney($borc->tutar); ?>

                                                    </a>
                                                    <span class="fs-12 text-danger">
                                                        <?php echo "G. Zammı : " . Helper::formattedMoney($borc->hesaplanan_gecikme_zammi); ?>
                                                    </span>
                                                </div>
                                            </TD>
                                        </tr>
                                    <?php endforeach ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                <!-- Kayıt yok ise  -->
                <?php if (empty($kisi_borclar)): ?>
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

                        </div>
                    </div>
                </div>
                <div class="overflow-auto tasks-items-wrapper" style="height: 340px;">
                    <div class="card-body custom-card-action p-0">
                        <div class="table-responsive tickets-items-wrapper">
                            <table class="table table-hover mb-0">
                                <tbody>
                                    <?php foreach ($tahsilatlar as $tahsilat):
                                        $enc_id = Security::encrypt($tahsilat['id']);
                                    ?>
                                        <tr class="cursor-pointer">
                                            <td style="width:4%;">
                                                <div class="avatar-text bg-gray-100">
                                                    <a href="javascript:void(0);">
                                                        <i class="fa fa-money-bill-wave"></i>
                                                        <!-- İkonu değiştirebilirsiniz -->
                                                    </a>
                                                </div>
                                            </td>
                                            <td style="width:60%; vertical-align: top;">
                                                <!-- Ana Açıklama ve Eylemler -->
                                                <a href="javascript:void(0);" class="fw-bold">
                                                    Tahsilat Fişi #<?php echo $tahsilat['id']; ?>
                                                </a>
                                                <p class="fs-12 text-muted text-truncate-1-line tickets-sort-desc">
                                                    <?php echo !empty($tahsilat['ana_aciklama']) ? $tahsilat['ana_aciklama'] : "Genel Tahsilat"; ?>
                                                </p>
                                                <div class="tickets-list-action d-flex align-items-center gap-3">

                                                    <a href="javascript:void(0);" data-id="<?php echo $enc_id ?>"
                                                        class="text-primary makbuz-yazdir">Makbuz Yazdır</a>
                                                    <a href="javascript:void(0);" data-id="<?php echo $enc_id ?>"
                                                        class="text-danger tahsilat-sil">Sil</a>
                                                </div>

                                                <!-- TAHSİLAT DETAYLARI ALT LİSTESİ -->
                                                <?php if (!empty($tahsilat['detaylar'])): ?>
                                                    <ul class="list-unstyled mt-2 fs-12 text-muted">
                                                        <?php foreach ($tahsilat['detaylar'] as $detay): ?>
                                                            <li class="mb-2">
                                                                <div class="ps-3">
                                                                    <i class="fa fa-check text-success me-1"></i>
                                                                    <span class="fw-bold text-dark"><?php echo htmlspecialchars($detay['borc_adi']); ?></span>
                                                                    <div class="ps-3 mt-1 text-muted">
                                                                        <?php echo htmlspecialchars($detay['aciklama'] ) ; ?>
                                                                        : <span class="fw-bold text-primary"><?php echo Helper::formattedMoney($detay['tutar']); ?></span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>

                                            </td>
                                            <td class="text-end" style="width: 35%; vertical-align: top;">
                                                <!-- Toplam Tutar ve Tarih -->
                                                <a href="javascript:void(0);" class="fw-bold d-block">
                                                    <?php echo Helper::formattedMoney($tahsilat['toplam_tutar']); ?>
                                                </a>
                                                <span class="fs-12 text-muted">
                                                    <?php echo Date::dmY($tahsilat['islem_tarihi']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Kayıt yok ise -->
                <?php if (empty($tahsilatlar)): ?>
                    <div class="text-center text-muted p-4">
                        <p>Bu kişiye ait herhangi bir tahsilat kaydı bulunamadı.</p>
                    </div>
                <?php endif; ?>



            </div>
        </div>

    </div>
</div>