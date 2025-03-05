<?php
require_once "App/Helper/helper.php";

use App\Helper\Helper;

require_once 'Model/DefinesModel.php';
$defineObj = new DefinesModel();
$id = $_GET['id'] ?? 0;
$incexp = $defineObj->find($id);

$pageTitle = $id > 0 ? 'Gelir-Gider Türü Güncelleme' : 'Yeni Gelir-Gider Türü';

?>
<div class="container-xl">
    <div class="row row-deck row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="fw-bold mb-0 me-4">
                        <span class="d-block mb-2"><?php echo $pageTitle;   ?></span>
                        <span class="fs-12 fw-normal text-muted text-truncate-1-line">Gelir/Gider türü ekleme, düzenleme işlemlerini yapabilirsiniz.</span>
                    </h5>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="defines/incexp/list">
                            <i class="feather-arrow-left me-2"></i>
                            Listeye Dön
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="saveIncExpType">
                            <i class="feather-save  me-2"></i>
                            Kaydet
                        </button>
                    </div>
                </div>


                <div class="main-content">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card border-top-0">
                                <form action='' id='incExpForm'>
                                    <div class="card-body personal-info">
                                        <div class="row mb-4 align-items-center">
                                            <!--********** HIDDEN ROW************** -->
                                            <div class='row d-none'>
                                                <div class='col-md-4'>
                                                    <input type='text' name='id' class='form-control'
                                                        value="<?php echo $incexp->id ?? 0 ?>">
                                                </div>
                                                <div class='col-md-4'>
                                                    <input type='text' name='action' value='saveIncExpType' class='form-control'>
                                                </div>
                                            </div>
                                            <!--********** HIDDEN ROW************** -->
                                            <div class="col-lg-2">
                                                <label for="incexp_name" class="fw-semibold">Adı: </label>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="input-group">
                                                    <div class="input-group-text"><i class="feather-user-plus"></i></div>
                                                    <input type="text" class="form-control" id="incexp_name" name="incexp_name " value="<?php echo $incexp->incexp_name ?? '' ?>">
                                                </div>
                                            </div>

                                            <div class="col-lg-2">
                                                <label for="incexp_type" class="fw-semibold">Tipi: </label>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="input-group">
                                                    <div class="input-group-text"><i class="feather-dollar-sign"></i></div>
                                                    <?php echo Helper::incExpTypeSelect("incexp_type", $incexp->type_id ?? 1) ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-4 align-items-center">
                                            <div class="col-lg-2">
                                                <label for="description" class="fw-semibold">Açıklama: </label>
                                            </div>
                                            <div class="col-lg-10">
                                                <div class="input-group">
                                                    <div class="input-group-text"><i class="feather-type"></i></div>
                                                    <textarea class="form-control" id="description" cols="30" rows="3" value="<?php echo $incexp->description ?? '' ?>"></textarea>
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
</div>