<?php
require_once "Model/Cases.php";
require_once "App/Helper/company.php";
require_once "App/Helper/helper.php";
require_once "App/Helper/financial.php";
require_once "Model/CaseTransactions.php";


use App\Helper\Helper;
use App\Helper\Security;

$Cases = new Cases();
$CaseTransactions = new CaseTransactions();
$company = new CompanyHelper();

$Auths->checkFirmReturn();
$perm->checkAuthorize("cash_register_list");


$is_main_user = $_SESSION['user']->parent_id;
if ($is_main_user == 0) {
    $cases = $Cases->allCaseWithFirmId();
} else {
    $cases = $Cases->getCasesByUserIds();
}
$financialHelper = new Financial();


?>
<div class="container-xl">

    <!-- Alert component'i dahil et -->
    <?php
    $title = "Kasa Listesi!";
    $text = "Firmanız için tanımlı kasaları buradan yönetebilirsiniz.Gelir gider işlemleriniz içini varsayılan kasayı seçmeyi unutmayın!";
    require_once 'pages/components/alert.php'
    ?>
    <div class="row row-deck row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Kasa Listesi</h4>
                    <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                        <div class="col-auto ms-auto d-flex align-items-center ">
                            <?php
                            $link = $Auths->Authorize("cash_register_add_update") ? "financial/case/manage" : "authorize";
                            ?>
                            <a href="#" class="btn btn-primary route-link" data-page="<?php echo $link; ?>">
                                <i class="feather-plus me-2"></i><span>Yeni</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover" id="customerList"> <!--id="customerList" burasını sayfaya göre değiştireceğiz asset/js/ klasöründe ayarlarını yapacağız-->
                        <thead>
                            <tr>
                                <th style="width:7%">id</th>
                                <th>Firması</th>
                                <th>Kasa Adı</th>
                                <th>Bankası</th>
                                <th>Şubesi</th>
                                <th>Para Birimi</th>
                                <th>Varsayılan mı?</th>
                                <th>Güncel Bakiye</th>
                                <th>Açıklama</th>
                                <th style="width:7%">İşlem</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            //Kullanıcı firma id ve session firm_id eşleşiyorsa;
                            if ($Auths->checkFirm()): ?>

                                <?php
                                $i = 1;
                                foreach ($cases as $case):
                                    $id = Security::encrypt($case->id);
                                    $balance = $CaseTransactions->getCaseBalance($case->id)->balance;
                                ?>
                                    <tr>
                                        <td class="text-center"><?php echo $i; ?></td>
                                        <td><?php echo $company->getFirmName($case->company_id ?? ''); ?></td>
                                        <td> <a class="nav-item route-link" data-tooltip="Detay/Güncelle"
                                                data-page="financial/case/manage&id=<?php echo $id ?>" href="#">
                                                <?php echo $case->case_name; ?>
                                            </a>
                                        </td>
                                        <td><?php echo $case->bank_name; ?></td>
                                        <td><?php echo $case->branch_name; ?></td>
                                        <td><?php echo Helper::money($case->case_money_unit); ?></td>
                                        <td class="text-center"><?php

                                                                if ($case->isDefault == 1) {
                                                                    echo '<i class="ti ti-check icon color-green"></i>';
                                                                }
                                                                ?></td>
                                        <td class="text-center">
                                            <?php echo Helper::formattedMoney($balance); ?>
                                        </td>
                                        <td><?php echo $case->description; ?></td>

                                        <td class="text-end">
                                            <div class="dropdown">
                                                <button class="btn dropdown-toggle align-text-top"
                                                    data-bs-toggle="dropdown">İşlem</button>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item route-link"
                                                        data-page="financial/case/manage&id=<?php echo $id ?>" href="#">
                                                        <i class="ti ti-transfer icon me-3"></i> Kasa Hareketleri
                                                    </a>

                                                    <?php
                                                    //Ekleme ve güncelleme yetkisi varsa
                                                    if ($Auths->hasPermission("cash_register_add_update")) { ?>
                                                        <a class="dropdown-item route-link"
                                                            data-page="financial/case/manage&id=<?php echo $id ?>" href="#">
                                                            <i class="ti ti-edit icon me-3"></i> Güncelle/Detay
                                                        </a>
                                                    <?php } ?>

                                                    <!-- Kasalararası virman yetkisi varsa -->
                                                    <?php if ($Auths->hasPermission("intercash_transfer")) {; ?>
                                                        <a class="dropdown-item intercash-transfer" data-id="<?php echo $id ?>"
                                                            href="#">
                                                            <i class="ti ti-transform icon me-3"></i> Kasalararası Virman
                                                        </a>
                                                    <?php } ?>
                                                    <a class="dropdown-item default-case" data-id="<?php echo $id ?>" href="#">
                                                        <i class="ti ti-checks icon me-3"></i> Varsayılan Yap
                                                    </a>
                                                    <?php
                                                    //Ekleme ve güncelleme yetkisi varsa
                                                    if ($Auths->hasPermission("cash_delete")) { ?>
                                                        <a class="dropdown-item delete-case" data-id="<?php echo $id ?>" href="#">
                                                            <i class="ti ti-trash icon me-3"></i> Sil
                                                        <?php } ?>
                                                        </a>
                                                </div>
                                            </div>


                                        </td>
                                    </tr>
                                <?php
                                    $i++;
                                endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- //modali dahil et -->
<?php require_once "content/intercash_transfer-modal.php"; ?>