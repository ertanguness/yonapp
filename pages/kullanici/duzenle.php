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
if($id == null && isset($_GET['id'])) {
    header("Location: /index.php?p=kullanici/list");
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
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
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

                <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="kullanici/list">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </button>
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
                            <?php require_once "content/0-home.php" ?>
                          
                        </div>
                    </div>
                </div>
          
            </div>
        </div>
        <div class="tab-pane fade" id="tasksTab">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card stretch stretch-full">
                        <div class="card-body task-header d-lg-flex align-items-center justify-content-between">
                            <div class="mb-4 mb-lg-0">
                                <h4 class="mb-3 fw-bold text-truncate-1-line">Duralux || CRM Applications &amp; Admin
                                    Dashboar</h4>
                                <span class="badge bg-soft-primary text-primary me-2">In Prograss</span>
                                <span class="badge bg-soft-warning text-warning">Recurring Task </span>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="javascript:void(0);" class="btn btn-icon" data-bs-toggle="tooltip" title=""
                                    data-bs-original-title="Make as Complete">
                                    <i class="feather-check-circle"></i>
                                </a>
                                <a href="javascript:void(0);" class="btn btn-icon" data-bs-toggle="tooltip" title=""
                                    data-bs-original-title="Timesheets">
                                    <i class="feather-calendar"></i>
                                </a>
                                <a href="javascript:void(0);" class="btn btn-icon" data-bs-toggle="tooltip" title=""
                                    data-bs-original-title="Statistics">
                                    <i class="feather-bar-chart-2"></i>
                                </a>
                                <a href="javascript:void(0);" class="btn btn-success" data-bs-toggle="tooltip" title=""
                                    data-bs-original-title="Timesheets">
                                    <i class="feather-clock me-2"></i>
                                    <span>Start Timer</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-8 col-xl-6">
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title">Description</h5>
                            <a href="javascript:void(0);" class="avatar-text avatar-md" data-bs-toggle="tooltip"
                                title="" data-bs-original-title="Update Description">
                                <i class="feather-edit"></i>
                            </a>
                        </div>
                        <div class="card-body">
                            <p>Web Design Company is still a broad term and refers to any company that specializes in
                                designing and creating websites. These companies typically offer a range of services
                                including website design, development, and maintenance. They may also offer other
                                related services such as search engine optimization (SEO), e-commerce solutions, and
                                website hosting.</p>
                            <ul class="list-unstyled text-muted mb-0">
                                <li class="d-flex align-items-start mb-3">
                                    <span class="avatar-text avatar-sm bg-soft-success text-success me-3">
                                        <i class="feather-check fs-10"></i>
                                    </span>
                                    <span>Participated in the initial wave of developers learning and implementing the
                                        React.JS library. </span>
                                </li>
                                <li class="d-flex align-items-start mb-3">
                                    <span class="avatar-text avatar-sm bg-soft-success text-success me-3">
                                        <i class="feather-check fs-10"></i>
                                    </span>
                                    <span>Tested, debugged, and shipped 10s of 1000s of lines of code to various
                                        development teams. This lead to 100% bug-free deployment. </span>
                                </li>
                                <li class="d-flex align-items-start mb-3">
                                    <span class="avatar-text avatar-sm bg-soft-success text-success me-3">
                                        <i class="feather-check fs-10"></i>
                                    </span>
                                    <span>Introduced Kanban Board style ticketing system to promote highly efficient
                                        asynchronous and synchronous work, increasing efficiency by 12%. </span>
                                </li>
                                <li class="d-flex align-items-start mb-3">
                                    <span class="avatar-text avatar-sm bg-soft-success text-success me-3">
                                        <i class="feather-check fs-10"></i>
                                    </span>
                                    <span>Utilized HTML, CSS, and JavaScript to create 100+ responsive landing pages for
                                        both company and client. </span>
                                </li>
                                <li class="d-flex align-items-start mb-3">
                                    <span class="avatar-text avatar-sm bg-soft-success text-success me-3">
                                        <i class="feather-check fs-10"></i>
                                    </span>
                                    <span>Tested, debugged, and shipped 10s of 1000s of lines of code to various
                                        development teams. This lead to 100% bug-free deployment. </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title">Checklist</h5>
                            <div class="dropdown">
                                <a href="javascript:void(0);" class="avatar-text avatar-md" data-bs-toggle="dropdown"
                                    data-bs-offset="25, 25">
                                    <div data-bs-toggle="tooltip" title="" data-bs-original-title="Options">
                                        <i class="feather-more-vertical"></i>
                                    </div>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a href="javascript:void(0);" class="dropdown-item"><i
                                            class="feather-at-sign"></i>New</a>
                                    <a href="javascript:void(0);" class="dropdown-item"><i
                                            class="feather-calendar"></i>Event</a>
                                    <a href="javascript:void(0);" class="dropdown-item"><i
                                            class="feather-bell"></i>Snoozed</a>
                                    <a href="javascript:void(0);" class="dropdown-item"><i
                                            class="feather-trash-2"></i>Deleted</a>
                                    <div class="dropdown-divider"></div>
                                    <a href="javascript:void(0);" class="dropdown-item"><i
                                            class="feather-settings"></i>Settings</a>
                                    <a href="javascript:void(0);" class="dropdown-item"><i
                                            class="feather-life-buoy"></i>Tips &amp; Tricks</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body task-checklist">
                            <ul class="list-unstyled" id="checklist">
                                <li class="checked">Tested, debugged, and shipped 10s of 1000s of lines of code to
                                    various development teams.<span class="close">×</span></li>
                                <li>Introduced Kanban Board style ticketing system to promote highly.<span
                                        class="close">×</span></li>
                                <li>Utilized HTML, CSS, and JavaScript to create 100+ responsive landing pages for both
                                    company and client.<span class="close">×</span></li>
                                <li class="checked">Rewrote HTML to meet industry and company standards for SEO and
                                    Accessibility.<span class="close">×</span></li>
                                <li>Led bi-weekly stand-up to ensure team worked effectively.<span
                                        class="close">×</span></li>
                                <li>Worked with Quality Assurance to get new pages/products tested.<span
                                        class="close">×</span></li>
                            </ul>
                            <div class="input-group mt-3">
                                <input id="checklistInput" type="text" class="form-control" placeholder="Title...">
                                <a href="javascript:void(0)" class="input-group-text addCheckList"
                                    onclick="newElement()">
                                    <i class="feather-plus me-2"></i>
                                    <span>Add Checklist</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title">Topics</h5>
                            <a href="javascript:void(0);" class="btn btn-md btn-light-brand">
                                <i class="feather-plus me-2"></i>
                                <span>Add New Topic</span>
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xxl-6">
                                    <div class="card border border-gray-2 rounded-2 my-2 overflow-hidden">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="wd-50 ht-50 bg-gray-100 me-3 d-flex align-items-center justify-content-center">
                                                    <i class="feather-file-text"></i>
                                                </div>
                                                <a href="javascript:void(0);">How to upload data to the system?</a>
                                            </div>
                                            <a href="javascript:void(0);" class="avatar-text avatar-sm me-3">
                                                <i class="feather-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card border border-gray-2 rounded-2 my-2 overflow-hidden">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="wd-50 ht-50 bg-gray-100 me-3 d-flex align-items-center justify-content-center">
                                                    <i class="feather-file-text"></i>
                                                </div>
                                                <a href="javascript:void(0);">How to draw a land plot on a map?</a>
                                            </div>
                                            <a href="javascript:void(0);" class="avatar-text avatar-sm me-3">
                                                <i class="feather-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card border border-gray-2 rounded-2 my-2 overflow-hidden">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="wd-50 ht-50 bg-gray-100 me-3 d-flex align-items-center justify-content-center">
                                                    <i class="feather-file-text"></i>
                                                </div>
                                                <a href="javascript:void(0);">How to to view expire services?</a>
                                            </div>
                                            <a href="javascript:void(0);" class="avatar-text avatar-sm me-3">
                                                <i class="feather-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card border border-gray-2 rounded-2 my-2 overflow-hidden">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="wd-50 ht-50 bg-gray-100 me-3 d-flex align-items-center justify-content-center">
                                                    <i class="feather-file-text"></i>
                                                </div>
                                                <a href="javascript:void(0);">How to integrate new web applications?</a>
                                            </div>
                                            <a href="javascript:void(0);" class="avatar-text avatar-sm me-3">
                                                <i class="feather-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card border border-gray-2 rounded-2 my-2 overflow-hidden">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="wd-50 ht-50 bg-gray-100 me-3 d-flex align-items-center justify-content-center">
                                                    <i class="feather-file-text"></i>
                                                </div>
                                                <a href="javascript:void(0);">How do I set the geometry of an
                                                    object?</a>
                                            </div>
                                            <a href="javascript:void(0);" class="avatar-text avatar-sm me-3">
                                                <i class="feather-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-6">
                                    <div class="card border border-gray-2 rounded-2 my-2 overflow-hidden">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="wd-50 ht-50 bg-gray-100 me-3 d-flex align-items-center justify-content-center">
                                                    <i class="feather-file-text"></i>
                                                </div>
                                                <a href="javascript:void(0);">How to upload data to the system?</a>
                                            </div>
                                            <a href="javascript:void(0);" class="avatar-text avatar-sm me-3">
                                                <i class="feather-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card border border-gray-2 rounded-2 my-2 overflow-hidden">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="wd-50 ht-50 bg-gray-100 me-3 d-flex align-items-center justify-content-center">
                                                    <i class="feather-file-text"></i>
                                                </div>
                                                <a href="javascript:void(0);">How to draw a land plot on a map?</a>
                                            </div>
                                            <a href="javascript:void(0);" class="avatar-text avatar-sm me-3">
                                                <i class="feather-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card border border-gray-2 rounded-2 my-2 overflow-hidden">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="wd-50 ht-50 bg-gray-100 me-3 d-flex align-items-center justify-content-center">
                                                    <i class="feather-file-text"></i>
                                                </div>
                                                <a href="javascript:void(0);">How to to view expire services?</a>
                                            </div>
                                            <a href="javascript:void(0);" class="avatar-text avatar-sm me-3">
                                                <i class="feather-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card border border-gray-2 rounded-2 my-2 overflow-hidden">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="wd-50 ht-50 bg-gray-100 me-3 d-flex align-items-center justify-content-center">
                                                    <i class="feather-file-text"></i>
                                                </div>
                                                <a href="javascript:void(0);">How to integrate new web applications?</a>
                                            </div>
                                            <a href="javascript:void(0);" class="avatar-text avatar-sm me-3">
                                                <i class="feather-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card border border-gray-2 rounded-2 my-2 overflow-hidden">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="wd-50 ht-50 bg-gray-100 me-3 d-flex align-items-center justify-content-center">
                                                    <i class="feather-file-text"></i>
                                                </div>
                                                <a href="javascript:void(0);">How do I set the geometry of an
                                                    object?</a>
                                            </div>
                                            <a href="javascript:void(0);" class="avatar-text avatar-sm me-3">
                                                <i class="feather-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-xl-6">
                    <div class="card stretch stretch-full">
                        <div class="card-body task-info">
                            <div class="mb-4">
                                <h5 class="card-title mb-1">Task Info</h5>
                                <span class="fs-12 fw-normal text-muted d-block">Created at 2023-02-12 08:47:47</span>
                            </div>
                            <div class="task-info-list">
                                <div class="row align-items-center mb-3 task-list-row">
                                    <div class="col-6">
                                        <i class="feather-star me-2"></i>
                                        <span class="fw-semibold">Status:</span>
                                    </div>
                                    <div class="col-6 d-flex">
                                        <span class="border-bottom border-bottom-dashed border-gray-5">In
                                            Progress</span>
                                        <div class="dropdown ms-2">
                                            <a href="javascript:void(0);" class="avatar-text avatar-sm"
                                                data-bs-toggle="dropdown">
                                                <i class="feather-edit-3"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a href="javascript:void(0);" class="dropdown-item">
                                                    <span class="wd-7 ht-7 bg-primary rounded-circle me-3"></span>
                                                    <span>In Progress</span>
                                                </a>
                                                <a href="javascript:void(0);" class="dropdown-item">
                                                    <span class="wd-7 ht-7 bg-secondary rounded-circle me-3"></span>
                                                    <span>Pending</span>
                                                </a>
                                                <a href="javascript:void(0);" class="dropdown-item">
                                                    <span class="wd-7 ht-7 bg-success rounded-circle me-3"></span>
                                                    <span>Completed</span>
                                                </a>
                                                <a href="javascript:void(0);" class="dropdown-item">
                                                    <span class="wd-7 ht-7 bg-danger rounded-circle me-3"></span>
                                                    <span>Rejected</span>
                                                </a>
                                                <a href="javascript:void(0);" class="dropdown-item">
                                                    <span class="wd-7 ht-7 bg-warning rounded-circle me-3"></span>
                                                    <span>Upcoming</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row align-items-center mb-3 task-list-row">
                                    <div class="col-6">
                                        <i class="feather-airplay me-2"></i>
                                        <span class="fw-semibold">Priority:</span>
                                    </div>
                                    <div class="col-6 d-flex">
                                        <span class="border-bottom border-bottom-dashed border-gray-5">Medium</span>
                                        <div class="dropdown ms-2">
                                            <a href="javascript:void(0);" class="avatar-text avatar-sm"
                                                data-bs-toggle="dropdown">
                                                <i class="feather-edit-3"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a href="javascript:void(0);" class="dropdown-item">
                                                    <span class="wd-7 ht-7 bg-primary rounded-circle me-3"></span>
                                                    <span>Low</span>
                                                </a>
                                                <a href="javascript:void(0);" class="dropdown-item">
                                                    <span class="wd-7 ht-7 bg-secondary rounded-circle me-3"></span>
                                                    <span>Normal</span>
                                                </a>
                                                <a href="javascript:void(0);" class="dropdown-item">
                                                    <span class="wd-7 ht-7 bg-success rounded-circle me-3"></span>
                                                    <span>Medium</span>
                                                </a>
                                                <a href="javascript:void(0);" class="dropdown-item">
                                                    <span class="wd-7 ht-7 bg-warning rounded-circle me-3"></span>
                                                    <span>High</span>
                                                </a>
                                                <a href="javascript:void(0);" class="dropdown-item">
                                                    <span class="wd-7 ht-7 bg-danger rounded-circle me-3"></span>
                                                    <span>Urgent</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row align-items-center mb-3 task-list-row">
                                    <div class="col-6">
                                        <i class="feather-activity me-2"></i>
                                        <span class="fw-semibold">Start Date:</span>
                                    </div>
                                    <div class="col-6 d-flex">
                                        <span class="border-bottom border-bottom-dashed border-gray-5">26 May,
                                            2023</span>
                                        <div class="ms-2">
                                            <a href="javascript:void(0);" class="avatar-text avatar-sm">
                                                <i class="feather-edit-3"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="row align-items-center mb-3 task-list-row">
                                    <div class="col-6">
                                        <i class="feather-calendar me-2"></i>
                                        <span class="fw-semibold">Due Date:</span>
                                    </div>
                                    <div class="col-6 d-flex">
                                        <span class="border-bottom border-bottom-dashed border-gray-5">30 May,
                                            2023</span>
                                        <div class="ms-2">
                                            <a href="javascript:void(0);" class="avatar-text avatar-sm">
                                                <i class="feather-edit-3"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="row align-items-center mb-3 task-list-row">
                                    <div class="col-6">
                                        <i class="feather-clock me-2"></i>
                                        <span class="fw-semibold">Hourly Rate:</span>
                                    </div>
                                    <div class="col-6 d-flex">
                                        <span class="border-bottom border-bottom-dashed border-gray-5">$12.00</span>
                                        <div class="ms-2">
                                            <a href="javascript:void(0);" class="avatar-text avatar-sm">
                                                <i class="feather-edit-3"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="row align-items-center mb-3 task-list-row">
                                    <div class="col-6">
                                        <i class="feather-clipboard me-2"></i>
                                        <span class="fw-semibold">Billable:</span>
                                    </div>
                                    <div class="col-6 d-flex">
                                        <span class="border-bottom border-bottom-dashed border-gray-5">Billable</span>
                                        <div class="ms-2">
                                            <a href="javascript:void(0);" class="avatar-text avatar-sm">
                                                <i class="feather-edit-3"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="row align-items-center mb-3 task-list-row">
                                    <div class="col-6">
                                        <i class="feather-dollar-sign me-2"></i>
                                        <span class="fw-semibold">Amount:</span>
                                    </div>
                                    <div class="col-6 d-flex">
                                        <span class="border-bottom border-bottom-dashed border-gray-5">$250.00</span>
                                        <div class="ms-2">
                                            <a href="javascript:void(0);" class="avatar-text avatar-sm">
                                                <i class="feather-edit-3"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="row align-items-center mb-3 task-list-row">
                                    <div class="col-6">
                                        <i class="feather-power me-2"></i>
                                        <span class="fw-semibold">Login:</span>
                                    </div>
                                    <div class="col-6 d-flex">
                                        <span class="border-bottom border-bottom-dashed border-gray-5">09:30AM</span>
                                        <div class="ms-2">
                                            <a href="javascript:void(0);" class="avatar-text avatar-sm">
                                                <i class="feather-edit-3"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="row align-items-center">
                                    <div class="col-6">
                                        <i class="feather-bell me-2"></i>
                                        <span class="fw-semibold">Remainders:</span>
                                    </div>
                                    <div class="col-6">
                                        <a href="javascript:void(0);" class="text-primary">Create Remain</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card stretch stretch-full">
                        <div class="card-body task-tags">
                            <div class="mb-4">
                                <h5 class="card-title mb-1">Tags</h5>
                                <span class="fs-12 fw-normal text-muted d-block">Tags label for tasks</span>
                            </div>
                            <select class="form-select form-control select2-hidden-accessible"
                                data-select2-selector="tag" multiple="" data-select2-id="select2-data-1-1nif"
                                tabindex="-1" aria-hidden="true">
                                <option value="primary" data-bg="bg-primary" selected=""
                                    data-select2-id="select2-data-3-i8uo">Team</option>
                                <option value="teal" data-bg="bg-teal">Primary</option>
                                <option value="success" data-bg="bg-success">Updates</option>
                                <option value="warning" data-bg="bg-warning" selected=""
                                    data-select2-id="select2-data-4-b7f9">Personal</option>
                                <option value="danger" data-bg="bg-danger">Promotions</option>
                                <option value="indigo" data-bg="bg-indigo" selected=""
                                    data-select2-id="select2-data-5-hcw2">Custom</option>
                                <option value="success" data-bg="bg-success">Important</option>
                                <option value="dark" data-bg="bg-dark">Tomorrow</option>
                                <option value="info" data-bg="bg-info" selected=""
                                    data-select2-id="select2-data-6-q8js">review</option>
                            </select><span class="select2 select2-container select2-container--bootstrap-5" dir="ltr"
                                data-select2-id="select2-data-2-wmwy" style="width: auto;"><span class="selection"><span
                                        class="select2-selection select2-selection--multiple" role="combobox"
                                        aria-haspopup="true" aria-expanded="false" tabindex="-1" aria-disabled="false">
                                        <ul class="select2-selection__rendered" id="select2-ugsa-container">
                                            <li class="select2-selection__choice" title="Team"
                                                data-select2-id="select2-data-7-d5xe"><button type="button"
                                                    class="select2-selection__choice__remove" tabindex="-1"
                                                    title="Remove item" aria-label="Remove item"
                                                    aria-describedby="select2-ugsa-container-choice-hq8r-primary"><span
                                                        aria-hidden="true">×</span></button><span
                                                    class="select2-selection__choice__display"
                                                    id="select2-ugsa-container-choice-hq8r-primary"><span
                                                        class="hstack gap-2"> <span
                                                            class="wd-7 ht-7 rounded-circle bg-primary"></span>
                                                        Team</span></span></li>
                                            <li class="select2-selection__choice" title="Personal"
                                                data-select2-id="select2-data-8-iip1"><button type="button"
                                                    class="select2-selection__choice__remove" tabindex="-1"
                                                    title="Remove item" aria-label="Remove item"
                                                    aria-describedby="select2-ugsa-container-choice-hecw-warning"><span
                                                        aria-hidden="true">×</span></button><span
                                                    class="select2-selection__choice__display"
                                                    id="select2-ugsa-container-choice-hecw-warning"><span
                                                        class="hstack gap-2"> <span
                                                            class="wd-7 ht-7 rounded-circle bg-warning"></span>
                                                        Personal</span></span></li>
                                            <li class="select2-selection__choice" title="Custom"
                                                data-select2-id="select2-data-9-ua18"><button type="button"
                                                    class="select2-selection__choice__remove" tabindex="-1"
                                                    title="Remove item" aria-label="Remove item"
                                                    aria-describedby="select2-ugsa-container-choice-ixf0-indigo"><span
                                                        aria-hidden="true">×</span></button><span
                                                    class="select2-selection__choice__display"
                                                    id="select2-ugsa-container-choice-ixf0-indigo"><span
                                                        class="hstack gap-2"> <span
                                                            class="wd-7 ht-7 rounded-circle bg-indigo"></span>
                                                        Custom</span></span></li>
                                            <li class="select2-selection__choice" title="review"
                                                data-select2-id="select2-data-10-xl8h"><button type="button"
                                                    class="select2-selection__choice__remove" tabindex="-1"
                                                    title="Remove item" aria-label="Remove item"
                                                    aria-describedby="select2-ugsa-container-choice-19qr-info"><span
                                                        aria-hidden="true">×</span></button><span
                                                    class="select2-selection__choice__display"
                                                    id="select2-ugsa-container-choice-19qr-info"><span
                                                        class="hstack gap-2"> <span
                                                            class="wd-7 ht-7 rounded-circle bg-info"></span>
                                                        review</span></span></li>
                                        </ul><span class="select2-search select2-search--inline"><textarea
                                                class="select2-search__field" type="search" tabindex="0"
                                                autocorrect="off" autocapitalize="none" spellcheck="false"
                                                role="searchbox" aria-autocomplete="list" autocomplete="off"
                                                aria-label="Search" aria-describedby="select2-ugsa-container"
                                                placeholder="" style="width: 0.75em;"></textarea></span>
                                    </span></span><span class="dropdown-wrapper" aria-hidden="true"></span></span>
                        </div>
                    </div>
                    <div class="card stretch stretch-full">
                        <div class="card-body task-assignees">
                            <div class="mb-4">
                                <h5 class="card-title mb-1">Assignees</h5>
                                <span class="fs-12 fw-normal text-muted d-block">Assigne member to this tasks</span>
                            </div>
                            <select class="form-select form-control select2-hidden-accessible"
                                data-select2-selector="user" multiple="" data-select2-id="select2-data-11-kn9i"
                                tabindex="-1" aria-hidden="true">
                                <option value="alex@outlook.com" data-user="1" selected=""
                                    data-select2-id="select2-data-13-v46c">alex@outlook.com</option>
                                <option value="john.deo@outlook.com" data-user="2">john.deo@outlook.com</option>
                                <option value="green.cutte@outlook.com" data-user="3">green.cutte@outlook.com</option>
                                <option value="nancy.elliot@outlook.com" data-user="4" selected=""
                                    data-select2-id="select2-data-14-a8m1">nancy.elliot@outlook.com</option>
                                <option value="mar.audrey@gmail.com" data-user="5">mar.audrey@gmail.com</option>
                                <option value="erna.serpa@outlook.com" data-user="6" selected=""
                                    data-select2-id="select2-data-15-o5la">erna.serpa@outlook.com</option>
                                <option value="alex@outlook.com" data-user="7">alex@outlook.com</option>
                                <option value="john.deo@outlook.com" data-user="8">john.deo@outlook.com</option>
                                <option value="green.cutte@outlook.com" data-user="9" selected=""
                                    data-select2-id="select2-data-16-xyw7">green.cutte@outlook.com</option>
                                <option value="nancy.elliot@outlook.com" data-user="10">nancy.elliot@outlook.com
                                </option>
                                <option value="mar.audrey@gmail.com" data-user="11">mar.audrey@gmail.com</option>
                                <option value="erna.serpa@outlook.com" data-user="12" selected=""
                                    data-select2-id="select2-data-17-o3mu">erna.serpa@outlook.com</option>
                            </select><span class="select2 select2-container select2-container--bootstrap-5" dir="ltr"
                                data-select2-id="select2-data-12-c5fk" style="width: auto;"><span
                                    class="selection"><span class="select2-selection select2-selection--multiple"
                                        role="combobox" aria-haspopup="true" aria-expanded="false" tabindex="-1"
                                        aria-disabled="false">
                                        <ul class="select2-selection__rendered" id="select2-cbg3-container">
                                            <li class="select2-selection__choice" title="alex@outlook.com"
                                                data-select2-id="select2-data-18-535g"><button type="button"
                                                    class="select2-selection__choice__remove" tabindex="-1"
                                                    title="Remove item" aria-label="Remove item"
                                                    aria-describedby="select2-cbg3-container-choice-7ac0-alex@outlook.com"><span
                                                        aria-hidden="true">×</span></button><span
                                                    class="select2-selection__choice__display"
                                                    id="select2-cbg3-container-choice-7ac0-alex@outlook.com"><span
                                                        class="hstack gap-3"> <img src="./../assets/images/avatar/1.png"
                                                            class="avatar-image avatar-sm">
                                                        alex@outlook.com</span></span></li>
                                            <li class="select2-selection__choice" title="nancy.elliot@outlook.com"
                                                data-select2-id="select2-data-19-31jr"><button type="button"
                                                    class="select2-selection__choice__remove" tabindex="-1"
                                                    title="Remove item" aria-label="Remove item"
                                                    aria-describedby="select2-cbg3-container-choice-vwf8-nancy.elliot@outlook.com"><span
                                                        aria-hidden="true">×</span></button><span
                                                    class="select2-selection__choice__display"
                                                    id="select2-cbg3-container-choice-vwf8-nancy.elliot@outlook.com"><span
                                                        class="hstack gap-3"> <img src="./../assets/images/avatar/4.png"
                                                            class="avatar-image avatar-sm">
                                                        nancy.elliot@outlook.com</span></span></li>
                                            <li class="select2-selection__choice" title="erna.serpa@outlook.com"
                                                data-select2-id="select2-data-20-yuon"><button type="button"
                                                    class="select2-selection__choice__remove" tabindex="-1"
                                                    title="Remove item" aria-label="Remove item"
                                                    aria-describedby="select2-cbg3-container-choice-4p72-erna.serpa@outlook.com"><span
                                                        aria-hidden="true">×</span></button><span
                                                    class="select2-selection__choice__display"
                                                    id="select2-cbg3-container-choice-4p72-erna.serpa@outlook.com"><span
                                                        class="hstack gap-3"> <img src="./../assets/images/avatar/6.png"
                                                            class="avatar-image avatar-sm">
                                                        erna.serpa@outlook.com</span></span></li>
                                            <li class="select2-selection__choice" title="green.cutte@outlook.com"
                                                data-select2-id="select2-data-21-6m2l"><button type="button"
                                                    class="select2-selection__choice__remove" tabindex="-1"
                                                    title="Remove item" aria-label="Remove item"
                                                    aria-describedby="select2-cbg3-container-choice-flt0-green.cutte@outlook.com"><span
                                                        aria-hidden="true">×</span></button><span
                                                    class="select2-selection__choice__display"
                                                    id="select2-cbg3-container-choice-flt0-green.cutte@outlook.com"><span
                                                        class="hstack gap-3"> <img src="./../assets/images/avatar/9.png"
                                                            class="avatar-image avatar-sm">
                                                        green.cutte@outlook.com</span></span></li>
                                            <li class="select2-selection__choice" title="erna.serpa@outlook.com"
                                                data-select2-id="select2-data-22-837g"><button type="button"
                                                    class="select2-selection__choice__remove" tabindex="-1"
                                                    title="Remove item" aria-label="Remove item"
                                                    aria-describedby="select2-cbg3-container-choice-287k-erna.serpa@outlook.com"><span
                                                        aria-hidden="true">×</span></button><span
                                                    class="select2-selection__choice__display"
                                                    id="select2-cbg3-container-choice-287k-erna.serpa@outlook.com"><span
                                                        class="hstack gap-3"> <img
                                                            src="./../assets/images/avatar/12.png"
                                                            class="avatar-image avatar-sm">
                                                        erna.serpa@outlook.com</span></span></li>
                                        </ul><span class="select2-search select2-search--inline"><textarea
                                                class="select2-search__field" type="search" tabindex="0"
                                                autocorrect="off" autocapitalize="none" spellcheck="false"
                                                role="searchbox" aria-autocomplete="list" autocomplete="off"
                                                aria-label="Search" aria-describedby="select2-cbg3-container"
                                                placeholder="" style="width: 0.75em;"></textarea></span>
                                    </span></span><span class="dropdown-wrapper" aria-hidden="true"></span></span>
                        </div>
                    </div>
                    <div class="card stretch stretch-full">
                        <div class="card-body task-followers">
                            <div class="mb-4">
                                <h5 class="card-title mb-1">Followers</h5>
                                <span class="fs-12 fw-normal text-muted d-block">5 followers for this task</span>
                            </div>
                            <select class="form-select form-control select2-hidden-accessible"
                                data-select2-selector="user" multiple="" data-select2-id="select2-data-23-k172"
                                tabindex="-1" aria-hidden="true">
                                <option value="alex@outlook.com" data-user="1">alex@outlook.com</option>
                                <option value="john.deo@outlook.com" data-user="2" selected=""
                                    data-select2-id="select2-data-25-7u7x">john.deo@outlook.com</option>
                                <option value="green.cutte@outlook.com" data-user="3">green.cutte@outlook.com</option>
                                <option value="nancy.elliot@outlook.com" data-user="4">nancy.elliot@outlook.com</option>
                                <option value="mar.audrey@gmail.com" data-user="5" selected=""
                                    data-select2-id="select2-data-26-1bc2">mar.audrey@gmail.com</option>
                                <option value="erna.serpa@outlook.com" data-user="6">erna.serpa@outlook.com</option>
                                <option value="alex@outlook.com" data-user="7" selected=""
                                    data-select2-id="select2-data-27-bmd9">alex@outlook.com</option>
                                <option value="john.deo@outlook.com" data-user="8">john.deo@outlook.com</option>
                                <option value="green.cutte@outlook.com" data-user="9" selected=""
                                    data-select2-id="select2-data-28-wp6r">green.cutte@outlook.com</option>
                                <option value="nancy.elliot@outlook.com" data-user="10">nancy.elliot@outlook.com
                                </option>
                                <option value="mar.audrey@gmail.com" data-user="11">mar.audrey@gmail.com</option>
                                <option value="erna.serpa@outlook.com" data-user="12" selected=""
                                    data-select2-id="select2-data-29-6cf7">erna.serpa@outlook.com</option>
                            </select><span class="select2 select2-container select2-container--bootstrap-5" dir="ltr"
                                data-select2-id="select2-data-24-9q8r" style="width: auto;"><span
                                    class="selection"><span class="select2-selection select2-selection--multiple"
                                        role="combobox" aria-haspopup="true" aria-expanded="false" tabindex="-1"
                                        aria-disabled="false">
                                        <ul class="select2-selection__rendered" id="select2-xn62-container">
                                            <li class="select2-selection__choice" title="john.deo@outlook.com"
                                                data-select2-id="select2-data-30-2d12"><button type="button"
                                                    class="select2-selection__choice__remove" tabindex="-1"
                                                    title="Remove item" aria-label="Remove item"
                                                    aria-describedby="select2-xn62-container-choice-l66x-john.deo@outlook.com"><span
                                                        aria-hidden="true">×</span></button><span
                                                    class="select2-selection__choice__display"
                                                    id="select2-xn62-container-choice-l66x-john.deo@outlook.com"><span
                                                        class="hstack gap-3"> <img src="./../assets/images/avatar/2.png"
                                                            class="avatar-image avatar-sm">
                                                        john.deo@outlook.com</span></span></li>
                                            <li class="select2-selection__choice" title="mar.audrey@gmail.com"
                                                data-select2-id="select2-data-31-0l6e"><button type="button"
                                                    class="select2-selection__choice__remove" tabindex="-1"
                                                    title="Remove item" aria-label="Remove item"
                                                    aria-describedby="select2-xn62-container-choice-4fp0-mar.audrey@gmail.com"><span
                                                        aria-hidden="true">×</span></button><span
                                                    class="select2-selection__choice__display"
                                                    id="select2-xn62-container-choice-4fp0-mar.audrey@gmail.com"><span
                                                        class="hstack gap-3"> <img src="./../assets/images/avatar/5.png"
                                                            class="avatar-image avatar-sm">
                                                        mar.audrey@gmail.com</span></span></li>
                                            <li class="select2-selection__choice" title="alex@outlook.com"
                                                data-select2-id="select2-data-32-j292"><button type="button"
                                                    class="select2-selection__choice__remove" tabindex="-1"
                                                    title="Remove item" aria-label="Remove item"
                                                    aria-describedby="select2-xn62-container-choice-77ac-alex@outlook.com"><span
                                                        aria-hidden="true">×</span></button><span
                                                    class="select2-selection__choice__display"
                                                    id="select2-xn62-container-choice-77ac-alex@outlook.com"><span
                                                        class="hstack gap-3"> <img src="./../assets/images/avatar/7.png"
                                                            class="avatar-image avatar-sm">
                                                        alex@outlook.com</span></span></li>
                                            <li class="select2-selection__choice" title="green.cutte@outlook.com"
                                                data-select2-id="select2-data-33-p0i5"><button type="button"
                                                    class="select2-selection__choice__remove" tabindex="-1"
                                                    title="Remove item" aria-label="Remove item"
                                                    aria-describedby="select2-xn62-container-choice-ahd4-green.cutte@outlook.com"><span
                                                        aria-hidden="true">×</span></button><span
                                                    class="select2-selection__choice__display"
                                                    id="select2-xn62-container-choice-ahd4-green.cutte@outlook.com"><span
                                                        class="hstack gap-3"> <img src="./../assets/images/avatar/9.png"
                                                            class="avatar-image avatar-sm">
                                                        green.cutte@outlook.com</span></span></li>
                                            <li class="select2-selection__choice" title="erna.serpa@outlook.com"
                                                data-select2-id="select2-data-34-g4r6"><button type="button"
                                                    class="select2-selection__choice__remove" tabindex="-1"
                                                    title="Remove item" aria-label="Remove item"
                                                    aria-describedby="select2-xn62-container-choice-sugy-erna.serpa@outlook.com"><span
                                                        aria-hidden="true">×</span></button><span
                                                    class="select2-selection__choice__display"
                                                    id="select2-xn62-container-choice-sugy-erna.serpa@outlook.com"><span
                                                        class="hstack gap-3"> <img
                                                            src="./../assets/images/avatar/12.png"
                                                            class="avatar-image avatar-sm">
                                                        erna.serpa@outlook.com</span></span></li>
                                        </ul><span class="select2-search select2-search--inline"><textarea
                                                class="select2-search__field" type="search" tabindex="0"
                                                autocorrect="off" autocapitalize="none" spellcheck="false"
                                                role="searchbox" aria-autocomplete="list" autocomplete="off"
                                                aria-label="Search" aria-describedby="select2-xn62-container"
                                                placeholder="" style="width: 0.75em;"></textarea></span>
                                    </span></span><span class="dropdown-wrapper" aria-hidden="true"></span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="notesTab">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card stretch stretch-full">
                        <div class="card-header d-flex justify-content-between border-bottom-0">
                            <div>
                                <h5>Notes:</h5>
                                <p class="fs-12 text-muted">Notes for this tasks</p>
                            </div>
                            <a href="javascript:void(0);">3 Notes </a>
                        </div>
                        <div class="card-body py-0">
                            <textarea class="form-control" rows="5" placeholder="Write note here..."></textarea>
                        </div>
                        <div class="card-footer border-top-0">
                            <a href="javascript:void(0);" class="btn btn-primary wd-200">
                                <i class="feather-plus me-2"></i>
                                <span>Add Note</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card stretch stretch-full">
                        <div class="card-body d-flex justify-content-between">
                            <div class="d-flex">
                                <a href="javascript:void(0);" class="avatar-image me-3">
                                    <img src="assets/images/avatar/1.png" class="img-fluid" alt="">
                                </a>
                                <div>
                                    <div class="mb-2">
                                        <a href="javascript:void(0);" class="mb-1 d-block">Alexandra Della</a>
                                        <a href="javascript:void(0);"
                                            class="fs-11 fw-normal text-uppercase text-muted d-block">2023-02-13
                                            14:20:35 </a>
                                    </div>
                                    <p class="text-muted">Lorem ipsum dolor sit, amet consectetur adipisicing elit.
                                        Nemo, quasi nostrum iure nesciunt dolores in, dolorem sequi quidem accusantium
                                        voluptates officia nihil.</p>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="javascript:void(0);" class="avatar-text avatar-sm" data-bs-toggle="tooltip"
                                    title="" data-bs-original-title="Edit">
                                    <i class="feather-edit"></i>
                                </a>
                                <a href="javascript:void(0);" class="avatar-text avatar-sm text-danger"
                                    data-bs-toggle="tooltip" title="" data-bs-original-title="Delete">
                                    <i class="feather-x"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card stretch stretch-full">
                        <div class="card-body d-flex justify-content-between">
                            <div class="d-flex">
                                <a href="javascript:void(0);" class="avatar-image me-3">
                                    <img src="assets/images/avatar/2.png" class="img-fluid" alt="">
                                </a>
                                <div>
                                    <div class="mb-2">
                                        <a href="javascript:void(0);" class="mb-1 d-block">Anderson Thomas</a>
                                        <a href="javascript:void(0);"
                                            class="fs-11 fw-normal text-uppercase text-muted d-block">2023-02-13
                                            14:20:35 </a>
                                    </div>
                                    <p class="text-muted">See resolved goodness felicity shy civility domestic had but
                                        Drawings offended yet answered Jennings perceive.</p>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="javascript:void(0);" class="avatar-text avatar-sm" data-bs-toggle="tooltip"
                                    title="" data-bs-original-title="Edit">
                                    <i class="feather-edit"></i>
                                </a>
                                <a href="javascript:void(0);" class="avatar-text avatar-sm text-danger"
                                    data-bs-toggle="tooltip" title="" data-bs-original-title="Delete">
                                    <i class="feather-x"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card stretch stretch-full">
                        <div class="card-body d-flex justify-content-between">
                            <div class="d-flex">
                                <a href="javascript:void(0);" class="avatar-image me-3">
                                    <img src="assets/images/avatar/3.png" class="img-fluid" alt="">
                                </a>
                                <div>
                                    <div class="mb-2">
                                        <a href="javascript:void(0);" class="mb-1 d-block">Marianne Audrey</a>
                                        <a href="javascript:void(0);"
                                            class="fs-11 fw-normal text-uppercase text-muted d-block">2023-02-13
                                            14:20:35 </a>
                                    </div>
                                    <p class="text-muted">Lorem ipsum dolor sit, amet consectetur adipisicing elit.
                                        Nemo, quasi nostrum iure nesciunt dolores in, dolorem sequi quidem accusantium
                                        voluptates officia nihil, ipsa ex voluptatem ratione mollitia alias perferendis
                                        omnis?</p>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="javascript:void(0);" class="avatar-text avatar-sm" data-bs-toggle="tooltip"
                                    title="" data-bs-original-title="Edit">
                                    <i class="feather-edit"></i>
                                </a>
                                <a href="javascript:void(0);" class="avatar-text avatar-sm text-danger"
                                    data-bs-toggle="tooltip" title="" data-bs-original-title="Delete">
                                    <i class="feather-x"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card stretch stretch-full">
                        <div class="card-body d-flex justify-content-between">
                            <div class="d-flex">
                                <a href="javascript:void(0);" class="avatar-image me-3">
                                    <img src="assets/images/avatar/3.png" class="img-fluid" alt="">
                                </a>
                                <div>
                                    <div class="mb-2">
                                        <a href="javascript:void(0);" class="mb-1 d-block">Marianne Audrey</a>
                                        <a href="javascript:void(0);"
                                            class="fs-11 fw-normal text-uppercase text-muted d-block">2023-02-13
                                            14:20:35 </a>
                                    </div>
                                    <p class="text-muted">Lorem ipsum dolor sit, amet consectetur adipisicing elit.
                                        Nemo, quasi nostrum iure nesciunt dolores in, dolorem sequi quidem accusantium
                                        voluptates officia nihil, ipsa ex voluptatem ratione mollitia alias perferendis
                                        omnis?</p>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="javascript:void(0);" class="avatar-text avatar-sm" data-bs-toggle="tooltip"
                                    title="" data-bs-original-title="Edit">
                                    <i class="feather-edit"></i>
                                </a>
                                <a href="javascript:void(0);" class="avatar-text avatar-sm text-danger"
                                    data-bs-toggle="tooltip" title="" data-bs-original-title="Delete">
                                    <i class="feather-x"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="commentTab">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card stretch stretch-full">
                        <div class="card-body">
                            <div class="task-comment pb-4">
                                <div class="mb-2 d-flex align-items-center justify-content-between">
                                    <div>
                                        <h5>Comments:</h5>
                                        <p class="fs-12 text-muted mb-0">Responses for this tasks</p>
                                    </div>
                                    <a href="javascript:void(0);" class="">6 Comments </a>
                                </div>
                                <hr class="border-dashed my-4">
                                <!--! BEGIN: comment !-->
                                <div class="d-flex mb-4">
                                    <div class="avatar-image me-3">
                                        <a href="javascript:void(0);">
                                            <img src="assets/images/avatar/1.png" class="img-fluid" alt="">
                                        </a>
                                    </div>
                                    <div class="">
                                        <a href="javascript:void(0);" class="mb-1 d-flex align-items-center">
                                            <span>Alexandra Della</span>
                                            <span
                                                class="wd-3 ht-3 bg-gray-500 rounded-circle d-flex mx-2 d-none d-sm-block"></span>
                                            <span class="fs-10 text-uppercase text-muted d-none d-sm-block">57 Min
                                                Ago</span>
                                        </a>
                                        <div class="d-flex align-items-center">
                                            <p class="p-3 fs-12 bg-gray-200 rounded mb-0">Removed demands expense
                                                account in outward tedious do. Particular way thoroughly unaffected
                                                projection.</p>
                                            <div class="dropdown ms-2">
                                                <a href="javascript:void(0);" class="avatar-text avatar-sm"
                                                    data-bs-toggle="dropdown">
                                                    <i class="feather-more-vertical"></i>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a href="javascript:void(0);" class="dropdown-item">
                                                            <i class="feather-bell-off me-3"></i>
                                                            <span>Mute</span>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0);" class="dropdown-item">
                                                            <i class="feather-eye-off me-3"></i>
                                                            <span>Hide</span>
                                                        </a>
                                                    </li>
                                                    <li class="dropdown-divider"></li>
                                                    <li>
                                                        <a href="javascript:void(0);" class="dropdown-item">
                                                            <i class="feather-slash me-3"></i>
                                                            <span>Block</span>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0);" class="dropdown-item">
                                                            <i class="feather-flag me-3"></i>
                                                            <span>Report</span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="fs-10 text-uppercase d-flex align-items-center mt-2">
                                            <a href="javascript:void(0);" class="text-muted">Like (6)</a>
                                            <span class="wd-3 ht-3 bg-gray-500 rounded-circle d-flex mx-2"></span>
                                            <a href="javascript:void(0);" class="text-muted">Reply</a>
                                            <span class="wd-3 ht-3 bg-gray-500 rounded-circle d-flex mx-2"></span>
                                            <a href="javascript:void(0);" class="text-muted">5 Replies</a>
                                        </div>
                                    </div>
                                </div>
                                <!--! BEGIN: reply !-->
                                <div class="ms-4">
                                    <div class="ms-4">
                                        <!--! BEGIN: single-reply !-->
                                        <div class="d-flex mb-4">
                                            <div class="avatar-image me-3">
                                                <a href="javascript:void(0);">
                                                    <img src="assets/images/avatar/3.png" class="img-fluid" alt="">
                                                </a>
                                            </div>
                                            <div class="">
                                                <a href="javascript:void(0);" class="mb-1 d-flex align-items-center">
                                                    <span>Marianne Audrey</span>
                                                    <span
                                                        class="wd-3 ht-3 bg-gray-500 rounded-circle d-flex mx-2 d-none d-sm-block"></span>
                                                    <span class="fs-10 text-uppercase text-muted d-none d-sm-block">50
                                                        Min Ago</span>
                                                </a>
                                                <div class="d-flex align-items-center">
                                                    <p class="p-3 fs-12 bg-gray-200 rounded mb-0">Wishing calling is
                                                        warrant settled was lucky.</p>
                                                    <div class="dropdown ms-2">
                                                        <a href="javascript:void(0);" class="avatar-text avatar-sm"
                                                            data-bs-toggle="dropdown">
                                                            <i class="feather-more-vertical"></i>
                                                        </a>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li>
                                                                <a href="javascript:void(0);" class="dropdown-item">
                                                                    <i class="feather-bell-off me-3"></i>
                                                                    <span>Mute</span>
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="javascript:void(0);" class="dropdown-item">
                                                                    <i class="feather-eye-off me-3"></i>
                                                                    <span>Hide</span>
                                                                </a>
                                                            </li>
                                                            <li class="dropdown-divider"></li>
                                                            <li>
                                                                <a href="javascript:void(0);" class="dropdown-item">
                                                                    <i class="feather-slash me-3"></i>
                                                                    <span>Block</span>
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="javascript:void(0);" class="dropdown-item">
                                                                    <i class="feather-flag me-3"></i>
                                                                    <span>Report</span>
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="fs-10 text-uppercase d-flex align-items-center mt-2">
                                                    <a href="javascript:void(0);" class="text-muted">Like</a>
                                                    <span
                                                        class="wd-3 ht-3 bg-gray-500 rounded-circle d-flex mx-2"></span>
                                                    <a href="javascript:void(0);" class="text-muted">Reply</a>
                                                </div>
                                            </div>
                                        </div>
                                        <!--! BEGIN: single-reply !-->
                                        <div class="d-flex mb-4">
                                            <div class="avatar-image me-3">
                                                <a href="javascript:void(0);">
                                                    <img src="assets/images/avatar/2.png" class="img-fluid" alt="">
                                                </a>
                                            </div>
                                            <div class="">
                                                <a href="javascript:void(0);" class="mb-1 d-flex align-items-center">
                                                    <span>Curtis Green</span>
                                                    <span
                                                        class="wd-3 ht-3 bg-gray-500 rounded-circle d-flex mx-2 d-none d-sm-block"></span>
                                                    <span class="fs-10 text-uppercase text-muted d-none d-sm-block">45
                                                        Min Ago</span>
                                                </a>
                                                <div class="d-flex align-items-center">
                                                    <p class="p-3 fs-12 bg-gray-200 rounded mb-0">Removed demands
                                                        expense account in outward tedious do. Particular way thoroughly
                                                        unaffected projection.</p>
                                                    <div class="dropdown ms-2">
                                                        <a href="javascript:void(0);" class="avatar-text avatar-sm"
                                                            data-bs-toggle="dropdown">
                                                            <i class="feather-more-vertical"></i>
                                                        </a>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li>
                                                                <a href="javascript:void(0);" class="dropdown-item">
                                                                    <i class="feather-bell-off me-3"></i>
                                                                    <span>Mute</span>
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="javascript:void(0);" class="dropdown-item">
                                                                    <i class="feather-eye-off me-3"></i>
                                                                    <span>Hide</span>
                                                                </a>
                                                            </li>
                                                            <li class="dropdown-divider"></li>
                                                            <li>
                                                                <a href="javascript:void(0);" class="dropdown-item">
                                                                    <i class="feather-slash me-3"></i>
                                                                    <span>Block</span>
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="javascript:void(0);" class="dropdown-item">
                                                                    <i class="feather-flag me-3"></i>
                                                                    <span>Report</span>
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="fs-10 text-uppercase d-flex align-items-center mt-2">
                                                    <a href="javascript:void(0);" class="text-muted">Like (2)</a>
                                                    <span
                                                        class="wd-3 ht-3 bg-gray-500 rounded-circle d-flex mx-2"></span>
                                                    <a href="javascript:void(0);" class="text-muted">Reply</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ms-4 mb-4">
                                            <a href="javascript:void(0);" class="d-flex align-items-center text-muted">
                                                <i class="feather-more-horizontal fs-12"></i>
                                                <span class="fs-10 text-uppercase ms-2 text-truncate-1-line">Load More
                                                    Replies</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <!--! BEGIN: comment !-->
                                <div class="d-flex mb-4">
                                    <div class="avatar-image me-3">
                                        <a href="javascript:void(0);">
                                            <img src="assets/images/avatar/4.png" class="img-fluid" alt="">
                                        </a>
                                    </div>
                                    <div class="">
                                        <a href="javascript:void(0);" class="mb-1 d-flex align-items-center">
                                            <span>Holland Scott</span>
                                            <span
                                                class="wd-3 ht-3 bg-gray-500 rounded-circle d-flex mx-2 d-none d-sm-block"></span>
                                            <span class="fs-10 text-uppercase text-muted d-none d-sm-block">42 Min
                                                Ago</span>
                                        </a>
                                        <div class="d-flex align-items-center">
                                            <p class="p-3 fs-12 bg-gray-200 rounded mb-0">See resolved goodness felicity
                                                shy civility domestic had but Drawings offended yet answered Jennings
                                                perceive.</p>
                                            <div class="dropdown ms-2">
                                                <a href="javascript:void(0);" class="avatar-text avatar-sm"
                                                    data-bs-toggle="dropdown">
                                                    <i class="feather-more-vertical"></i>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a href="javascript:void(0);" class="dropdown-item">
                                                            <i class="feather-bell-off me-3"></i>
                                                            <span>Mute</span>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0);" class="dropdown-item">
                                                            <i class="feather-eye-off me-3"></i>
                                                            <span>Hide</span>
                                                        </a>
                                                    </li>
                                                    <li class="dropdown-divider"></li>
                                                    <li>
                                                        <a href="javascript:void(0);" class="dropdown-item">
                                                            <i class="feather-slash me-3"></i>
                                                            <span>Block</span>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0);" class="dropdown-item">
                                                            <i class="feather-flag me-3"></i>
                                                            <span>Report</span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="fs-10 text-uppercase d-flex align-items-center mt-2">
                                            <a href="javascript:void(0);" class="text-muted">Like (8)</a>
                                            <span class="wd-3 ht-3 bg-gray-500 rounded-circle d-flex mx-2"></span>
                                            <a href="javascript:void(0);" class="text-muted">Reply</a>
                                        </div>
                                    </div>
                                </div>
                                <!--! BEGIN: comment !-->
                                <div class="d-flex mb-4">
                                    <div class="avatar-image me-3">
                                        <a href="javascript:void(0);">
                                            <img src="assets/images/avatar/5.png" class="img-fluid" alt="">
                                        </a>
                                    </div>
                                    <div class="">
                                        <a href="javascript:void(0);" class="mb-1 d-flex align-items-center">
                                            <span>Olive Delarosa</span>
                                            <span
                                                class="wd-3 ht-3 bg-gray-500 rounded-circle d-flex mx-2 d-none d-sm-block"></span>
                                            <span class="fs-10 text-uppercase text-muted d-none d-sm-block">34 Min
                                                Ago</span>
                                        </a>
                                        <div class="d-flex align-items-center">
                                            <p class="p-3 fs-12 bg-gray-200 rounded mb-0">Dependent on so extremely
                                                delivered by. Yet no jokes worse her why.</p>
                                            <div class="dropdown ms-2">
                                                <a href="javascript:void(0);" class="avatar-text avatar-sm"
                                                    data-bs-toggle="dropdown">
                                                    <i class="feather-more-vertical"></i>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a href="javascript:void(0);" class="dropdown-item">
                                                            <i class="feather-bell-off me-3"></i>
                                                            <span>Mute</span>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0);" class="dropdown-item">
                                                            <i class="feather-eye-off me-3"></i>
                                                            <span>Hide</span>
                                                        </a>
                                                    </li>
                                                    <li class="dropdown-divider"></li>
                                                    <li>
                                                        <a href="javascript:void(0);" class="dropdown-item">
                                                            <i class="feather-slash me-3"></i>
                                                            <span>Block</span>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0);" class="dropdown-item">
                                                            <i class="feather-flag me-3"></i>
                                                            <span>Report</span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="fs-10 text-uppercase d-flex align-items-center mt-2">
                                            <a href="javascript:void(0);" class="text-muted">Like (8)</a>
                                            <span class="wd-3 ht-3 bg-gray-500 rounded-circle d-flex mx-2"></span>
                                            <a href="javascript:void(0);" class="text-muted">Reply</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="input-group mb-4">
                                <input type="text" name="name" id="name" class="form-control"
                                    placeholder="Your comment..." aria-describedby="suffixId">
                                <a href="javascript:void(0);" class="input-group-text" id="suffixId">Submit</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>