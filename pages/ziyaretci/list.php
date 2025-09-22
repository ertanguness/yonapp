<?php

use App\Helper\Security;
use Model\UserModel;
use Model\ZiyaretciModel;
use App\Helper\Date;

$Ziyaretci = new ZiyaretciModel();

$Ziyaretciler = $Ziyaretci->Ziyaretciler();
?>
<style>
    .exit-hidden {
        background-color: #f9f9f9;
        /* Hafif gri arka plan */
    }

    /* Switch label küçük ekranlarda da düzgün olsun */
    .form-check.form-switch {
        display: flex;
        align-items: center;
    }
</style>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Güvenlik ve Ziyaretçi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Ziyaretçi Yönetimi</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Back</span>
                </a>
            </div>
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">


                <a href="#" class="btn btn-primary route-link" data-page="ziyaretci/manage">
                    <i class="feather-plus me-2"></i>
                    <span>Yeni Ziyaretçi Ekle</span>
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
  
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="showExitedVisitors"   style="width: 3rem; height: 1.5rem;">
            <label class="form-check-label" for="showExitedVisitors">
                Çıkış yapmış ziyaretçileri göster
            </label>
        </div>
   

  
        <div class="card">
            <div class="table-responsive position-relative">
                <table class="table table-hover datatables" id="ZiyaretciList">
                    <thead>
                        <tr>
                            <th style="width:5%">Blok</th>
                            <th style="width:5%">Daire</th>
                            <th style="width:15%">Ziyaret Edilen</th>
                            <th style="width:15%">Ziyaretçi Ad Soyad</th>
                            <th style="width:7%">Ziyaret Tarihi</th>
                            <th style="width:7%">Giriş Saati</th>
                            <th style="width:7%">Çıkış Saati</th>
                            <th style="width:10%">Durum</th>
                            <th style="width:8%">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($Ziyaretciler as $item):
                            $enc_id = Security::encrypt($item->id);
                        ?>
                            <tr class="text-center <?= ($item->durum == 1 ? 'exit-hidden' : ''); ?>">
                                <td><?= htmlspecialchars($item->blok_adi ?? '-'); ?></td>
                                <td><?= htmlspecialchars($item->daire_no ?? '-'); ?></td>
                                <td><?= htmlspecialchars($item->ziyaret_edilen ?? 'Bilinmiyor'); ?></td>
                                <td><?= htmlspecialchars($item->ad_soyad); ?></td>
                                <td><?= $item->giris_tarihi !== null && $item->giris_tarihi !== '' ? htmlspecialchars(Date::dmY($item->giris_tarihi)) : '-'; ?></td>
                                <td><?= htmlspecialchars($item->giris_saati); ?></td>
                                <td><?= htmlspecialchars($item->cikis_saati ?? '-'); ?></td>
                                <td>
                                    <?php
                                    switch ($item->durum) {
                                        case 0:
                                            echo '<span class="text-success"><i class="feather-clock"></i> Giriş Yaptı</span>';
                                            break;
                                        case 1:
                                            echo '<span class="text-danger"><i class="feather-check-circle"></i> Çıkış Yaptı</span>';
                                            break;
                                        default:
                                            echo '<span class="text-muted">Bilinmiyor</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <div class="hstack gap-2">
                                        <a href="index?p=ziyaretci/manage&id=<?= $enc_id; ?>" class="avatar-text avatar-md">
                                            <i class="feather-edit"></i>
                                        </a>
                                        <a href="javascript:void(0);"
                                            data-name="<?= htmlspecialchars($item->ad_soyad); ?>"
                                            data-id="<?= $enc_id; ?>"
                                            class="avatar-text avatar-md sil-ziyaretci">
                                            <i class="feather-trash-2"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php
                        endforeach;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Durum 1 olan satırları başlangıçta gizle
        $(".exit-hidden").hide();

        // Switch değiştiğinde satırları göster/gizle
        $("#showExitedVisitors").change(function() {
            if ($(this).is(":checked")) {
                $(".exit-hidden").show();
            } else {
                $(".exit-hidden").hide();
            }
        });
    });
</script>