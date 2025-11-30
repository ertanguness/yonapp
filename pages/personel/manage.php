<?php

use App\Helper\Helper;
use App\Helper\Security;
use Model\PersonelModel;

$PersonelModel = new PersonelModel();

/* id=> route'tan geliyor,
 * encrypt true olduğunda find fonksiyonu çözer
 */
$id = $id ?? 0;
$personel = $PersonelModel->find($id, true);
$person_id = Security::decrypt($id) ?? 0;

?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Personel Yönetimi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Personel Yönetimi</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Geri</span>
                </a>
            </div>
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <a href="/personel-listesi" type="button" class="btn btn-outline-secondary me-2">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </a>
                <button type="button" class="btn btn-primary" id="savePerson">
                    <i class="feather-save me-2"></i>
                    Kaydet
                </button>
            </div>
        </div>
    </div>
</div>
<div class="bg-white py-3 border-bottom rounded-0 p-md-0 mb-0 ">
    <!-- Nav tabs -->
    <ul class="nav nav-tabs flex-wrap w-100 text-center customers-nav-tabs"
        id="myTab" role="tablist">
        <li class="nav-item flex-fill border-top" role="presentation">
            <a href="javascript:void(0);" class="nav-link active"
                data-bs-toggle="tab" data-bs-target="#personsInfoTab"
                role="tab">Personel Bilgileri</a>
        </li>
        <li class="nav-item flex-fill border-top" role="presentation">
            <a href="javascript:void(0);" class="nav-link"
                data-bs-toggle="tab" data-bs-target="#taskManagementTab"
                role="tab">Görev Yönetimi</a>
        </li>
        <li class="nav-item flex-fill border-top" role="presentation">
            <a href="javascript:void(0);" class="nav-link"
                data-bs-toggle="tab" data-bs-target="#leaveTrackingTab"
                role="tab">İzin Takip Yönetimi</a>
        </li>

        <li class="nav-item flex-fill border-top" role="presentation">
            <a href="javascript:void(0);" class="nav-link"
                data-bs-toggle="tab" data-bs-target="#paymentsTab"
                role="tab">Ödemeler</a>
        </li>
    </ul>
</div>

<div class="main-content">
    <div class="col-lg-12">
        <div class="card stretch stretch-full">
            <div class="card-body task-header d-lg-flex align-items-center justify-content-between">

                <div class="hstack gap-3">
                    <div class="avatar-image">
                        <img src="/assets/images/avatar/1.png" alt="" class="img-fluid">
                    </div>
                    <div>
                        <a href="javascript:void(0);"><?php echo $personel->adi_soyadi ?? '' ?></a>
                        <div class="fs-11 text-muted"><?php echo $personel->personel_tipi ?? '' ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="container-xl">
                <div class="row row-deck row-cards">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-body custom-card-action p-0">
                                <div class="card-body persons-info">
                                    <div class="row align-items-center">


                                        <div class="tab-content">
                                            <!-- Personel Bilgileri -->
                                            <div class="tab-pane fade show active" id="personsInfoTab" role="tabpanel">
                                                <?php
                                                require_once 'pages/personel/content/PersonsInfoTab.php';

                                                ?>
                                            </div>

                                            <!-- Görev Yönetimi -->
                                            <div class="tab-pane fade" id="taskManagementTab" role="tabpanel">
                                                <?php
                                                if ($person_id != 0) {
                                                    require_once 'pages/personel/content/TaskManagementTab.php';
                                                } else {
                                                    include 'pages/personel/content/AlertTab.php';
                                                }
                                                ?>
                                            </div>

                                            <!-- İzin Takip Yönetimi -->
                                            <div class="tab-pane fade" id="leaveTrackingTab" role="tabpanel">
                                                <?php
                                                if ($person_id != 0) {
                                                    require_once 'pages/personel/content/LeaveTrackingTab.php';
                                                } else {
                                                    include 'pages/personel/content/AlertTab.php';
                                                }
                                                ?>
                                            </div>


                                            <!-- Ödemeler -->
                                            <div class="tab-pane fade" id="paymentsTab" role="tabpanel">
                                                <?php
                                                if ($person_id != 0) {
                                                    require_once 'pages/personel/content/PaymentsTab.php';
                                                } else {
                                                    include 'pages/personel/content/AlertTab.php';
                                                }
                                                ?>
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
    </div>
</div>
<script>
    window.personId = <?php echo isset($person_id) ? (int)$person_id : 0; ?>;
   
</script>
