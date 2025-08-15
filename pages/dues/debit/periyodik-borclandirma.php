<?php
use App\Helper\Security;
use App\Helper\Date;
use App\Helper\Helper;

use Model\DueModel;
use Model\BorclandirmaModel;
use Model\BorclandirmaDetayModel;
use Model\PeriyodikBorclandirmaModel;

$Borc = new PeriyodikBorclandirmaModel();
$BorcDetay = new BorclandirmaDetayModel();
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
            <li class="breadcrumb-item"><a href="index?p=home">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Borç Listesi</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <a href="index?p=dues/debit/periyodik-borc-manage" class="btn btn-primary">
            <i class="feather-plus me-2"></i>
           Yeni Borçlandırma
        </a>
    </div>
</div>

<div class="main-content">
    <?php
    $title = "Borç Listesi";
    $text = "Tüm borçlandırmaları buradan yönetebilirsiniz.";
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
                                            <th>Başlık</th>
                                            <th>Tutar</th>
                                            <th style="width:10%">Başlangıç Tarihi</th>
                                            <th style="width:10%">Son Ödeme</th>
                                            <th>Kime</th>
                                            <th>Toplam Borç Miktarı</th>
                                            <th>Ödenen Tutar</th>
                                            <th>Açıklama</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        foreach ($borclar as $borc){
                                            $enc_id = Security::encrypt($borc->id);
                                            //Ödenme yüzdesini hesapla
                                            $odeme_yuzdesi = 0;
                                            if ($borc->toplam_borc > 0) {
                                                $odeme_yuzdesi = round(($borc->toplam_tahsilat / $borc->toplam_borc) * 100, 2);

                                                
                                            }
                                          



                                        ?>
                                        <tr class="">
                                            <td><?php echo $i++; ?></td>
                                            <td><?php echo $Due->getDueName($borc->borc_tipi_id); ?></td>
                                            <td><?php echo $borc->tutar; ?></td>
                                            <td><?php echo Date::dmY($borc->baslangic_tarihi); ?></td>
                                            <td><?php echo Date::dmY($borc->bitis_tarihi); ?></td>

                                            <td>
                                                <?php
                                                   $borc_tipi = $borc->hedef_tipi;
                                                   $borclandirma_tipi='';
                                                    $borclandirma_detay = '';
                                                   
                                                   if($borc_tipi == 'all'){
                                                      $borclandirma_tipi = "Tüm Site";
                                                      $borclandirma_detay =  "Site Üyeleri";
                                                    }elseif($borc_tipi == 'block'){
                                                      $borclandirma_tipi = "Blok";
                                                      $borclandirma_detay =  $BorcDetay->BorclandirilmisBlokIsimleri($borc->id);
                                                   }elseif($borc_tipi == 'person'){
                                                      $borclandirma_tipi = "Kişi";
                                                      $borclandirma_detay =  'Kişilere göre borçlandırma yapıldı';
                                                    }elseif($borc_tipi == 'dairetipi'){
                                                        $borclandirma_tipi = "Daire Tipi";
                                                        $borclandirma_detay =  $BorcDetay->BorclandirilmisDaireTipleri($borc->id);
                                                    }
                                                   ?>
                                                <a href="javascript:void(0)"
                                                    class="hstack gap-3 text-decoration-none text-dark text-left">
                                                    <!-- <div class="avatar-image avatar-md bg-warning text-white">N</div> -->
                                                    <div>
                                                        <span
                                                            class="text-truncate-1-line text-left"><?php echo $borclandirma_tipi ?></span>
                                                        <small
                                                            class="fs-12 fw-normal text-muted"><?php echo $borclandirma_detay; ?></small>
                                                    </div>
                                                </a>
                                            </td>
                                            <td>
                                                <?php echo Helper::formattedMoney($borc->toplam_borc); ?>
                                            </td>
                                            <td>
                                                <div class="text-truncate cursor-pointer" data-bs-toggle="tooltip"
                                                    data-bs-trigger="hover" title=""
                                                    data-bs-original-title="Yapılan tahsilat görüntülemek için tıklayın!"
                                                    style="max-width: 200px;">

                                                    <div class="mb-2">
                                                        <div class="fs-14 fw-bold text-dark">
                                                            <a href="index?p=dues/collections/detail&id=<?php echo $enc_id?>">
                                                                <?php echo Helper::formattedMoney($borc->toplam_tahsilat); ?>
                                                            </a>
                                                        </div>
                                                        <label
                                                            class="fs-12 text-muted"><?php echo $odeme_yuzdesi ?>%</label>
                                                    </div>


                                                    <div class="progress ht-5">
                                                        <div class="progress-bar bg-success" role="progressbar"
                                                            style="width: <?php echo $odeme_yuzdesi ?>%"></div>
                                                    </div>
                                                </div>





                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;">
                                                    <?php echo $borc->aciklama; ?>
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
                                                        data-id="<?php echo $enc_id; ?>"
                                                        data-name="<?php echo $Due->getDueName($borc->borc_tipi_id); ?>">
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