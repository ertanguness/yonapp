<style>
<?php require_once "style.css";
?>
</style>

<?php

use App\Helper\Security;
use Model\UserRolesModel;

$UserRoles = new UserRolesModel();
$enc_id = $role_id;
$role_id = Security::decrypt($role_id) ?? 0;
$role = $UserRoles->find($role_id);

?>


<?php
$maintitle = "Ana Sayfa";
$title = "Yetki Yönetimi ". ($role ? " - ( " . $role->role_name . " )" : "");
?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Yetkiler</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Yetkileri Düzenle</li>
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

                <div class="d-flex gap-2">
                    <a href="/kullanici-gruplari" class="btn btn-outline-secondary">
                        <i class="feather-arrow-left font-size-16 align-middle me-2"></i> Listeye Dön
                    </a>
                    <button id="resetChanges" class="btn btn-outline-danger">
                        <i class="feather-x-square font-size-16 align-middle me-2"></i> Sıfırla
                    </button>
                    <button id="selectAllPermissions" class="btn btn-outline-primary">
                        <i class="feather-check-circle font-size-16 align-middle me-2"></i> Tümünü Seç
                    </button>
                
                    <a href="#" id="savePermissions" class="btn btn-primary route-link" data-page="kullanici/duzenle">
                        <i class="feather-plus me-2"></i>
                        <span>Kaydet</span>
                    </a>

                </div>
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
    <div class="row">
        <input type="text" id="role_id" name="role_id" value="<?php echo $enc_id ?? 0 ?>" hidden >
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <!-- Kaydetme Alanı -->
                    <div class="me-auto">
                        <span id="selectedCount" class="text-muted badge badge-count">0</span>
                        <span class="text-muted ms-2">yetki seçildi</span>
                        <span class="text-muted ms-3 d-none d-sm-inline">(<span id="requiredCount">0</span>
                            zorunlu)</span>
                    </div>
                    <div class="d-flex gap-2">

                        <button class="btn btn-sm btn-outline-primary" id="selectHighlighted">
                            <i class="ti ti-check"></i> Arama Sonuçlarını Seç
                        </button>
                        <input type="text" class="form-control" id="permissionSearch"
                            placeholder="Yetki veya grup adı ara...">
                    </div>


                </div>
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                        <div class="col-md-10">

                            <div class="filter-chips mb-2 mb-md-0 d-flex flex-wrap gap-1" id="filterChips">

                            </div>
                        </div>
                        <div class="col-md-2 d-flex justify-content-end">

                            <div class="align-items-center gap-1">

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="showTreeView">
                                    <label class="form-check-label" for="showTreeView">Ağaç Görünümü</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Yetki Grupları (Kart Görünümü) -->
                    <div id="cardViewContainer">

                        <div id="permissionContainer" class="mb-4">
                            <!-- Dinamik olarak yüklenecek -->
                        </div>

                        <!-- Yükleme Skeleton -->
                        <div id="loadingSkeleton" style="display: none;">
                            <div class="permission-group loading mb-3">
                                <div class="group-header placeholder-glow">
                                    <div class="d-flex align-items-center w-100">
                                        <div class="permission-icon placeholder me-3"></div>
                                        <div class="flex-grow-1"><span class="placeholder col-6"></span></div>
                                        <span class="placeholder col-2"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="permission-group loading mb-3">
                                <div class="group-header placeholder-glow">
                                    <div class="d-flex align-items-center w-100">
                                        <div class="permission-icon placeholder me-3"></div>
                                        <div class="flex-grow-1"><span class="placeholder col-7"></span></div>
                                        <span class="placeholder col-2"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ağaç Görünümü -->
                    <div id="treeViewContainer" class="permission-tree mb-4" style="display: none;">
                        <!-- Ağaç yapısı buraya yüklenecek -->
                    </div>

                    <div class="toast-container"></div>


                </div>
            </div>
        </div>
    </div>
</div>