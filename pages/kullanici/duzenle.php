<?php

use Model\UserModel;
use App\Helper\Security;
use App\Helper\UserHelper;


$User = new UserModel();
$UserHelper = new UserHelper();

//Sayfa başlarında eklenecek alanlar
//$perm->checkAuthorize("user_add_update");
$id = isset($_GET["id"]) ? Security::decrypt($_GET['id']) : 0;
$new_id = isset($_GET["id"]) ? $_GET['id'] : 0;

//Eğer url'den id yazılmışsa veya id boş ise projeler sayfasına gider
if ($id == null && isset($_GET['id'])) {
    header("Location: /kullanicilar");
    exit;
}
$user = $User->find($id);
//$Auths->checkFirm();
?>



<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10"> Kullanıcılar </h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Yeni Kullanıcı Ekle</li>
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

                <a href="/kullanici-listesi" type="button" class="btn btn-outline-secondary route-link me-2">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </a>
                <button type="button" class="btn btn-primary" id="userSaveBtn">
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
<div class="bg-white py-3 border-bottom rounded-0 p-md-0 mb-0">
    <div class="d-md-none d-flex align-items-center justify-content-between">
        <a href="javascript:void(0)" class="page-content-left-open-toggle">
            <i class="feather-align-left fs-20"></i>
        </a>
        <a href="javascript:void(0)" class="page-content-right-open-toggle">
            <i class="feather-align-right fs-20"></i>
        </a>
    </div>
    <div class="d-flex align-items-center justify-content-between">
        <div class="nav-tabs-wrapper page-content-left-sidebar-wrapper">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-content-left-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Back</span>
                </a>
            </div>
            <ul class="nav nav-tabs nav-tabs-custom-style" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#proposalTab">Genel Bilgiler</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tasksTab">Giriş Kayıtları</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#notesTab">Aktiviteler</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#commentTab">Comments</button>
                </li>
            </ul>
        </div>
        <div class="page-content-right-sidebar-wrapper">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-content-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Back</span>
                </a>
            </div>
            <div class="proposal-action-btn">
                <div class="d-md-none d-lg-flex">
                    <a href="javascript:void(0);" class="action-btn" data-bs-toggle="tooltip" title=""
                        data-bs-original-title="Views Trackign">
                        <i class="feather-eye"></i>
                    </a>
                </div>
                <div class="d-md-none d-lg-flex">
                    <a href="javascript:void(0);" class="action-btn" data-bs-toggle="tooltip" title=""
                        data-bs-original-title="Send to Email">
                        <i class="feather-mail"></i>
                    </a>
                </div>
                <div class="d-md-none d-lg-flex">
                    <a href="proposal-edit.html" class="action-btn" data-bs-toggle="tooltip" title=""
                        data-bs-original-title="Edit Proposal">
                        <i class="feather-edit"></i>
                    </a>
                </div>
                <div class="dropdown">
                    <a href="javascript:void(0);" class="action-btn dropdown-toggle c-pointer" data-bs-toggle="dropdown"
                        data-bs-offset="0, 2" data-bs-auto-close="outside">Convert</a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-check-square me-3"></i>
                            <span>Draft</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-dollar-sign me-3"></i>
                            <span>Invoice</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-cast me-3"></i>
                            <span>Estimate</span>
                        </a>
                    </div>
                </div>
                <div class="dropdown">
                    <a class="action-btn dropdown-toggle c-pointer" data-bs-toggle="dropdown" data-bs-offset="0, 2"
                        data-bs-auto-close="outside">More</a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-eye me-3"></i>
                            <span>View</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-copy me-3"></i>
                            <span>Copy</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-link me-3"></i>
                            <span>Attachment</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-book-open me-3"></i>
                            <span>Make as Open</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-send me-3"></i>
                            <span>Make as Sent</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-edit me-3"></i>
                            <span>Make as Draft</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-crop me-3"></i>
                            <span>Make as Revised</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-check-circle me-3"></i>
                            <span>Make as Accepted</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-plus me-3"></i>
                            <span>Create New</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-trash-2 me-3"></i>
                            <span>Delete Proposal</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>




<div class="main-content">
    <div class="tab-content">
        <div class="tab-pane fade active show" id="proposalTab">
            <?php

            $title = 'Yeni Kullanıcı Ekleme';
            $text = "Gerekli bilgileri girerek yeni kullanıcı ekleyebilir ve yetkilendirebilirsiniz.";

            require_once 'pages/components/alert.php'
            ?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card stretch stretch-full">
                        <div class="card-body">
                            <form action="" id="userForm">
                                <?php require_once "content/0-home.php" ?>
                            </form>

                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="tab-pane fade" id="tasksTab">

        </div>
        <div class="tab-pane fade" id="notesTab">

        </div>
        <div class="tab-pane fade" id="commentTab">

        </div>
    </div>
</div>
<script>
   

    // Kullanım örneği:
    setupShortcut('s', function() {
        // Burada yapılacak işlemi tanımlıyoruz
        $('#userSaveBtn').trigger('click');
    });
</script>