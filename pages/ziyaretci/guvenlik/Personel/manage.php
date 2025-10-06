<?php

use App\Helper\Helper;
use App\Helper\Security;

use Model\GuvenlikPersonelModel;
use Model\GuvenlikGorevYeriModel;
use App\Helper\Date;

$Personeller = new GuvenlikPersonelModel();
$GorevYerleri = new GuvenlikGorevYeriModel();

$id = Security::decrypt($id ?? 0);

$Personel = $Personeller->PersonelBilgileri($id);
$gorevYeri = $GorevYerleri->GorevYerleri();
?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10"> Güvenlik ve Ziyaretçi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Güvenlik Yönetimi</li>
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

                <a href="/personel-listesi" class="btn btn-outline-secondary route-link me-2">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </a>
                <button type="button" class="btn btn-primary" id="guvenlikPersonel_kaydet">
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
        <div class="card">
            <div class="card-body blocks-info">
                <form method="POST" id="guvenlikPersonelForm">
                    <input type="hidden" name="guvenlikPersonel_id" id="guvenlikPersonel_id" value="<?php echo Security::encrypt($id) ?? 0; ?>">

                    <div class="card">
                        <div class="card-header p-0">
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs flex-wrap w-100 text-center customers-nav-tabs" id="personelTab" role="tablist">
                                <li class="nav-item flex-fill border-top" role="presentation">
                                    <a href="javascript:void(0);" class="nav-link active" data-bs-toggle="tab" data-bs-target="#temelTab" role="tab">Temel Bilgiler</a>
                                </li>
                                <li class="nav-item flex-fill border-top" role="presentation">
                                    <a href="javascript:void(0);" class="nav-link" data-bs-toggle="tab" data-bs-target="#iletisimTab" role="tab">İletişim Bilgileri</a>
                                </li>
                                <li class="nav-item flex-fill border-top" role="presentation">
                                    <a href="javascript:void(0);" class="nav-link" data-bs-toggle="tab" data-bs-target="#isTab" role="tab">İş Bilgileri</a>
                                </li>
                                <li class="nav-item flex-fill border-top" role="presentation">
                                    <a href="javascript:void(0);" class="nav-link" data-bs-toggle="tab" data-bs-target="#acilTab" role="tab">Acil Durum</a>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body">
                            <div class="tab-content">
                                <!-- TEMEL BİLGİLER -->
                                <div class="tab-pane fade show active" id="temelTab" role="tabpanel">
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2"><label class="fw-semibold">Adı Soyadı:</label></div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="fas fa-user"></i></div>
                                                <input type="text" class="form-control" name="adi_soyadi" id="adi_soyadi" required value="<?= htmlspecialchars($Personel->adi_soyadi ?? ''); ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-2"><label class="fw-semibold">TC Kimlik No:</label></div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="fas fa-id-card"></i></div>
                                                <input type="text" class="form-control" name="tc_kimlik_no" id="tc_kimlik_no" value="<?= htmlspecialchars($Personel->tc_kimlik_no ?? ''); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2"><label class="fw-semibold">Doğum Tarihi:</label></div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="fas fa-calendar"></i></div>
                                                <input type="text" class="form-control flatpickr"
                                                    name="dogum_tarihi" id="dogum_tarihi"
                                                    value="<?php echo Date::dmY($Personel->dogum_tarihi ?? ""); ?>">

                                            </div>
                                        </div>
                                        <div class="col-lg-2"><label class="fw-semibold">Cinsiyet:</label></div>
                                        <div class="col-lg-4">
                                            <div class="input-group flex-nowrap w-100">
                                                <div class="input-group-text"><i class="fas fa-venus-mars"></i></div>
                                                <select class="form-select select2 w-100" name="cinsiyet" id="cinsiyet">
                                                    <option value="">Seçiniz</option>
                                                    <option value="Erkek" <?= (isset($Personel->cinsiyet) && $Personel->cinsiyet === 'Erkek') ? 'selected' : ''; ?>>Erkek</option>
                                                    <option value="Kadın" <?= (isset($Personel->cinsiyet) && $Personel->cinsiyet === 'Kadın') ? 'selected' : ''; ?>>Kadın</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- İLETİŞİM BİLGİLERİ -->
                                <div class="tab-pane fade" id="iletisimTab" role="tabpanel">
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2"><label class="fw-semibold">Telefon:</label></div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="fas fa-phone"></i></div>
                                                <input type="text" class="form-control" name="telefon" id="telefon" placeholder="0(5__) ___ __ __" value="<?= htmlspecialchars($Personel->telefon ?? ''); ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-2"><label class="fw-semibold">E-Posta:</label></div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="fas fa-envelope"></i></div>
                                                <input type="email" class="form-control" name="eposta" id="eposta" value="<?= htmlspecialchars($Personel->eposta ?? ''); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2"><label class="fw-semibold">Adres:</label></div>
                                        <div class="col-lg-10">
                                            <textarea class="form-control" name="adres" id="adres" rows="2"><?= htmlspecialchars($Personel->adres ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- İŞ BİLGİLERİ -->
                                <div class="tab-pane fade" id="isTab" role="tabpanel">
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2"><label class="fw-semibold">Görev Yeri:</label></div>
                                        <div class="col-lg-4">
                                            <div class="input-group flex-nowrap w-100">
                                                <div class="input-group-text"><i class="fas fa-building"></i></div>
                                                <select class="form-select select2 w-100" name="gorev_yeri" id="gorev_yeri">
                                                    <option value="">Seçiniz</option>
                                                    <?php foreach ($gorevYeri as $item): ?>
                                                        <option value="<?= htmlspecialchars($item->id); ?>" <?= (isset($Personel->gorev_yeri) && $Personel->gorev_yeri == $item->id) ? 'selected' : ''; ?>>
                                                            <?= htmlspecialchars($item->ad); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-2"><label class="fw-semibold">Durum:</label></div>
                                        <div class="col-lg-4">
                                            <div class="input-group flex-nowrap w-100">
                                                <div class="input-group-text"><i class="fas fa-toggle-on"></i></div>
                                                <select class="form-select select2 w-100" name="durum" id="durum" required>
                                                    <option value="1" <?= (isset($Personel->durum) && $Personel->durum == '1') ? 'selected' : ''; ?>>Aktif</option>
                                                    <option value="0" <?= (isset($Personel->durum) && $Personel->durum == '0') ? 'selected' : ''; ?>>Pasif</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2"><label class="fw-semibold">Başlama Tarihi:</label></div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="fas fa-calendar-check"></i></div>
                                                <input type="text" class="form-control flatpickr"
                                                    name="baslangic_tarihi" id="baslangic_tarihi"
                                                    value="<?php echo Date::dmY($Personel->baslangic_tarihi ?? ""); ?>">

                                            </div>
                                        </div>
                                        <div class="col-lg-2"><label class="fw-semibold">Bitiş Tarihi:</label></div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="fas fa-calendar-times"></i></div>
                                                <input type="date" class="form-control flatpickr"
                                                    name="bitis_tarihi" id="bitis_tarihi"
                                                    value="<?php echo Date::dmY($Personel->bitis_tarihi ?? ""); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ACİL DURUM -->
                                <div class="tab-pane fade" id="acilTab" role="tabpanel">
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2"><label class="fw-semibold">Acil Kişi:</label></div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="fas fa-user-friends"></i></div>
                                                <input type="text" class="form-control" name="acil_kisi" id="acil_kisi" value="<?= htmlspecialchars($Personel->acil_kisi ?? ''); ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-2"><label class="fw-semibold">Yakınlık:</label></div>
                                        <div class="col-lg-4">
                                            <div class="input-group flex-nowrap w-100">
                                                <div class="input-group-text"><i class="fas fa-users"></i></div>
                                                <select class="form-select select2 w-100" name="yakinlik" id="yakinlik">
                                                    <option value="">Yakınlık Seçiniz</option>
                                                    <?php foreach (Helper::RELATIONSHIP as $key => $relation): ?>
                                                        <option value="<?= htmlspecialchars($key); ?>"
                                                            <?= (isset($Personel->yakinlik) && $Personel->yakinlik == $key) ? 'selected' : ''; ?>>
                                                            <?= htmlspecialchars($relation); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>

                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2"><label class="fw-semibold">Acil Telefon:</label></div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="fas fa-phone-alt"></i></div>
                                                <input type="text" class="form-control" name="acil_telefon" id="acil_telefon" placeholder="0(5__) ___ __ __" value="<?= htmlspecialchars($Personel->acil_telefon ?? ''); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            // Telefon ve acil telefon için input mask
            $("#telefon, #acil_telefon").inputmask({
            mask: "0(999) 999 99 99",
            placeholder: "0(___) ___ __ __",
            showMaskOnHover: false,
            showMaskOnFocus: true,
            clearIncomplete: true
            });

            // TC Kimlik No için input mask (11 haneli rakam)
            $("#tc_kimlik_no").inputmask({
            mask: "99999999999",
            placeholder: "___________",
            showMaskOnHover: false,
            showMaskOnFocus: true,
            clearIncomplete: true
            });
        });
    </script>
    