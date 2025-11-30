 $(document).ready(function() {
        var dt = initDataTable("#paymentsTable", {
            processing: true,
            serverSide: true,
            retrieve: true,
            ajax: {
                url: '/pages/personel/api/payments_server_side.php',
                type: 'GET',
                data: function(d) {
                    d.person_id = window.personId || 0;
                }
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
            ]
        });
    });
    document.querySelector('a[data-bs-target="#paymentsTab"]').addEventListener('shown.bs.tab', function() {
        try {
            $('#paymentsTable').DataTable().columns.adjust().responsive.recalc();
        } catch (e) {}
    });


    /** Yeni ödeme ve Güncelleme */
    $(document).on('click', '#newPaymentBtn , .payment-edit', function() {
        var id = $(this).data('id');
        $.get('/pages/personel/modal/payment_modal.php', {
                id: id
            })
            .done(function(html) {
                $('#paymentModal .payment-modal').html(html);
                $('#paymentModal').modal('show');
                $(".flatpickr-input").flatpickr({
                    dateFormat: "Y-m-d",
                    locale: "tr"
                });
                $(".select2").select2({ 
                    dropdownParent: $('#paymentModal'),
                });
            })
            .fail(function() {
                $('#paymentModal .payment-modal').html('<div class="p-3">İçerik yüklenemedi</div>');
                $('#paymentModal').modal('show');
            });
    });

    /** Ödeme Kaydetme/Modaldan */
    $(document).on('click', '#savePaymentBtn', function() {
        var form = $('#paymentForm');
        var fd = new FormData(form[0]);
        fd.append('person_id', window.personId);

        form.validate({
            rules: {
                payment_amount: {
                    required: true,
                },
                payment_date: {
                    required: true,
                },
            },
            messages: {
                payment_amount: {
                    required: "Ödeme tutarı gerekli",
                },
                payment_date: {
                    required: "Ödeme tarihi gerekli",
                },
            }
        });
        if (!form.valid()) {
            return;
        }




        $.ajax({
            url: '/pages/personel/api/paymentApi.php',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function(response) {
                var data = {};
                try {
                    data = JSON.parse(response);
                } catch (e) {
                    data = {
                        status: 'error',
                        message: 'Geçersiz cevap'
                    };
                }
                var title = data.status == 'success' ? 'Başarılı' : 'Hata';
                swal.fire({
                        title: title,
                        text: data.message,
                        icon: data.status,
                        confirmButtonText: 'Tamam'
                    })
                    .then(function() {
                        if (data.status === 'success') {
                            $('#paymentModal').modal('hide');
                            $('#paymentsTable').DataTable().ajax.reload(null, false);
                        }
                    });
            },
            error: function(xhr, status, err) {
                console.error('AJAX error', status, err, xhr.responseText);
                alert('İstek gönderilemedi. Konsolu kontrol edin.');
            }
        });
    });

    /** Ödeme Silme */
    $(document).on('click', '.payment-delete', function() {
        var id = $(this).data('id');
        swal.fire({
            title: 'Emin misiniz?',
            text: 'Bu işlem geri alınamaz!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, sil!',
            cancelButtonText: 'Hayır, iptal et'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.post('/pages/personel/api/paymentApi.php', {
                    action: 'deletePayment',
                    payment_id: id
                }, function(response) {
                    var data = {};
                    try {
                        data = JSON.parse(response);
                    } catch (e) {
                        data = {
                            status: 'error',
                            message: 'Geçersiz cevap'
                        };
                    }
                    var title = data.status == 'success' ? 'Başarılı' : 'Hata';
                    swal.fire({
                            title: title,
                            text: data.message,
                            icon: data.status,
                            confirmButtonText: 'Tamam'
                        })
                        .then(function() {
                            if (data.status === 'success') {
                                $('#paymentsTable').DataTable().ajax.reload(null, false);
                            }
                        });
                });
            }
        });
    });