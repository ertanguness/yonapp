<?php 
use App\Services\Gate;
use App\Helper\Helper;


?>

<div class="row row-cards card-wrapper" data-card="row-cards">
    <div class="d-flex justify-content-end px-2 pt-2">
        <span class="drag-handle" title="Taşı"><i class="bi bi-arrows-move"></i></span>
    </div>
    <!-- [Mini Card] start -->
    <div class="col-xxl-3 col-md-6">
        <div class="card stretch stretch-full">
            <div class="card-body">
                <div class="hstack justify-content-between">
                    <div>
                        <h4 class="text-success"><?php echo Helper::formattedMoney($toplam_aidat_geliri); ?></h4>
                        <div class="text-muted">Toplam Aidat Geliri</div>
                    </div>
                    <div class="text-end">
                        <i class="feather-credit-card fs-2"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-success py-3">
                <div class="hstack justify-content-between">
                    <p class="text-white mb-0">+5% artış</p>
                    <div class="text-end">
                        <i class="feather-trending-up text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xxl-3 col-md-6">
        <div class="card stretch stretch-full">
            <div class="card-body">
                <div class="hstack justify-content-between">
                    <div>
                        <h4 class="text-danger"><?php echo Helper::formattedMoney($geciken_odeme_tutari); ?></h4>
                        <div class="text-muted">Gecikmiş Ödemeler</div>
                    </div>
                    <div class="text-end">
                        <i class="feather-alert-triangle fs-2"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-danger py-3">
                <div class="hstack justify-content-between">
                    <p class="text-white mb-0">+2.5% artış</p>
                    <div class="text-end">
                        <i class="feather-trending-up text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xxl-3 col-md-6">
        <div class="card stretch stretch-full">
            <div class="card-body">
                <div class="hstack justify-content-between">
                    <div>
                        <h4 class="text-warning"><?php echo Helper::formattedMoney($toplam_gider); ?></h4>
                        <div class="text-muted">Toplam Giderler</div>
                    </div>
                    <div class="text-end">
                        <i class="feather-dollar-sign fs-2"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-warning py-3">
                <div class="hstack justify-content-between">
                    <p class="text-white mb-0">-1.2% azalma</p>
                    <div class="text-end">
                        <i class="feather-trending-down text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xxl-3 col-md-6">
        <div class="card stretch stretch-full">
            <div class="card-body">
                <div class="hstack justify-content-between">
                    <div>
                        <h4 class="text-danger"><?php echo Helper::formattedMoney($geciken_tahsilat_sayisi); ?></h4>
                        <div class="text-muted">Gecikmiş Aidat Sayısı</div>
                    </div>
                    <div class="text-end">
                        <i class="feather-alert-circle fs-2"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-info py-3">
                <div class="hstack justify-content-between">
                    <p class="text-white mb-0">Sabit</p>
                    <div class="text-end">
                        <i class="feather-minus text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [Mini Card] end -->
</div>
