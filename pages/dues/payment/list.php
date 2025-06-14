<?php

use App\Helper\Security;
use App\Helper\Helper;

use Model\BloklarModel;
use Model\DairelerModel;
use Model\KisilerModel;
use Model\BorclandirmaDetayModel;
use Model\TahsilatModel;

$Blok = new BloklarModel();
$Daire = new DairelerModel();
$KisiModel = new KisilerModel();
$BorcDetay = new BorclandirmaDetayModel();
$Tahsilat = new TahsilatModel();


$kisiler = $KisiModel->SiteKisileri($_SESSION['site_id']);


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
                                            <th class="wd-30 no-sorting" tabindex="0" aria-controls="customerList" style="width: 40px;">
                                                <div class="btn-group mb-1">
                                                    <div class="custom-control custom-checkbox ms-1">
                                                        <input type="checkbox" class="custom-control-input" id="checkAllCustomer">
                                                        <label class="custom-control-label" for="checkAllCustomer"></label>
                                                    </div>
                                                </div>
                                            </th>
                                            <th>Daire Adı</th>
                                            <th>Ad Soyad</th>
                                            <th class="text-end" style="width:11%">Borç Tutarı</th>
                                            <th class="text-end" style="width:11%">Ödenen</th>
                                            <th class="text-end" style="width:11%">BAKİYE</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php

                                        foreach ($kisiler as $index => $kisi):
                                            $enc_id = Security::encrypt($kisi->id);
                                            $toplam_borc = -$BorcDetay->KisiToplamBorc($kisi->id);
                                            $toplam_tahsilat = $Tahsilat->KisiToplamTahsilat($kisi->id);
                                            $kalan_borc =  $toplam_borc + $toplam_tahsilat;
                                            $tahsilat_color = $toplam_tahsilat > 0 ? 'success' : 'secondary';
                                            //$color = $kalan_borc < 0 ? 'danger' : 'success';

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


                                                <td><?= $Daire->DaireKodu($kisi->daire_id) ?> </td>
                                                <td><?= $kisi->adi_soyadi ?></td>
                                                <td class="text-end">
                                                    <i class="feather-trending-down fw-bold text-danger"></i>
                                                    
                                                    <?= Helper::formattedMoney($toplam_borc)   ?>
                                                </td>
                                                <td class="text-end"><?= Helper::formattedMoney($toplam_tahsilat) ?></td>
                                                <td class="text-end"><?= Helper::formattedMoney($kalan_borc) ?></td>
                                                <td style="width:5%;">
                                                    <div class="hstack gap-2 ">
                                                        <a href="#" data-id="<?php echo $enc_id ?>" 
                                                                    class="avatar-text avatar-md kisi-borc-detay">
                                                            <i class="feather-eye"></i>
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
<div class="modal fade" id="kisiBorcDetay" tabindex="-1" data-bs-keyboard="false" role="dialog">
    <div class="modal-dialog modal-dialog-scrollable modal-xl modal-dialog-centered" role="document">
        <div class="modal-content borc-detay">

            <div class="modal-footer">
                <button id="btn-n-save" class="float-left btn btn-success">Save</button>
                <button class="btn btn-danger" data-dismiss="modal">Discard</button>
                <button id="btn-n-add" class="btn btn-success" disabled="disabled">Add Note</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).on('click', '.kisi-borc-detay', function() {
    var kisiId = $(this).data('id');

    $.get("pages/dues/payment/detail.php", {
        kisi_id: kisiId
    }, function(data) {
        // Verileri tabloya ekle
        $('.borc-detay').html(data);
        // Modal'ı göster
        $('#kisiBorcDetay').modal('show');
    });
});

</script>