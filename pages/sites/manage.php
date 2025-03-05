<?php
require_once "Model/Company.php";
require_once "App/Helper/security.php";

use App\Helper\Security;

$companyObj = new Company();

//Sayfa başlarında eklenecek alanlar
$perm->checkAuthorize("company_add_update");
$id = isset($_GET["id"]) ? Security::decrypt($_GET['id']) : 0;
$new_id = isset($_GET["id"]) ? $_GET['id'] : 0;

//Eğer url'den id yazılmışsa veya id boş ise projeler sayfasına gider
if ($id == null && isset($_GET['id'])) {
    header("Location: /index.php?p=sites/list");
    exit;
}

$pageTitle = $id > 0 ? "Site Güncelle" : "Yeni Site Ekle";
$myfirm = $companyObj->findMyFirm($id);
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
                        <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="sites/list">
                            <i class="feather-arrow-left me-2"></i>
                            Listeye Dön
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="saveMyFirm">
                            <i class="feather-save  me-2"></i>
                            Kaydet
                        </button>
                    </div>
                </div>

                <div class="main-content">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card border-top-0">
                                <div class="card-header p-0">
                                    <!-- Nav tabs -->
                                    <ul class="nav nav-tabs flex-wrap w-100 text-center customers-nav-tabs" id="myTab" role="tablist">
                                        <li class="nav-item flex-fill border-top" role="presentation">
                                            <a href="javascript:void(0);" class="nav-link active" data-bs-toggle="tab" data-bs-target="#sitebilgileriTab" role="tab">Site Bilgiler</a>
                                        </li>
                                        <li class="nav-item flex-fill border-top" role="presentation">
                                            <a href="javascript:void(0);" class="nav-link" data-bs-toggle="tab" data-bs-target="#blokbilgileriTab" role="tab">Blok Bilgileri</a>
                                        </li>
                                        <li class="nav-item flex-fill border-top" role="presentation">
                                            <a href="javascript:void(0);" class="nav-link" data-bs-toggle="tab" data-bs-target="#dairebilgileriTab" role="tab">Daire Bilgileri</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="tab-content">
                                    <div class="tab-pane fade show active" id="sitebilgileriTab" role="tabpanel">
                                        <div class="card-body personal-info">
                                            <div class="row mb-4 align-items-center">
                                                <div class="col-lg-2">
                                                    <label for="fullnameInput" class="fw-semibold">Site Adı: </label>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="input-group">
                                                        <div class="input-group-text"><i class="feather-home"></i></div>
                                                        <input type="text" class="form-control" id="fullnameInput" placeholder="Name">
                                                    </div>
                                                </div>

                                                <div class="col-lg-2">
                                                    <label for="fullnameInput" class="fw-semibold">Blok Sayısı: </label>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="input-group">
                                                        <div class="input-group-text"><i class="feather-trello"></i></div>
                                                        <input type="text" class="form-control" id="fullnameInput" placeholder="Name">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-4 align-items-center">
                                                <div class="col-lg-2">
                                                    <label for="fullnameInput" class="fw-semibold">İl </label>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="input-group">
                                                        <div class="input-group-text"><i class="feather-tag"></i></div>
                                                        <input type="text" class="form-control" id="il" placeholder="Name">
                                                    </div>
                                                </div>

                                                <div class="col-lg-2">
                                                    <label for="fullnameInput" class="fw-semibold">İlçe </label>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="input-group">
                                                        <div class="input-group-text"><i class="feather-tag"></i></div>
                                                        <input type="text" class="form-control" id="ilce" placeholder="Name">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-4 align-items-center">
                                                <div class="col-lg-2">
                                                    <label for="addressInput_1" class="fw-semibold">Adres: </label>
                                                </div>
                                                <div class="col-lg-10">
                                                    <div class="input-group">
                                                        <div class="input-group-text"><i class="feather-map-pin"></i></div>
                                                        <textarea class="form-control" id="addressInput_1" cols="30" rows="3" placeholder="Address"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-4 align-items-center">
                                                <div class="col-lg-2">
                                                    <label for="aboutInput" class="fw-semibold">About: </label>
                                                </div>
                                                <div class="col-lg-10">
                                                    <div class="input-group">
                                                        <div class="input-group-text"><i class="feather-type"></i></div>
                                                        <textarea class="form-control" id="aboutInput" cols="30" rows="5" placeholder="About"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="blokbilgileriTab" role="tabpanel">
                                        Blok bilgileri gelecek
                                    </div>
                                    <div class="tab-pane fade" id="dairebilgileriTab" role="tabpanel">
                                        Daire Bilgileri Buraya gelecek
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