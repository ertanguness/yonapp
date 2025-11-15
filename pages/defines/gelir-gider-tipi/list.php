<?php



use App\Helper\Helper;
use App\Helper\Date;
use Model\DefinesModel;
use App\Helper\Security;

$defines = new DefinesModel();
$items = $defines->getGelirGiderTipleri();


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

                <a href="/gelir-gider-tipi-ekle" class="btn btn-primary">
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
                                            <th>İşlem Kodu</th>
                                            <th>Türü</th>
                                            <th style="width: 40%;">Adı</th>
                                            <th style="width: 20%;">Açıklama</th>
                                            <th>Eklenme Tarihi</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        foreach ($items as $item) :
                                            $enc_id = Security::encrypt($item->id);
                                            //tip adına göre badge yap
                                        ?>

                                            </td>
                                            <tr>
                                                <td><?php echo $i; ?></td>
                                                <td><?php echo $item->islem_kodu; ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $item->type_name == 'Gelir' ? 'success' : ($item->type_name == 'Gider' ? 'danger' : 'secondary'); ?>">
                                                        <?php echo $item->type_name; ?>
                                                    </span>

                                                </td>
                                                <td><?php echo $item->define_name; ?></td>
                                                <td><?php echo $item->description; ?></td>
                                                <td><?php echo Date::dmY($item->created_at); ?></td>

                                                <td>
                                                    <div class="hstack gap-2 ">
                                                        <a href="/gelir-gider-tipi-duzenle/<?php echo $enc_id ?>" class="avatar-text avatar-md">
                                                            <i class="feather-edit"></i>
                                                        </a>
                                                        <a href="javascript:void(0);"
                                                            class="avatar-text avatar-md gelir-gider-tipi-sil"
                                                            data-id="<?php echo $enc_id ?>">
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

<script>
    $(function() {
        /*  */
    });
</script>