<?php

use App\Helper\Helper;
use App\Services\Gate;
use Model\AcilDurumKisileriModel;

Gate::authorizeOrDie('acil_durum_kisileri_view', '', false);

$AcilDurumKisiModel = new AcilDurumKisileriModel();


?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Acil Durum Kişileri</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Sakinler</li>
            <li class="breadcrumb-item">Acil Durum Kişileri</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <a href="/pages/acil-durum-kisileri/export/acil_durum_kisileri.php" class="btn btn-icon has-tooltip tooltip-bottom" data-tooltip="Excel İndir">
                    <i class="feather-download me-2"></i>
                    Excel
                </a>
                <a href="javascript:void(0)" class="btn btn-primary" id="btnYeniKisi">
                    <i class="feather-plus"></i>
                    <span>Yeni Kişi Ekle</span>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="row">
        <div class="container-xl">
            <?php
            $title = "Acil Durum Kişileri";
            $text = "Acil durum kişilerini görüntüleyebilir, detaylarına ulaşabilir ve Excel olarak dışa aktarabilirsiniz.";
            require_once 'pages/components/alert.php';
            ?>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tblAcil">
                            <thead>
                                <tr class="text-center">
                                    <th>#</th>
                                    <th>Ad Soyad</th>
                                    <th>Telefon</th>
                                    <th>Yakınlık</th>
                                    <th>Kayıt Tarihi</th>
                                    <th>İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $m = new AcilDurumKisileriModel();
                                $rows = $m->listOrdered(true);
                                foreach ($rows as $r):
                                    $telRaw = (string)($r->telefon ?? '');
                                    $telDigits = preg_replace('/\D+/', '', $telRaw);
                                    $telFmt = $telDigits ? preg_replace('/^(\d{3})(\d{3})(\d{2})(\d{2})$/', '+90 $1 $2 $3 $4', $telDigits) : '-';
                                    $relMap = Helper::RELATIONSHIP;
                                    $relTxt = isset($relMap[$r->yakinlik]) ? $relMap[$r->yakinlik] : ($r->yakinlik ?? '-');
                                    $dateTxt = $r->kayit_tarihi ?? '-';
                                ?>
                                    <tr class="text-center">
                                        <td><?php echo (int)$r->id; ?></td>
                                        <td><?php echo htmlspecialchars($r->adi_soyadi ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($telFmt); ?></td>
                                        <td><?php echo htmlspecialchars($relTxt); ?></td>
                                        <td><?php echo htmlspecialchars($dateTxt); ?></td>
                                        <td>
                                            <div class="hstack gap-2">
                                                <a href="javascript:void(0)" class="avatar-text avatar-md btn-edit" data-id="<?php echo (int)$r->id; ?>"><i class="feather-edit"></i></a>
                                                <a href="javascript:void(0)" class="avatar-text avatar-md btn-del" data-id="<?php echo (int)$r->id; ?>" data-name="<?php echo htmlspecialchars($r->adi_soyadi ?? ''); ?>"><i class="feather-trash-2"></i></a>
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



<div id="acilModal" class="custom-modal">
    <div class="modal fade-scale" id="mdlAcil" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>

            </div>
        </div>
    </div>
</div>



<script>
    window.SITE_ID = <?= (int)($_SESSION['site_id'] ?? 0) ?>;
</script>

<script src="/pages/acil-durum-kisileri/js/acil_durum_kisileri.js"></script>
<script>
    $('#btnYeniKisi').on('click', function() {
        $.get('/pages/acil-durum-kisileri/modal/acil_durum_kisi_modal.php', function(html) {
            var $modal = $('#mdlAcil');
            $modal.find('.modal-content').html(html);
            $modal.modal('show');
            $modal.find('.select2').select2({
                dropdownParent: $modal,
            })
$("#selBlok").trigger('change');

        });
        

    });

</script>