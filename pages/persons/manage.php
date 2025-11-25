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
                <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="persons/list">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </button>
                <button type="button" class="btn btn-primary" id="savepersons">
                    <i class="feather-save me-2"></i>
                    Kaydet
                </button>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <?php
   /* $title = $pageTitle;
    if ($pageTitle === 'Yeni Personel Ekle') {
        $text = "Yeni Personel Ekleme sayfasındasınız. Bu sayfada yeni bir personel ekleyebilirsiniz.";
    } else {
        $text = "Personel Güncelleme sayfasındasınız. Bu sayfada personel bilgilerini güncelleyebilirsiniz.";
    }
    require_once 'pages/components/alert.php'; */
    ?>

    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <form action='' id='personsForm'>
                            <div class="card-body custom-card-action p-0">
                                <div class="card-body persons-info">
                                    <div class="row mb-4 align-items-center">
                                        <!--********** HIDDEN ROW************** -->
                                        <div class='row d-none'>
                                            <div class='col-md-4'>
                                                <input type='text' name='id' class='form-control'
                                                    value="<?php echo $incexp->id ?? 0 ?>">
                                            </div>
                                            <div class='col-md-4'>
                                                <input type='text' name='action' value='savepersons' class='form-control'>
                                            </div>
                                        </div>
                                        <!--********** HIDDEN ROW************** -->

                                        <div class="card-header p-0">
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
                                                        data-bs-toggle="tab" data-bs-target="#salaryTrackingTab"
                                                        role="tab">Maaş Bilgileri</a>
                                                </li>
                                                <li class="nav-item flex-fill border-top" role="presentation">
                                                    <a href="javascript:void(0);" class="nav-link"
                                                        data-bs-toggle="tab" data-bs-target="#paymentsTab"
                                                        role="tab">Ödemeler</a>
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="tab-content">
                                            <!-- Personel Bilgileri -->
                                            <div class="tab-pane fade show active" id="personsInfoTab" role="tabpanel">
                                                <?php
                                                require_once 'pages/persons/content/PersonsInfoTab.php';
                                                ?>
                                            </div>

                                            <!-- Görev Yönetimi -->
                                            <div class="tab-pane fade" id="taskManagementTab" role="tabpanel">
                                                <?php
                                                require_once 'pages/persons/content/TaskManagementTab.php';
                                                ?>
                                            </div>

                                            <!-- İzin Takip Yönetimi -->
                                            <div class="tab-pane fade" id="leaveTrackingTab" role="tabpanel">
                                                <?php
                                                require_once 'pages/persons/content/LeaveTrackingTab.php';
                                                ?>
                                            </div>

                                            <!-- Maaş ve Ödeme Takibi -->
                                            <div class="tab-pane fade" id="salaryTrackingTab" role="tabpanel">
                                                <?php
                                                require_once 'pages/persons/content/SalaryTrackingTab.php';
                                                ?>
                                            </div>

                                            <!-- Ödemeler -->
                                            <div class="tab-pane fade" id="paymentsTab" role="tabpanel">
                                                <?php
                                                require_once 'pages/persons/content/PaymentsTab.php';
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
