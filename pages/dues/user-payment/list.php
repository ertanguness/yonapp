<?php
use App\Helper\Helper;
use App\Helper\Security;


use Model\UserPaymentModel;

// Kullanıcı Ödemeleri
$UserPayment = new UserPaymentModel();

// Kullanıcının Gruplanmış Borç Başlıklarını ve Ödeme Durumlarını Getirir
$gruplanmisBorc = $UserPayment->KategoriBazliOzet(113);
$hesap_ozet = $UserPayment->KullaniciToplamBorc(113);
$bakiye_color = $hesap_ozet->bakiye > 0 ? "success" : "danger";


?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Borçlarım</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Borçlarım</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <a href="index?p=dues/user-payment/manage" class="btn btn-success">
            <i class="feather-credit-card me-2"></i>
            Borç Öde
        </a>
    </div>
</div>
<style>
.activity-feed-1 .feed-item {
    padding-bottom: 15px !important;

}
</style>

<div class="main-content">
    <div class="row">
        <div class="container-xl mb-5">
            <div class="row">


                <div class="col-xxl-4 col-lg-4 col-md-6">
                    <div class="card stretch stretch-full">
                        <div class="card-body">
                            <div class="fs-12 fw-medium text-muted mb-3">BORÇ (₺)</div>
                            <div class="hstack justify-content-between lh-base">
                                <h3><span class="counter text-danger"><?php echo $hesap_ozet->toplam_borc ?> ₺</span></h3>
                                <div class="hstack gap-2 fs-11 text-success">
                                    <i class="feather-arrow-up-circle fs-12"></i>
                                    <span>+25.48%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-lg-4 col-md-6">
                    <div class="card stretch stretch-full">
                        <div class="card-body">
                            <div class="fs-12 fw-medium text-muted mb-3">ÖDENEN (₺)</div>
                            <div class="hstack justify-content-between lh-base">
                                <h3><span class="counter text-success"><?php echo $hesap_ozet->toplam_tahsilat ?> ₺</span></h3>
                                <div class="hstack gap-2 fs-11 text-success">
                                    <i class="feather-arrow-up-circle fs-12"></i>
                                    <span>+25.48%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-lg-4 col-md-6">
                    <div class="card stretch stretch-full">
                        <div class="card-body">
                            <div class="fs-12 fw-medium text-muted mb-3">KALAN (₺)</div>
                            <div class="hstack justify-content-between lh-base">
                                <h3><span class="counter text-<?php echo $bakiye_color ?>"><?php echo $hesap_ozet->bakiye ?> ₺</span></h3>
                                <div class="hstack gap-2 fs-11 text-success">
                                    <i class="feather-arrow-up-circle fs-12"></i>
                                    <span>+25.48%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="col-xxl-12 col-lg-12 ">
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title">Özet Bilgilerim</h5>
                            <div class="card-header-action">
                                <div class="card-header-btn">

                                    <div data-bs-toggle="tooltip" title="" data-bs-original-title="Refresh">
                                        <a href="javascript:void(0);" class="avatar-text avatar-xs bg-warning"
                                            data-bs-toggle="refresh"> </a>
                                    </div>
                                    <div data-bs-toggle="tooltip" title="" data-bs-original-title="Maximize/Minimize">
                                        <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success"
                                            data-bs-toggle="expand"> </a>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="card-body custom-card-action">
                            <div class="accordion" id="weeklyBestsellerAccordion">
                                <?php foreach ($gruplanmisBorc as $borc) { 
                                    $color = $borc->bakiye > 0 ? "success" : "danger";
                                    ?>

                                <div class="accordion-item border border-dashed border-gray-500 my-3">
                                    <h2 class="accordion-header" id="heading<?php echo $borc->kategori_adi; ?>">
                                        <button class="accordion-button collapsed bg-white" type="button"
                                            aria-expanded="true" data-bs-toggle="collapse"
                                            data-bs-target="#collapse<?php echo $borc->kategori_adi; ?>">
                                            <div class="d-flex align-items-center w-100">
                                                <div
                                                    class="avatar-text avatar-lg bg-soft-success text-success border-soft-success rounded me-3">
                                                    <i class="feather-award"></i>
                                                </div>
                                                <div>
                                                    <a href="javascript:void(0);"><?php echo $borc->kategori_adi; ?></a>
                                                </div>
                                                <div class="text-end ms-auto pe-3">
                                                    <span class="fw-bold d-block text-<?php echo $color; ?>"><?php echo Helper::formattedMoney($borc->bakiye) ?> </span>
                                                    <span class="fs-12 text-muted"><?php echo $borc->kayit_sayisi ?> Hareket</span>
                                                </div>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse<?php echo $borc->kategori_adi; ?>" class="accordion-collapse collapse"
                                        aria-labelledby="heading<?php echo $borc->kategori_adi; ?>">
                                        <div class="accordion-body">

                                            <ul class="list-unstyled mb-0 activity-feed-1">
                                               

                                                <?php 
                                                $borc_detay = $UserPayment->KullaniciBorcDetaylari(113,$borc->kategori_adi);

                                              
                                                foreach ($borc_detay as $detay) { 
                                                    $enc_id = Security::encrypt($detay->borc_id ?? 0);
                                                    $color = $detay->islem_turu == "tahsilat" ? "success" : "danger";


                                                ?>
                                                <li class="feed-item feed-item-<?php echo  $color ; ?>">
                                                    <div class="d-flex gap-4 justify-content-between">
                                                        <div>
                                                            <div class="mb-2 text-truncate-1-line"><a
                                                                    href="javascript:void(0)"
                                                                    class="fw-semibold text-dark">
                                                                <?php echo $detay->islem_adi ; ?>
                                                                </a>
                                                            </div>
                                                            <p class="fs-12 text-muted mb-3 text-truncate-2-line">
                                                            <?php echo  $detay->aciklama ; ?></p>

                                                        </div>
                                                        <div class="text-end">
                                                            <div>

                                                                <div
                                                                    class="fw-semibold text-<?php echo  $color ; ?> text-uppercase text-muted text-nowrap">
                                                                    <?php echo Helper::formattedMoneyWithoutCurrency($detay->tutar); ?> ₺
                                                                </div>
                                                            </div>
                                                            <span class="fs-10 fw-semibold text-muted">Bak. <?php echo Helper::formattedMoneyWithoutCurrency($detay->bakiye); ?>
                                                                ₺</span>
                                                        </div>
                                                    </div>
                                                </li>
                                                <?php } ?>
                                            </ul>

                                        </div>
                                    </div>
                                </div>

                                <?php } ?>
                            </div>


                        </div>

                    </div>
                </div>

            </div>

        </div>

    </div>


</div>