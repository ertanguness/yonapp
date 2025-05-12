<?php 

use Model\DueModel;
use App\Helper\Security;

$Dues = new DueModel();

$id = isset($_GET['id']) ? Security::decrypt($_GET['id']) : null;
$due = $Dues->find($id  ?? null);

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
    <?php
    /* $title = $pageTitle;
     if ($pageTitle === 'Yeni Personel Ekle') {
         $text = "Yeni Personel Ekleme sayfasındasınız. Bu sayfada yeni bir personel ekleyebilirsiniz.";
     } else {
         $text = "Personel Güncelleme sayfasındasınız. Bu sayfada personel bilgilerini güncelleyebilirsiniz.";
     }
     require_once 'pages/components/alert.php'; */
    ?>
    <div class="row">

        <div class="container-xl">
            <div class="card">
                <div class="card-body aidat-info">
                    <form action="" id="duesForm" method="POST">
                        <input type="hidden" name="aidat_id" value="<?php echo $aidatData->id ?? ''; ?>">

                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-2">
                                <label for="block" class="fw-semibold">Blok:</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group flex-nowrap w-100">
                                    <div class="input-group-text"><i class="fas fa-building"></i></div>
                                    <select class="form-control select2 w-100" name="block" id="block" required>
                                        <option value="genel" <?php echo (isset($aidatData) && $aidatData->block == "genel") ? "selected" : ""; ?>>Tüm Site</option>
                                        <option value="A Blok" <?php echo (isset($aidatData) && $aidatData->block == "A Blok") ? "selected" : ""; ?>>A Blok</option>
                                        <option value="B Blok" <?php echo (isset($aidatData) && $aidatData->block == "B Blok") ? "selected" : ""; ?>>B Blok</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <label for="amount" class="fw-semibold">Aidat Tutarı (₺):</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="fas fa-money-bill"></i></div>
                                    <input type="number" class="form-control" name="amount" id="amount"
                                        placeholder="Aidat Tutarı Giriniz"
                                        value="<?php echo $due->amount ?? ''; ?>" required step="0.01">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-2">
                                <label for="start_date" class="fw-semibold">Başlangıç Tarihi:</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                                    <input type="date" class="form-control" name="start_date" id="start_date" required
                                        value="<?php echo $due->start_date ?? ''; ?>">
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <label for="due_days" class="fw-semibold">Ödeme Süresi (Gün):</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="fas fa-hourglass-half"></i></div>
                                    <input type="number" class="form-control" name="due_days" id="due_days"
                                        placeholder="Ödeme Süresi (Gün)" required
                                        value="<?php echo $due->due_days ?? ''; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-2">
                                <label for="status" class="fw-semibold">Durum:</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group flex-nowrap w-100">
                                    <div class="input-group-text"><i class="fas fa-toggle-on"></i></div>
                                    <select class="form-control select2 w-100" name="status" id="status" required>
                                        <option value="Aktif" <?php echo (isset($aidatData) && $aidatData->status == "Aktif") ? "selected" : ""; ?>>Aktif</option>
                                        <option value="Pasif" <?php echo (isset($aidatData) && $aidatData->status == "Pasif") ? "selected" : ""; ?>>Pasif</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <label for="penalty_rate" class="fw-semibold">Ceza Oranı (%):</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="fas fa-percentage"></i></div>
                                    <input type="number" class="form-control" name="penalty_rate" id="penalty_rate"
                                        placeholder="Ceza Oranı Giriniz"
                                        value="<?php echo $due->penalty_rate ?? ''; ?>" required step="0.01"
                                        min="0">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-2">
                                <label for="description" class="fw-semibold">Açıklama:</label>
                            </div>
                            <div class="col-lg-10">
                                <textarea class="form-control" name="description" id="description"
                                    placeholder="Açıklama Giriniz" rows="3"><?php echo $due->description ?? ''; ?></textarea>
                            </div>
                        </div>

         

                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
