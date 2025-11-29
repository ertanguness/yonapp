<?php
$id = $_GET['id'] ?? null;
?>
<div class="modal-header">
    <h5 class="modal-title">Görev</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <div class="mb-3">
        <label class="form-label">Başlık</label>
        <input type="text" class="form-control" id="task_title" value="">
    </div>
    <div class="mb-3">
        <label class="form-label">Açıklama</label>
        <textarea class="form-control" id="task_desc" rows="3"></textarea>
    </div>
    <div class="row g-2">
        <div class="col-md-6">
            <label class="form-label">Başlangıç</label>
            <input type="text" class="form-control flatpickr" id="task_start">
        </div>
        <div class="col-md-6">
            <label class="form-label">Bitiş</label>
            <input type="text" class="form-control flatpickr" id="task_end">
        </div>
    </div>
    <div class="mt-3">
        <label class="form-label">Durum</label>
        <select class="form-select" id="task_status">
            <option value="Beklemede">Beklemede</option>
            <option value="Devam">Devam</option>
            <option value="Tamamlandı">Tamamlandı</option>
        </select>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
    <button type="button" class="btn btn-primary" id="saveTaskBtn">Kaydet</button>
</div>