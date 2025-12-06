<?php

use App\Helper\Helper;
use Model\FinansalRaporModel;

$FinansalRaporModel = new FinansalRaporModel();

$amount = -2000;
$minAmount = $FinansalRaporModel->getKisiBorclarByMinAmount($amount);
//Helper::dd($minAmount);

?>



<div class="col-xxl-12 col-lg-6 card-wrapper" id="borc-listele">
    <div class="card stretch stretch-full">

        <div class="card-header">
            <h5 class="card-title">Borcluları Listele</h5>
            <div class="card-header-action">
        <button class="btn btn-primary" id="borc-search-all">Tümü</button>
                <span class="drag-handle" title="Taşı"><i class="bi bi-arrows-move"></i></span>

            </div>
        </div>

        <div class="card-body custom-card-action borc-listele">
           <?php foreach ($minAmount as $borc): ?>
           <div class="d-md-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                   <div class="avatar-text avatar-lg rounded bg-soft-danger text-danger border-soft-danger me-3">
                    <?php echo Helper::getInitials($borc->adi_soyadi,2) ?>
                </div>
                    <div>
                        <a href="javascript:void(0);"><?= $borc->adi_soyadi ?></a>
                        <p class="fs-12 text-muted mb-0"><?= $borc->daire_kodu ?></p>
                    </div>
                </div>
                <div class="mt-2 mt-md-0 text-md-end mg-l-60 ms-md-0">
                    <a href="javascript:void(0);" class="fw-bold d-block"><?= Helper::formattedMoney($borc->bakiye) ?></a>
                </div>
            </div>  
            <hr class="border-dashed my-3">
           <?php endforeach; ?>

        </div>
    </div>
</div>