<?php
$id = $_GET['id'] ?? null;
?>
<div class="modal-header">
    <h5 class="modal-title">İzin</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <div class="row g-2">
        <div class="col-md-6">
            <label class="form-label">Başlangıç</label>
            <input type="text" class="form-control flatpickr" id="leave_start">
        </div>
        <div class="col-md-6">
            <label class="form-label">Bitiş</label>
            <input type="text" class="form-control flatpickr" id="leave_end">
        </div>
    </div>
    <div class="mt-3">
        <label class="form-label">Tür</label>
        <select class="form-select" id="leave_type">
            <option value="Yıllık">Yıllık</option>
            <option value="Mazeret">Mazeret</option>
            <option value="Hastalık">Hastalık</option>
        </select>
    </div>
    <div class="mt-3">
        <label class="form-label">Açıklama</label>
        <textarea class="form-control" id="leave_desc" rows="3"></textarea>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
    <button type="button" class="btn btn-primary" id="saveLeaveBtn">Kaydet</button>
</div>