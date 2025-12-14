<?php 
use App\Services\Gate;
use App\Helper\Helper;


?>
<style>
.card-wrapper[data-card="row-cards"] .card { overflow: hidden; }
.card-wrapper[data-card="row-cards"] .card-body .hstack,
.card-wrapper[data-card="row-cards"] .card-footer .hstack { display: flex; align-items: center; justify-content: space-between; gap: .5rem; min-width: 0; }
.card-wrapper[data-card="row-cards"] .hstack > div:first-child { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; align-items: center; gap: .5rem; }
.card-wrapper[data-card="row-cards"] .hstack > .text-end { flex: 0 0 auto; min-width: 2.25rem; }
.card-wrapper[data-card="row-cards"] .card-body h4 { margin: 0; white-space: nowrap; font-size: 1.25rem; line-height: 1.3; text-align: center; display: block; width: 100%; font-weight: 700; }
.card-wrapper[data-card="row-cards"] .card-body .text-muted { margin: 0; white-space: normal; overflow: visible; text-overflow: clip; word-break: break-word; overflow-wrap: anywhere; text-align: center; }
.card-wrapper[data-card="row-cards"] .metric-inline { display: flex; flex-direction: column; align-items: center; gap: .25rem; flex-wrap: nowrap; min-width: 0; margin: 0; }
.card-wrapper[data-card="row-cards"] .metric-inline .text-muted { flex: 0 0 auto; min-width: 0; }
.card-wrapper[data-card="row-cards"] .metric-inline h4 { flex: 0 0 auto; }
.card-wrapper[data-card="row-cards"] .metric-icon { display: block; align-self: center; margin-bottom: .25rem; opacity: .8; }
.card-wrapper[data-card="row-cards"] .late-count-center { width: 100%; display: flex; justify-content: center; }
.card-wrapper[data-card="row-cards"] .late-count-center h4 { text-align: center; }
.card-wrapper[data-card="row-cards"] .amount-center { width: 100%; display: flex; justify-content: center; flex: 0 0 100%; }
.card-wrapper[data-card="row-cards"] .amount-center h4 { text-align: center; display: block; width: 100%; }
.card-wrapper[data-card="row-cards"] .card-footer { min-height: 64px; height: auto; display: flex; align-items: center; }
.card-wrapper[data-card="row-cards"] .card-footer .hstack { width: 100%; gap: .5rem; align-items: center; }
.card-wrapper[data-card="row-cards"] .card-footer p { margin: 0; white-space: normal; overflow: visible; text-overflow: clip; }
.card-wrapper[data-card="row-cards"] .card-footer .text-end { flex: 0 0 auto; min-width: 2rem; }
</style>

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
                        <i class="feather-credit-card fs-2 metric-icon"></i>
                        <div class="metric-inline">
                            <div class="amount-center"><h4 class="text-success"><?php echo Helper::formattedMoney($toplam_aidat_geliri); ?></h4></div>
                            <div class="text-muted">Toplam Aidat Geliri</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-success py-3">
                <div class="hstack justify-content-between">
                    <p class="text-white mb-0"><?php echo $aidat_tr['text'] ?? 'Sabit'; ?></p>
                    <div class="text-end">
                        <i class="<?php echo ($aidat_tr['icon'] ?? 'feather-minus'); ?> text-white"></i>
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
                        <i class="feather-alert-triangle fs-2 metric-icon"></i>
                        <div class="metric-inline">
                            <div class="amount-center"><h4 class="text-danger"><?php echo Helper::formattedMoney($geciken_odeme_tutari); ?></h4></div>
                            <div class="text-muted">Gecikmiş Ödemeler</div>
                            <a href="/gecikmis-odemeler" class="link-primary small">Borçları Gör</a>


                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-danger py-3">
                <div class="hstack justify-content-between">
                    <p class="text-white mb-0"><?php echo $overdue_tr['text'] ?? 'Sabit'; ?></p>
                    <div class="text-end">
                        <i class="<?php echo ($overdue_tr['icon'] ?? 'feather-minus'); ?> text-white"></i>
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
                        <i class="feather-dollar-sign fs-2 metric-icon"></i>
                        <div class="metric-inline">
                            <div class="amount-center"><h4 class="text-warning"><?php echo Helper::formattedMoney($toplam_gider); ?></h4></div>
                            <div class="text-muted">Toplam Giderler</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-warning py-3">
                <div class="hstack justify-content-between">
                    <p class="text-white mb-0"><?php echo $gider_tr['text'] ?? 'Sabit'; ?></p>
                    <div class="text-end">
                        <i class="<?php echo ($gider_tr['icon'] ?? 'feather-minus'); ?> text-white"></i>
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
                        <i class="feather-alert-circle fs-2 metric-icon"></i>
                        <div class="metric-inline">
                            <div class="late-count-center"><h4 class="text-danger"><?php echo (int)$geciken_tahsilat_sayisi; ?></h4></div>
                            <div class="text-muted">Gecikmiş Aidat Sayısı</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-info py-3">
                <div class="hstack justify-content-between">
                    <p class="text-white mb-0"><?php echo $late_tr['text'] ?? 'Sabit'; ?></p>
                    <div class="text-end">
                        <i class="<?php echo ($late_tr['icon'] ?? 'feather-minus'); ?> text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [Mini Card] end -->
</div>
