<?php
require_once dirname(__DIR__, levels: 4) . '/configs/bootstrap.php';

use App\Helper\Helper;
use App\Helper\Security;
use App\Helper\Date;
use Model\DefinesModel;
use Model\KasaModel;
use Model\KasaHareketModel;


$Tanimlamalar = new DefinesModel();
$KasaModel = new KasaModel();
$kasaHareketModel = new KasaHareketModel();

$enc_id = $_GET['id'] ?? 0;
$id = Security::decrypt($_GET['id'] ?? 0);

//Kasa hareket bilgilerini getir
if ($id > 0) {
    $kasaHareket = $kasaHareketModel->find($id);
}
$gelirGiderTipi = $kasaHareket->islem_tipi ?? 'gider';
$type_code = $gelirGiderTipi == 'gelir' ? 6 : 7;
$checked_gelir = ($gelirGiderTipi == 'gelir') ? 'checked' : '';
$checked_gider = ($gelirGiderTipi == 'gider') ? 'checked' : '';
$tutar = Helper::formattedMoney($kasaHareket->tutar ?? 0) ?? 0;




?>
<style>
    #islem_tipi_gider.card-input-element:checked+.card {
        border: 1px dashed var(--bs-danger);
    }

    #islem_tipi_gider.card-input-element:checked+.card .avatar-text {
        color: var(--bs-danger);
        border: 1px dashed var(--bs-danger);
    }

    #islem_tipi_gider.card-input-element:checked+.card .text-muted {
        color: var(--bs-danger) !important;
    }

    #islem_tipi_gider.card-input-element:checked+.card::after {
        color: var(--bs-danger);
    }
</style>
<div class="modal-header">
    <h5 class="modal-title" id="gelirGiderModalLabel">Gelir Gider İşlemleri</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="gelirGiderForm" method="post">
        <input type="hidden" class="form-control d-none mb-3" name="islem_id" id="islem_id" value="<?= $enc_id; ?>">

        <!-- İşlem Türü -->
        <div class="mb-3">
            <div class="row">
                <div class="col-md-6">
                    <label class="w-100" for="islem_tipi_gelir">
                        <input class="card-input-element visually-hidden" style="margin:0" type="radio" name="islem_tipi" id="islem_tipi_gelir" value="gelir" <?= $checked_gelir ?>>
                        <span class="card card-body d-flex flex-row justify-content-between align-items-center p-3">
                            <span class="hstack gap-2">
                                <span class="avatar-text">
                                    <i class="feather-trending-up"></i>
                                </span>
                                <span>
                                    <span class="d-block fs-13 fw-bold text-dark">Gelir</span>
                                    <span class="d-block text-muted mb-0">Gelir işlem kaydı</span>
                                </span>
                            </span>
                        </span>
                    </label>
                </div>
                <div class="col-md-6">
                    <label class="w-100" for="islem_tipi_gider">
                        <input class="card-input-element visually-hidden" style="margin:0" type="radio" name="islem_tipi" id="islem_tipi_gider" value="gider" <?= $checked_gider ?>>
                        <span class="card card-body d-flex flex-row justify-content-between align-items-center p-3">
                            <span class="hstack gap-2">
                                <span class="avatar-text">
                                    <i class="feather-trending-down"></i>
                                </span>
                                <span>
                                    <span class="d-block fs-13 fw-bold text-dark">Gider</span>
                                    <span class="d-block text-muted mb-0">Gider işlem kaydı</span>
                                </span>
                            </span>
                        </span>
                    </label>
                </div>
            </div>
        </div>



        <!-- İşleme Tarihi -->
        <div class="mb-3">
            <div class="row">

                <div class="col-md-6">

                    <label for="islem_tarihi" class="form-label">İşlem Tarihi *</label>
                    <input type="text" class="form-control flatpickr flatpickr-time-input" name="islem_tarihi" id="islem_tarihi" required
                        value="<?= Date::dmYHIS($kasaHareket->islem_tarihi ??  date('d.m.Y H:i:s')); ?>">
                </div>

                <div class="col-md-6">

                    <label for="tutar" class="form-label">Tutar (₺) *</label>
                    <div class="input-group">
                        <input type="text" class="form-control money" id="tutar" name="tutar"
                            placeholder="0.00" required
                            value="<?= $tutar ?? 0; ?>">
                        <span class="input-group-text">₺</span>
                    </div>
                </div>
            </div>
        </div>


        <!-- Kategori -->
        <div class="mb-3">
            <label for="kategori" class="form-label islem-tipi-grup"><?= ucfirst($gelirGiderTipi); ?> Grubu *</label>



            <?php echo $Tanimlamalar->getGelirGiderTipiSelect(
                                        "gelir_gider_grubu", 
                                        $type_code, 
                                        $kasaHareket->kategori ?? ''); ?>
        </div>
        <!-- Kategori -->
        <div class="mb-3 islem-kalemi">
            <label for="kategori" class="form-label islem-tipi-kalem"><?= ucfirst($gelirGiderTipi); ?> Kalemi </label>
            <select name="gelir_gider_kalemi" id="gelir_gider_kalemi" class="form-select select2">
            </select>
        </div>


        <!-- Açıklama -->
        <div class="mb-3">
            <label for="aciklama" class="form-label">Açıklama</label>
            <textarea class="form-control" id="aciklama" name="aciklama" rows="3"
                placeholder="Gelir gider işlemleriyle ilgili detaylı açıklama..."><?= $kasaHareket->aciklama ?? ''; ?></textarea>
        </div>

        <!-- Ödeme Yöntemi -->
        <div class="mb-3">
            <label for="odeme_yontemi" class="form-label">Ödeme Yöntemi</label>
            <?php echo Helper::getOdemeYontemiSelect("odeme_yontemi", 3) ?>
        </div>

        <!-- Belge No -->
        <div class="mb-3">
            <label for="makbuz_no" class="form-label">Makbuz No</label>
            <input type="text" class="form-control" id="makbuz_no" name="makbuz_no"
                value="<?= $kasaHareket->makbuz_no ?? ''; ?>"
                placeholder="Fatura, fiş veya belge numarası">
        </div>

        <div class="alert alert-info">
            <small><strong>*</strong> işaretli alanlar zorunludur.</small>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
    <button type="button" class="btn btn-primary" id="gelirGiderKaydet">Kaydet</button>
</div>


<script>
  
</script>