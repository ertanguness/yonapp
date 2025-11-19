<?php
use App\Helper\Security;
use Model\BloklarModel;
use Model\DairelerModel;
use Model\KisilerModel;
use Model\AraclarModel;

$site_id = $_SESSION['site_id'] ?? 0;
$enc_id = $id ?? 0;
$id = Security::decrypt($id ?? 0) ?? 0;

$Blocks = new BloklarModel();
$Daireler = new DairelerModel();
$Kisiler = new KisilerModel();
$Araclar = new AraclarModel();

$arac = $Araclar->AracBilgileri($id);
$kisiBilgileri = $Kisiler->KisiBilgileri($arac->kisi_id ?? null);
$blocks = $Blocks->SiteBloklari(site_id: $site_id);
$daireler = $Daireler->BlokDaireleri($kisiBilgileri->blok_id ?? 0);
$daireKisileri = $Kisiler->DaireKisileri($kisiBilgileri->daire_id ?? null);
?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Araç <?= $id ? 'Düzenle' : 'Ekle' ?></h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item"><a href="/site-araclari" class="route-link">Site Araçları</a></li>
            <li class="breadcrumb-item">Araç <?= $id ? 'Düzenle' : 'Ekle' ?></li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <a href="/site-araclari" class="btn btn-outline-secondary route-link">
                    <i class="feather-arrow-left me-2"></i>Listeye Dön
                </a>
                <button id="AracEkle" class="btn btn-primary">
                    <i class="feather-save me-2"></i>Kaydet
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form id="aracEkleForm">
            <input type="hidden" name="arac_id" id="arac_id" value="<?= Security::encrypt($id) ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Blok Adı</label>
                    <div class="input-group flex-nowrap w-100">
                        <span class="input-group-text"><i class="fas fa-building"></i></span>
                        <select class="form-select select2 w-100 blokAdi" name="blok_id">
                            <option value="">Blok Seçiniz</option>
                            <?php foreach ($blocks as $block): ?>
                                <option value="<?= htmlspecialchars($block->id) ?>" <?= ($kisiBilgileri->blok_id ?? null) == $block->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($block->blok_adi) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Daire No</label>
                    <div class="input-group flex-nowrap w-100">
                        <span class="input-group-text"><i class="fas fa-door-closed"></i></span>
                        <select class="form-select select2 w-100 daireNo" name="daire_id">
                            <option value="">Daire Seçiniz</option>
                            <?php if (!empty($daireler)) : foreach ($daireler as $daire): ?>
                                <option value="<?= $daire->id ?>" <?= ($kisiBilgileri->daire_id ?? null) == $daire->id ? 'selected' : '' ?>>
                                    <?= $daire->daire_no ?>
                                </option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Kişi Seç</label>
                    <div class="input-group flex-nowrap w-100">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <select id="kisi_id" class="form-select select2 w-100 kisiSec" name="kisi_id">
                            <option value="">Kişi Seçiniz</option>
                            <?php if (!empty($daireKisileri)) : foreach ($daireKisileri as $kisi): ?>
                                <option value="<?= $kisi->id ?>" <?= ($kisiBilgileri->id ?? null) == $kisi->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kisi->adi_soyadi) ?>
                                </option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
</div>

<script src="/src/daire-kisi.js"></script>
<script src="/src/blok-daire.js"></script>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Araç Plakası</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-car"></i></span>
                        <input type="text" id="modalAracPlaka" name="modalAracPlaka" class="form-control" placeholder="Plaka giriniz" value="<?= htmlspecialchars($arac->plaka ?? '') ?>">
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Araç Markası / Modeli</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-car-side"></i></span>
                        <input type="text" id="modalAracMarka" name="modalAracMarka" class="form-control" placeholder="Marka/Model" value="<?= htmlspecialchars($arac->marka_model ?? '') ?>">
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>