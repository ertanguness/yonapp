<?php 

use App\Helper\Security;

use Model\BorclandirmaDetayModel;

$BorcDetayModel = new BorclandirmaDetayModel();
$borc_listesi = $BorcDetayModel->gruplanmisBorcListesi();



?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Site Borç Listesi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Borç Listesi</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>

                </a>
            </div>
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">

                <a href="index?p=dues/payment/tahsilat_onay" class="btn btn-outline-success">
                    <i class="feather-check me-2"></i>Onay Bekleyen Ödemeler
                </a>
                <a href="index?p=dues/payment/upload-from-xls" class="btn btn-outline-secondary">
                    <i class="feather-copy me-2"></i>Eşleşmeyen Ödemeler
                </a>
                <a href="index?p=dues/payment/upload-from-xls" class="btn btn-outline-primary">
                    <i class="feather-file-plus me-2"></i>Excelden Ödeme Yükle
                </a>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <?php
    $title = "Blok ve Daireye Göre Toplam Aidat Borç Takibi";
    $text = "Bu sayfada blok ve daire bazında toplam aidat borçlarını takip edebilir, detay butonu ile borç detaylarına ulaşabilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>

    <div class="row mb-5">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover datatables" id="debtListTable">
                                    <thead>
                                        <tr>
                                        <th class="wd-30 no-sorting" tabindex="0" aria-controls="customerList"  style="width: 40px;">
                                                        <div class="btn-group mb-1">
                                                            <div class="custom-control custom-checkbox ms-1">
                                                                <input type="checkbox" class="custom-control-input" id="checkAllCustomer">
                                                                <label class="custom-control-label" for="checkAllCustomer"></label>
                                                            </div>
                                                        </div>
                                                    </th>
                                            <th>Blok Adı</th>
                                            <th>Ad Soyad</th>
                                            <th>Borç Tutarı</th>
                                            <th>Ödenen</th>
                                            <th>Kalan Borç</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                       
                                        foreach ($borc_listesi as $index => $borc):
                                            $enc_id = Security::encrypt($borc->borc_id);
                                        ?>
                                        <tr>

                                            <td>
                                            <div class="item-checkbox ms-1">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input checkbox" id="checkBox_1">
                                                            <label class="custom-control-label" for="checkBox_1"></label>
                                                        </div>
                                                    </div>
                                            </td>


                                            <td><?= $borc->blok_adi ?> ...</td>
                                            <td><?= $borc->kisi_adi ?></td>
                                            <td><?= $borc->toplam_borc   ?> TL</td>
                                            <td><?= $borc->odenen_borc ?? 0 ?> TL</td>
                                            <td><?= $borc->kalan_borc ?? 0 ?> TL</td>
                                            <td style="width:10%">
                                                <div class="hstack gap-2 ">
                                                    <a href="index?p=dues/payment/detail&id=<?php echo $enc_id ?>"
                                                        class="avatar-text avatar-md">
                                                        <i class="feather-eye"></i>
                                                    </a>
                                                    <a href="index?p=dues/dues-defines/manage&id=<?php echo $enc_id ?>"
                                                        class="avatar-text avatar-md">
                                                        <i class="feather-edit"></i>
                                                    </a>
                                                    <a href="javascript:void(0);"
                                                        data-name="<?php echo $borc->borc_adi ?>"
                                                        data-id="<?php echo $enc_id ?>"
                                                        class="avatar-text avatar-md delete-dues">
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