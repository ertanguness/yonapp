<?php

use Model\IsletmeProjesiModel;
use App\Helper\Helper;
use App\Helper\Security;

$Model = new IsletmeProjesiModel();

Security::ensureSiteSelected();
$siteId = $_SESSION['site_id'] ?? 0;
$projeler = $Model->getProjectsBySite($siteId);
?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">İşletme Projesi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">İşletme Projesi Listesi</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items d-flex align-items-center gap-2">
            <a href="/isletme-projesi-ekle" class="btn btn-primary route-link">
                <i class="feather-plus me-2"></i>
                Yeni Proje Ekle
            </a>
        </div>
    </div>
    
</div>

<div class="main-content">
    <?php
    $title = 'İşletme Projeleri';
    $text = 'Bu sayfada sitenize ait tüm işletme projelerini görebilir, yeni proje ekleyebilir veya mevcut projeleri düzenleyebilirsiniz.';
    require_once 'pages/components/alert.php';
    ?>

    <div class="row row-deck row-cards mb-5 ">
        <div class="col-12">
            <div class="card mb-5">
                <div class="card-header">
                    <h4 class="card-title">Projeler</h4>
                </div>
                <div class="table-responsive">
                                <table class="table table-hover table-bordered datatables" id="isletmeProjesiTable">
                        <thead >
                            <tr>
                                <th>#</th>
                                <th>Proje Adı</th>
                                <th>Dönem</th>
                                <th>Toplam Gelir</th>
                                <th>Toplam Gider</th>
                                <th>Net Yıllık Gider</th>
                                <th>Aylık Avans Toplam</th>
                                <th class="text-center">İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projeler as $p): $enc_id = Security::encrypt($p->id); ?>
                                <tr>
                                    <td><?= $p->id ?></td>
                                    <td><?= htmlspecialchars($p->proje_adi) ?></td>
                                    <td><?= date('d.m.Y', strtotime($p->donem_baslangic)) ?> - <?= date('d.m.Y', strtotime($p->donem_bitis)) ?></td>
                                    <td><?= Helper::formattedMoney($p->toplam_gelir) ?></td>
                                    <td><?= Helper::formattedMoney($p->toplam_gider) ?></td>
                                    <td><?= Helper::formattedMoney($p->net_yillik_gider) ?></td>
                                    <td><?= Helper::formattedMoney($p->aylik_avans_toplam) ?></td>
                                    <td>
                                        <div class="hstack gap-2 justify-content-center">
                                            <a href="javascript:void(0);" class="avatar-text avatar-md route-link" data-page="isletme-projesi-detay/<?= $enc_id ?>" title="Görüntüle">
                                                <i class="feather-eye"></i>
                                            </a>
                                            <a href="javascript:void(0);" class="avatar-text avatar-md route-link" data-page="isletme-projesi-duzenle/<?= $enc_id ?>" title="Düzenle">
                                                <i class="feather-edit"></i>
                                            </a>
                                            <a href="javascript:void(0);" class="avatar-text avatar-md proje-sil" data-id="<?= $enc_id ?>" title="Sil">
                                                <i class="feather-trash-2"></i>
                                            </a>
                                            <a href="/isletme-projesi-pdf/<?= $enc_id ?>" target="_blank" class="avatar-text avatar-md" title="PDF">
                                                <i class="bi bi-filetype-pdf"></i>
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

<script type="module">
    import '/pages/isletme-projesi/js/projects.js';
</script>