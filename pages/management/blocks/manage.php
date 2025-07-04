<?php
use Model\BloklarModel;
use App\Helper\Security;
use Model\SitelerModel;

$Siteler = new SitelerModel();
$Bloklar = new BloklarModel();

$id = isset($_GET['id']) ? Security::decrypt($_GET['id']) : 0;
$blok = $Bloklar->find($id  ?? null);

$site = $Siteler->SiteBilgileri($_SESSION['site_id'] ?? null);

?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Tanımlamalar</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Bloklar</li>
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

                <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="management/blocks/list">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </button>
                <button type="button" class="btn btn-primary" id="save_blocks">
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
    if ($pageTitle === 'Yeni Blok Ekle') {
        $text = "Yeni Blok tanımlayabilirsiniz.";
    } else {
        $text = "Seçtiğiniz Siteye ait blok bilgilerini güncelleyebilirsiniz.";
    }
    require_once 'pages/components/alert.php'
    */
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <form action='' id='blocksForm'>
                            <div class="card-body custom-card-action p-0">
                                <div class="card-body personal-info">
                                    <div class="row mb-4 align-items-center">
                                    <input type="hidden" name="blok_id" id="blok_id" value="<?php echo $_GET['id'] ?? 0; ?>">
                                        <?php
                                            if (!empty($id) && $id != 0) {
                                                require_once 'pages/management/blocks/content/BlokDuzenle.php';
                                            } else {
                                                require_once 'pages/management/blocks/content/BlocksNumberPage.php';
                                            }

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
