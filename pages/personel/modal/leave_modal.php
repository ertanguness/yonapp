<?php
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';
use App\Helper\Date;
use Model\PersonelIzinlerModel;
$id = $_GET['id'] ?? 0;
$model = new PersonelIzinlerModel();
$leave = $model->find($id, true);
?>
<div class="modal-header">
    <h5 class="modal-title">İzin</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <form id="leaveForm" method="post" enctype="multipart/form-data">
        <input type="hidden" id="leave_id" name="leave_id" value="<?php echo (int)($id ?? 0); ?>">
        <input type="hidden" id="action" name="action" value="saveLeave">
        <div class="row g-2">

            <div class="col-md-6">
                <label class="form-label">Başlangıç</label>
                <div class="input-group">
                    <div class="input-group-text"><i class="feather-calendar"></i></div>
                    <input type="text" class="form-control flatpickr" 
                    id="leave_start" name="leave_start" autocomplete="off"
                    value="<?php echo Date::dmY($leave->start_date ?? ''); ?>">
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Bitiş</label>
                <div class="input-group">
                    <div class="input-group-text"><i class="feather-calendar"></i></div>
                    <input type="text" class="form-control flatpickr" id="leave_end" name="leave_end" autocomplete="off"
                    value="<?php echo Date::dmY($leave->end_date ?? ''); ?>">
                </div>
            </div>
        </div>
        
        <div class="mt-3">
            <label class="form-label">Tür</label>
            <div class="input-group">
                <div class="input-group-text"><i class="feather-list"></i></div>
                <select class="form-select select2" id="leave_type" name="leave_type">
                    <?php $type = $leave->type ?? ''; ?>
                    <option value="Yıllık" <?php echo $type === 'Yıllık' ? 'selected' : ''; ?>>Yıllık</option>
                    <option value="Mazeret" <?php echo $type === 'Mazeret' ? 'selected' : ''; ?>>Mazeret</option>
                    <option value="Hastalık" <?php echo $type === 'Hastalık' ? 'selected' : ''; ?>>Hastalık</option>
                </select>
            </div>
        </div>
        <div class="mt-3">
            <label class="form-label">Açıklama</label>
            <div class="input-group">
                <div class="input-group-text"><i class="feather-file-text"></i></div>
                <textarea class="form-control" id="leave_desc" name="leave_desc" rows="3"><?php echo htmlspecialchars($leave->description ?? ''); ?></textarea>
            </div>
        </div>
    </form>
    
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
    <button type="button" class="btn btn-primary" id="saveLeaveBtn">Kaydet</button>
</div>
