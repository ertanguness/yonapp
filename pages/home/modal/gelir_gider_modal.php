<?php
require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php'; // Gerekli dosyaları yükle


use App\Helper\Helper;

?>



<div class="modal-header">
    <h5 class="modal-title" id="gelirGiderModalLabel">Gelir Gider İşlemleri</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="gelirGiderForm" method="post">
        <input type="hidden" name="islem_id" id="islem_id" value="0">


        <!-- İşlem Tarihi -->
        <div class="mb-3">
            <div class="row">

                <div class="col-md-6">

                    <label for="islem_tarihi" class="form-label">İşlem Tarihi *</label>
                    <input type="text" class="form-control flatpickr flatpickr-time-input" name="islem_tarihi" id="islem_tarihi" required
                        value="<?= date('d-m-Y H:i'); ?>">
                </div>

                <div class="col-md-6">

                    <label for="tutar" class="form-label">Tutar (₺) *</label>
                    <div class="input-group">
                        <input type="text" class="form-control money" id="tutar" name="tutar"
                            placeholder="0.00" required>
                        <span class="input-group-text">₺</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Kategori -->
        <div class="mb-3">
            <label for="kategori" class="form-label">Kategori *</label>
            <?php echo Helper::getOdemeKategoriSelect("kategori", 6) ?>

        </div>
        <div class="mb-3 kisiler d-none">
            <label for="kisiler" class="form-label">Daire Sakini *</label>
            <?php //echo $KisiHelper->KisiSelect("kisiler") ?>

        </div>

        <!-- Açıklama -->
        <div class="mb-3">
            <label for="aciklama" class="form-label">Açıklama</label>
            <textarea class="form-control" id="aciklama" name="aciklama" rows="3"
                placeholder="Gelir gider işlemleriyle ilgili detaylı açıklama..."></textarea>
        </div>

        <!-- Ödeme Yöntemi -->
        <div class="mb-3">
            <label for="odeme_yontemi" class="form-label">Ödeme Yöntemi</label>
            <?php echo Helper::getOdemeYontemiSelect("odeme_yontemi") ?>
        </div>

        <!-- Belge No -->
        <div class="mb-3">
            <label for="belge_no" class="form-label">Belge No</label>
            <input type="text" class="form-control" id="belge_no" name="belge_no"
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