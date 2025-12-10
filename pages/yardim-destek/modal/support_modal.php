<?php
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use Model\DestekModel;

$Model = new DestekModel();
$id = $_GET['id'] ?? 0;
$talep = $Model->find($id, true);
?>

<div class="modal-header">
    <h5 class="modal-title">Destek Talebi</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
<div class="modal-body">
    <form id="supportForm" method="post" enctype="multipart/form-data">
        <input type="hidden" id="support_id" name="support_id" value="<?php echo htmlspecialchars($id); ?>">
        <input type="hidden" id="action" name="action" value="saveSupport">

        <div class="mb-3">
            <label class="form-label">Konu</label>
            <div class="input-group">
                <div class="input-group-text"><i class="feather-type"></i></div>
                <input type="text" class="form-control" id="support_subject" name="support_subject" value="<?php echo htmlspecialchars($talep->konu ?? ''); ?>">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Açıklama</label>
            <div class="input-group">
                <div class="input-group-text"><i class="feather-file-text"></i></div>
                <textarea class="form-control" id="support_desc" name="support_desc" rows="3"><?php echo htmlspecialchars($talep->aciklama ?? ''); ?></textarea>
            </div>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
    <button type="button" class="btn btn-primary" id="saveSupportBtn">Kaydet</button>
    </div>

