<?php
require_once '../../../../vendor/autoload.php';
session_start();
$site_id = $_SESSION['site_id'] ?? 0;

use Model\BloklarModel;
use App\Helper\Security;
use App\Helper\Helper;
use App\Helper\Form;

$Block = new BloklarModel();

$blocks = $Block->SiteBloklari($site_id);
$blockOptions = [];
foreach ($blocks as $block) {
    $blockOptions[$block->id] = $block->blok_adi;
}
$id = isset($_GET['id']) ? Security::decrypt($_GET['id']) : 0;



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
                        <div class="input-group flex-nowrap w-100">
                            <span class="input-group-text"><i class="fas fa-building"></i></span>
                           
                            <?php echo Form::Select2(
                                'blok_id', 
                                $blockOptions,
                                2, 
                             ) ?>

                             
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
                        <div class="input-group flex-nowrap w-100">
                            <span class="input-group-text"><i class="fas fa-user-friends"></i></span>
                            
                            <?php echo Form::Select2(
                                'yakinlik', 
                                Helper::RELATIONSHIP,
                                1, 
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


