let url = "/pages/finans-yonetimi/kasa/api.php";
$(document).on('click', '#kasa_kaydet', function() {
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

    if(!form.valid()) {
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
            icon:data.status,
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
$(document).on('click', '.kasa-sil', function() {
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
                success: function(response) {
                    if (response.status === 'success') {
                        row.fadeOut(500, function() {
                            $(this).remove();
                        });
                        swal.fire({
                            icon: 'success',
                            title: 'Başarılı',
                            text: 'Kasa silme işlemi başarılı',
                        }); 
                    }else{
                        swal.fire({
                            icon: 'error',
                            title: 'Hata',
                            text: response.message,
                            confirmButtonText: 'Tamam'
                        });
                    }
                },
                error: function(xhr, status, error) {
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
