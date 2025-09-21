<?php

use App\Helper\Security;
use Model\UserModel;
use Model\GuvenlikGorevYeriModel;
use App\Helper\Date;
use Model\GuvenlikVardiyaModel;

$Kullanıcılar = new UserModel();
$GorevYeri = new GuvenlikGorevYeriModel();
$Vardiya = new GuvenlikVardiyaModel();

$GorevYerleri = $GorevYeri->GorevYerleri();
$Vardiyalar = $Vardiya->Vardiyalar();
?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Güvenlik ve Ziyaretçi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Vardiya Tanımları</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <a href="#" class="btn btn-primary route-link" data-page="ziyaretci/guvenlik/Vardiya/manage">
                    <i class="feather-plus me-2"></i>
                    <span>Yeni Vardiya</span>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <?php
    $title = "Vardiya Tanımları!";
    $text  = " Bu sayfadan güvenlik görevlilerinin vardiya tanımlarını yapabilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover datatables" id="vardiyaList">
                                    <thead>
                                        <tr class="text-center">
                                            <th>#</th>
                                            <th>Görev Yeri</th>
                                            <th>Vardiya Adı</th>
                                            <th>Başlangıç</th>
                                            <th>Bitiş</th>
                                            <th>Açıklama</th>
                                            <th>Durum</th>
                                            <th>Eklenme Tarihi</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($Vardiyalar as $index => $vardiya):
                                        $enc_id = Security::encrypt($vardiya->id);
                                        $gorevYeriAd = '-';
                                        foreach ($GorevYerleri as $gorevYeri) {
                                            if ($gorevYeri->id == $vardiya->gorev_yeri_id) {
                                                $gorevYeriAd = htmlspecialchars($gorevYeri->ad ?? '-');
                                                break;
                                            }
                                        }
                                    ?>
                                        <tr class="<?= ($vardiya->durum == 0 ? 'exit-hidden' : ''); ?>">
                                            <td><?= $index + 1 ?></td>
                                            <td><?= $gorevYeriAd ?></td>
                                            <td><?= htmlspecialchars($vardiya->vardiya_adi ?? '-'); ?></td>
                                            <td><?= !empty($vardiya->vardiya_baslangic) ? $vardiya->vardiya_baslangic : '-'; ?></td>
                                            <td><?= !empty($vardiya->vardiya_bitis) ? $vardiya->vardiya_bitis : '-'; ?></td>
                                            <td><?= htmlspecialchars($vardiya->aciklama ?? '-'); ?></td>
                                            <td>
                                                <?php if ($vardiya->durum == 1): ?>
                                                        <i class="feather-check-circle me-1 text-success"></i> <span class="text-success">Aktif</span>
                                                <?php else: ?>
                                                        <i class="feather-x-circle me-1 text-muted"></i> <span class="text-muted">Pasif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= !empty($vardiya->kayit_tarihi) ? Date::dmY($vardiya->kayit_tarihi) : '-'; ?></td>
                                            <td>
                                                <div class="hstack gap-2">
                                                    <a href="index?p=ziyaretci/guvenlik/Vardiya/manage&id=<?= $enc_id; ?>" class="avatar-text avatar-md">
                                                        <i class="feather-edit"></i>
                                                    </a>
                                                    <a href="javascript:void(0);"
                                                        data-name="<?= htmlspecialchars($vardiya->vardiya_adi); ?>"
                                                        data-id="<?= $enc_id; ?>"
                                                        class="avatar-text avatar-md sil-vardiya">
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
        </div>
    </div>
</div>
