<?php

use Model\SitelerModel;
use App\Helper\Security;
use App\Helper\Cities;

$Sites = new SitelerModel();
$cities = new Cities();

$enc_id = $id ?? 0;

$id = Security::decrypt($id ?? 0);

$site = $Sites->find($id  ?? null);
$siteYeniID= $Sites->siteSonID() ?? 0;

?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Yönetim</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Siteler</li>
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

                <a href="/siteler" class="btn btn-outline-secondary route-link me-2">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
</a>
                <button type="button" class="btn btn-primary" id="save_sites">
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

    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <form id="sitesForm" method="POST">
                            <input type="hidden" name="sites_id" id="sites_id" value="<?php echo $enc_id ?? 0; ?>">
                            <div class="row">
                                <div class="container-xl">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Site Bilgileri Sayfası</h5>
                                        </div>
                                        <div class="card-body aidat-info">

                                            <?php
                                            require_once 'pages/management/sites/content/SitesInformationPage.php';
                                            ?>

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