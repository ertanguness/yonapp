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

// Sunucu tarafı güvenlik: Modal her durumda üretilebilsin
if (!$acilKisi || !is_object($acilKisi)) {
    $acilKisi = (object)['adi_soyadi'=>'','telefon'=>'','yakinlik'=>null,'kisi_id'=>null];
}
if (!$kisiBilgileri || !is_object($kisiBilgileri)) {
    $kisiBilgileri = (object)['blok_id'=>'','daire_id'=>'','id'=>'','adi_soyadi'=>''];
}
$blocks = $Block->SiteBloklari(site_id: $site_id) ?: [];
$daireKisileri= $Kisiler->DaireKisileri($kisiBilgileri->daire_id ?? null) ?: [];
$daireler = $Daireler->BlokDaireleri($kisiBilgileri->blok_id ?? 0) ?: [];


// Görsel alanlarda göstermek için seçilmiş blok, daire ve kişi adlarını hazırla
$blokAdi = '';
if (isset($kisiBilgileri->blok_id)) {
    foreach ($blocks as $b) {
        if ($b->id == $kisiBilgileri->blok_id) { $blokAdi = $b->blok_adi; break; }
    }
}
$daireNo = '';
if (isset($kisiBilgileri->daire_id)) {
    foreach ($daireler as $d) {
        if ($d->id == $kisiBilgileri->daire_id) { $daireNo = $d->daire_no; break; }
    }
}
$kisiAdi = $kisiBilgileri->adi_soyadi ?? '';


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
                    <!-- Seçilmiş Bilgiler (Değiştirilemez) -->
                    <input type="hidden" name="blok_id" value="<?= htmlspecialchars($kisiBilgileri->blok_id ?? '') ?>">
                    <input type="hidden" name="daire_id" value="<?= htmlspecialchars($kisiBilgileri->daire_id ?? '') ?>">
                    <input type="hidden" name="kisi_id" value="<?= htmlspecialchars($kisiBilgileri->id ?? '') ?>">
                    <!-- Blok/Daire/Kişi bilgileri gizlendi -->
                    <div style="display:none">
                        <!-- Gizli Blok Select -->
                        <select class="form-select select2 w-100 blokAdi" disabled>
                            <option value="">Blok Seçiniz</option>
                            <?php foreach ($blocks as $block): ?>
                                <option value="<?= htmlspecialchars($block->id) ?>"
                                    <?= (isset($kisiBilgileri->blok_id) && $kisiBilgileri->blok_id == $block->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($block->blok_adi) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <!-- Gizli Daire Select -->
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
                        <!-- Gizli Kişi Select -->
                        <select class="form-select select2 w-100 kisiSec" disabled>
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
                            <select class="form-select" name="yakinlik" id="yakinlik">
                                <?php $seciliYakinlik = $acilKisi->yakinlik ?? null; ?>
                                <?php foreach (Helper::RELATIONSHIP as $k => $v): ?>
                                    <option value="<?= htmlspecialchars($k) ?>" <?= ($seciliYakinlik !== null && $seciliYakinlik == $k) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($v) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                           
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


