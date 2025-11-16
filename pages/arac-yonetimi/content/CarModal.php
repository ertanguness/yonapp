<?php
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$site_id = $_SESSION['site_id'] ?? 0;

use Model\BloklarModel;
use App\Helper\Security;
use Model\KisilerModel;
use Model\AraclarModel;
use Model\DairelerModel;

$Daireler = new DairelerModel();
$Araclar = new AraclarModel();
$Kisiler = new KisilerModel();
$Block = new BloklarModel();

$kisi_id = isset($_GET['kisi_id']) ? Security::decrypt($_GET['kisi_id']) : 0;
$id = isset($_GET['id']) ? Security::decrypt($_GET['id']) : 0;

$araclar = $Araclar->AracBilgileri($id);

if (!empty($kisi_id)) {
    $kisiBilgileri = $Kisiler->KisiBilgileri($kisi_id);
} else {
    $kisiBilgileri = $Kisiler->KisiBilgileri($araclar->kisi_id ?? null);
}
$blocks = $Block->SiteBloklari(site_id: $site_id);
$daireKisileri = $Kisiler->DaireKisileri($kisiBilgileri->daire_id ?? null);
$daireler = $Daireler->BlokDaireleri($kisiBilgileri->blok_id ?? 0);
?>
<div class="modal fade" id="aracEkleModal" tabindex="-1" data-bs-keyboard="false" role="dialog">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Araç Bilgisi Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="aracEkleForm">
                    <input type="hidden" name="arac_id" id="arac_id" value="<?= $_GET['id'] ?? 0; ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Blok Adı</label>
                        <div class="input-group flex-nowrap w-100">
                            <span class="input-group-text"><i class="fas fa-building"></i></span>
                            <select class="form-select select2 w-100 blokAdi" name="blok_id">
                                <option value="">Blok Seçiniz</option>
                                <?php foreach ($blocks as $block): ?>
                                    <option value="<?= htmlspecialchars($block->id) ?>" <?= (isset($kisiBilgileri->blok_id) && $kisiBilgileri->blok_id == $block->id) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($block->blok_adi) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Daire No</label>
                        <div class="input-group flex-nowrap w-100">
                            <span class="input-group-text"><i class="fas fa-door-closed"></i></span>
                            <select class="form-select select2 w-100 daireNo" name="daire_id">
                                <option value="">Daire Seçiniz</option>
                                <?php if (!empty($daireler)) : foreach ($daireler as $daire): ?>
                                    <option value="<?= $daire->id ?>" <?= ($kisiBilgileri->daire_id == $daire->id) ? 'selected' : '' ?>>
                                        <?= $daire->daire_no ?>
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kişi Seç</label>
                        <div class="input-group flex-nowrap w-100">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <select id="kisi_id" class="form-select select2 w-100 kisiSec" name="kisi_id">
                                <option value="">Kişi Seçiniz</option>
                                <?php if (!empty($daireKisileri)) : foreach ($daireKisileri as $kisi): ?>
                                    <option value="<?= $kisi->id ?>" <?= (isset($kisiBilgileri->id) && $kisiBilgileri->id == $kisi->id) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($kisi->adi_soyadi) ?>
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Araç Plakası</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-car"></i></span>
                            <input type="text" id="modalAracPlaka" name="modalAracPlaka" class="form-control" placeholder="Plaka giriniz" value="<?= $araclar->plaka ?? '' ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Araç Markası / Modeli</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-car-side"></i></span>
                            <input type="text" id="modalAracMarka" name="modalAracMarka" class="form-control" placeholder="Marka giriniz" value="<?= $araclar->marka_model ?? '' ?>">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" data-bs-dismiss="modal">İptal</button>
                <button id="AracEkle" name="AracEkle" class="btn btn-success">Kaydet</button>
            </div>
        </div>
    </div>
</div>