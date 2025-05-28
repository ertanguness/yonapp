<?php 
use App\Helper\Security;
use App\Helper\Date;

use Model\DueModel;
use Model\BorclandirmaModel;


$Borc = new BorclandirmaModel();
$Due = new DueModel();

//borçlandırmaları getir
$borclar = $Borc->all();




?>


<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Borçlandırma</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Borç Listesi</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <a href="index?p=dues/debit/manage" class="btn btn-primary">
            <i class="feather-plus me-2"></i>
            Borçlandırma Yap
        </a>
    </div>
</div>

<div class="main-content">
    <?php
    $title = "Borç Listesi";
    $text = "Tüm borçlandırmaları buradan yönetebilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>

    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover datatables" id="debitTable">
                                    <thead>
                                        <tr class="text-center">
                                            <th>#</th>
                                            <th>Başlık</th>
                                            <th>Tutar</th>
                                            <th style="width:10%">Başlangıç Tarihi</th>
                                            <th style="width:10%">Son Ödeme</th>
                                            <th>Kime</th>
                                            <th>Durum</th>
                                            <th>Açıklama</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        foreach ($borclar as $borc){
                                            $enc_id = Security::encrypt($borc->id);
                                            
                                        ?>
                                            <tr class="text-center">
                                                <td><?php echo $i++; ?></td>
                                                <td><?php echo $Due->getDueName($borc->borc_tipi_id); ?></td>
                                                <td><?php echo $borc->tutar; ?></td>
                                                <td><?php echo Date::dmY($borc->baslangic_tarihi); ?></td>
                                                <td><?php echo Date::dmY($borc->bitis_tarihi); ?></td>
                                                <td>
                                                   TÜM SİTE
                                                </td>
                                                <td>
                                                 
                                                </td>
                                                <td>
                                                    <div class="text-truncate" style="max-width: 200px;">
                                                        <?php echo $borc->aciklama; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="hstack gap-2">
                                                        <a href="index?p=dues/debit/detail&id=<?php echo $enc_id ?>" class="avatar-text avatar-md" title="Görüntüle">
                                                            <i class="feather-eye"></i>
                                                        </a>
                                                        <a href="index?p=dues/debit/manage&id=<?php echo $enc_id; ?>" class="avatar-text avatar-md" 
                                                        
                                                        title="Düzenle">
                                                            <i class="feather-edit"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" class="avatar-text avatar-md delete-debit" title="Sil"
                                                        data-id="<?php echo $enc_id; ?>" data-name="<?php echo $Due->getDueName($borc->borc_tipi_id); ?>"
                                                        >
                                                            <i class="feather-trash-2"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php } ; ?>
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
