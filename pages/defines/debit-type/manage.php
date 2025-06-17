<?php
require_once "App/Helper/helper.php";

use App\Helper\Helper;

require_once 'Model/DefinesModel.php';
$defineObj = new DefinesModel();
$id = $_GET['id'] ?? 0;
$debitType = $defineObj->find($id);

$pageTitle = $id > 0 ? 'Borçlandırma Türü Güncelleme' : 'Yeni Borçlandırma Türü Tanımlama';

?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Tanımlamalar</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Borçlandırma İşlemleri</li>
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

                <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="defines/debit-type/list">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </button>
                <button type="button" class="btn btn-primary" id="saveDebitType"></button>
                    <i class="feather-save  me-2"></i>
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
    <?php
    $title = $pageTitle;
    if ($pageTitle === 'Yeni Borçlandırma Türü Tanımlama') {
        $text = "Yeni borçlandırma türü tanımlayabilirsiniz.";
    } else {
        $text = "Seçtiğiniz borçlandırma türünü güncelleyebilirsiniz.";
    }
    require_once 'pages/components/alert.php'
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <form action='' id='debitTypeForm'></form>
                            <div class="card-body custom-card-action p-0">
                                <div class="card-body personal-info">
                                    <div class="row mb-4 align-items-center">
                                        <!--********** HIDDEN ROW************** -->
                                        <div class='row d-none'>
                                            <div class='col-md-4'>
                                                <input type='text' name='id' class='form-control'
                                                    value="<?php echo $debitType->id ?? 0 ?>">
                                            </div>
                                            <div class='col-md-4'>
                                                <input type='text' name='action' value='saveDebitType' class='form-control'>
                                            </div>
                                        </div>
                                        <!--********** HIDDEN ROW************** -->
                                        <div class="col-lg-2">
                                            <label for="debit_name" class="fw-semibold">Adı: </label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-user-plus"></i></div>
                                                <input type="text" class="form-control" id="debit_name" name="debit_name" value="<?php echo $debitType->debit_name ?? '' ?>">
                                            </div>
                                        </div>

                                        <div class="col-lg-2">
                                            <label for="debit_type" class="fw-semibold">Tipi: </label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group flex-nowrap w-100">
                                                <div class="input-group-text"><i class="feather-dollar-sign"></i></div>
                                                <?php echo Helper::debitTypeSelect("debit_type", $debitType->type_id ?? 1) ?>
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