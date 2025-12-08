<?php

use App\Services\Gate;

?>

<?php if (!empty($person_id)): ?>

    <div class="my-3 my-xxl-0 my-md-3 my-md-0">
        <div class="fs-20 text-dark"><span class="fw-bold"><?php echo number_format($yearTotal ?? 0, 2, ',', '.'); ?></span> / <em class="fs-11 fw-medium">TL</em></div>
        <div class="fs-12 text-muted mt-1">Bu yıl <a href="javascript:void(0);" class="badge bg-primary text-white ms-2"><?php echo date('Y'); ?></a></div>
    </div>

<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3 mt-3">
    <h6 class="mb-0">Ödeme Listesi</h6>
    
    <div class="d-flex gap-2">
        <!-- Ödeme ekleme yetkisi kontrolü -->
        <?php if (Gate::allows("odeme_ekle_guncelle_sil")): ?>
            <button  type="button" class="btn btn-outline-primary btn-sm" id="newPaymentBtn">Yeni Ödeme</button>
        <?php else: ?>
            <button type="button" class="btn btn-outline-primary btn-sm" id="newPaymentBtn" disabled>Yeni Ödeme</button>
        <?php endif; ?>

    </div>

</div>
<div class="table-responsive w-100">
    <table class="table table-hover dttables w-100" id="paymentsTable">
        <thead>
            <tr>
                <th style="width:7%">Sıra</th>
                <th>Tutar</th>
                <th>Tarih</th>
                <th>Açıklama</th>
                <th>Durum</th>
                <th style="width:10%">İşlem</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content payment-modal"></div>
    </div>
</div>
<script src="/pages/personel/js/payment.js?<?= filemtime("pages/personel/js/payment.js") ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var goBtn = document.getElementById('goNewPayment');
    if (goBtn) {
        goBtn.addEventListener('click', function () {
            var trigger = document.getElementById('newPaymentBtn');
            if (trigger && !trigger.disabled) {
                trigger.click();
            }
        });
    }
});
</script>
