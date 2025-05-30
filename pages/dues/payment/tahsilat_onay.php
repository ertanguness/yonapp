<?php 

use App\Helper\Security;
use App\Helper\Helper;
use App\Helper\Due;
use Model\DairelerModel;
use Model\TahsilatOnayModel;
use Model\KisilerModel;

$DueHelper = new Due();
$Daire = new DairelerModel();
$Kisi = new KisilerModel();

$TahsilatOnay = new TahsilatOnayModel();

$bekleyen_tahsilatlar = $TahsilatOnay->BekleyenTahsilatlar($_SESSION['site_id'] );

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

                   <a href="index?p=dues/payment/upload-from-xls" class="btn btn-outline-primary">
                    <i class="feather-file-plus me-2"></i>Toplu Onay
                </a>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <?php
    $title = "Tahsilat Onaylama";
    $text = "Bu sayfada yüklenen tahsilatlardan kategori bazlı ödeme onayı yapabilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>

    <div class="row mb-5">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card widget-tasks-content">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive tasks-items-wrapper m-3">
                                <table class="table table-hover datatables " id="tahsilatOnayTable">
                                    <thead>
                                        <tr>
                                            <th>Sıra</th>
                                            <th>Referans No</th>
                                            <th style="width:30%">Açıklama</th>
                                            <th>Ödenen Tutarı</th>
                                            <th>İşlenen Tutar</th>
                                            <th>Kalan Tutar</th>
                                            <th>İşlenecek Tutar</th>
                                            <th>Tahsilat Tipi</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                       
                                        foreach ($bekleyen_tahsilatlar as $index => $onay):
                                            $enc_id = Security::encrypt($onay->id);
                                            $tahsilat_tutari =$onay->tutar ?? 0;
                                            $islenen_tutar = $TahsilatOnay->OnaylanmisTahsilatToplami($onay->id) ?? 0;
                                            $kalan_tutar = $tahsilat_tutari - $islenen_tutar;


                                        ?>
                                        <tr>
                                            <td><?php echo $index +1 ?></td>
                                            <td style="width:7%;">
                                                <?php echo Helper::short($onay->referans_no,11); ?>
                                            </td>

                                            <td>
                                                <a href="javascript:void(0);"><?php echo $Daire->DaireKodu($onay->daire_id); ?><span
                                                        class="fs-12 fw-normal text-muted">
                                                        / <?= $Kisi->KisiAdi($onay->kisi_id) ?> /
                                                        <?php echo $onay->islem_tarihi; ?></span> </a>
                                                <p class="fs-12 text-muted mt-2 text-truncate-1-line tasks-sort-desc">
                                                    <?php echo $onay->aciklama ?></p>
                                                <div class="tasks-list-action d-flex align-items-center gap-3">
                                                    <a href="javascript:void(0);" data-id="<?php echo $enc_id; ?>"
                                                        class="detay-goruntule">Görüntüle</a>
                                                    <span>|</span>
                                                    <a href="javascript:void(0);">Düzenle</a>
                                                    <span>|</span>
                                                    <a href="javascript:void(0);">Eşleşmeyen Havuzuna Gönder</a>
                                                    <span>|</span>
                                                    <a href="javascript:void(0);" class="text-danger">Sil</a>
                                                </div>
                                            </td>


                                            <td class="text-right">
                                                <?= Helper::formattedMoney($tahsilat_tutari) ?>
                                            </td>
                                            <td class="text-right"><?php echo
                                                Helper::formattedMoney($islenen_tutar) ?? 0 ?>
                                            </td>

                                            
                                            <td class="text-right"><?php echo Helper::formattedMoney($kalan_tutar)  ?> </td>
                                            <td style="width:150px;">
                                                <input type="text" class="form-control islenecek-tutar money"
                                                    name="islenecek_tutar[<?= $onay->id ?>]" 
                                                    id="islenecek_tutar<?= $index ?>"
                                                    value="<?php echo Helper::formattedMoneyWithoutCurrency($kalan_tutar ?? 0) ?>" />

                                            </td>
                                            <td>
                                                <?php echo $DueHelper->getDuesSelect("borc_baslik". $index) ?>

                                            </td>
                                            <td style="width:5%">
                                                <!-- kalan tutar 0 dan büyükse göster -->
                                                <?php if ($kalan_tutar > 0): ?>
                                                <div class="hstack gap-2  ">
                                                    <a href="#" data-id="<?php echo $enc_id ?>"
                                                        class="avatar-text avatar-md tahsilat-onayla bg-success">
                                                        <i class="feather-check text-white"></i>
                                                    </a>
                                                    
                                                </div>
                                                <?php endif ?>
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
<div class="modal fade" id="tahsilatDetayModal" tabindex="-1" data-bs-keyboard="false" role="dialog">
    <div class="modal-dialog modal-dialog-scrollable modal-xl modal-dialog-centered" role="document">
        <div class="modal-content tahsilat-detay">

            <div class="modal-footer">
                <button id="btn-n-save" class="float-left btn btn-success">Save</button>
                <button class="btn btn-danger" data-dismiss="modal">Discard</button>
                <button id="btn-n-add" class="btn btn-success" disabled="disabled">Add Note</button>
            </div>
        </div>
    </div>
</div>

<script>

</script>