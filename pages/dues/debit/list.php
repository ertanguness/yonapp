<?php 
use Model\DebitModel;
use Model\DueModel;
use App\Helper\Security;
use App\Helper\Date;

$Debit = new DebitModel();
$Due = new DueModel();

//borçlandırmaları getir
$debits = $Debit->getDebits();




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
            Yeni Borç Ekle
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
                                            <th>Son Ödeme</th>
                                            <th>Kime</th>
                                            <th>Durum</th>
                                            <th>Açıklama</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        foreach ($debits as $debt){
                                            $enc_id = Security::encrypt($debt->id);
                                            
                                        ?>
                                            <tr class="text-center">
                                                <td><?php echo $i++; ?></td>
                                                <td><?php echo $Due->getDueName($debt->due_id); ?></td>
                                                <td><?php echo $debt->amount; ?></td>
                                                <td><?php echo Date::dmY($debt->end_date); ?></td>
                                                <td>
                                                   
                                                </td>
                                                <td>
                                                 
                                                </td>
                                                <td>
                                                    <div class="text-truncate" style="max-width: 200px;">
                                                        <?php echo $debt->description; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="hstack gap-2">
                                                        <a href="javascript:void(0);" class="avatar-text avatar-md" title="Görüntüle">
                                                            <i class="feather-eye"></i>
                                                        </a>
                                                        <a href="index?p=dues/debit/manage&id=<?php echo $enc_id; ?>" class="avatar-text avatar-md" 
                                                        
                                                        title="Düzenle">
                                                            <i class="feather-edit"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" class="avatar-text avatar-md delete-debit" title="Sil"
                                                        data-id="<?php echo $enc_id; ?>" data-name="<?php echo $debt->due_id; ?>"
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
