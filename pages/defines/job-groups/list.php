<?php
require_once "App/Helper/helper.php";
require_once "Model/JobGroupsModel.php";

use App\Helper\Helper;
use App\Helper\Security;

$JobGroups = new JobGroupsModel();
$jobGroups = $JobGroups->all();
?>

<div class="container-xl">
    <?php
    $title = "İş Grubu Tanımlama!";
    $text = "Firmanız için iş grupları tanımlayabilir ve çalışanlarınızı kolaylıkla takip edebilirsiniz!";
    require_once 'pages/components/alert.php';
    ?>
    <div class="row row-deck row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="fw-bold mb-0 me-4">
                        <span class="d-block mb-2">İş Grubu Listesi</span>
                    </h5>
                    <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                        <div class="col-auto ms-auto d-flex align-items-center ">
                            <a href="#" class="btn btn-primary route-link" data-page="defines/job-groups/manage">
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
                                        <th>Grup Adı</th>
                                        <th>Açıklama</th>
                                        <th>Eklenme Tarihi</th>
                                        <th>İşlem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 1;
                                    foreach ($jobGroups as $jobs):
                                        $id = Security::encrypt($jobs->id);
                                    ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td>
                                                <a class="route-link" data-page="defines/job-groups/manage&id=<?php echo $id ?>" href="#">
                                                <?php echo $jobs->group_name; ?>
                                                </a>
                                            </td>
                                            <td><?php echo $jobs->description; ?></td>
                                            <td><?php echo $jobs->created_at; ?></td>
                                            <td>
                                                <div class="hstack gap-2">
                                                    <a href="javascript:void(0);" class="avatar-text avatar-md route-link" data-page="defines/job-groups/manage&id=<?php echo $id ?>">
                                                        <i class="feather-edit"></i>
                                                    </a>
                                                    <a href="javascript:void(0);" class="avatar-text avatar-md delete-job-groups" data-id="<?php echo $id ?>">
                                                        <i class="feather-trash-2"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>