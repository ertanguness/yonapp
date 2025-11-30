<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">Ödeme Listesi</h6>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm" id="newPaymentBtn">Yeni Ödeme</button>
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