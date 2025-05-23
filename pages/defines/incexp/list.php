<?php
require_once "App/Helper/helper.php";
require_once "App/Helper/date.php";
require_once "Model/DefinesModel.php";

use App\Helper\Helper;
use App\Helper\Date;

$defines = new DefinesModel();

$items = $defines->getIncExpTypesByFirm();

$user_id = $_SESSION['user']->id;


?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Tanımlamalar</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Gelir Gider İşlemleri</li>
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

                <a href="#" class="btn btn-primary route-link" data-page="defines/incexp/manage">
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
    $title = "Gelir/Gider Türü Listesi!";
    $text = "Gelir/Gider türü ekleme, düzenleme, silme işlemlerinizi buradan yapabilirsiniz.";
    require_once 'pages/components/alert.php'
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover datatables" id="incexpList">
                                    <thead>
                                        <tr class="text-center">
                                            <th>Sıra</th>
                                            <th>Adı</th>
                                            <th>Türü</th>
                                            <th>Açıklama</th>
                                            <th>Eklenme Tarihi</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        foreach ($items as $item) :
                                        ?>
                                            <tr>
                                                <td><?php echo $i; ?></td>
                                                <td><?php echo $item->name; ?></td>
                                                <td><?php echo Helper::getIncExpTypeName($item->type_id); ?></td>
                                                <td><?php echo $item->description; ?></td>
                                                <td><?php echo Date::dmY($item->created_at); ?></td>

                                                <td>
                                                    <div class="hstack gap-2 ">
                                                        <a href="javascript:void(0);" class="avatar-text avatar-md  route-link" data-page="defines/incexp/manage&id=<?php echo $item->id ?>">
                                                            <i class="feather-edit"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" class="avatar-text avatar-md delete-incexp" data-id="<?php echo $item->id ?>">
                                                            <i class="feather-trash-2"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php
                                            $i++;
                                        endforeach; ?>
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