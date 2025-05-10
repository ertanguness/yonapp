<?php
require_once "App/Helper/helper.php";
require_once "Model/JobGroupsModel.php";

use App\Helper\Helper;
use App\Helper\Security;

$JobGroups = new JobGroupsModel();
$jobGroups = $JobGroups->all();
?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Tanımlamalar</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">İş Grubu Tanımlama</li>
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
                <?php
                require_once 'pages/components/search.php';
                require_once 'pages/components/download.php'
                ?>

                <a href="#" class="btn btn-primary route-link" data-page="defines/job-groups/manage">
                    <i class="feather-plus me-2"></i>
                    <span>Yeni İşlem</span>
                </a>
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
    $title = "İş Grubu Tanımlama!";
    $text = "Siteniz için iş grupları tanımlayabilir ve çalışanlarınızı kolaylıkla takip edebilirsiniz!";
    require_once 'pages/components/alert.php';
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover datatables" id="jobGroupsList">
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
</div>