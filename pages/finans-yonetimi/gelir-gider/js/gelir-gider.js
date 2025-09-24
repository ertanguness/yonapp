let url = "pages/finans-yonetimi/gelir-gider/api.php"
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

            swal.fire(title, text, data.status);
            console.log(data);
            
        },
        error: function (xhr, status, error) {
            // Hata durumunda yapılacak işlemler
            console.error(error);
            swal.fire("Hata!", "İşlem sırasında bir hata oluştu.", "error");
        }
    });
});