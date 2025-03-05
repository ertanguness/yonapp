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
if($id == null && isset($_GET['id'])) {
    header("Location: /index.php?p=mycompany/list");
    exit;
}


$pageTitle = $id > 0 ? "Firma Güncelle" : "Yeni Firma";
$myfirm = $companyObj->findMyFirm($id);
?>
<div class="page-wrapper">
    <!-- Page header -->
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <?php echo $pageTitle ; ?>
                    </h2>
                </div>

                <!-- Page title actions -->
                <div class="col-auto ms-auto d-print-none">
                    <button type="button" class="btn btn-outline-secondary route-link" data-page="mycompany/list">
                        <i class="ti ti-list icon me-2"></i>
                        Listeye Dön
                    </button>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <button type="button" class="btn btn-primary" id="saveMyFirm">
                        <i class="ti ti-device-floppy icon me-2"></i>
                        Kaydet
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Page body -->
    <div class="page-body">
        <div class="container-xl">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a href="#tabs-home-3" class="nav-link active" data-bs-toggle="tab" aria-selected="true" role="tab"><!-- Download SVG icon from http://tabler-icons.io/i/home -->
                                    <i class="ti ti-home icon me-1"></i>
                                    Genel Bilgiler</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a href="#tabs-profile-3" class="nav-link" data-bs-toggle="tab" aria-selected="false" tabindex="-1" role="tab"><!-- Download SVG icon from http://tabler-icons.io/i/user -->
                                    <i class="ti ti-receipt-tax icon me-1"></i>
                                    Vergi Bilgileri</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <form action="" id="myFirmForm">
                            <div class="tab-content">
                                <div class="row d-none">
                                    <div class="col-md-4">
                                        <input type="text" name="id" class="form-control" value="<?php echo $new_id ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" name="action" class="form-control" value="saveMyCompany">
                                    </div>
                                </div>
                                <div class="tab-pane active show" id="tabs-home-3" role="tabpanel">
                                    <?php include_once "content/0-home.php"; ?>
                                </div>
                                <div class="tab-pane" id="tabs-profile-3" role="tabpanel">
                                    <?php include_once "content/1-tax-info.php"; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>