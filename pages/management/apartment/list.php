<?php
use App\Helper\Security;
use Model\ApartmentModel;
use Model\BlockModel;

$Apartment = new ApartmentModel();
$Block = new BlockModel();

$apartments = $Apartment->getApartmentBySite($_SESSION['site_id'] ?? null);
?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Yönetim</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Daireler</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Geri</span>
                </a>
            </div>
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <?php
                require_once 'pages/components/search.php';
                require_once 'pages/components/download.php';
                ?>
                <a href="#" class="btn btn-primary route-link" data-page="management/apartment/manage">
                    <i class="feather-plus me-2"></i>
                    <span>Yeni Daire Ekle</span>
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
    $title = "Daireler Listesi!";
    $text = "Seçili siteye ait daireleri görüntüleyip ekleme, düzenleme, silme işlemlerini yapabilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover datatables" id="apartmentsList">
                                    <thead>
                                        <tr class="text-center">
                                            <th>Sıra</th>
                                            <th>Blok Adı</th>
                                            <th>Daire No</th>
                                            <th>Kat Maliki</th>
                                            <th>Kiracı</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        foreach ($apartments as $apartment):
                                            $enc_id = Security::encrypt($apartment->id);
                                            $block = $Block->getBlockByID($apartment->blok_id);
                                        ?>
                                            <tr class="text-center">
                                                <td><?php echo $i; ?></td>
                                                <td>
                                                    <a data-page="management/blocks/manage&id=<?php echo $block->id ?? 0; ?>" href="#">
                                                        <?php echo htmlspecialchars($block->block_name ?? ''); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo htmlspecialchars($apartment->daire_no); ?></td>
                                                <td></td>
                                                <td></td>
                                                <td>
                                                    <div class="hstack gap-2">
                                                        <a href="index?p=management/apartments/manage&id=<?php echo $enc_id; ?>" class="avatar-text avatar-md">
                                                            <i class="feather-eye"></i>
                                                        </a>
                                                        <a href="index?p=management/apartment/manage&id=<?php echo $enc_id; ?>" class="avatar-text avatar-md">
                                                            <i class="feather-edit"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" data-name="Daire <?php echo $apartment->apartment_no ?>" data-id="<?php echo $enc_id ?>" class="avatar-text avatar-md delete-apartment">
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
