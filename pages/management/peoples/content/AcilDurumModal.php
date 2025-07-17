<?php
require_once dirname(__DIR__ ,levels: 4). '/configs/bootstrap.php';
session_start();
$site_id = $_SESSION['site_id'] ?? 0;

use Model\BloklarModel;
use App\Helper\Security;
use Model\AcilDurumKisileriModel;
use Model\KisilerModel;
use Model\DairelerModel;
use App\Helper\Form;
use App\Helper\Helper;

$Daireler= new DairelerModel();
$AcilDurumKisi = new AcilDurumKisileriModel();
$Kisiler = new KisilerModel();
$Block = new BloklarModel();

$kisi_id = isset($_GET['kisi_id']) ? Security::decrypt($_GET['kisi_id']) : 0;
$id = isset($_GET['id']) ? Security::decrypt($_GET['id']) : 0;

$acilKisi = $AcilDurumKisi->AcilDurumKisiBilgileri($id);

// Eğer GET ile kişi ID geldiyse onu kullan, yoksa araçtan gelen kişi ID'yi kullan
if (!empty($kisi_id)) {
    $kisiBilgileri = $Kisiler->KisiBilgileri($kisi_id);
} else {
    $kisiBilgileri = $Kisiler->KisiBilgileri($acilKisi->kisi_id ?? null);
}
$blocks = $Block->SiteBloklari(site_id: $site_id);
$daireKisileri= $Kisiler->DaireKisileri($kisiBilgileri->daire_id ?? null);
$daireler = $Daireler->BlokDaireleri($kisiBilgileri->blok_id ?? 0);



?>
<div class="modal fade" id="acilDurumEkleModal" tabindex="-1" data-bs-keyboard="false" role="dialog">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Acil Durum Bilgisi Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="acilDurumKisileriEkleForm">
                <input type="hidden" name="acil_kisi_id" id="acil_kisi_id" value="<?php echo $_GET['id'] ?? 0; ?>">
                    <!-- Blok Seçimi -->
                    <div class="mb-3">
                        <label for="blokAdi" class="form-label fw-semibold">Blok Seçimi</label>
                        <div class="input-group flex-nowrap w-100">
                            <span class="input-group-text"><i class="fas fa-building"></i></span>
                            <select class="form-select select2 w-100 blokAdi" name="blok_id">
                                <option value="">Blok Seçiniz</option>
                                <?php foreach ($blocks as $block): ?>
                                    <option value="<?= htmlspecialchars($block->id) ?>"
                                        <?= (isset($kisiBilgileri->blok_id) && $kisiBilgileri->blok_id == $block->id) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($block->blok_adi) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>                
                        </div>
                    </div>
                    <!-- Daire No -->
                    <div class="mb-3">
                        <label for="daireNo" class="form-label fw-semibold">Daire No</label>
                        <div class="input-group flex-nowrap w-100">
                            <span class="input-group-text"><i class="fas fa-door-closed"></i></span>
                            <select class="form-select select2 w-100 daireNo" name="daire_id">
                                <option value="">Daire Seçiniz</option>
                                <?php if (!empty($daireler)) : ?>
                                    <?php foreach ($daireler as $daire): ?>
                                        <option value="<?= $daire->id ?>" <?= ($kisiBilgileri->daire_id == $daire->id) ? 'selected' : '' ?>>
                                            <?= $daire->daire_no ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                  <!-- Kişi Seç -->
                  <div class="mb-3">
                        <label for="kisiSec" class="form-label fw-semibold">Kişi Seç</label>
                        <div class="input-group flex-nowrap w-100">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <select id="kisi_id" class="form-select select2 w-100 kisiSec" name="kisi_id">
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
                    </div>
                    <div class="mb-3">
                        <label for="acilDurumKisi" class="form-label fw-semibold">Acil Durumda Ulaşılacak Kişi Adı:</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user-shield"></i></span>
                            <input type="text" id="acilDurumKisi" name="acilDurumKisi" class="form-control" placeholder="Acil Durum Kişisi Giriniz" value="<?php echo $acilKisi->adi_soyadi ?? ''; ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="acilDurumKisiTelefon" class="form-label fw-semibold">Acil Durumda Ulaşılacak Telefon Numarası:</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="text" id="acilDurumKisiTelefon" name="acilDurumKisiTelefon" class="form-control" placeholder="Telefon Numarası Giriniz" value="<?php echo $acilKisi->telefon ?? ''; ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="yakinlik" class="form-label fw-semibold">Yakınlık Derecesi:</label>
                        <div class="input-group flex-nowrap w-100">
                            <span class="input-group-text"><i class="fas fa-user-friends"></i></span>
                            <?php echo Form::Select2(
                                'yakinlik', 
                                ["" => "Lütfen Yakınlık Durumunu Seçiniz"] + Helper::RELATIONSHIP,
                                isset($acilKisi->yakinlik) ? $acilKisi->yakinlik : ""
                            ) ?>
                           
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button id="AcilDurumEkle" class="btn btn-success">Kaydet</button>
                <button class="btn btn-danger" data-bs-dismiss="modal">İptal</button>
            </div>
        </div>
    </div>
</div>


