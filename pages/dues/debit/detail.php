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
        <div class="btn-list d-flex gap-2">

            <a href="index?p=dues/debit/list" class="btn btn-outline-secondary">
                <i class="feather-arrow-left me-2"></i>
                Listeye Dön
            </a>
            
            <a href="index?p=dues/debit/single-manage&id=<?php echo $_GET['id'] ?>" class="btn btn-primary">
                <i class="feather-plus me-2"></i>
                Yeni Ekle
            </a>
        </div>
    </div>
</div>

<div class="main-content ">
    <?php
    $title = "Borçlandırma Detayı";
    $text = "Var olan borçlandırmaya yeni bir borç ekleyebilir, kayıtların borç bilgilerini düzenleyebiir veya borçlandırmadan bazı kayıtları silebilirisiniz.";
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
                                            <th>Oturum Türü</th>
                                            <th>Kişi Adı</th>
                                            <th>Borç Adı</th>
                                            <th>Tutar</th>
                                            <th>Başlangıç Tarihi</th>
                                            <th>Son Ödeme Tarihi</th>
                                            <th>Gecikme Oranı%</th>
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
                                            <td><?php echo $detay->id; ?></td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;">
                                                    <?php echo $detay->uyelik_tipi; ?>
                                                </div>  
                                            </td>

                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;">
                                                    <?php echo $detay->adi_soyadi ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;">
                                                    <?php echo $detay->borc_adi; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;">
                                                    <?php echo Helper::formattedMoney($detay->tutar ?? 0); ?> 
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
                                                  
                                                    <a href="index?p=dues/debit/single-manage&id=<?php echo $enc_id; ?>"
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