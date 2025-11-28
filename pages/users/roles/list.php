<?php
require_once "Model/RolesModel.php";
require_once "App/Helper/security.php";



use App\Helper\Security;

$roleObj = new Roles();
//$roles = $roleObj->getRolesByFirm($firm_id);
//$perm->checkAuthorize("permission_groups");


?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Kullanıcılar</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/ana-sayfa">Ana Sayfa</a></li>
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
                <?php if ($Auths->hasPermission('permission_group_add_update')) { ?>
                    <a href="#" class="btn btn-primary route-link" data-page="users/roles/manage">
                        <i class="feather-plus me-2"></i>
                        <span>Yeni İşlem</span>
                    </a>
                <?php } ?>
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
    $title = "Rol Listesi!";
    $text = "Seçili site/apartman için dilediğiniz kadar rol ekleyebilir, rolleri düzenleyebilir, roller arası yetkileri kopyalayabilir
     ve istediğiniz rolleri silebilirsiniz.";
    require_once 'pages/components/alert.php'
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-responsive datatables" id="roleTable">
                                    <thead>
                                        <tr>
                                            <th style="width:7%">Sıra</th>
                                            <th style="width:27%">Pozisyon Adı</th>
                                            <th>Açıklama</th>
                                            <th style="width:7%">Durumu</th>
                                            <th style="width:7%">İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        if ($Auths->checkFirm()) {
                                            foreach ($roles as $role):
                                                $id = Security::encrypt($role->id);
                                        ?>
                                                <tr>
                                                    <td class="text-center"><?php echo $i; ?></td>
                                                    <td><?php echo $role->roleName; ?></td>
                                                    <td><?php echo $role->roleDescription; ?></td>
                                                    <td><?php echo $role->isActive; ?></td>

                                                    <td>
                                                        <div class="hstack gap-2 ">
                                                            <?php if ($Auths->hasPermission('transaction_permissions')) { ?>
                                                                <a href="javascript:void(0);" class="avatar-text avatar-md  route-link" data-page="users/auths/auths&id=<?php echo $id ?>">
                                                                    <i class="feather-edit-3"></i>
                                                                </a>
                                                                <!-- Başka yetkilerin ana kullanıcı rölüne kopyalanmasını engellemek için -->
                                                                <?php if ($role->main_role != 1) { ?>
                                                                    <a href="javascript:void(0);" class="avatar-text avatar-md  copy-roles" data-bs-toggle="modal"
                                                                        data-id="<?php echo $id ?>" data-name="<?php echo $role->roleName ?>"
                                                                        data-bs-target="#modal-small">
                                                                        <i class="feather-copy"></i>
                                                                    </a>
                                                                <?php } ?>
                                                            <?php } ?>
                                                            <!-- Yetki grubunu güncelleme işlemleri -->
                                                            <?php if ($Auths->hasPermission('permission_group_add_update')) { ?>
                                                                <a href="javascript:void(0);" class="avatar-text avatar-md route-link" data-page="users/roles/manage&id=<?php echo $id ?>">
                                                                    <i class="feather-edit"></i>
                                                                </a>
                                                            <?php } ?>
                                                            <?php if ($role->main_role != 1) { ?>
                                                                <!-- Yetki grubunu silme işlemleri -->
                                                                <?php if ($Auths->hasPermission('permission_group_delete')) { ?>

                                                                    <a href="javascript:void(0);" class="avatar-text avatar-md delete_role" data-id="<?php echo $id ?>">
                                                                        <i class="feather-trash-2"></i>
                                                                    </a>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        </div>
                                                    </td>

                                                </tr>
                                        <?php
                                                $i++;
                                            endforeach;
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal modal-blur fade" id="modal-small" tabindex="-1" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="" id="copyRoleForm">
                    <input type="hidden" id="copy_role_id" name="copy_role_id" class="form-control">
                    <input type="hidden" name="action" value="copyRolesModal">

                    <div class="modal-body">
                        <div class="modal-title">Emin misiniz?</div>
                        <div><strong id="role_name">Admin</strong> İsimli yetki grubuna aşağıdaki yetki grubunun yetkileri
                            kopyalanacaktır!
                        </div>
                        <div class="col mt-5 ">
                            <label class="form-label">Yetkileri Kopyalanacak Grubu Seçin</label>
                            <select name="role_to_copy" id="role_to_copy" class="form-control select2" style="width:100%">
                            </select>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary me-auto"
                            data-bs-dismiss="modal">Vazgeç</button>
                        <button type="button" id="copy_roles" class="btn btn-danger" data-bs-dismiss="modal">Evet,
                            Kopyala!
                        </button>
                </form>
            </div>
        </div>
    </div>
</div>