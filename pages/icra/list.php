<?php

use App\Helper\Security;
use App\Helper\Date;
use App\Helper\Helper;
use Model\IcraModel;
use Model\KisilerModel;

$Icra = new IcraModel();
$kisiler = new KisilerModel();

$Icralar = $Icra->Icralar();

?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">İcra Takip</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">İcra Takip</li>
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

                <a href="#" class="btn btn-primary route-link" data-page="icra/manage">
                    <i class="feather-plus me-2"></i>
                    <span>Yeni İcra Başlat</span>
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
    $title = "İcra Takip Listesi!";
    $text = "Kişilere ait açılan icra dosyalarını görüntüleyebilir, tahsilat ve süreç takibi yapabilir, yeni icra takibi başlatabilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover datatables" id="icraList">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Dosya No</th>
                                            <th>Kişi / Daire</th>
                                            <th>Borç Tutarı</th>
                                            <th>Başlangıç Tarihi</th>
                                            <th>Faiz Oranı (%)</th>
                                            <th>İcra Dairesi</th>
                                            <th>Durum</th>
                                            <th class="text-end">İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($Icralar)) : ?>
                                            <?php foreach ($Icralar as $index => $icra) : ?>
                                                <?php  $enc_id = Security::encrypt($icra->id);?>
                                                <tr>
                                                    <td><?= $index + 1 ?></td>
                                                    <td><?= htmlspecialchars($icra->dosya_no) ?></td>
                                                    <td>
                                                        <?php
                                                        $kisi = $kisiler->kisiBilgileri($icra->kisi_id);
                                                        echo htmlspecialchars($kisi->adi_soyadi ?? 'Bilinmiyor');
                                                        ?>
                                                    </td>
                                                    <td><?= number_format($icra->borc_tutari, 2, ',', '.') ?> ₺</td>
                                                    <td><?= Date::dmY($icra->icra_baslangic_tarihi) ?></td>
                                                    <td><?= htmlspecialchars($icra->faiz_orani) ?></td>
                                                    <td><?= htmlspecialchars($icra->icra_dairesi) ?></td>
                                                    <td class="text-center">
                                                        <?php
                                                        $durumKey = $icra->durum ?? 0; // Eğer durum null ise 0 = Bilinmiyor
                                                        $durum    = Helper::Durum[$durumKey];
                                                        ?>
                                                        <div class="d-flex justify-content-center align-items-center" style="min-height: 40px;">
                                                            <span class="badge <?= $durum['class'] ?>">
                                                                <i class="<?= $durum['icon'] ?> me-1"></i>
                                                                <?= htmlspecialchars($durum['label']) ?>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="hstack gap-2">
                                                            <a href="index?p=icra/detay/manage&id=<?= $enc_id; ?>" class="avatar-text avatar-md">
                                                                <i class="feather-eye"></i>
                                                            </a>
                                                            <a href="index?p=icra/manage&id=<?= $enc_id; ?>" class="avatar-text avatar-md">
                                                                <i class="feather-edit"></i>
                                                            </a>
                                                            <a href="javascript:void(0);"
                                                                data-name="<?= htmlspecialchars($icra->dosya_no); ?>"
                                                                data-id="<?= $enc_id; ?>"
                                                                class="avatar-text avatar-md sil-icra">
                                                                <i class="feather-trash-2"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <tr>
                                                <td colspan="9" class="text-center">Kayıt bulunamadı.</td>
                                            </tr>
                                        <?php endif; ?>
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