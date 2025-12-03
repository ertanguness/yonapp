<?php
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use App\Helper\Date;
use Model\PersonelOdemelerModel;

$id = $_GET['id'] ?? 0;
$model = new PersonelOdemelerModel();
$payment = $model->find($id, true);
?>
<div class="modal-header">
    <h5 class="modal-title">Ödeme</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <form id="paymentForm" method="post" enctype="multipart/form-data">
        <input type="hidden" id="payment_id" name="payment_id" value="<?php echo ($id ?? 0); ?>">
        <input type="hidden" id="action" name="action" value="savePayment">
        <div class="mb-3">
            <label class="form-label">Tutar</label>
            <div class="input-group">
                <div class="input-group-text"><i class="feather-dollar-sign"></i></div>
                <input type="text" class="form-control money" id="payment_amount" name="payment_amount" value="<?php echo htmlspecialchars($payment->amount ?? ''); ?>">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Tarih</label>
            <div class="input-group">
                <div class="input-group-text"><i class="feather-calendar"></i></div>
                <input type="text" class="form-control flatpickr-input" 
                id="payment_date" name="payment_date" autocomplete="off"
                value="<?php echo Date::dmY($payment->date ?? ''); ?>">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Açıklama</label>
            <div class="input-group">
                <div class="input-group-text"><i class="feather-file-text"></i></div>
                <textarea class="form-control" id="payment_desc" name="payment_desc" rows="3"><?php echo htmlspecialchars($payment->description ?? ''); ?></textarea>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Durum</label>
            <?php $status = $payment->status ?? ''; ?>
           <div class="input-group flex-nowrap w-100">
                <div class="input-group-text"><i class="feather-check-circle"></i></div>
                <select class="form-select select2" id="payment_status" name="payment_status">
                    <option value="Beklemede" <?php echo $status === 'Beklemede' ? 'selected' : ''; ?>>Beklemede</option>
                    <option value="Onaylı" <?php echo $status === 'Onaylı' ? 'selected' : ''; ?>>Onaylı</option>
                </select>
            </div>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
    <button type="button" class="btn btn-primary" id="savePaymentBtn">Kaydet</button>
</div>
