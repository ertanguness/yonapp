<?php

use App\Helper\Date;
use App\Helper\Due;
use App\Helper\Helper;
use App\Helper\Security;
use Model\DebitModel;
use App\Helper\Debit;

$DueHelper = new Due();
$Debit = new DebitModel();

$id = Security::decrypt(@$_GET["id"] ?? 0) ?? 0;
$debit = $Debit->find($id ) ?? null;

$DebitHelper = new Debit();

?>


<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Borç Ekle</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Borçlandırma</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">

            <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="dues/debit/list">
                <i class="feather-arrow-left me-2"></i>
                Listeye Dön
            </button>
            <button type="button" class="btn btn-primary" id="save_debit">
                <i class="feather-save  me-2"></i>
                Kaydet
            </button>
        </div>
    </div>
</div>

<div class="main-content">
    <?php
    /* $title = $pageTitle;
 if ($pageTitle === 'Borç Ekle') {
      $text = "Borç Ekleme sayfasındasınız. Bu sayfada yeni bir borç ekleyebilirsiniz.";
 } else {
      $text = "Borç Güncelleme sayfasındasınız. Bu sayfada borç bilgilerini güncelleyebilirsiniz.";
 }
 require_once 'pages/components/alert.php'; */
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="card">
                <div class="card-header">
                    <h5>Borçlandırma Biglieri  </h5>
                </div>
                <div class="card-body">
                    <form id="debitForm" action="" method="POST">
                        <input type="text" class="form-control d-none" name="debit_id" id="debit_id" value="<?php echo $_GET["id"] ?? 0 ?>">
                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-2">
                                <label for="title" class="fw-semibold">Borç Başlığı:</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group flex-nowrap w-100">
                                    <div class="input-group-text"><i class="fas fa-file-invoice"></i></div>
                                    <?php echo $DueHelper->getDuesSelect("due_title") ?>

                                </div>
                            </div>

                            <div class="col-lg-2">
                                <label for="amount" class="fw-semibold">Tutar (₺):</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="fas fa-money-bill"></i></div>
                                    <input type="text" class="form-control money" name="amount" id="amount"
                                    value="<?php echo $debit->amount ?? ''; ?>"
                                        placeholder="Tutar giriniz" required>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-2">
                                <label for="end_date" class="fw-semibold">Son Ödeme Tarihi:</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                                    <input type="text" class="form-control flatpickr" name="end_date" id="end_date"
                                    value="<?php echo Date::dmY($debit->end_date ?? ''); ?>" autocomplete="off" required>
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <label for="penalty_rate" class="fw-semibold">Ceza Oranı (%):</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="fas fa-percentage"></i></div>
                                    <input type="number" class="form-control" name="penalty_rate" id="penalty_rate"
                                        placeholder="Ceza oranı" step="0.01" min="0">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-2">
                                <label for="target_type" class="fw-semibold">Kime Borçlandırılacak:</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group flex-nowrap w-100">
                                    <div class="input-group-text"><i class="fas fa-users"></i></div>
                                   <?php echo Helper::targetTypeSelect('target_type', $debit->target_type ?? "all"); ?>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <label for="block_id" class="fw-semibold">Blok Seç:</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group flex-nowrap w-100">
                                    <div class="input-group-text"><i class="fas fa-building"></i></div>
                                    <select class="form-control select2-single" name="block_id" id="block_id" disabled>
                                        <option value="">Seçiniz</option>
                                        <?php foreach ($blocks as $block): ?>
                                        <option value="<?= $block->id ?>"><?= $block->name ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                        </div>
                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-2">
                                <label for="target_person" class="fw-semibold">Kişi(ler):</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group flex-nowrap w-100">
                                    <div class="input-group-text"><i class="fas fa-user-friends"></i></div>
                                   <select name="target_person" id="target_person" disabled class="form-control select2"></select>

                                </div>
                            </div>
                            <div class="col-lg-2">
                                <label for="status" class="fw-semibold">Durum:</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-check form-switch form-switch-md">
                                    <input class="form-check-input" type="checkbox" id="status" name="status"
                                        value="Aktif" checked>
                                    <label class="form-check-label" for="status">Aktif</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-2">
                                <label for="description" class="fw-semibold">Açıklama:</label>
                            </div>
                            <div class="col-lg-10">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="fas fa-info-circle"></i></div>
                                    <textarea class="form-control" name="description" id="description" rows="3"
                                        placeholder="Açıklama giriniz"><?php echo $debit->description ?? ''; ?></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    $('.select2-single').select2({
        placeholder: 'Seçiniz',
        width: '100%',
        minimumResultsForSearch: Infinity
    });

    $('.select2-multiple').select2({
        placeholder: 'Kişi seçiniz',
        width: '100%'
    });

    const $targetType = $('#target_type');
    const $targetPerson = $('#target_person');
    const $blockSelect = $('#block_id');

    $targetType.on('change', function() {
        const type = $(this).val();

        if (type === 'person') {
            $targetPerson.prop('disabled', false);
            $blockSelect.prop('disabled', true).val(null).trigger('change');
            $targetPerson.find('option').show();
            $targetPerson.val(null).trigger('change');

        } else if (type === 'all') {
            const allValues = $targetPerson.find('option').map(function() {
                return $(this).val();
            }).get();
            $targetPerson.val(allValues).trigger('change');
            $targetPerson.prop('disabled', true);
            $blockSelect.prop('disabled', true).val(null).trigger('change');

        } else if (type === 'block') {
            getBlocksBySite();
            $blockSelect.prop('disabled', false);
          
            $targetPerson.find('option').hide(); // şimdilik tümü gizlenir
        } else {
            $targetPerson.prop('disabled', true).val(null).trigger('change');
            $blockSelect.prop('disabled', true).val(null).trigger('change');
        }
    });

    $blockSelect.on('change', function() {
        const selectedBlock = $(this).val();
        $targetPerson.val(null).trigger('change');
        $targetPerson.find('option').hide();

        $targetPerson.find('option').each(function() {
            if ($(this).data('block') == selectedBlock) {
                $(this).show();
            }
        });
    });
});
</script>