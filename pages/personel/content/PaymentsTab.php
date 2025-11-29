<div class="p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">Ödeme Listesi</h6>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm" id="newPaymentBtn">Yeni Ödeme</button>
        </div>
    </div>
    <div class="table-responsive w-100">
        <table class="table table-hover datatables w-100" id="paymentsTable">
            <thead>
                <tr>
                    <th style="width:40px">Sıra</th>
                    <th>Tutar</th>
                    <th>Tarih</th>
                    <th>Açıklama</th>
                    <th>Durum</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content payment-modal"></div>
    </div>
</div>
<script>
    if (typeof window.onDataTablesReady !== 'function') {
        window.onDataTablesReady = function(cb) {
            var tries = 0;
            (function wait() {
                if (window.jQuery && jQuery.fn && jQuery.fn.DataTable && typeof window.initDataTable === 'function') {
                    cb();
                    return;
                }
                if (tries++ > 100) {
                    console.error('DataTables veya initDataTable yüklenemedi');
                    return;
                }
                setTimeout(wait, 100);
            })();
        };
    }
    
    window.onDataTablesReady(function() {
        var dt = initDataTable('#paymentsTable', {
            processing: true,
            serverSide: true,
            retrieve: true,
            ajax: {
                url: '/pages/personel/api/payments_server_side.php',
                type: 'GET'
            },
            columns: [{
                    data: null,
                    orderable: false,
                    render: function(d, t, r, m) {
                        return m.row + 1 + m.settings._iDisplayStart;
                    }
                },
                {
                    data: 'amount'
                },
                {
                    data: 'date'
                },
                {
                    data: 'description'
                },
                {
                    data: 'status'
                },
                {
                    data: 'actions',
                    orderable: false
                }
            ],
            order: [
                [1, 'asc']
            ]
        });
        document.querySelector('a[data-bs-target="#paymentsTab"]').addEventListener('shown.bs.tab', function() {
            try {
                $('#paymentsTable').DataTable().columns.adjust().responsive.recalc();
            } catch (e) {}
        });
        $(document).on('click', '#newPaymentBtn', function() {
            $.get('/pages/personel/modal/payment_modal.php')
                .done(function(html) {
                    $('#paymentModal .payment-modal').html(html);
                    $('#paymentModal').modal('show');
                })
                .fail(function() {
                    $('#paymentModal .payment-modal').html('<div class="p-3">İçerik yüklenemedi</div>');
                    $('#paymentModal').modal('show');

                    $(".flatpickr").flatpickr({
                        dateFormat: "d.m.Y",
                    });
                });
        });
        $(document).on('click', '.payment-edit', function() {
            var id = $(this).data('id');
            $.get('/pages/personel/modal/payment_modal.php', {
                    id: id
                })
                .done(function(html) {
                    $('#paymentModal .payment-modal').html(html);
                    $('#paymentModal').modal('show');
                })
                .fail(function() {
                    $('#paymentModal .payment-modal').html('<div class="p-3">İçerik yüklenemedi</div>');
                    $('#paymentModal').modal('show');
                });
        });

    })();
</script>