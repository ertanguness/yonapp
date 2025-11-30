<?php
$id = $_GET['id'] ?? null;
?>
<div class="modal-header">
    <h5 class="modal-title">Ödeme</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <div class="mb-3">
        <label class="form-label">Tutar</label>
        <input type="text" class="form-control" id="payment_amount">
    </div>
    <div class="mb-3">
        <label class="form-label">Tarih</label>
        <input type="text" class="form-control flatpickr" id="payment_date">
    </div>
    <div class="mb-3">
        <label class="form-label">Açıklama</label>
        <textarea class="form-control" id="payment_desc" rows="3"></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Durum</label>
        <select class="form-select" id="payment_status">
            <option value="Beklemede">Beklemede</option>
            <option value="Onaylı">Onaylı</option>
        </select>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
    <button type="button" class="btn btn-primary" id="savePaymentBtn">Kaydet</button>
</div>