<?php

use App\Controllers\AuthController;
use App\Helper\Alert;
use App\Helper\Helper;
use App\Helper\Security;
use Model\SikayetOneriModel;

AuthController::checkAuthentication();

$id = Security::decrypt($id ?? 0);
$model = new SikayetOneriModel();
$sikayet = $id ? $model->find($id) : null;
$idEnc = $sikayet ? Security::encrypt($sikayet->id) : null;
$type = $sikayet->type ?? 'Şikayet';

?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Yeni Şikayet / Öneri</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Taleplerim</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <a href="index?p=sakin/sikayet-oneri-listem" class="btn btn-outline-secondary">
            <i class="feather-arrow-left me-2"></i> Listeye Dön
        </a>
    </div>
</div>

<div class="main-content">
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <h5 class="card-title mb-0">Şikayet & Öneri Düzenle</h5>
                            <button class="btn btn-secondary ms-auto me-2" onclick="history.back()">
                            <i class="feather-arrow-left me-2"></i>    
                            İptal</button>
                            <button class="btn btn-primary" id="btnSubmit">
                                <i class="feather-send me-2"></i>    
                            Gönder</button>
                        </div>
                        <div class="card-body">
                            <form id="formSikayetOneri">
                                <input type="text" id="id" name="id" value="<?= $idEnc ?? '' ?>">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Tür</label>
                                        <select class="form-select select2" id="inpType" name="inpType">
                                            <option value="Şikayet" <?= $type == 'Şikayet' ? 'selected' : '' ?>>Şikayet</option>
                                            <option value="Öneri" <?= $type == 'Öneri' ? 'selected' : '' ?>>Öneri</option>
                                        </select>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Başlık</label>
                                        <input type="text" class="form-control" id="inpTitle" name="inpTitle" placeholder="Kısa başlık" value="<?= $sikayet->title ?? '' ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">İçerik</label>
                                        <textarea class="form-control" id="inpContent" name="inpContent" rows="6" placeholder="Detaylı açıklama"><?= $sikayet->message ?? '' ?></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    
</script>