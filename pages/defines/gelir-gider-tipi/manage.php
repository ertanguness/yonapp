<?php

use App\Helper\Security;
use App\Helper\Helper;
use Model\DefinesModel;

$Tanimlamalar = new DefinesModel();

$enc_id = $id ?? 0;  
$id = Security::decrypt($id ?? 0);

$gelirgidertipi = $Tanimlamalar->getGelirGiderTipi($id);

//echo "<pre>"; print_r($gelirgidertipi); echo "</pre>"; exit;



?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Tanımlamalar</h5>
        </div>
        <ul class="breadcrumb"></ul>
            <li class="breadcrumb-item"><a href="index?p=home">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Gelir-Gider Tipi Tanımlama</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Back</span>
                </a>
            </div>
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <a href="/gelir-gider-tipi-listesi" class="btn btn-outline-secondary route-link me-2">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </a>
                <button type="button" class="btn btn-primary" id="saveGelirGiderTipi">
                    <i class="feather-save me-2"></i>
                    Kaydet
                </button>
            </div>
        </div>
        <div class="d-md-none d-flex align-items-center">
            <a href="javascript:void(0)" class="page-header-right-open-toggle">
                <i class="feather-align-right fs-20"></i>
            </a>
        </div>
    </div>
</div>
<div class="main-content">
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <form action="" id="gelirGiderTipiForm">
                            <div class="card-body custom-card-action p-0">
                                <div class="card-body personal-info">
                                    <div class="row mb-4 align-items-center">
                                    <input type="hidden"  class="form-control "
                                    name="gelir_gider_tipi_id" id="gelir_gider_tipi_id" value="<?php echo $enc_id   ; ?>">

                                        <div class="col-lg-2">
                                            <label for="" class="fw-semibold">Gelir Gider Tipi Adı: </label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-briefcase"></i></div>
                                                <input type="text" class="form-control" id="gelir_gider_tipi_name" name="gelir_gider_tipi_name" value="<?php echo $gelirgidertipi->define_name ?? ''; ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                            <label for="" class="fw-semibold">Tipi(Gelir/Gider): </label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group flex-nowrap w-100">
                                                <div class="input-group-text"><i class="feather-home"></i></div>
                                            <?php echo Helper::getGelirGiderTipiSelect('gelir_gider_tipi', $gelirgidertipi->tip ?? null); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2">
                                            <label for="islem_kodu" class="fw-semibold">İşlem Kodu: </label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-code"></i></div>
                                                <input type="text" class="form-control" id="islem_kodu" name="islem_kodu" value="<?php echo $gelirgidertipi->islem_kodu ?? ''; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-2">
                                            <label for="description" class="fw-semibold">Açıklama: </label>
                                        </div>
                                        <div class="col-lg-10">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-type"></i></div>
                                                <textarea class="form-control" id="description" name="description" cols="30" rows="3"><?php echo $gelirgidertipi->description ?? ''; ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
