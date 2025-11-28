<?php
$site_id = $_SESSION['site_id'];

use App\Helper\Date;
use App\Helper\Helper;
use Model\KisilerModel;
use Model\IcraModel;
use App\Helper\Security;

$kisilerModel = new KisilerModel();
$Icra = new IcraModel();

$kisiler = $kisilerModel->SiteTumKisileri($site_id);

$id = Security::decrypt($id ?? 0) ;
$icraBilgileri = $Icra->IcraBilgileri($id);
?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">İcra Takibi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">İcra Takibi</li>
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

                <a href="/icra-takibi" class="btn btn-outline-secondary me-2">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </a>
                <button type="button" class="btn btn-primary" id="icra_kaydet" name="icra_kaydet">
                    <i class="feather-save me-2"></i>
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
    $title = $id ? "İcra Takibi Düzenle" : "Yeni İcra Takibi Başlat";
    $text = $id ? "İcra bilgilerini güncelleyebilirsiniz." : "Yeni bir icra takibi başlatabilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>

    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <form method="POST" id="icraForm" name="icraForm">
                            <input type="hidden" name="icra_id" id="icra_id" value="<?php echo Security::encrypt($id) ?? 0; ?>">
                            <div class="card-body custom-card-action p-0">
                                <div class="card-body personal-info">

                                    <!-- Dosya No & Durum -->
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2">
                                            <label class="fw-semibold">Dosya No:</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-hash"></i></div>
                                                <input type="text" name="dosya_no" class="form-control"
                                                    placeholder="Dosya Numarası"
                                                    value="<?= htmlspecialchars($icraBilgileri->dosya_no ?? '') ?>">
                                            </div>
                                        </div>

                                        <div class="col-lg-2">
                                            <label class="fw-semibold">İcra Durumu:</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group flex-nowrap w-100">
                                                <div class="input-group-text"><i class="feather-info"></i></div>
                                                <select class="form-select select2 w-100" name="icra_durumu" id="icra_durumu">
                                                    <?php foreach (Helper::Durum as $key => $value): ?>
                                                        <option value="<?= $key; ?>"
                                                            <?= (!empty($icraBilgileri->durum) && $icraBilgileri->durum == $key) ? 'selected' : ''; ?>>
                                                            <?= htmlspecialchars($value['label']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Kişi / TC -->
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2">
                                            <label class="fw-semibold">Kişi:</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group flex-nowrap w-100">
                                                <div class="input-group-text"><i class="feather-user"></i></div>
                                                <select class="form-select select2 w-100" name="kisi_id" id="kisi_id">
                                                    <option value="">Seçiniz</option>
                                                    <?php foreach ($kisiler as $kisi): ?>
                                                        <option value="<?= htmlspecialchars($kisi->id); ?>"
                                                            <?= (!empty($icraBilgileri->kisi_id) && $icraBilgileri->kisi_id == $kisi->id) ? 'selected' : ''; ?>>
                                                            <?= htmlspecialchars($kisi->adi_soyadi); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="fw-semibold">TC Kimlik No:</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-credit-card"></i></div>
                                                <input type="text" name="tc" id="tc" class="form-control"
                                                    value="<?= htmlspecialchars($icraBilgileri->tc ?? '') ?>" readonly>
                                            </div>
                                        </div>
                                    </div>

                                   

                                    <!-- Tarih & Daire -->
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2">
                                            <label class="fw-semibold">Başlangıç Tarihi:</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-calendar"></i></div>
                                                <input type="text" class="form-control flatpickr"
                                                    name="baslangic_tarihi" id="baslangic_tarihi"
                                                    value="<?= !empty($icraBilgileri->icra_baslangic_tarihi) ? Date::dmY($icraBilgileri->icra_baslangic_tarihi) : '' ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="fw-semibold">İcra Dairesi:</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-briefcase"></i></div>
                                                <input type="text" name="icra_dairesi" class="form-control"
                                                    placeholder="İcra Dairesi"
                                                    value="<?= htmlspecialchars($icraBilgileri->icra_dairesi ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Açıklama -->
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2">
                                            <label class="fw-semibold">Açıklama:</label>
                                        </div>
                                        <div class="col-lg-10">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-file-text"></i></div>
                                                <textarea name="aciklama" class="form-control" rows="4" placeholder="Açıklama yazınız..."><?= htmlspecialchars($icraBilgileri->aciklama ?? '') ?></textarea>
                                            </div>
                                        </div>
                                    </div>

                                </div> <!-- /.card-body personal-info -->
                            </div> <!-- /.card-body custom-card-action -->
                        </form>
                    </div> <!-- /.card -->
                </div> <!-- /.col-12 -->
            </div> <!-- /.row-cards -->
        </div> <!-- /.container-xl -->
    </div> <!-- /.row -->
</div> <!-- /.main-content -->
<!-- Ödeme Planı Modal -->

<script src="/src/kisi-bilgileri.js"></script>