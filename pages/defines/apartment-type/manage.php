<?php

use App\Helper\Security;
use App\Helper\Helper;
use Model\DefinesModel;

$Tanimlamalar = new DefinesModel();

$id = Security::decrypt($id ?? 0);

$daireTipi = $Tanimlamalar->daireTipiGetir($id);
?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Tanımlamalar</h5>
        </div>
        <ul class="breadcrumb"></ul>
            <li class="breadcrumb-item"><a href="index?p=home">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Daire Tipi Tanımlama</li>
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
                <a href="/daire-tipi-listesi" class="btn btn-outline-secondary route-link me-2">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </a>
                <button type="button" class="btn btn-primary" id="saveApartmentType">
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
                        <form action="" id="apartmentTypeForm">
                            <div class="card-body custom-card-action p-0">
                                <div class="card-body personal-info">
                                    <div class="row mb-4 align-items-center">
                                    <input type="hidden" name="apartment-type_id" id="apartment-type_id" value="<?php echo Security::encrypt($id ?? 0)  ; ?>">

                                        <div class="col-lg-2">
                                            <label for="apartment_type_name" class="fw-semibold">Daire Tipi Adı: </label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-home"></i></div>
                                                <input type="text" class="form-control" id="apartment_type_name" name="apartment_type_name" value="<?php echo $daireTipi->define_name ?? ''; ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                            <label for="apartment_type_code" class="fw-semibold">Mülk Tipi: </label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group flex-nowrap w-100">
                                                <div class="input-group-text"><i class="feather-home"></i></div>
                                                <?php echo Helper::getMulkTipiSelect('mulk_tipi', $daireTipi->mulk_tipi ?? null); ?>
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
                                                <textarea class="form-control" id="description" name="description" cols="30" rows="3"><?php echo $daireTipi->description ?? ''; ?></textarea>
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
