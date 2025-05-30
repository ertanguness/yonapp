<?php

use App\Helper\Security;
use Model\SitelerModel;
use Model\BloklarModel;

$Bloklar = new BloklarModel();
$Siteler = new SitelerModel();
$Sitem = $Siteler->Sitelerim();

$blokSayisi = $Bloklar->SitedekiBloksayisi($_SESSION['site_id'] ?? null);

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

                                <table class="table table-hover datatables" id="SitelerList">
                                    <thead>
                                        <tr class="text-center">
                                            <th>Sıra</th>
                                            <th>Site Adı</th>
                                            <th>Blok Sayısı</th>
                                            <th>Bağımsız Bölüm Sayısı</th>
                                            <th>Telefon</th>
                                            <th>Adres</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        foreach ($Sitem as $Siteler):
                                            $enc_id = Security::encrypt($Siteler->id);
                                            $blokSayisi = $Bloklar->SitedekiBloksayisi($Siteler->id ?? null);
                                            $daireSayisi = $Bloklar->SitedekiDaireSayisi($Siteler->id ?? null);

                                        ?>
                                            <tr>
                                                <td><?php echo $i; ?></td>
                                                <td><a data-page="Siteler/manage&id=<?php echo $id ?>" href="#">
                                                        <?php echo $Siteler->site_adi; ?>
                                                    </a>
                                                </td>
                                                <td class="text-start"><?php echo $blokSayisi; ?></td>
                                                <td><?php echo $daireSayisi; ?></td>
                                                <td><?php echo $Siteler->telefon; ?></td>
                                                <td><?php echo $Siteler->tam_adres; ?></td>
                                                <td>
                                                    <div class="hstack gap-2 ">
                                                        <a href="index?p=management/Siteler/manage&id=<?php echo $enc_id ?>" class="avatar-text avatar-md">
                                                            <i class="feather-eye"></i>
                                                        </a>
                                                        <a href="index?p=management/Siteler/manage&id=<?php echo $enc_id ?>" class="avatar-text avatar-md">
                                                            <i class="feather-edit"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" data-name="<?php echo $Siteler->site_adi?>" data-id="<?php echo $enc_id ?>" class="avatar-text avatar-md delete-Siteler" data-id="<?php echo $enc_id; ?>" data-name="<?php echo $Siteler->site_adi; ?>">
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