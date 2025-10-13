import { getBorclandirmaInfo,getBorclandirmaDetayInfo } from "/assets/js/utils/debit.js";
let url = "/pages/dues/payment/api.php";
$(document).ready(function () {
    //console.log("Borç Ekle JS yüklendi ve çalışıyor.");

    // Event delegation kullanarak modal içindeki dinamik elementleri dinle
    $(document).on("change", "#borclandirmalar", function () {
        let borclandirmaId = $(this).val();
        // Borçlandırma bilgilerini getir
        getBorclandirmaInfo(borclandirmaId)
            .then((data) => {
                // Başarılı bir şekilde verileri aldık
                console.log(data);
                $(".borc-baslangic").text("Başlangıç Tarihi : " + data.baslangic_tarihi);
                $(".borc-bitis").text("Bitiş Tarihi : " + data.bitis_tarihi);
                $("#borc_tutar").val(data.tutar.replace(".", ","));
                $("#borc_islem_tarihi").flatpickr({
                    defaultDate: [data.baslangic_tarihi],
                    enableTime: true,
                    dateFormat: "d.m.Y H:i",
                    time_24hr: true,
                    locale: "tr",
                });
            })
            .catch((error) => {
                // Hata durumunda
                console.error(error);
            });
    });
});

$(document).on('click', '#borcEkleBtn', function () {
    let form = $("#borcEkleForm");
    let formData = new FormData(form[0]);
    let kisiId = $("input[name='kisi_id']").val();

 
    form.validate({
        rules: {
            borclandirmalar: { required: true },
            tutar: { required: true },
            islem_tarihi: { required: true },
        },
        messages: {
            borclandirmalar: { required: "Lütfen bir borç türü seçiniz." },
            tutar: { required: "Lütfen tutar giriniz." },
            islem_tarihi: { required: "Lütfen işlem tarihi giriniz." },
        },

    })
    if (!form.valid()) {
        return;
    }


    formData.append("action", "borc_ekle");

    // for (let pair of formData.entries()) {
    //     console.log(pair[0] + ', ' + pair[1]);
    // }
    // return;

    $.ajax({
        url: url,
        method: "POST",
        data: formData,
        processData: false, // Prevent jQuery from processing the data
        contentType: false, // Prevent jQuery from setting the content type
        success: function (response) {
            const res = JSON.parse(response);
            if (res.status === "success") {
                // Başarılı bir yanıt aldık
                swal.fire({
                    title: "Başarılı!",
                    text: res.message,
                    icon: "success",
                }).then(() => {
                   
                    // Modalı kapat
                    $('#borcEkleModal').modal('hide');
                    //KisiBorcDetay modalını güncelle
                    $.get("pages/dues/payment/tahsilat-detay.php", {
                        kisi_id: kisiId
                    }, function (data) {
                        // Verileri tabloya ekle
                        $('.borc-detay').html(data);
                        // Modal'ı göster
                        $('#kisiBorcDetay').modal('show');
                    });

                })




            }
        },
        error: function (xhr, status, error) {
            // Hata durumunda
            console.error("AJAX Hatası:", status, error);
            swal.fire({
                title: "Hata!",
                text: "İşlem sırasında bir hata oluştu. Lütfen tekrar deneyiniz.",
                icon: "error",
            });
        }
    });
});

