<?php
    use App\Helper\Security;
    use Model\DefinesModel;

    $Defines = new DefinesModel();
    $apartmentTypes = $Defines->getAllByApartmentType(3); // daire tipleri filtreleniyor
    ?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Tanımlamalar</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Daire Tipi Tanımlama</li>
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
              
                <a href="/daire-turu-ekle" class="btn btn-primary route-link" >
                    <i class="feather-plus me-2"></i>
                    <span>Yeni Daire Tipi</span>
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
    $title = "Daire Tipi Tanımlama!";
    $text = "Siteniz için daire tipleri tanımlayabilir ve projelerinizi kolaylıkla yönetebilirsiniz!";
    require_once 'pages/components/alert.php';
    ?>
   <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover datatables" id="apartmentTypesList">
                                    <thead>
                                        <tr class="text-center">
                                            <th>Sıra</th>
                                            <th>Daire Tipi</th>
                                            <th>Açıklama</th>
                                            <th>Eklenme Tarihi</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        foreach ($apartmentTypes as $type):
                                            $enc_id = Security::encrypt($type->id);
                                        ?>
                                            <tr class="text-center">
                                                <td><?= $i ?></td>
                                                <td><?= $type->define_name ?></td>
                                                <td><?= $type->description ?></td>
                                                <td><?= $type->create_at ?></td>
                                                <td>
                                                    <div class="hstack gap-2 justify-content-center">
                                                        <a href="daire-turu-duzenle/<?= $enc_id ?>" class="avatar-text avatar-md">
                                                            <i class="feather-edit"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" data-name="<?php echo $type->define_name; ?>" data-id="<?php echo $enc_id; ?>" class="avatar-text avatar-md delete-apartment-type" data-id="<?php echo $enc_id; ?>" data-name="<?php echo $type->define_name; ?>">
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
</div>
