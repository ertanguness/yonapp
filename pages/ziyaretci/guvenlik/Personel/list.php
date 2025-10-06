<?php

use App\Helper\Security;
use Model\UserModel;
use Model\ZiyaretciModel;
use App\Helper\Date;
use Model\GuvenlikPersonelModel;
use Model\GuvenlikGorevYeriModel;

$Kullanıcılar = new UserModel();
$Personel = new GuvenlikPersonelModel();
$GuvenlikGorevYeri = new GuvenlikGorevYeriModel();

$Personeller = $Personel->Personeller();
$GorevYerleri = $GuvenlikGorevYeri->GorevYerleri();
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
            <li class="breadcrumb-item"><a href="ana-sayfa">Ana Sayfa</a></li>
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


                <a href="personel-ekle" class="btn btn-primary route-link" >
                    <i class="feather-plus me-2"></i>
                    <span>Yeni Personel Ekle</span>
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
    <div class="container-xl mb-2 d-flex justify-content-end">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="showExitedVisitors"   style="width: 3rem; height: 1.5rem;">
            <label class="form-check-label" for="showExitedVisitors">
                Çıkış Yapmış Personelleri Göster
            </label>
        </div>
    </div>

    <div class="container-xl">
        <div class="card">
            <div class="table-responsive position-relative">
                <table class="table table-hover datatables" id="guvenlikPersonelList">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th style="width:15%">Adı Soyadı</th>
                            <th style="width:10%">TC Kimlik No</th>
                            <th style="width:10%">Telefon</th>
                            <th style="width:10%">Görev Yeri</th>
                            <th style="width:7%">Durum</th>
                            <th style="width:10%">Başlama Tarihi</th>
                            <th style="width:10%">Bitiş Tarihi</th>
                            <th style="width:10%">Acil Kişi</th>
                            <th style="width:10%">Acil Telefon</th>
                            <th style="width:8%">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        foreach ($Personeller as $item):
                            $enc_id = Security::encrypt($item->id);
                        ?>
                            <tr class="text-center <?= ($item->durum == 0 ? 'exit-hidden' : ''); ?>">
                                <td><?php echo $i; ?></td>
                                <td><?= htmlspecialchars($item->adi_soyadi); ?></td>
                                <td><?= htmlspecialchars($item->tc_kimlik_no); ?></td>
                                <td><?= htmlspecialchars($item->telefon); ?></td>
                                <td>
                                    <?php
                                    $gorevYeriAd = '-';
                                    foreach ($GorevYerleri as $gorevYeri) {
                                        if ($gorevYeri->id == $item->gorev_yeri) {
                                            $gorevYeriAd = htmlspecialchars($gorevYeri->ad);
                                            break;
                                        }
                                    }
                                    echo $gorevYeriAd;
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    switch ($item->durum) {
                                        case 1:
                                            echo '<span class="text-success"><i class="feather-check-circle"></i> Aktif</span>';
                                            break;
                                        case 0:
                                            echo '<span class="text-danger"><i class="feather-x-circle"></i> Pasif</span>';
                                            break;
                                        default:
                                            echo '<span class="text-muted">Bilinmiyor</span>';
                                    }
                                    ?>
                                </td>
                                <td><?= !empty($item->baslama_tarihi) ? htmlspecialchars(Date::dmY($item->baslama_tarihi)) : '-'; ?></td>
                                <td><?= !empty($item->bitis_tarihi) ? htmlspecialchars(Date::dmY($item->bitis_tarihi)) : '-'; ?></td>
                                <td><?= htmlspecialchars($item->acil_kisi); ?></td>
                                <td><?= htmlspecialchars($item->acil_telefon); ?></td>
                                <td>
                                    <div class="hstack gap-2">
                                        <a href="personel-duzenle/<?= $enc_id; ?>" class="avatar-text avatar-md">
                                            <i class="feather-edit"></i>
                                        </a>
                                        <a href="javascript:void(0);"
                                            data-name="<?= htmlspecialchars($item->adi_soyadi); ?>"
                                            data-id="<?= $enc_id; ?>"
                                            class="avatar-text avatar-md sil-guvenlikPersonel">
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