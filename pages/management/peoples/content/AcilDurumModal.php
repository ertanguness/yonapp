<?php
require_once '../../../../vendor/autoload.php';
session_start();
$site_id = $_SESSION['site_id'] ?? 0;

use Model\BloklarModel;
use App\Helper\Security;

$Block = new BloklarModel();

$blocks = $Block->SiteBloklari($site_id);
$id = isset($_GET['id']) ? Security::decrypt($_GET['id']) : 0;

$relationOptions = [
    1  => "Anne",
    2  => "Baba",
    3  => "Kardeş",
    4  => "Eş",
    5  => "Çocuk",
    6  => "Dede",
    7  => "Babaanne",
    8  => "Anneanne",
    9  => "Amca",
    10 => "Dayı",
    11 => "Teyze",
    12 => "Hala",
    13 => "Kuzen",
    14 => "Diğer"
];

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
                <input type="hidden" name="acil_kisi_id" id="acil_kisi_id" value="<?php echo $id; ?>">
                    <!-- Blok Seçimi -->
                    <div class="mb-3">
                        <label for="blokAdi" class="form-label fw-semibold">Blok Seçimi</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-building"></i></span>
                            <select id="blokAdi" class="form-select blokAdi" name="blok_id">
                                <option value="">Blok Seçiniz</option>
                                <?php foreach ($blocks as $block): ?>
                                    <option value="<?= $block->id ?>">
                                        <?= htmlspecialchars($block->blok_adi) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <!-- Daire No -->
                    <div class="mb-3">
                        <label for="daireNo" class="form-label fw-semibold">Daire No</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-door-closed"></i></span>
                            <select id="daireNo" class="form-select daireNo" name="daire_id">
                                <option value="">Daire Seçiniz</option>
                            </select>
                        </div>
                    </div>
                  <!-- Kişi Seç -->
                  <div class="mb-3">
                        <label for="kisiSec" class="form-label fw-semibold">Kişi Seç</label>
                        <div class="input-group flex-nowrap w-100">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <select id="kisiSec" class="form-select select2 w-100 kisiSec" name="kisi_id">
                                <option value="">Kişi Seçiniz</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="acilDurumKisi" class="form-label fw-semibold">Acil Durumda Ulaşılacak Kişi Adı:</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user-shield"></i></span>
                            <input type="text" id="acilDurumKisi" name="acilDurumKisi" class="form-control" placeholder="Acil Durum Kişisi Giriniz">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="acilDurumKisiTelefon" class="form-label fw-semibold">Acil Durumda Ulaşılacak Telefon Numarası:</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="text" id="acilDurumKisiTelefon" name="acilDurumKisiTelefon" class="form-control" placeholder="Telefon Numarası Giriniz">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="yakinlik" class="form-label fw-semibold">Yakınlık Derecesi:</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user-friends"></i></span>
                            <select id="yakinlik" name="yakinlik" class="form-select">
                                <option value="">Yakınlık Derecesi Seçiniz</option>
                                <?php foreach ($relationOptions as $key => $option): ?>
                                    <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($option) ?></option>
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
