<?php

use App\Helper\Security;

require_once "Model/Cases.php";
require_once "App/Helper/company.php";


$company = new CompanyHelper();
$caseObj = new Cases();


//Sayfa başlarında eklenecek alanlar
$perm->checkAuthorize("cash_register_add_update");
$id = isset($_GET["id"]) ? Security::decrypt($_GET['id']) : 0;
$new_id = isset($_GET["id"]) ? $_GET['id'] : 0;

//Eğer url'den id yazılmışsa veya id boş ise projeler sayfasına gider
if ($id == null && isset($_GET['id'])) {
    header("Location: /index.php?p=financial/case/list");
    exit;
}

$case = $caseObj->find($id);
$pageTitle = $id > 0 ? "Kasa Güncelle" : "Yeni Kasa";

?>
<div class="container-xl">
    <div class="row row-deck row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="fw-bold mb-0 me-4">
                        <span class="d-block mb-2"><?php echo $pageTitle; ?></span>
                        <span class="fs-12 fw-normal text-muted text-truncate-1-line">Aşağıdaki Siteye ait bilgileri doldurarak kayıt ediniz!</span>
                    </h5>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="financial/case/list">
                            <i class="feather-arrow-left me-2"></i>
                            Listeye Dön
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="saveMyFirm">
                            <i class="feather-save  me-2"></i>
                            Kaydet
                        </button>
                    </div>
                </div>

                <!-- Page body -->
                <div class="main-content">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card border-top-0">
                                <div class="card-header p-0">
                                    <!-- Nav tabs -->
                                    <ul class="nav nav-tabs flex-wrap w-100 text-center customers-nav-tabs" id="myTab" role="tablist">
                                        <li class="nav-item flex-fill border-top" role="presentation">
                                            <a href="javascript:void(0);" class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabs-home-3" role="tab">Genel Bilgiler</a>
                                        </li>
                                        <li class="nav-item flex-fill border-top" role="presentation">
                                            <a href="javascript:void(0);" class="nav-link" data-bs-toggle="tab" data-bs-target="#tabs-payment-3" role="tab">Kasa Hareketleri</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="tab-content">
                                    <div class="tab-pane fade show active" id="tabs-home-3" role="tab">
                                        <?php include_once 'content/0-home.php' ?>
                                    </div>
                                    <div class="tab-pane fade" id="tabs-payment-3" role="tab">
                                        <?php include_once 'content/1-transactions.php' ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>