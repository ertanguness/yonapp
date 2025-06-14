<?php
require_once '../../../../vendor/autoload.php';
session_start();
$site_id = $_SESSION['site_id'] ?? 0;

use Model\BloklarModel;
use App\Helper\Security;
use App\Helper\Form;
use App\Helper\Helper;

$id = isset($_GET['id']) ? Security::decrypt($_GET['id']) : 0;

$Block = new BloklarModel();
$blocks = $Block->SiteBloklari($site_id);
$blockOptions = [];
foreach ($blocks as $block) {
    $blockOptions[$block->id] = $block->blok_adi;
}

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
                <input type="hidden" name="arac_id" id="arac_id" value="<?php echo $id; ?>">
                    <div class="mb-3">
                        <label for="blokAdi" class="form-label fw-semibold">Blok Adı</label>
                        <div class="input-group flex-nowrap w-100">
                            <span class="input-group-text"><i class="fas fa-building"></i></span>
                            <?php echo Form::Select2(
                                'blok_id', 
                                $blockOptions,
                                1, 
                             ) ?>

                        </div>
                    </div>

                    <!-- Daire No -->
                    <div class="mb-3">
                        <label for="daireNo" class="form-label fw-semibold">Daire No</label>
                        <div class="input-group flex-nowrap w-100">
                            <span class="input-group-text"><i class="fas fa-door-closed"></i></span>
                            <select id="daireNo" class="form-select select2 w-100 daireNo" name="daire_id">
                                <option value="">Daire Seçiniz</option>
                            </select>
                        </div>
                    </div>

                    <!-- Kişi Seç -->
                    <div class="mb-3">
                        <label for="kisiSec" class="form-label fw-semibold">Kişi Seç</label>
                        <div class="input-group flex-nowrap w-100">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <select id="kisiSec" class="form-select select2 w-100 kisiSec" name="kisiSec">
                                <option value="">Kişi Seçiniz</option>
                            </select>
                        </div>
                    </div>


                    <!-- Araç Plakası -->
                    <div class="mb-3">
                        <label for="modalAracPlaka" class="form-label fw-semibold">Araç Plakası</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-car"></i></span>
                            <input type="text" id="modalAracPlaka" name="modalAracPlaka" class="form-control" placeholder="Plaka giriniz">
                        </div>
                    </div>

                    <!-- Araç Markası / Modeli -->
                    <div class="mb-3">
                        <label for="modalAracMarka" class="form-label fw-semibold">Araç Markası / Modeli</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-car-side"></i></span>
                            <input type="text" id="modalAracMarka" name="modalAracMarka" class="form-control" placeholder="Marka giriniz">
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


