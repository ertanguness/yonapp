let url = "/pages/finans-yonetimi/gelir-gider/api.php"
$(document).on('click', '#gelirGiderKaydet', function () {
    var form = $('#gelirGiderForm');

     form.validate({
        rules: {
            islem_tarihi: { required: true },
            tutar: { required: true },
            kategori: { required: true },
        },
        messages: {
            islem_tarihi: { required: "İşlem tarihi zorunludur." },
            tutar: { required: "Tutar zorunludur." },
            kategori: { required: "Kategori zorunludur." },
        },
    }); // jQuery Validation'ı tetikle

    if (!form.valid()) {
        return;
    }

    var formData = new FormData(form[0]);
    formData.append('action', 'gelir-gider-kaydet');
    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            var data = JSON.parse(response);

            let title = data.status === "success" ? "Başarılı!" : "Hata!";
            let text = data.message;
          
            swal.fire(title, text, data.status).then(() => {
                if (data.status === "success") {
                    location.reload(); // Sayfayı yenile
                }
            });
            
        },
        error: function (xhr, status, error) {
            // Hata durumunda yapılacak işlemler
            console.error(error);
            swal.fire("Hata!", "İşlem sırasında bir hata oluştu.", "error");
        }
    });

 
});

//Gelir gider güncelle
$(document).on('click', '.gelirGiderGuncelle', function () {
    var id = $(this).data('id');

    $.ajax({
        url: url,
        type: 'POST',
        data: {
            action: 'gelir-gider-getir',
            islem_id: id
        },
        success: function (response) {
            var data = JSON.parse(response);
            if (data.status === "success") {
                var islem = data.data;
                console.log(islem);
                $('#islem_id').val(id);
                $('#islem_tarihi').val(islem.islem_tarihi);
                $('#tutar').val(islem.tutar.toString().replace('.', ','));
                // Select2 için özel işlem
                $('#kategori').val(islem.kategori).trigger('change');
                $('#aciklama').val(islem.aciklama);
                $('#islem_tipi').val(islem.islem_tipi);
                $('#kasa_id').val(islem.kasa_id);
                $('#gelirGiderModal').modal('show');


            } else {
                swal.fire("Hata!", data.message, "error");
            }
        },
        error: function (xhr, status, error) {
            console.error(error);
            swal.fire("Hata!", "İşlem sırasında bir hata oluştu.", "error");
        }
    });
});

//Guncellenemez işlem için 
$(document).on('click', '.GuncellemeYetkisiYok', function () {
    swal.fire("Hata!", "Bu işlem buradan güncellenemez!.", "error");
});

// Gelir/Gider sil
$(document).on('click', '.gelirGiderSil', function () {
    var id = $(this).data('id');
    let row = $(this).closest('tr');
    console.log(url);
    swal.fire({
        title: "Emin misiniz?",
        text: "Bu işlem geri alınamaz!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Evet, sil!",
        cancelButtonText: "Hayır, iptal et"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    action: 'gelir-gider-sil',
                    islem_id: id
                },
                success: function (response) {
                    var data = JSON.parse(response);
                    console.log(data);
                    swal.fire(data.status === "success" ? "Başarılı!" : "Hata!", data.message, data.status);
                    if (data.status === "success") {
                        row.remove();
                        //Net Kalanı güncelle
                        $('#netKalan').text(data.data.bakiye);
                        $('#toplamGelir').text(data.data.toplam_gelir);
                        $('#toplamGider').text(data.data.toplam_gider);
                    }
                },
                error: function (xhr, status, error) {
                    console.error(error);
                    swal.fire("Hata!", "İşlem sırasında bir hata oluştu.", "error");
                }
            });
        }
    });
});

//Silme Yetkisi olmayan butona basınca
$(document).on('click', '.SilmeYetkisiYok', function () {
    swal.fire("Hata!", "Bu işlem buradan silinemez!.", "error");
});

$(document).on('click', '.dropdown-item.export', function() {
    var format = $(this).data('format');
    var url = 'pages/finans-yonetimi/gelir-gider/export.php?format=' + format;
    window.open(url, '_blank');
    
});