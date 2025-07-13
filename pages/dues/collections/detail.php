<?php

use App\Helper\Security;
use App\Helper\Date;
use App\Helper\Helper;
use App\Services\Gate;

use Model\BorclandirmaModel;
use Model\TahsilatDetayModel;

$BorclandirmaModel = new BorclandirmaModel();
$TahsilatDetayModel = new TahsilatDetayModel();

$id = Security::decrypt($_GET['id'] ?? 0) ?? 0;


$tahsilat_detay = $TahsilatDetayModel->getTahsilatlarByBorclandirmaId($id);
$borc = $BorclandirmaModel->findByID($_SESSION["site_id"],$id);

$toplam_borc = $borc->toplam_borc ?? 0;
$toplam_tahsilat = $borc->toplam_tahsilat ?? 0;

$odeme_yuzdesi = $toplam_borc > 0 ? ($toplam_tahsilat / $toplam_borc) * 100 : 0;
$odeme_yuzdesi = number_format($odeme_yuzdesi, 2, ',', '.'); // Yüzdeyi formatlamak için


//Daha sonra burayı aktif edeceğiz
// Gate::authorizeOrDie('debit_detail');


?>


<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Borçlandırma</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Borç Listesi</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <a href="index?p=dues/debit/list" class="btn btn-outline-secondary me-2">
            <i class="feather-arrow-left me-2"></i>
            Listeye Dön
        </a>
    </div>
</div>

<div class="main-content">
    <?php
    $title = "Tahsilat Detayları";
    $text = "İlgili borçlandırmaya ait tahsilat detaylarını görebilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>

    <div class="row mb-5">
        <div class="container-xl">

            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="subscription-plan px-4 pt-4">
                            <div
                                class="p-4 mb-4 d-xxl-flex d-xl-block d-md-flex align-items-center justify-content-between gap-4 border border-dashed border-gray-5 rounded-1">
                                <div>
                                    <div class="fs-14 fw-bold text-dark mb-1">
                                        <?php echo $borc->aciklama; ?>
                                        <a href="javascript:void(0);" class="badge bg-primary text-white ms-2 text-capitalize">
                                            
                                        <?php echo $borc->tekrarlama_sikligi ?>        
                                    </a>
                                    </div>
                                    <div class="fs-12 text-muted">A simple start for everyone</div>
                                </div>
                                <div class="my-3 my-xxl-0 my-md-3 my-md-0">
                                    <div class="fs-20 text-dark"><span class="fw-bold">

                                            <?php echo Helper::formattedMoney($borc->toplam_tahsilat); ?>
                                        </span> / <em
                                            class="fs-20 text-dark"><?php echo Helper::formattedMoney($borc->toplam_borc); ?></em>
                                    </div>
                                    <div class="fs-12 text-muted mt-1">

                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="w-100">
                                                <span class="fs-12 text-dark">
                                                    % <?php echo $odeme_yuzdesi; ?> Ödendi
                                                </span>
                                            </div>
                                        </div>
                                        <div class="progress mt-2 ht-3">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $odeme_yuzdesi?>%">
                                            </div>
                                        </div>

                                    </div>
                                </div>
                           
                            </div>

                        </div>
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover datatables" id="debitTable">
                                    <thead>
                                        <tr class="text-center">
                                            <th>#</th>
                                            <th>Borç Adı</th>
                                            <th>Borç Açıklama</th>
                                            <th>Tutar</th>
                                            <th>Ödeme Tarihi</th>
                                            <th>Kime</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        foreach ($tahsilat_detay as $detay) {
                                            $enc_id = Security::encrypt($detay->id);
                                        ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>

                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;">
                                                    <?php echo $detay->borc_adi; ?>
                                                </div>
                                            </td>

                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;">
                                                    <?php echo $detay->borc_aciklama; ?>
                                                </div>
                                            </td>

                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;">
                                                    <?php echo Helper::formattedMoney($detay->odenen_tutar); ?>
                                                </div>
                                            </td>

                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;">
                                                    <?php echo Date::dmY($detay->kayit_tarihi); ?>
                                                </div>
                                            </td>

                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;">
                                                    <?php echo $detay->adi_soyadi; ?>
                                                </div>
                                            </td>

                                            <td>
                                                <div class="hstack gap-2">
                                                    <a href="index?p=dues/debit/detail&id=<?php echo $enc_id ?>"
                                                        class="avatar-text avatar-md" title="Görüntüle">
                                                        <i class="feather-eye"></i>
                                                    </a>
                                                    <a href="index?p=dues/debit/manage&id=<?php echo $enc_id; ?>"
                                                        class="avatar-text avatar-md" title="Düzenle">
                                                        <i class="feather-edit"></i>
                                                    </a>
                                                    <a href="javascript:void(0);"
                                                        class="avatar-text avatar-md delete-debit" title="Sil"
                                                        data-id="<?php echo $enc_id; ?>">
                                                        <i class="feather-trash-2"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php }; ?>
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