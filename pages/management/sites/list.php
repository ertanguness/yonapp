<?php

use Model\SitesModel;
use App\Helper\Security;
use Model\MyFirmModel;

$Sites = new SitesModel();

$Sites = new SitesModel();
$mysite = $Sites->getSites();


?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Yönetim</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Siteler</li>
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

                <a href="#" class="btn btn-primary route-link" data-page="management/sites/manage">
                    <i class="feather-plus me-2"></i>
                    <span>Yeni Site Ekle</span>
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
    $title = "Siteler Listesi!";
    $text = "Sitelerinizi görüntüleyip ekleme, düzenleme, silme ve yeni site tanımlama işlemlerinizi  yapabilirsiniz.";
    require_once 'pages/components/alert.php'
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">

                                <table class="table table-hover datatables" id="sitesList">
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
                                        <?php
                                        $i = 1;
                                        foreach ($mysite as $mysites):
                                            $enc_id = Security::encrypt($mysites->id);

                                        ?>
                                            <tr>
                                                <td><?php echo $i; ?></td>
                                                <td><a data-page="sites/manage&id=<?php echo $id ?>" href="#">
                                                        <?php echo $mysites->firm_name; ?>
                                                    </a>
                                                </td>
                                                <td class="text-start"><?php echo $mysites->phone; ?></td>
                                                <td><?php echo "3" ?></td>
                                                <td><?php echo "2" ?></td>
                                                <td><?php echo $mysites->created_at; ?></td>
                                                <td>
                                                    <div class="hstack gap-2 ">
                                                        <a href="index?p=management/sites/manage&id=<?php echo $enc_id ?>" class="avatar-text avatar-md">
                                                            <i class="feather-eye"></i>
                                                        </a>
                                                        <a href="index?p=management/sites/manage&id=<?php echo $enc_id ?>" class="avatar-text avatar-md">
                                                            <i class="feather-edit"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" data-name="<?php echo $mysites->firm_name?>" data-id="<?php echo $enc_id ?>" class="avatar-text avatar-md delete-sites" data-id="<?php echo $enc_id; ?>" data-name="<?php echo $mysites->firm_name; ?>">
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