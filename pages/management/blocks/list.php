<?php

use Model\BloklarModel;
use App\Helper\Security;
use Model\SitelerModel;
use Model\SitesModel;

$Site = new SitelerModel();


$Blocks = new BloklarModel();
$Bloklar = $Blocks->SiteBloklari($_SESSION['site_id'] ?? null);

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
                                        <tr class="text-center align-middle">
                                            <th>Sıra</th>
                                            <th>Site Adı</th>
                                            <th>Blok Adı</th>
                                            <th style="width: 100px;">B. Bölüm Sayısı</th>
                                            <th style="min-width: 250px;">Açıklama</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        foreach ($Bloklar as $blok):
                                            $enc_id = Security::encrypt($blok->id);
                                            $site = $Site->SiteAdi($blok->site_id);

                                        ?>
                                            <tr class="text-center">
                                                <td><?php echo $i; ?></td>
                                                <td>
                                                <a data-page="sites/manage&id=<?php echo $blok->site_id; ?>" href="#">
                                                    <?php echo $site->site_adi; ?>
                                                </a>
                                                </td>
                                                <td class="text-start"><?php echo htmlspecialchars($blok->blok_adi); ?></td>
                                                <td style="width: 100px;"><?php echo htmlspecialchars($blok->daire_sayisi); ?></td>
                                                <td style="min-width: 250px;"><?php echo !empty($blok->aciklama) ? htmlspecialchars($blok->aciklama) : '-'; ?></td>
                                                <td>
                                                    <div class="hstack gap-2">
                                                        <a href="index?p=management/blocks/manage&id=<?php echo $enc_id; ?>" class="avatar-text avatar-md">
                                                            <i class="feather-eye"></i>
                                                        </a>
                                                        <a href="index?p=management/blocks/manage&id=<?php echo $enc_id; ?>" class="avatar-text avatar-md">
                                                            <i class="feather-edit"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" data-name="<?php echo $blok->blok_adi ?>" data-id="<?php echo $enc_id ?>" class="avatar-text avatar-md delete-blocks" data-id="<?php echo $enc_id; ?>" data-name="<?php echo $blok->blok_adi; ?>">
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