let url = "/pages/finans-yonetimi/kasa/api.php";
$(document).on('click', '#kasa_kaydet', function () {
    var form = $('#kasaForm');

    form.validate({
        rules: {
            'kasa_adi': {
                required: true,
                maxlength: 100
            },
        },
        messages: {
            'kasa_adi': {
                required: 'Kasa adı boş bırakılamaz.',
                maxlength: 'Kasa adı en fazla 100 karakter olabilir.'
            },
        },
    });

    if (!form.valid()) {
        return false;
    }

    let formData = new FormData(form[0]);
    formData.append('action', 'kasa_kaydet');

    fetch(url, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            let title = data.status == 'success' ? 'Başarılı' : 'Hata';
            swal.fire({
                icon: data.status,
                title: title,
                text: data.message,
            })


        })
        .catch(error => {
            swal.fire({
                icon: 'error',
                title: 'Hata',
                text: error.message,
                confirmButtonText: 'Tamam'
            });
        });


});

/* Kasa Sil */
$(document).on('click', '.kasa-sil', function () {
    let kasa_id = $(this).data('id');
    let row = $(this).closest('tr');


    swal.fire({
        title: 'Emin misiniz?',
        text: "Bu işlemi geri alamazsınız!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Evet, sil',
        cancelButtonText: 'Hayır, iptal et'
    }).then((result) => {
        if (result.isConfirmed) {
            // Silme işlemini gerçekleştir
            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    action: 'kasa_sil',
                    kasa_id: kasa_id
                },
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        row.fadeOut(500, function () {
                            $(this).remove();
                        });
                        swal.fire({
                            icon: 'success',
                            title: 'Başarılı',
                            text: 'Kasa silme işlemi başarılı',
                        });
                    } else {
                        swal.fire({
                            icon: 'error',
                            title: 'Hata',
                            text: response.message,
                            confirmButtonText: 'Tamam'
                        });
                    }
                },
                error: function (xhr, status, error) {
                    swal.fire({
                        icon: 'error',
                        title: 'Hata',
                        text: error.message,
                        confirmButtonText: 'Tamam'
                    });
                }
            });
        }
    });
});


$(document).on('click', '.is-default', function () {
    var row = $(this).closest("tr");
    var id = $(this).data("id");


    swal.fire({
        title: 'Emin misiniz?',
        text: "Kasa varsayılan olarak ayarlanacaktır!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Evet, ayarla',
        cancelButtonText: 'Hayır, iptal et'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    action: 'varsayilan_kasa_yap',
                    kasa_id: id
                },
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        swal.fire({
                            icon: 'success',
                            title: 'Başarılı',
                            text: 'Kasa varsayılan olarak ayarlandı',
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        swal.fire({
                            icon: 'error',
                            title: 'Hata',
                            text: response.message,
                            confirmButtonText: 'Tamam'
                        });
                    }
                },
                error: function (xhr, status, error) {
                    swal.fire({
                        icon: 'error',
                        title: 'Hata',
                        text: error.message,    
                        confirmButtonText: 'Tamam'
                    });
                }
            });
        }
});

});
// Kasa Transfer Modal: hedef kasa seçeneklerini kaynak kasayı hariç tutacak şekilde ayarla
$(document).on('click', '.kasa-transfer', function () {
  const sourceEnc = $(this).data('source-id');
  const $sel = $('#target_kasa_id');
  $sel.find('option').prop('disabled', false).show();
  if (sourceEnc) {
    $sel.find(`option[value='${sourceEnc}']`).prop('disabled', true).hide();
  }
  $sel.val('').trigger('change');
});

// Kasa Transfer Formu Gönderimi
$(document).on('click', '#kasaTransferSubmit', function (e) {

  addCustomValidationMethods(false);
  const form = $('#kasaTransferForm');

  form.validate({
    rules: {
      target_kasa_id: { required: true },
      transfer_tutar: { required: true, validNumber: true },
      transfer_tarih: { required: true },
      transfer_aciklama: { required: true, minlength: 10 }
    },
    messages: {
      target_kasa_id: { required: 'Hedef kasa zorunludur.' },
      transfer_tutar: { required: 'Tutar zorunludur.' },
      transfer_tarih: { required: 'Tarih zorunludur.' },
      transfer_aciklama: { required: 'Açıklama zorunludur.', minlength: 'En az 10 karakter girin.' }
    }
  });

  if (!form.valid()) { return false; }


  const sourceEnc = $('#transferSourceId').val();
  const targetEnc = $('#target_kasa_id').val();
  if (sourceEnc && targetEnc && sourceEnc === targetEnc) {
    swal.fire({ icon: 'error', title: 'Hata', text: 'Aynı kasa seçilemez.' });
    return false;
  }

  const formData = new FormData(form[0]);
  setButtonLoading('#kasaTransferSubmit', true, 'Gönderiliyor...', 'Transfer Yap');

  fetch(url, { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      setButtonLoading('#kasaTransferSubmit', false, '', 'Transfer Yap');
      if (data.status === 'success') {
        const d = data.data || {};
        const html = `Referans: <strong>${d.ref || '-'}</strong><br/>Kaynak Yeni Bakiye: <strong>${d.source_new_balance || '-'}</strong><br/>Hedef Yeni Bakiye: <strong>${d.target_new_balance || '-'}</strong>`;
        swal.fire({ icon: 'success', title: 'Transfer Başarılı', html: html }).then(() => {
          $('#kasaTransferModal').modal('hide');
          try { location.reload(); } catch(e) {}
        });
      } else {
        swal.fire({ icon: 'error', title: 'Hata', text: data.message || 'İşlem başarısız.' });
      }
    })
    .catch(err => {
      setButtonLoading('#kasaTransferSubmit', false, '', 'Transfer Yap');
      swal.fire({ icon: 'error', title: 'Hata', text: err.message || 'Bir hata oluştu.' });
    });
});





