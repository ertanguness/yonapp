<?php
require_once "Model/RolesModel.php";
require_once "App/Helper/security.php";

use App\Helper\Security;
$roleObj = new Roles();

//Sayfa başlarında eklenecek alanlar
$perm->checkAuthorize("permission_group_add_update");
$id = isset($_GET["id"]) ? Security::decrypt($_GET['id']) : 0;
$new_id = isset($_GET["id"]) ? $_GET['id'] : 0;

//Eğer url'den id yazılmışsa veya id boş ise projeler sayfasına gider
if($id == null && isset($_GET['id'])) {
    header("Location: /index.php?p=users/roles/list");
    exit;
}

$roles = $roleObj->find($id);

$pageTitle = $id > 0 ? "Yetki Grubu Düzenle" : "Yeni Yetki Grubu";
?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Kullanıcılar</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Yetki Grupları</li>
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

                <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="users/roles/list">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </button>
                <button type="button" class="btn btn-primary" id="rol_kaydet">
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
    if ($pageTitle === 'Yeni Yetki Grubu') {
        $text = "Yeni Rol tanımlayabilirsiniz.";
    } else {
        $text = "Seçtiğiniz Rolü güncelleyebilirsiniz.";
    }
    require_once 'pages/components/alert.php'
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <form action='' id='roleForm'>
                            <div class="card-body custom-card-action p-0">
                                <div class="card-body personal-info">
                                    <div class="row mb-4 align-items-center">
                                    <input type="hidden" class="form-control mb-3" id="role_id" value="<?php echo $new_id ?>">
                                        <div class="col-lg-2">
                                            <label for="" class="fw-semibold">Pozisyon Adı: </label>
                                        </div>
                                        <div class="col-lg-10">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-user-plus"></i></div>
                                                <input type="text" class="form-control" id="role_name" name="r_name " value="<?php echo $roles->roleName ?? "" ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2">
                                            <label for="description" class="fw-semibold">Pozisyon Açıklama: </label>
                                        </div>
                                        <div class="col-lg-10">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-type"></i></div>
                                                <textarea class="form-control" id="role_description " name="role_description" cols="30" rows="3" value="<?php echo $roles->roleDescription ?? "" ?>"></textarea>
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