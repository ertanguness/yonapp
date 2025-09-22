<?php 
use App\Helper\Security;
use Model\UserModel;
use Model\UserRolesModel;


$UserModel = new UserModel();
$UserRolesModel = new UserRolesModel();



$id= isset($_GET['id']) ? Security::decrypt($_GET['id']) : 0;
$enc_id= isset($_GET['id']) ? $_GET['id'] : 0;

$role = $UserRolesModel->find($id);

?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Yetki Yönetimi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Yetki Grubu Ekle</li>
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
                <a href="kullanici-gruplari" type="button" class="btn btn-outline-secondary route-link me-2">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </a>
                <button type="button" class="btn btn-primary" id="saveRoleBtn">
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
    $title = 'Kullanıcı Grup Düzenleme';
    $text = 'Tanımlı kasalarınızı görüntüleyebilir, yeni kasa ekleyebilir veya düzenleyebilirsiniz. Gelir/Gider işlemleri için varsayılan kasayı unutmayın!';
    require_once 'pages/components/alert.php';
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <form action="" id="roleForm">
                            <div class="card-body custom-card-action p-0">
                                <div class="card-body personal-info">
                                    <div class="row mb-4 align-items-center">
                                        <!-- Hidden Row -->
                                        <div class="row d-none">
                                            <div class="col-md-4">
                                                <input type="text" name="id" class="form-control" value="<?= $enc_id?>">
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" name="action" value="saveRole" class="form-control">
                                            </div>
                                        </div>
                                        <!-- Hidden Row -->

                                        <div class="col-lg-2">
                                            <label class="fw-semibold">Yetki Grubu Adı:</label>
                                        </div>
                                        <div class="col-lg-10">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-credit-card"></i></div>
                                                <input type="text" class="form-control" name="role_name" id="role_name" value="<?= $role->role_name ?? '' ?>">
                                            </div>
                                        </div>

                                       
                                    </div>

                                    

                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2">
                                            <label class="fw-semibold">Açıklama:</label>
                                        </div>
                                        <div class="col-lg-10">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-info"></i></div>
                                                <textarea class="form-control" name="description" id="description" rows="3"><?= $role->description ?? '' ?></textarea>
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

<script>
    $(document).ready(function() {
        setupShortcut('s',function(){
            document.getElementById('saveRoleBtn').click();
        })
    });
</script>