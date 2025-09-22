<?php

use Model\DueModel;
use App\Helper\Security;
use App\Helper\Helper;
use App\Helper\Date;



$Dues = new DueModel();

$dues = $Dues->getDues();

?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Aidat Listesi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Aidat Yönetimi</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <a href="aidat-turu-tanimlama" class="btn btn-primary route-link">
                <i class="feather-plus me-2"></i>
                <span>Yeni Aidat Tanımla</span>
            </a>
        </div>
    </div>
</div>

<div class="main-content">
    <?php
    $title = "Aidat Yönetimi!";
    $text = "Tanımlanan aidatları listeleyebilir, düzenleyebilir veya silebilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>

    <div class="row mb-5">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover datatables" id="duesTable">
                                    <thead>
                                        <tr>
                                            <th style="width:7%">#</th>
                                            <th>Blok</th>
                                            <th>Aidat Adı</th>
                                            <th>Aidat Tutarı</th>
                                            <th>Başlangıç/Bitiş Tarihi</th>
                                            <th>Period</th>
                                            <th>Durum</th>
                                            <th style="width:7%">İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($dues as $key => $due) :
                                            $enc_id = Security::encrypt($due->id);
                                            //$page = Security::encrypt("dues/dues-defines/manage");
                                        ?>
                                            <tr>
                                                <td class="text-center"><?php echo $key + 1; ?></td>
                                                <td><?php echo $due->block_id == 0 ? 'TÜM SİTE' : ''; ?></td>
                                                <td>
                                                    <?php echo $due->due_name; ?>
                                                    <div>
                                                        <?php if ($due->description) : ?>
                                                            <span class="hstack gap-1 fs-11 fw-normal text-muted"><?php echo $due->description; ?></span>
                                                        <?php endif; ?>

                                                    </div>
                                                </td>
                                                <td><?php echo $due->amount; ?> ₺</td>
                                                <td>
                                                <div class="d-flex align-items-center gap-3">
                                                      
                                                        <a href="javascript:void(0);" class="text-muted">
                                                            <span class="d-block"><?php echo $due->start_date; ?></span>
                                                            <span class="d-block"><?php echo $due->end_date; ?></span>
                                                        </a>
                                                    </div>
                                                   
                                                </td>
                                                <td><?php echo $due->period_text; ?> </td>
                                                <td>
                                                    <?php echo Helper::getState($due->state) ?>
                                                </td>
                                                <td>
                                                    <div class="hstack gap-2 ">
                                                        <a href="index?p=dues/dues-defines/detail&id=<?php echo $enc_id ?>" class="avatar-text avatar-md">
                                                            <i class="feather-eye"></i>
                                                        </a>
                                                        <a href="aidat-turu-duzenle/<?php echo $enc_id ?>" class="avatar-text avatar-md">
                                                            <i class="feather-edit"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" data-name="<?php echo $due->due_name ?>" data-id="<?php echo $enc_id ?>" class="avatar-text avatar-md delete-dues" data-id="<?php echo $enc_id; ?>" data-name="<?php echo $due->due_name; ?>">
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