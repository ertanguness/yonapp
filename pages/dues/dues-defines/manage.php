<?php

use App\Helper\Helper;
use App\Helper\Security;
use Model\DueModel;

$Dues = new DueModel();

// Yeni eklemelerde 0 olarak gönderilmesi gerekir
$id = Security::decrypt($_GET['id'] ?? 0) ?? 0;
$due = $Dues->find($id ?? null);

?>


<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Aidat Tanımlama</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Aidat Yönetimi</li>
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

                <button type="button" class="btn btn-outline-secondary route-link me-2"
                    data-page="dues/dues-defines/list">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </button>
                <button type="button" class="btn btn-primary" id="save_dues">
                    <i class="feather-save  me-2"></i>
                    Kaydet
                </button>
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
    <form id="duesForm" method="POST">
        <input type="hidden" name="dues_id" id="dues_id" value="<?php echo $_GET['id'] ?? 0; ?>">
        <div class="row">
            <div class="container-xl">
                <div class="card">
                    <div class="card-header">
                        <h5>Aidat Bilgileri</h5>
                    </div>
                    <div class="card-body aidat-info">

                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-2">
                                <label for="block" class="fw-semibold">Tür Adı:</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group flex-nowrap w-100">
                                    <div class="input-group-text"><i class="fas fa-building"></i></div>
                                    <input type="text" class="form-control" name="due_name" id="due_name"
                                        placeholder="Aidat/Borç adı Giriniz" value="<?php echo $due->due_name ?? ''; ?>" required>
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <label for="status" class="fw-semibold">Durum:</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group flex-nowrap w-100">
                                    <div class="input-group-text"><i class="fas fa-toggle-on"></i></div>
                                    <?php echo Helper::StateSelect("state", $due->state ?? 1); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-2">
                                <label for="description" class="fw-semibold">Açıklama:</label>
                            </div>
                            <div class="col-lg-10">
                                <textarea class="form-control" name="description" id="description"
                                    placeholder="Açıklama Giriniz"
                                    rows="3"><?php echo $due->description ?? ''; ?></textarea>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-5">
            <div class="container-xl">
                <div class="card">
                    <div class="card-header">
                        <h5>Aidat Ayarları</h5>
                    </div>
                    <div class="card-body aidat-info">
                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-2">
                                <label for="block" class="fw-semibold">Blok:</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group flex-nowrap w-100">
                                    <div class="input-group-text"><i class="fas fa-building"></i></div>
                                    <select class="form-control select2 w-100" name="block_id" id="block_id" required>
                                        <option value="genel"
                                            <?php echo (isset($aidatData) && $aidatData->block == 'genel') ? 'selected' : ''; ?>>
                                            Tüm Site</option>
                                        <option value="A Blok"
                                            <?php echo (isset($aidatData) && $aidatData->block == 'A Blok') ? 'selected' : ''; ?>>
                                            A Blok</option>
                                        <option value="B Blok"
                                            <?php echo (isset($aidatData) && $aidatData->block == 'B Blok') ? 'selected' : ''; ?>>
                                            B Blok</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <label for="amount" class="fw-semibold">Aidat Tutarı (₺):</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="fas fa-money-bill"></i></div>
                                    <input type="text" class="form-control money" name="amount" id="amount"
                                        placeholder="Aidat Tutarı Giriniz" value="<?php echo Helper::formattedMoneyWithoutCurrency($due->amount ?? 0) ?? ''; ?>"
                                        required>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-2">
                                <label for="start_date" class="fw-semibold">Başlangıç/Bitiş Tarihi:</label>
                            </div>
                            <div class="col-lg-2">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                                    <input type="text" class="form-control flatpickr" name="start_date" id="start_date"
                                        required value="<?php echo $due->start_date ?? ''; ?>">
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                                    <input type="text" class="form-control flatpickr" name="end_date" id="end_date"
                                        value="<?php echo $due->end_date ?? ''; ?>">
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <label for="penalty_rate" class="fw-semibold">Ceza Oranı (%):</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="fas fa-percentage"></i></div>
                                    <input type="number" class="form-control" name="penalty_rate" id="penalty_rate"
                                        placeholder="Ceza Oranı Giriniz" value="<?php echo $due->penalty_rate ?? ''; ?>"
                                        required step="0.01" min="0">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-2">
                                <label for="block" class="fw-semibold">Periyodu:</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group flex-nowrap w-100">
                                    <div class="input-group-text"><i class="fas fa-building"></i></div>
                                    <?php echo Helper::PeriodSelect(); ?>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <label for="block" class="fw-semibold">Gün Bazında:</label>
                            </div>
                            <div class="col-lg-1">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input " name="day_based"
                                        id="day_based" <?php echo isset($_POST['day_based']) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label c-pointer text-muted" for="day_based"></label>

                                </div>
                            </div>
                            <div class="col-lg-2">
                                <label for="block" class="fw-semibold">Otomatik Yenileme:</label>
                            </div>
                            <div class="col-lg-1">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input " name="auto_renew"
                                        id="auto_renew" <?php echo isset($_POST['auto_renew']) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label c-pointer text-muted" for="auto_renew"></label>

                                </div>
                            </div>


                        </div>

                    </div>
                </div>
            </div>
    </form>
</div>

<script>
    $(document).ready(function() {
        $('#period').on('change', function() {
            var period = $(this).val();
            switch (parseInt(period)) {
                case 0: // Aylık
                case 1: // 3 Aylık
                case 2: // 6 Aylık
                case 3: // Yıllık
                    $('#day_based, #auto_renew').prop('checked', false);
                    $('#day_based, #auto_renew').prop('disabled', false);
                    break;
                default: // Tek Seferlik
                    $('#day_based, #auto_renew').prop('checked', false);
                    $('#day_based, #auto_renew').prop('disabled', true);

            }
        })
    })
</script>