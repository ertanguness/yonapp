<?php
use App\Helper\Security;

$id = isset($_GET['id']) ? Security::decrypt($_GET['id']) : 0;

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
                <button type="button" class="btn btn-primary" id="save_apartment">
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
    /*
    $title = $pageTitle;
    if ($pageTitle === 'Yeni Daire Ekle') {
        $text = "Yeni Daire tanımlayabilirsiniz.";
    } else {
        $text = "Seçtiğiniz Bloğa ait Daire bilgilerini güncelleyebilirsiniz.";
    }
    require_once 'pages/components/alert.php'
    */
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
                                    <input type="hidden" name="apartment_id" id="apartment_id" value="<?php echo $_GET['id'] ?? 0; ?>">

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
