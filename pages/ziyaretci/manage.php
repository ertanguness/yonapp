<?php
require_once dirname(__DIR__, levels: 2) . '/configs/bootstrap.php';
$site_id = $_SESSION['site_id'] ?? 0;

use Model\BloklarModel;
use App\Helper\Security;
use Model\KisilerModel;
use Model\DairelerModel;
use Model\ZiyaretciModel;
use App\Helper\Date;    

$Daireler = new DairelerModel();
$Kisiler = new KisilerModel();
$Block = new BloklarModel();
$Ziyaretciler = new ZiyaretciModel();

$id = isset($_GET['id']) ? Security::decrypt($_GET['id']) : 0;

$ZiyaretciBilgileri = $Ziyaretciler->ZiyaretciBilgileri($id);

// ziyaret edilen kişinin bilgilerini getir
$kisiBilgileri = null;
if (!empty($ZiyaretciBilgileri->ziyaret_edilen_id)) {
    $kisiBilgileri = $Kisiler->KisiBilgileri($ZiyaretciBilgileri->ziyaret_edilen_id);
}

$blocks = $Block->SiteBloklari($site_id);
$daireler = $Daireler->BlokDaireleri($kisiBilgileri->blok_id ?? 0);
$daireKisileri = $Kisiler->DaireKisileri($kisiBilgileri->daire_id ?? null);
?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10"> Güvenlik ve Ziyaretçi</h5>
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
                <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="ziyaretci/list">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </button>
                <button type="button" class="btn btn-primary" id="ziyaretci_kaydet">
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
    <div class="container-xl">
        <form id="ziyaretciForm" method="POST">
            <input type="hidden" name="ziyaretci_id" id="ziyaretci_id" value="<?php echo $_GET['id'] ?? 0; ?>">

            <!-- Ziyaretçi Bilgileri -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold">Ziyaretçi Bilgileri</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-lg-2"><label class="fw-semibold">Ad Soyad:</label></div>
                        <div class="col-lg-4">
                            <div class="input-group">
                                <div class="input-group-text"><i class="fas fa-user"></i></div>
                                <input type="text" class="form-control" name="ad-soyad" id="ad-soyad" placeholder="Ad Soyad Giriniz" value="<?php echo $ZiyaretciBilgileri->ad_soyad ?? ''; ?>">
                            </div>
                        </div>

                        <div class="col-lg-2"><label class="fw-semibold">Telefon:</label></div>
                        <div class="col-lg-4">
                            <div class="input-group">
                                <div class="input-group-text"><i class="fas fa-phone"></i></div>
                                <input type="text" class="form-control" name="ziyaretci-tel" id="ziyaretci-tel" placeholder="0(5__) ___ __ __" value="<?php echo $ZiyaretciBilgileri->telefon ?? ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-lg-2"><label class="fw-semibold">Araç Plaka (Varsa):</label></div>
                        <div class="col-lg-4">
                            <div class="input-group">
                                <div class="input-group-text"><i class="fas fa-car"></i></div>
                                <input type="text" class="form-control" name="plaka" id="plaka" placeholder="Plaka Numarası" value="<?php echo $ZiyaretciBilgileri->plaka ?? ''; ?>">
                            </div>
                        </div>

                        <div class="col-lg-2"><label class="fw-semibold">Giriş Tarihi:</label></div>
                        <div class="col-lg-4">
                            <div class="input-group">
                                <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                                <input type="text" class="form-control flatpickr" name="giris_tarihi" id="giris_tarihi"
                                    value="<?= !empty($ZiyaretciBilgileri->giris_tarihi) ? Date::dmY($ZiyaretciBilgileri->giris_tarihi) : Date::dmY(date('Y-m-d')); ?>">

                                </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-lg-2"><label class="fw-semibold">Giriş Saati:</label></div>
                        <div class="col-lg-4">
                            <div class="input-group">
                                <div class="input-group-text"><i class="fas fa-clock"></i></div>
                                <input type="time" class="form-control" name="giris_saati" id="giris_saati"
                                    value="<?= ($id == 0 || $id === null) ? date('H:i') : ($ZiyaretciBilgileri->giris_saati ?? '') ?>">
                                    
                            </div>
                        </div>

                        <div class="col-lg-2"><label class="fw-semibold">Çıkış Saati:</label></div>
                        <div class="col-lg-4">
                            <div class="input-group">
                                <div class="input-group-text"><i class="fas fa-clock"></i></div>
                                <input type="time" class="form-control" name="cikis_saati" id="cikis_saati"
                                    value="<?= ($id == 0 || $id === null) ? date('H:i') : ($ZiyaretciBilgileri->cikis_saati ?? '') ?>">
                                <small class="form-text text-muted">Aktif etmek için Çıkış Durumu alanını aktif etmeniz gerekmektedir.</small>

                            </div>
                        </div>

                        <div class="col-lg-2"> <label class="fw-semibold me-2">Çıkış Durumu:</label></div>
                        <div class="col-lg-4">
                            <div class="input-group">
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" name="cikisSaatiSwitch" id="cikisSaatiSwitch"
                                        value="<?php echo $ZiyaretciBilgileri->durum ?? '0'; ?>"
                                        <?php echo (isset($ZiyaretciBilgileri->durum) && $ZiyaretciBilgileri->durum == 1) ? 'checked' : ''; ?>
                                        style="transform: scale(1.5);">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ziyaret Edilen Kişi Bilgileri -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold">Ziyaret Edilen Kişi Bilgileri</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-lg-2"><label class="fw-semibold">Blok:</label></div>
                        <div class="col-lg-4">
                            <div class="input-group flex-nowrap w-100">
                                <div class="input-group-text"><i class="fas fa-building"></i></div>
                                <!-- Blok seçimi -->
                                <select class="form-select select2 w-100 blokAdi" name="blok_id" id="blok_id">
                                    <option value="">Blok Seçiniz</option>
                                    <?php foreach ($blocks as $block): ?>
                                        <option value="<?= $block->id ?>"
                                            <?= (!empty($kisiBilgileri->blok_id) && $kisiBilgileri->blok_id == $block->id) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($block->blok_adi) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-2"><label class="fw-semibold">Daire:</label></div>
                        <div class="col-lg-4">
                            <div class="input-group flex-nowrap w-100">
                                <div class="input-group-text"><i class="fas fa-door-closed"></i></div>
                                <select class="form-select select2 w-100 daireNo" name="daire_id" id="daire_id">
                                    <option value="">Daire Seçiniz</option>
                                    <?php if (!empty($daireler)) : ?>
                                        <?php foreach ($daireler as $daire): ?>
                                            <option value="<?= $daire->id ?>"
                                                <?= (!empty($kisiBilgileri->daire_id) && $kisiBilgileri->daire_id == $daire->id) ? 'selected' : '' ?>>
                                                <?= $daire->daire_no ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-lg-2"><label class="fw-semibold">Ad Soyad:</label></div>
                        <div class="col-lg-4">
                            <div class="input-group flex-nowrap w-100">
                                <div class="input-group-text"><i class="fas fa-user-friends"></i></div>
                                <!-- Kişi seçimi -->
                                <select id="kisi_id" class="form-select select2 w-100 kisiSec" name="kisi_id">
                                    <option value="">Kişi Seçiniz</option>
                                    <?php if (!empty($daireKisileri)) : ?>
                                        <?php foreach ($daireKisileri as $kisi): ?>
                                            <option value="<?= $kisi->id ?>"
                                                <?= (!empty($kisiBilgileri->id) && $kisiBilgileri->id == $kisi->id) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($kisi->adi_soyadi) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-2"><label class="fw-semibold">Telefon:</label></div>
                        <div class="col-lg-4">
                            <div class="input-group">
                                <div class="input-group-text"><i class="fas fa-phone-alt"></i></div>
                                <!-- Telefon -->
                                <input type="text" class="form-control" name="telefon" id="telefon"
                                    value="<?= $kisiBilgileri->telefon ?? '' ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>
<script src="src/blok-daire.js"></script>
<script src="src/daire-kisi.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cikisSaatiSwitch = document.getElementById('cikisSaatiSwitch');
        const cikisSaatiInput = document.querySelector('input[name="cikis_saati"]');

        function toggleCikisSaati() {
            cikisSaatiInput.disabled = !cikisSaatiSwitch.checked;
            if (cikisSaatiSwitch.checked) {
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                cikisSaatiInput.value = `${hours}:${minutes}`;
            } else {
                cikisSaatiInput.value = '';
            }
        }
        cikisSaatiSwitch.addEventListener('change', toggleCikisSaati);
        toggleCikisSaati();
    });

    $(document).ready(function() {
        $("#ziyaretci-tel").inputmask({ 
            mask: "0(999) 999 99 99",
            placeholder: "0(___) ___ __ __",
            showMaskOnHover: false,
            showMaskOnFocus: true,
            clearIncomplete: true        });
    });
</script>