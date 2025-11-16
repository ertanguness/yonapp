<?php
require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';


use App\Helper\Helper;
use App\Helper\BlokHelper;
use Model\AcilDurumKisileriModel;

$BlokHelper = new BlokHelper();

$ADKModel = new AcilDurumKisileriModel();

$enc_id = $id ?? 0;

$adk = $ADKModel->find($id ?? 0);



?>

<div class="modal-header">
    <h5 class="modal-title" id="mdlTitle">Yeni Kişi</h5>
    <a href="javascript:void(0)" class="avatar-text avatar-md bg-soft-danger close-icon" data-bs-dismiss="modal">
        <i class="feather-x text-danger"></i>
    </a>
</div>
<div class="modal-body">
    <form id="frmAcil">
        <input type="hidden" name="id" id="frmId">
        <div class="col-lg-12 mb-3">
            <label class="form-label">Blok Adı </label>
            <div class="input-group flex-nowrap w-100">
                <div class="input-group-text">
                    <i class="feather-briefcase"></i>
                </div>
                <?php echo $BlokHelper->BlokSelect('selBlok', false,$adk->blok_id ?? 0); ?>
            </div>
        </div></label>

        <div class="mb-2">
            <label class="form-label">Daire</label>
            <div class="input-group flex-nowrap w-100">
                <span class="input-group-text"><i class="feather-hash"></i></span>
                <select class="form-select select2" id="selDaire" required></select>
            </div>
        </div>


        <div class="mb-2">
            <label class="form-label">Kişi</label>
            <div class="input-group flex-nowrap w-100">
                <span class="input-group-text"><i class="feather-user"></i></span>
                <select class="form-select select2" id="selKisi" required></select>
            </div>
        </div>
        <div class="mb-2">
            <label class="form-label">Ad Soyad</label>
            <div class="input-group">
                <span class="input-group-text"><i class="feather-user"></i></span>
                <input type="text" class="form-control" name="adi_soyadi" id="frmName" required>
            </div>
        </div>
        <div class="mb-2">
            <label class="form-label">Telefon</label>
            <div class="input-group">
                <span class="input-group-text"><i class="feather-phone"></i></span>
                <input type="text" class="form-control" name="telefon" id="frmPhone" required>
            </div>
        </div>
        <div class="mb-2">
            <label class="form-label">Yakınlık</label>
            <div class="input-group flex-nowrap w-100">
                <div class="input-group-text">
                    <i class="feather-briefcase"></i>
                </div>
                <?php echo Helper::relationshipSelect('yakinlik'); ?>
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