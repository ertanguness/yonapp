<?php

use App\Helper\Security;
use Model\DairelerModel;
use Model\BloklarModel;
use Model\KisilerModel;

$Apartment = new DairelerModel();
$Block = new BloklarModel();
$Kisiler = new KisilerModel();

$apartments = $Apartment->SitedekiDaireler($_SESSION['site_id'] ?? null);

?>
<style>
    #daireDetayOffcanvas {
        z-index: 1060 !important;
    }
</style>


<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Yönetim</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Daireler</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Geri</span>
                </a>
            </div>
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <?php
                //   require_once 'pages/components/search.php';
                //   require_once 'pages/components/download.php';
                ?>
                <a href="index?p=management/apartment/upload-from-xls"
                    data-tooltip="Daireleri Excelden Yükle"
                    class="btn btn-icon has-tooltip tooltip-bottom">
                    <i class="feather-upload me-2"></i>
                    Excelden Yükle
                </a>
                <a href="/daire-ekle" class="btn btn-primary route-link" >
                    <i class="feather-plus me-2"></i>
                    <span>Yeni Daire Ekle</span>
                </a>
            </div>
        </div>
        <div class="d-md-none d-flex align-items-center">
            <a href="javascript:void(0)" class="page-header-right-open-toggle">
                <i class="feather-align-right fs-20"></i>
            </a>
        </div>
    </div>
</div>

<div class="main-content">
    <?php
    $title = "Daireler Listesi!";
    $text = "Seçili siteye ait daireleri görüntüleyip ekleme, düzenleme, silme işlemlerini yapabilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>
    <div class="row mb-5">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover datatables" id="apartmentsList">
                                    <thead>
                                        <tr class="text-center">
                                            <th>#</th>
                                            <th>Daire Kodu</th>
                                            <th>Blok Adı</th>
                                            <th>Daire No</th>
                                            <th>Kat Maliki</th>
                                            <th>Kiracı</th>
                                            <th>Durumu</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        foreach ($apartments as $apartment):
                                            $enc_id = Security::encrypt($apartment->id);
                                            $block = $Block->Blok($apartment->blok_id);
                                        ?>
                                            <tr class="text-center">
                                                <td><?php echo $i; ?></td>
                                                <td><?php echo htmlspecialchars($apartment->daire_kodu); ?></td>
                                                <td>
                                                    <a data-page="management/blocks/manage&id=<?php echo $block->id ?? 0; ?>" href="#">
                                                        <?php echo htmlspecialchars($block->blok_adi ?? ''); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo htmlspecialchars($apartment->daire_no); ?></td>
                                                <td>
                                                    <?php
                                                    // Kat Maliki (uyelik_tipi = 1)
                                                    $malik = $Kisiler->AktifKisiByDaireId($apartment->id, 1);
                                                    if ($malik && isset($malik->adi_soyadi)) {
                                                        echo htmlspecialchars($malik->adi_soyadi);
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    // Kiracı (uyelik_tipi = 2)
                                                    $kiraci = $Kisiler->AktifKisiByDaireId($apartment->id, 2);
                                                    if ($kiraci && isset($kiraci->adi_soyadi)) {
                                                        echo htmlspecialchars($kiraci->adi_soyadi);
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if ($apartment->aktif_mi == 1): ?>
                                                        <span class="text-success">
                                                            <i class="feather-check-circle"></i> Dolu
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-danger">
                                                            <i class="feather-x-circle"></i> Boş
                                                        </span>
                                                    <?php endif; ?>
                                                </td>

                                                <td>
                                                    <div class="hstack gap-1">
                                                        <a href="javascript:void(0);" class="avatar-text avatar-md openDaireDetay" data-id="<?= $enc_id ?>">
                                                            <i class="feather-eye"></i>
                                                        </a>
                                                        <a href="daire-duzenle/<?php echo $enc_id; ?>" class="avatar-text avatar-md">
                                                            <i class="feather-edit"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" data-name=" <?php echo $apartment->daire_no ?>" data-id="<?php echo $enc_id ?>" class="avatar-text avatar-md delete-apartment" data-id="<?php echo $enc_id; ?>" data-name="<?php echo $apartment->daire_no; ?>">
                                                            <i class="feather-trash-2"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php
                                            $i++;
                                        endforeach;
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="daireDetay"></div>

</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('click', function(e) {
            const target = e.target.closest('.openDaireDetay');
            if (target) {
                const id = target.getAttribute('data-id');
                Pace.restart(); // varsa

                fetch('pages/management/apartment/content/daireDetay.php?id=' + id)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('daireDetay').innerHTML = html;
                        // DOM'a yeni offcanvas eklendikten sonra bul
                        const canvasElement = document.getElementById('daireDetayOffcanvas');
                        const offcanvasInstance = new bootstrap.Offcanvas(canvasElement);

                        // Bootstrap offcanvas'ı JS üzerinden başlat => backdrop otomatik oluşur
                        offcanvasInstance.show();
                    })
                    .catch(error => {
                        console.error('Detay yüklenemedi:', error);
                        alert('Bir hata oluştu.');
                    });
            }
        });
    });
</script>