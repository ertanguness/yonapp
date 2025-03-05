<?php
$user_id = $_SESSION['user']->id;
require_once "Model/MyFirmModel.php";
require_once "App/Helper/security.php";

use App\Helper\Security;


$perm->checkAuthorize("blocks_add_edit");
$Auths->checkFirmReturn();


$MyFirmModel = new MyFirmModel();
$myfirms = $MyFirmModel->getMyFirmByUserId();

?>
<div class="container-xl">

    <div class="row row-deck row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Bloklar Listesi</h3>
                    <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">

                        <div class="col-auto ms-auto d-flex align-items-center ">
                            <div class="dropdown">
                                <a class="btn btn-icon me-2" data-bs-toggle="dropdown" data-bs-offset="0, 10" data-bs-auto-close="outside">
                                    <i class="feather-download"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="bi bi-filetype-pdf me-3"></i>
                                        <span>PDF</span>
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="bi bi-filetype-csv me-3"></i>
                                        <span>CSV</span>
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="bi bi-filetype-xml me-3"></i>
                                        <span>XML</span>
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="bi bi-filetype-txt me-3"></i>
                                        <span>Text</span>
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="bi bi-filetype-exe me-3"></i>
                                        <span>Excel</span>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="bi bi-printer me-3"></i>
                                        <span>Print</span>
                                    </a>
                                </div>
                            </div>

                            <a href="#" class="btn btn-primary route-link" data-page="blocks/manage">
                                <i class="feather-plus me-2"></i><span>Yeni</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body custom-card-action p-0">
                    <div class="table-responsive">
                    <table class="table table-hover" id="customerList"> <!--id="customerList" burasını sayfaya göre değiştireceğiz asset/js/ klasöründe ayarlarını yapacağız-->
                    <thead>
                                <tr class="text-center">
                                    <th>Sıra</th>
                                    <th>Site Adı</th>
                                    <th>Blok Sayısı</th>
                                    <th>Bağımsız Bölüm Sayısı</th>
                                    <th>Site Adresi</th>
                                    <th>Açıklama</th>
                                    <th>İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                               
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>