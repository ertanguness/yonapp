<?php

use Model\BlockModel;
use App\Helper\Security;
use Model\SitesModel;

$Site = new SitesModel();


$Blocks = new BlockModel();
$blocks = $Blocks->getBlocksBySite();

?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Yönetim</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Bloklar</li>
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

                <a href="#" class="btn btn-primary route-link" data-page="management/blocks/manage">
                    <i class="feather-plus me-2"></i>
                    <span>Yeni Blok Ekle</span>
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
    $title = "Bloklar Listesi!";
    $text = "Seçili siteye ait blokları görüntüleyip ekleme, düzenleme, silme ve yeni Blok tanımlama işlemlerinizi  yapabilirsiniz.";
    require_once 'pages/components/alert.php'
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">

                                <table class="table table-hover datatables" id="blocksList">
                                    <thead>
                                        <tr class="text-center">
                                            <th>Sıra</th>
                                            <th>Site Adı</th>
                                            <th>Blok Adı</th>
                                            <th>Bağımsız Bölüm Sayısı</th>
                                            <th>Açıklama</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        foreach ($blocks as $block):
                                            $enc_id = Security::encrypt($block->id);
                                            $site = $Site->getSiteName($block->site_id);

                                        ?>
                                            <tr class="text-center">
                                                <td><?php echo $i; ?></td>
                                                <td>
                                                <a data-page="sites/manage&id=<?php echo $block->site_id; ?>" href="#">
                                                    <?php echo $site->firm_name; ?>
                                                </a>
                                                </td>
                                                <td class="text-start"><?php echo htmlspecialchars($block->block_name); ?></td>
                                                <td><?php echo htmlspecialchars($block->apartment_number); ?></td>
                                                <td><?php echo htmlspecialchars($block->description); ?></td>
                                                <td>
                                                    <div class="hstack gap-2">
                                                        <a href="index?p=management/blocks/manage&id=<?php echo $enc_id; ?>" class="avatar-text avatar-md">
                                                            <i class="feather-eye"></i>
                                                        </a>
                                                        <a href="index?p=management/blocks/manage&id=<?php echo $enc_id; ?>" class="avatar-text avatar-md">
                                                            <i class="feather-edit"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" data-name="<?php echo $block->block_name ?>" data-id="<?php echo $enc_id ?>" class="avatar-text avatar-md delete-blocks" data-id="<?php echo $enc_id; ?>" data-name="<?php echo $block->block_name; ?>">
                                                            <i class="feather-trash-2"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php
                                            $i++;
                                        endforeach;
                                        ?>
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