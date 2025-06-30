<?php

require_once "Model/JobGroupsModel.php";
$JobGroups = new JobGroupsModel();

use App\Helper\Security;

//Sayfa başlarında eklenecek alanlar
$perm->checkAuthorize("job_groups_add_update");
$id = isset($_GET["id"]) ? Security::decrypt($_GET['id']) : 0;
$new_id = isset($_GET["id"]) ? $_GET['id'] : 0;

//Eğer url'den id yazılmışsa veya id boş ise projeler sayfasına gider
if ($id == null && isset($_GET['id'])) {
    header("Location: /index.php?p=defines/job-groups/list");
    exit;
}

$jobGroups = $JobGroups->find($id);

$pageTitle = $id > 0 ? "İş Grubu Güncelleme" : "Yeni İş Grubu Tanımlama";

?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Tanımlamalar</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home">Ana Sayfa</a></li>
            <li class="breadcrumb-item">İş Grubu Tanımlama</li>
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
                <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="defines/job-groups/list">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </button>
                <button type="button" class="btn btn-primary" id="saveJobGroups">
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
    if ($pageTitle === 'Yeni İş Grubu Tanımlama') {
        $text = "Yeni İş Grubu tanımlayabilirsiniz.";
    } else {
        $text = "Seçtiğiniz İş Grubunu güncelleyebilirsiniz.";
    }
    require_once 'pages/components/alert.php'
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <form action="" id="jobGroupsForm">
                            <div class="card-body custom-card-action p-0">
                                <div class="card-body personal-info">
                                    <div class="row mb-4 align-items-center">
                                        <!--********** HIDDEN ROW************** -->
                                        <div class="row d-none">
                                            <div class="col-md-4">
                                                <input type="text" name="id" id="id" class="form-control"
                                                    value="<?php echo $id ?? '' ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" name="action" value="saveJobGroups" class="form-control">
                                            </div>
                                        </div>
                                        <!--********** HIDDEN ROW************** -->
                                        <div class="col-lg-2">
                                            <label for="incexp_name" class="fw-semibold">İş Grubu Adı: </label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-users"></i></div>
                                                <input type="text" class="form-control" id="job_group_name" name="job_group_name " value="<?php echo $jobGroups->group_name ?? '' ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-1">
                                            <label for="description" class="fw-semibold">Açıklama: </label>
                                        </div>
                                        <div class="col-lg-5">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-type"></i></div>
                                                <textarea class="form-control" id="description" cols="30" rows="3" value="<?php echo $jobGroups->description ?? '' ?>"></textarea>
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