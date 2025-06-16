<?php 
use App\Helper\Security;
use App\Helper\Date;
use App\Helper\Helper;
use Model\KisilerModel;

use Model\BorclandirmaDetayModel;

$Kisiler = new KisilerModel();
$BorcDetay = new BorclandirmaDetayModel();



$id = Security::decrypt($_GET['id']);


$borc_detay = $BorcDetay->BorclandirmaDetay($id);


// echo "<pre>";
// print_r($id);
// echo "</pre>";

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
        <a href="index?p=dues/debit/list" class="btn btn-outline-secondary">
            <i class="feather-arrow-left me-2"></i>
            Listeye Dön
        </a>
    </div>
</div>

<div class="main-content ">
    <?php
    $title = "Borçlandırma Detayı";
    $text = "Borçlandırmaya ait detayları buradan yönetebilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>

    <div class="row mb-5">
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
                                            <th>Kişi Adı</th>
                                            <th>Borç Adı</th>
                                            <th>Tutar</th>
                                            <th>Başlangıç Tarihi</th>
                                            <th>Son Ödeme Tarihi</th>
                                            <th>Gecikme Zammı</th>
                                            <th>Açıklama</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        foreach ($borc_detay as $detay){
                                            $enc_id = Security::encrypt($detay->id);
                                            
                                        ?>
                                        <tr class="text-center">
                                            <td><?php echo $i++; ?></td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;">
                                                    <?php echo $Kisiler->KisiAdi($detay->kisi_id) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;">
                                                    <?php echo $detay->borc_adi; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;">
                                                    <?php echo Helper::formattedMoney($detay->tutar); ?> 
                                                </div>
                                            </td>
                                            <td><?php echo Date::dmY($detay->baslangic_tarihi); ?></td>
                                            <td><?php echo Date::dmY($detay->bitis_tarihi); ?></td>
                                            <td>
                                                <?php echo $detay->ceza_orani; ?>
                                            </td>


                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;">
                                                    <?php echo $detay->aciklama; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="hstack gap-2">
                                                    <a href="javascript:void(0);" class="avatar-text avatar-md"
                                                        title="Görüntüle">
                                                        <i class="feather-eye"></i>
                                                    </a>
                                                    <a href="index?p=dues/debit/manage&id=<?php echo $enc_id; ?>"
                                                        class="avatar-text avatar-md" title="Düzenle">
                                                        <i class="feather-edit"></i>
                                                    </a>
                                                    <a href="javascript:void(0);"
                                                        class="avatar-text avatar-md delete-debit" title="Sil"
                                                        data-id="<?php echo $enc_id; ?>"
                                                        data-name="<?php echo $detay->borc_adi; ?>">
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