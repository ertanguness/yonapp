<?php

use App\Helper\Security;
use App\Helper\Date;
use App\Helper\Helper;

use Model\DueModel;
use Model\BorclandirmaModel;

$Borc = new BorclandirmaModel();
$Due = new DueModel();

$site_id = $_SESSION['site_id'];

// Site genelindeki borçlandırma özetlerini getir
$borclar = $Borc->getAll($site_id);
?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Site Borçlandırma Özeti</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Borçlandırma Özeti</li>
        </ul>
    </div>
</div>

<div class="main-content">
    <?php
    $title = "Borçlandırma Özet Listesi";
    $text = "Her borçlandırmanın tutarı, tahsilatı, kalan tutarı ve katılımcı sayıları";
    require_once 'pages/components/alert.php';
    ?>

    <div class="row mb-5">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover datatables" id="siteDebitSummary">
                                    <thead>
                                        <tr class="text-center">
                                            <th>#</th>
                                            <th>Başlık</th>
                                            <th>Toplam Borç</th>
                                            <th>Toplam Tahsilat</th>
                                            <th>Kalan</th>
                                            <th>Kişi Sayısı</th>
                                            <th>Detay Satır</th>
                                            <th>Ödenmemiş Satır</th>
                                            <th>Başlangıç</th>
                                            <th>Son Ödeme</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $i = 1; foreach ($borclar as $borc): ?>
                                            <tr>
                                                <td><?= $i++ ?></td>
                                                <td><?= htmlspecialchars($Due->getDueName($borc->borc_tipi_id)) ?></td>
                                                <td><?= Helper::formattedMoney($borc->toplam_borc ?? 0) ?></td>
                                                <td><?= Helper::formattedMoney($borc->toplam_tahsilat ?? 0) ?></td>
                                                <td class="text-<?= (($borc->toplam_kalan ?? 0) > 0 ? 'danger' : 'success') ?>">
                                                    <?= Helper::formattedMoney($borc->toplam_kalan ?? 0) ?>
                                                </td>
                                                <td class="text-center"><?= (int)($borc->kisi_sayisi ?? 0) ?></td>
                                                <td class="text-center"><?= (int)($borc->detay_sayisi ?? 0) ?></td>
                                                <td class="text-center"><?= (int)($borc->odenmemis_satir ?? 0) ?></td>
                                                <td><?= Date::dmY($borc->baslangic_tarihi) ?></td>
                                                <td><?= Date::dmY($borc->bitis_tarihi) ?></td>
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
