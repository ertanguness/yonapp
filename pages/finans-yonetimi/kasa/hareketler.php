
<?php 
use App\Helper\Security;
use App\Helper\Date;
use App\Helper\Helper;
use Model\FinansalRaporModel;

use Model\KasaHareketModel;
use Model\KasaModel;


$KasaHareket = new KasaHareketModel();
$Kasa = new KasaModel();

$kasa_id = Security::decrypt($_GET['id']) ?? 0;

$kasa_hareketleri = $KasaHareket->getKasaHareketleri($kasa_id);

// echo "<pre>";
//  print_r($kasa_hareketleri);
// echo "</pre>";


?>



<div class="page-header">

    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Finans Yönetimi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Kasa Hareketleri</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">

            <a href="javascript:void(0);" type="button" class="btn btn-outline-secondary me-2" 
            onclick="history.back();">

                <i class="feather-arrow-left me-2"></i>
                Geri
            </a>
            <button type="button" class="btn btn-primary" id="save_debit">
                <i class="feather-save  me-2"></i>
                Kaydet
            </button>
        </div>
    </div>
</div>



<div class="main-content">
    <?php
    $title = "Tahsilat Listesi";
    $text = "Bu sayfada siteye ait tüm tahsilatları görüntüleyebilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table id="tahsilatlarTable" class="table datatables" style="width:100%;">
                        <thead>
                            <tr>

                            <!-- id;islem_tarihi;islem_tipi;tutar;para_birimi;aciklama;kaynak_tablo;kaynak_id;kayit_yapan;created_at;updated_at -->

                                    <th>İşlem Tarihi</th>     
                                    <th>Kategori</th>
                                    <th>Açıklama</th>
                                    <th>Para Birimi</th>
                                    <th class="text-end">Tutar</th>
                                    <th class="text-center" style="width:10%">İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($kasa_hareketleri as $hareket):
                                $enc_id = Security::encrypt($hareket->id);
                            ?>
                                <tr>
                                    <td><?= Date::dmY($hareket->islem_tarihi, 'd.m.Y H:i') ?></td>
                                    <td><?= $hareket->islem_tipi ?></td>
                                    <td><?= $hareket->aciklama ?></td>
                                    <td><?= $hareket->para_birimi ?></td>
                                    <td class="text-end"><?= Helper::formattedMoney($hareket->tutar) ?></td>


                                    <td class="text-center">
                                        <div class="text-center d-flex justify-content-center align-items-center gap-1">

                                            <button class="avatar-text avatar-md hareket-detay-goster"
                                                data-id="<?= $enc_id ?>">
                                                <i class="feather-chevron-down"></i>
                                            </button>
                                            <a href="#" id="delete-hareket"
                                                data-id="<?= $enc_id ?>"
                                                class="avatar-text avatar-md" title="Hareketi Sil">
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
