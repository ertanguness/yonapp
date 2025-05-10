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
    header("Location: /index.php?p=apartment/list");
    exit;
}

$pageTitle = $id > 0 ? "Daire Güncelle" : "Yeni Daire Ekle";
$myfirm = $companyObj->findMyFirm($id);
?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Tanımlamalar</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Daireler</li>
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

                <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="management/apartment/list">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </button>
                <button type="button" class="btn btn-primary" id="saveMyFirm">
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
    if ($pageTitle === 'Yeni Daire Ekle') {
        $text = "Yeni Daire tanımlayabilirsiniz.";
    } else {
        $text = "Seçtiğiniz Bloğa ait Daire bilgilerini güncelleyebilirsiniz.";
    }
    require_once 'pages/components/alert.php'
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <form action='' id='apartmentForm'>
                            <div class="card-body custom-card-action p-0">
                                <div class="card-body apartment-info">
                                    <div class="row mb-4 align-items-center">
                                        <!--********** HIDDEN ROW************** -->
                                        <div class='row d-none'>
                                            <div class='col-md-4'>
                                                <input type='text' name='id' class='form-control'
                                                    value="<?php echo $incexp->id ?? 0 ?>">
                                            </div>
                                            <div class='col-md-4'>
                                                <input type='text' name='action' value='saveApartmentType' class='form-control'>
                                            </div>
                                        </div>
                                        <!--********** HIDDEN ROW************** -->
                                        <?php
                                            require_once 'pages/management/apartment/content/ApartmentInformation.php';
                                        ?>
                                    </div>
                                </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
