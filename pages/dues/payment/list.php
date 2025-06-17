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


$kisiler = $KisiModel->SiteKisiBorcOzet($_SESSION['site_id']);


?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Site Borç Listesi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home">Ana Sayfa</a></li>
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
                                <table class="table table-hover datatables" id="tahsilatTable">
                                    <thead>
                                        <tr>
                                            <th class="wd-30 no-sorting" style="width: 40px;">
                                              Sıra
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
                                            $enc_id = Security::encrypt($kisi->kisi_id);
                                            $tahsilat_color = 'secondary';
                                            //$color = $kalan_borc < 0 ? 'danger' : 'success';

                                        ?>
                                            <tr>

                                                <td><?php echo $index + 1 ?></td>
                                                <td><?= $Daire->DaireKodu($kisi->daire_id) ?> </td>
                                                <td><?= $kisi->adi_soyadi ?></td>
                                                <td class="text-end">
                                                    <i class="feather-trending-down fw-bold text-danger"></i>

                                                    <?= Helper::formattedMoney($kisi->toplam_borc)   ?>
                                                </td>
                                                <td class="text-end"><?= Helper::formattedMoney($kisi->toplam_tahsilat) ?></td>
                                                <td class="text-end"><?= Helper::formattedMoney($kisi->bakiye) ?></td>
                                                <td style="width:5%;">
                                                    <div class="hstack gap-2 ">
                                                        <a href="javascript:void(0);" data-id="<?php echo $enc_id ?>"
                                                            class="avatar-text avatar-md kisi-borc-detay">
                                                            <i class="feather-eye"></i>
                                                        </a>

                                                        <a href="javascript:void(0);" data-id="<?php echo $enc_id ?>" 
                                                                    title="Tahsilat Gir" 
                                                                    data-kisi-id="<?php echo Security::encrypt($kisi->kisi_id) ?>"
                                                            class="avatar-text avatar-md tahsilat-gir">
                                                            <i class="bi bi-credit-card-2-front"></i>
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


        </div>
    </div>
</div>
<div class="modal fade" id="tahsilatGir" tabindex="-1" data-bs-keyboard="false" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitleId">Tahsilat Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body tahsilat-modal-body">

            </div>
            <div class="modal-footer">
                <a class="btn btn-outline-secondary" data-bs-dismiss="modal">Vazgeç</a>
                <a href="javascript:void(0);" class="btn btn-outline-primary" id="tahsilatKaydet">
                    <i class="feather-save me-2"></i>Kaydet
                </a>
            </div>
        </div>
    </div>
</div>
<script>
    var kisiId;
    var row;
    $(document).on('click', '.kisi-borc-detay', function() {
        var id = $(this).data('id');
        kisiId = $(this).data('id');

        $.get("pages/dues/payment/detail.php", {
            id: id,
            kisi_id: kisiId
        }, function(data) {
            // Verileri tabloya ekle
            $('.borc-detay').html(data);
            // Modal'ı göster
            $('#kisiBorcDetay').modal('show');
        });
    });

    $(document).on('click', '.tahsilat-gir', function() {
        kisiId = $(this).data('kisi-id');
        table = $('#tahsilatTable').DataTable();
        row = table.row($(this).closest('tr')); 

        $.get("pages/dues/payment/tahsilat_gir_modal.php", {
            kisi_id: kisiId
        }, function(data) {
            // Verileri tabloya ekle
            $('.tahsilat-modal-body').html(data);
            // Modal'ı göster
            $('#tahsilatGir').modal('show');
            $(".select2").select2({
                placeholder: "Kasa Seçiniz",
                dropdownParent: $('#tahsilatGir'),
            });

            $("#tahsilat_turu").select2({
                tags: true,
                dropdownParent: $('#tahsilatGir'),
            });

            $(".flatpickr").flatpickr({
                dateFormat: "d.m.Y",
                locale: "tr", // locale for this instance only
            });
        });

        // Modal'ı göster
    });
</script>