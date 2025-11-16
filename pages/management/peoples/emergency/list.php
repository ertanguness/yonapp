<?php

use App\Helper\Helper;
use App\Services\Gate;

Gate::authorizeOrDie('acil_durum_kisileri_manage', '', false);

?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Acil Durum Kişileri</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Sakinler</li>
            <li class="breadcrumb-item">Acil Durum Kişileri</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <a href="/pages/management/peoples/export/acil_durum_kisileri.php" class="btn btn-icon has-tooltip tooltip-bottom" data-tooltip="Excel İndir">
                    <i class="feather-download me-2"></i>
                    Excel
                </a>
                <a href="javascript:void(0)" class="btn btn-primary" id="btnYeniKisi">
                    <i class="feather-plus"></i>
                    <span>Yeni Kişi Ekle</span>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="row">
        <div class="container-xl">
            <div class="card">
                <div class="card-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="fltName" placeholder="Ad Soyad">
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="fltPhone" placeholder="Telefon">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="fltRel">
                                <option value="">Yakınlık</option>
                                <?php foreach (Helper::RELATIONSHIP as $k => $v): ?>
                                    <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($v) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button class="btn btn-secondary" id="btnFilter">Filtrele</button>
                            <button class="btn btn-outline-secondary" id="btnClear">Temizle</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover" id="tblAcil">
                            <thead>
                                <tr class="text-center">
                                    <th>#</th>
                                    <th>Ad Soyad</th>
                                    <th>Telefon</th>
                                    <th>Yakınlık</th>
                                    <th>Kayıt Tarihi</th>
                                    <th>İşlem</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" tabindex="-1" id="mdlAcil">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mdlTitle">Yeni Kişi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="frmAcil">
                        <input type="hidden" name="id" id="frmId">
                        <div class="mb-2">
                            <label class="form-label">Site Sakini ID</label>
                            <input type="number" class="form-control" name="kisi_id" id="frmKisiId" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Ad Soyad</label>
                            <input type="text" class="form-control" name="adi_soyadi" id="frmName" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Telefon</label>
                            <input type="text" class="form-control" name="telefon" id="frmPhone" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Yakınlık</label>
                            <select class="form-select" name="yakinlik" id="frmRel" required>
                                <?php foreach (Helper::RELATIONSHIP as $k => $v): ?>
                                    <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($v) ?></option>
                                <?php endforeach; ?>
                            </select>
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
            </div>
        </div>
    </div>
</div>
<script src="/pages/management/peoples/js/acil_durum_kisileri_jq.js"></script>