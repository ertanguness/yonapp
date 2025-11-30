<?php
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use App\Helper\Date;
use App\Helper\Helper;
use Model\PersonelGorevlerModel;

$GorevModel = new PersonelGorevlerModel();



$id = $_GET['id'] ?? 0;
$gorev = $GorevModel->find($id,true);

// Helper::dd($gorev);

?>


<div class="modal-header">
    <h5 class="modal-title">Görev</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <form id="taskForm">
        <input type="hidden" id="task_id" value="<?php echo $id; ?>">
        <div class="mb-3">
            <label class="form-label">Başlık</label>
            <input type="text" class="form-control" id="task_title" value="<?php echo $gorev->title ?? ''; ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Açıklama</label>
            <textarea class="form-control" id="task_desc" rows="3"><?php echo $gorev->description ?? ''; ?></textarea>
        </div>
        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label">Başlangıç</label>
                <input type="text" class="form-control flatpickr" id="task_start" value="<?php echo Date::dmY($gorev->start_date ?? ''); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Bitiş</label>
                <input type="text" class="form-control flatpickr" id="task_end" value="<?php echo Date::dmY($gorev->end_date ?? ''); ?>">
            </div>
        </div>
        <div class="mt-3">
            <label class="form-label">Durum</label>
            <select class="form-select select2" id="task_status">
                <option <?php echo ($gorev->status ?? '') === 'Beklemede' ? 'selected' : ''; ?> value="Beklemede">Beklemede</option>
                <option <?php echo ($gorev->status ?? '') === 'Devam' ? 'selected' : ''; ?> value="Devam">Devam</option>
                <option <?php echo ($gorev->status ?? '') === 'Tamamlandı' ? 'selected' : ''; ?> value="Tamamlandı">Tamamlandı</option>
            </select>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
    <button type="button" class="btn btn-primary" id="saveTaskBtn">Kaydet</button>
</div>