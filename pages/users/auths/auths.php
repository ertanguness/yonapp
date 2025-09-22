<?php

require_once 'Model/AuthsModel.php';
require_once 'Model/RolesModel.php';
require_once 'Model/RoleAuthsModel.php';
require_once 'App/Helper/security.php';

use App\Helper\Security;
use Model\AuthsModel;

$authObj = new AuthsModel();
$roleObj = new Roles();
$roleAuthsObj = new RoleAuthsModel();
ob_start(); // Çıktı tamponlamasını başlatın

$id = Security::decrypt($_GET['id']) ?? 0;
// echo "manuel yazılan id :" . $id;
if (!isset($_GET['id']) || $id == 0) {
    header('Location: index.php?p=users/roles/list');
    exit();
}

$auths = $authObj->auths();
$role = $roleObj->find($id);

$roleAuths = $roleAuthsObj->getAuthsByRoleId($id); //Güncelleme yapılacak 
$auth_id = Security::encrypt($roleAuths->id) ?? 0;

//Yetki kontrolü yapılır
//$perm->checkAuthorize("transaction_permissions");

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
                <button type="button" class="btn btn-primary" id="authsSave">
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
    $title = "Yetki Düzenleme Sayfası!";
    $text = "Yetki düzenleme işlemlerinizi buradan yapabilirsiniz.";
    require_once 'pages/components/alert.php'
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="card-header">
                                <h3 class="card-title text-dark font-weight-bold d-flex align-items-center">
                                    <span class="bg-primary text-white rounded-circle d-inline-flex justify-content-center align-items-center"
                                        style="width: 40px; height: 40px; font-size: 20px;">
                                        <i class="fas fa-user-tag"></i>
                                    </span>
                                    <span class="ms-2 text-dark">İşlem Yaptığınız Pozisyon: <span class="text-primary"><?php echo $role->roleName ?? 'Bilinmiyor'; ?></span></span>
                                </h3>

                            </div>
                            <div class="card-body">

                                <section class="step-body mt-4">
                                    <form action="" id="authsForm">
                                        <div class="row d-none">
                                            <?php $csrf_token = Security::csrf(); ?>
                                            <input type="text" name="role_id" class="form-control" value="<?php echo $role->id; ?>">
                                            <input type="text" name="action" class="form-control" value="saveAuths">
                                            <input type="text" name="auth_id" id="auth_id" class="form-control" value="<?php echo $auth_id; ?>">
                                            <input type="text" name="csrf_token" class="form-control" value="<?php echo $csrf_token; ?>">
                                        </div>
                                        <section class="step-body mt-4">
                                            <form action="" id="authsForm">
                                                <section class="step-body mt-4">
                                                    <fieldset>
                                                        <div class="d-flex justify-content-end">
                                                            <label class="d-flex align-items-center position-relative cursor-pointer" for="checkAll">
                                                                <input class="card-input-element position-absolute opacity-0" type="checkbox" id="checkAll">
                                                                <span class="card card-body d-flex flex-row justify-content-between align-items-center px-2 py-1 w-auto" style="max-width: 250px; min-width: 180px; align-self: flex-end;">
                                                                    <span class="hstack gap-1">
                                                                        <span class="avatar-text">
                                                                            <i class="feather-lock" id="checkAllIcon" style="font-size: 14px;"></i>
                                                                        </span>
                                                                        <span class="fw-bold text-dark" style="font-size: 12px;">Tümünü Seç</span>
                                                                    </span>
                                                                </span>
                                                            </label>
                                                        </div>
                                                        <div class="datagrid">
                                                            <?php foreach ($auths as $auth) {
                                                                $auth_ids = $roleAuths->auth_ids ?? '';
                                                                $checked = in_array($auth->id, explode(',', $auth_ids)) ? 'checked' : '';
                                                            ?>
                                                                <label class="w-100 d-flex align-items-center position-relative cursor-pointer" for="auth_<?php echo $auth->id; ?>">
                                                                    <input class="card-input-element position-absolute opacity-0" type="checkbox" name="auths[]" value="<?php echo $auth->id; ?>" <?php echo $checked; ?> id="auth_<?php echo $auth->id; ?>">
                                                                    <span class="card card-body d-flex flex-row justify-content-between align-items-center w-100">
                                                                        <span class="hstack gap-3">
                                                                            <span class="avatar-text">
                                                                                <i class="<?php echo $checked ? 'feather-unlock' : 'feather-lock'; ?>"></i>
                                                                            </span>
                                                                            <span>
                                                                                <span class="d-block fs-13 fw-bold text-dark"><?php echo $auth->title; ?></span>
                                                                                <span class="d-block text-muted mb-0"><?php echo $auth->description; ?></span>
                                                                            </span>
                                                                        </span>
                                                                    </span>
                                                                </label>

                                                                <!-- Alt Yetkiler Bölümü -->
                                                                <div class="datagrid-content ps-5">
                                                                    <?php
                                                                    $sub_auths = $authObj->subAuths($auth->id);
                                                                    if (!empty($sub_auths)) {
                                                                        foreach ($sub_auths as $sub_auth) {
                                                                            $checked = in_array($sub_auth->id, explode(',', $auth_ids)) ? 'checked' : '';
                                                                    ?>
                                                                            <label class="w-100 d-flex align-items-center position-relative cursor-pointer" for="sub_auth_<?php echo $sub_auth->id; ?>">
                                                                                <input class="card-input-element position-absolute opacity-0" type="checkbox" name="auths[]" value="<?php echo $sub_auth->id; ?>" <?php echo $checked; ?> id="sub_auth_<?php echo $sub_auth->id; ?>">
                                                                                <span class="card card-body d-flex flex-row justify-content-between align-items-center w-100">
                                                                                    <span class="hstack gap-3">
                                                                                        <span class="avatar-text">
                                                                                            <i class="<?php echo $checked ? 'feather-unlock' : 'feather-lock'; ?>"></i>
                                                                                        </span>
                                                                                        <span>
                                                                                            <span class="d-block fs-13 fw-bold text-dark"><?php echo $sub_auth->title; ?></span>
                                                                                            <span class="d-block text-muted mb-0"><?php echo $sub_auth->description; ?></span>
                                                                                        </span>
                                                                                    </span>
                                                                                </span>
                                                                            </label>
                                                                        <?php } ?>
                                                                    <?php } ?>
                                                                </div>
                                                            <?php } ?>
                                                        </div>
                                                    </fieldset>
                                                </section>
                                            </form>
                                        </section>
                                    </form>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
   document.addEventListener("DOMContentLoaded", function () {
    const checkAll = document.getElementById("checkAll");
    const checkboxes = document.querySelectorAll(".card-input-element");

    checkAll.addEventListener("change", function () {
        checkboxes.forEach((checkbox) => {
            checkbox.checked = checkAll.checked;
            let icon = checkbox.closest("label").querySelector("i");
            if (checkbox.checked) {
                icon.classList.replace("feather-lock", "feather-unlock");
                checkbox.closest("label").classList.add("selected");
            } else {
                icon.classList.replace("feather-unlock", "feather-lock");
                checkbox.closest("label").classList.remove("selected");
            }
        });
    });

    checkboxes.forEach((checkbox) => {
        checkbox.addEventListener("change", function () {
            let icon = this.closest("label").querySelector("i");
            if (this.checked) {
                icon.classList.replace("feather-lock", "feather-unlock");
                this.closest("label").classList.add("selected");
            } else {
                icon.classList.replace("feather-unlock", "feather-lock");
                this.closest("label").classList.remove("selected");
            }

            // Eğer herhangi bir checkbox seçili değilse, "Tümünü Seç" checkbox'ını kaldır
            checkAll.checked = [...checkboxes].every((cb) => cb.checked);
        });
    });
});

</script>

<style>
    .cursor-pointer {
        cursor: pointer;
    }

    .selected .card {
        background-color: #e0f3ff;
        /* Seçili kartın rengini değiştirir */
        border-color: #007bff;
    }
</style>