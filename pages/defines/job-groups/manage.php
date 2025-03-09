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

$pageTitle = $id > 0 ? "İş Grubu Güncelleme" : "Yeni İş Grubu";

?>
<div class="container-xl">
    <?php
    $title = $pageTitle;
    if ($pageTitle === 'Yeni Gelir-Gider Türü Tanımlama') {
        $text = "Yeni İş Grubu tanımlayabilirsiniz.";
    } else {
        $text = "Seçtiğiniz İş Grubunu güncelleyebilirsiniz.";
    }
    require_once 'pages/components/alert.php'
    ?>
    <div class="row row-deck row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="fw-bold mb-0 me-4">
                        <span class="d-block mb-2"><?php echo $pageTitle;   ?></span>
                        <span class="fs-12 fw-normal text-muted text-truncate-1-line">İş Grubu tanımlayabilir çalışanlarınızın takibini kolaylıkla yapabilirsiniz.</span>
                    </h5>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="defines/job-groups/list">
                            <i class="feather-arrow-left me-2"></i>
                            Listeye Dön
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="saveJobGroups">
                            <i class="feather-save  me-2"></i>
                            Kaydet
                        </button>
                    </div>
                </div>
                <!-- Page body -->
                <div class="main-content">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card border-top-0"></div>
                            <!-- **************FORM**************** -->
                            <form action="" id="jobGroupsForm">
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
                                <div class="row mb-4 align-items-center">

                                    <div class="col-lg-2">
                                        <label for="incexp_name" class="fw-semibold">İş Grubu Adı: </label>
                                    </div>
                                    <div class="col-lg-10">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="feather-users"></i></div>
                                            <input type="text" class="form-control" id="job_group_name" name="job_group_name " value="<?php echo $jobGroups->group_name ?? '' ?>">
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
                                            <textarea class="form-control" id="description" cols="30" rows="3" value="<?php echo $jobGroups->description ?? '' ?>"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <!-- **************FORM**************** -->

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>