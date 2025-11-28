<?php
require_once dirname(__DIR__, levels: 4) . '/configs/bootstrap.php';
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

// Eğer GET ile kişi ID geldiyse onu kullan, yoksa araçtan gelen kişi ID'yi kullan
if (!empty($kisi_id)) {
    $kisiBilgileri = $Kisiler->KisiBilgileri($kisi_id);
} else {
    $kisiBilgileri = $Kisiler->KisiBilgileri($araclar->kisi_id ?? null);
}

// Fallbacklar: Modal her durumda üretilebilsin
if (!$araclar || !is_object($araclar)) {
    $araclar = (object)['plaka'=>'','marka_model'=>'','kisi_id'=>null];
}
if (!$kisiBilgileri || !is_object($kisiBilgileri)) {
    $kisiBilgileri = (object)['blok_id'=>'','daire_id'=>'','id'=>'','adi_soyadi'=>''];
}
$blocks = $Block->SiteBloklari(site_id: $site_id) ?: [];
$daireKisileri = $Kisiler->DaireKisileri($kisiBilgileri->daire_id ?? null) ?: [];
$daireler = $Daireler->BlokDaireleri($kisiBilgileri->blok_id ?? 0) ?: [];

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
                    <input type="hidden" name="arac_id" id="arac_id" value="<?php echo $_GET['id'] ?? 0; ?>">
                    <!-- Seçilmiş Bilgiler Gizli -->
                    <input type="hidden" name="blok_id" value="<?= htmlspecialchars($kisiBilgileri->blok_id ?? '') ?>">
                    <input type="hidden" name="daire_id" value="<?= htmlspecialchars($kisiBilgileri->daire_id ?? '') ?>">
                    <input type="hidden" name="kisi_id" value="<?= htmlspecialchars($kisiBilgileri->id ?? '') ?>">
                    <div style="display:none">
                        <select class="form-select select2 w-100 blokAdi" disabled>
                            <option value="">Blok Seçiniz</option>
                            <?php foreach ($blocks as $block): ?>
                                <option value="<?= htmlspecialchars($block->id) ?>" <?= (isset($kisiBilgileri->blok_id) && $kisiBilgileri->blok_id == $block->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($block->blok_adi) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <select class="form-select select2 w-100 daireNo" disabled>
                            <option value="">Daire Seçiniz</option>
                            <?php if (!empty($daireler)) : ?>
                                <?php foreach ($daireler as $daire): ?>
                                    <option value="<?= $daire->id ?>" <?= ($kisiBilgileri->daire_id == $daire->id) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($daire->daire_no) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <select id="kisi_id_hidden" class="form-select select2 w-100 kisiSec" disabled>
                            <option value="">Kişi Seçiniz</option>
                            <?php if (!empty($daireKisileri)) : ?>
                                <?php foreach ($daireKisileri as $kisi): ?>
                                    <option value="<?= $kisi->id ?>" <?= (isset($kisiBilgileri->id) && $kisiBilgileri->id == $kisi->id) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($kisi->adi_soyadi) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>


                    <!-- Araç Plakası -->
                    <div class="mb-3">
                        <label for="modalAracPlaka" class="form-label fw-semibold">Araç Plakası</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-car"></i></span>
                            <input type="text" id="modalAracPlaka" name="modalAracPlaka" class="form-control" placeholder="Plaka giriniz" value="<?php echo $araclar->plaka ?? ''; ?>">
                        </div>
                    </div>

                    <!-- Araç Markası / Modeli -->
                    <div class="mb-3">
                        <label for="modalAracMarka" class="form-label fw-semibold">Araç Markası / Modeli</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-car-side"></i></span>
                            <input type="text" id="modalAracMarka" name="modalAracMarka" class="form-control" placeholder="Marka giriniz" value="<?php echo $araclar->marka_model ?? ''; ?>">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button id="AracEkle" name="AracEkle" class="btn btn-success">Kaydet</button>
                <button class="btn btn-danger" data-bs-dismiss="modal">İptal</button>
            </div>
        </div>
    </div>
</div>