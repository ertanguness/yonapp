<?php
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use App\Helper\Security;
use App\Helper\BlokHelper;
use App\Helper\Arac;
use Model\KisilerModel;
use Model\AraclarModel;

$BlokHelper = new BlokHelper();
$KisiModel = new KisilerModel();
$AracModel = new AraclarModel();

$enc_id = $_GET['id'] ?? 0;
$id = Security::decrypt($enc_id) ?? 0;

$car = $id ? $AracModel->AracBilgileri($id) : null;
$kisiId = is_object($car) ? ($car->kisi_id ?? 0) : 0;
$kisi = $kisiId ? $KisiModel->find($kisiId) : null;
$blokId = is_object($kisi) ? ($kisi->blok_id ?? 0) : 0;
$daireId = is_object($kisi) ? ($kisi->daire_id ?? 0) : 0;




?>

<div class="modal-header">
    <h5 class="modal-title" id="mdlTitle">Araç Bilgisi</h5>
    <a href="javascript:void(0)" class="avatar-text avatar-md bg-soft-danger close-icon" data-bs-dismiss="modal">
        <i class="feather-x text-danger"></i>
    </a>
    </div>
<div class="modal-body">
    <form id="frmArac" name="frmArac">
        <input type="hidden" name="id" id="frmId" value="<?= $id ?>">

        <div class="col-lg-12 mb-3">
            <label class="form-label">Blok Adı </label>
            <div class="input-group flex-nowrap w-100">
                <div class="input-group-text">
                    <i class="feather-briefcase"></i>
                </div>
                <?php echo $BlokHelper->BlokSelect('selBlok', false, $blokId); ?>
            </div>
        </div>

        <div class="mb-2">
            <label class="form-label">Daire</label>
            <div class="input-group flex-nowrap w-100">
                <span class="input-group-text"><i class="feather-hash"></i></span>
                <select class="form-select select2" id="selDaire" name="selDaire" data-selected="<?= $daireId ?>" required></select>
            </div>
        </div>

        <div class="mb-2">
            <label class="form-label">Kişi</label>
            <div class="input-group flex-nowrap w-100">
                <span class="input-group-text"><i class="feather-user"></i></span>
                <select class="form-select select2" id="selKisi" name="selKisi" data-selected="<?= $kisiId ?>" required></select>
            </div>
        </div>

        <div class="mb-2">
            <label class="form-label">Araç Plakası</label>
            <div class="input-group">
                <span class="input-group-text"><i class="feather-hash"></i></span>
                <input type="text" class="form-control" name="frmPlaka" id="frmPlaka" value="<?= htmlspecialchars($car->plaka ?? '') ?>" required>
            </div>
        </div>

        <div class="mb-2">
            <label class="form-label">Marka / Model</label>
            <div class="input-group">
                <span class="input-group-text"><i class="feather-tag"></i></span>
                <input type="text" class="form-control" name="frmMarka" id="frmMarka" value="<?= htmlspecialchars($car->marka_model ?? '') ?>">
            </div>
        </div>
    </form>
    <div class="progress mt-2" style="height:6px; display:none" id="expProgress">
        <div class="progress-bar" role="progressbar" style="width: 0%" id="expBar"></div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
    <button type="button" class="btn btn-primary" id="btnKaydet">Kaydet</button>
</div>