<?php

use App\Helper\Security;
use Model\UserModel;
use Model\GuvenlikGorevYeriModel;
use App\Helper\Date;

$Kullanıcılar = new UserModel();
$GorevYeri = new GuvenlikGorevYeriModel();

$GorevYerleri = $GorevYeri->GorevYerleri();
?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Güvenlik ve Ziyaretçi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Görev Yeri Tanımla</li>
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
                <a href="#" class="btn btn-primary route-link" data-page="ziyaretci/guvenlik/GorevYeri/manage">
                    <i class="feather-plus me-2"></i>
                    <span>Yeni Görev Yeri</span>
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
            <input class="form-check-input" type="checkbox" id="showExitedVisitors" style="width: 3rem; height: 1.5rem;">
            <label class="form-check-label" for="showExitedVisitors">
                Aktif olmayan görev yerlerini göster
            </label>
        </div>
    </div>
    <?php
    $title = "Görev Yeri Tanımlama!";
    $text  = " Bu sayfadan güvenlik görevlilerinin görev yerlerini tanımlayabilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover datatables" id="GorevYeriList">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Görev Yeri Adı</th>
                                            <th>Açıklama</th>
                                            <th>Durum</th>
                                            <th>Eklenme Tarihi</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($GorevYerleri as $index => $item):
                                            $enc_id = Security::encrypt($item->id); // obje erişimi
                                        ?>
                                            <tr class="<?= ($item->durum == 0 ? 'exit-hidden' : ''); ?>">
                                                <td><?= $index + 1 ?></td>
                                                <td><?= htmlspecialchars($item->ad ?? '-'); ?></td>
                                                <td><?= htmlspecialchars($item->aciklama ?? '-'); ?></td>
                                                <td><?= !empty($item->kayit_tarihi) ? Date::dmY($item->kayit_tarihi) : '-'; ?></td>
                                                <td>
                                                    <?php if ($item->durum == 1): ?>
                                                        <i class="feather-check-circle me-1 text-success"></i> <span class="text-success">Aktif</span>
                                                    <?php else: ?>
                                                        <i class="feather-x-circle me-1 text-muted"></i> <span class="text-muted">Pasif</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="hstack gap-2">
                                                        <a href="index?p=ziyaretci/guvenlik/GorevYeri/manage&id=<?= $enc_id; ?>" class="avatar-text avatar-md">
                                                            <i class="feather-edit"></i>
                                                        </a>
                                                        <a href="javascript:void(0);"
                                                            data-name="<?= htmlspecialchars($item->ad); ?>"
                                                            data-id="<?= $enc_id; ?>"
                                                            class="avatar-text avatar-md sil-gorevYeri">
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
<script>
    $(document).ready(function() {
        function updateVisibleIndex() {
            let counter = 1;
            $("#GorevYeriList tbody tr:visible").each(function() {
                $(this).find("td:first").text(counter++);
            });
        }

        // Başlangıçta gizle
        $(".exit-hidden").hide();
        updateVisibleIndex();

        // Switch değiştiğinde satırları göster/gizle ve sıra numarasını güncelle
        $("#showExitedVisitors").change(function() {
            if ($(this).is(":checked")) {
                $(".exit-hidden").show();
            } else {
                $(".exit-hidden").hide();
            }
            updateVisibleIndex();
        });
    });
</script>