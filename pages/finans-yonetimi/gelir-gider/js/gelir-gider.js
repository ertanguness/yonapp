let url = "/pages/finans-yonetimi/gelir-gider/api.php"
$(document).on('click', '#gelirGiderKaydet', function () {
    var form = $('#gelirGiderForm');
    let $this = $(this);

    // "tutar" alanındaki ' TL' simgesini temizleyerek kontrol et
    form.validate({
        rules: {
            islem_tarihi: { required: true },
            tutar: {
                required: true,
                normalizer: function (value) {
                    // ₺ simgesi, TL metni ve tüm boşlukları temizle
                    // Nokta ayracını kaldır, virgülü noktaya çevir
                    return value
                        .replace('₺', '')
                        .replace(' TL', '')
                        .replace(/\s+/g, '') // Tüm boşlukları temizle
                        .replace(/\./g, '')  // Binlik ayracı noktaları kaldır
                        .replace(',', '.')   // Virgülü noktaya çevir
                        .trim();
                },
            },
            kategori: { required: true },
        },
        messages: {
            islem_tarihi: { required: "İşlem tarihi zorunludur." },
            tutar: {
                required: "Tutar zorunludur.",
            },
            kategori: { required: "Kategori zorunludur." },
        },
    }); // jQuery Validation'ı tetikle

    if (!form.valid()) {
        return;
    }

    var formData = new FormData(form[0]);
    formData.append('action', 'gelir-gider-kaydet');
    formData.append("kategori", $('#gelir_gider_grubu option:selected').text());
    formData.append("alt_tur", $('#gelir_gider_kalemi option:selected').text());

    /**Butonu disabled yap */
    $this.prop('disabled', true);
    $this.html('<i class="fa fa-spinner fa-spin"></i> İşlem yapılıyor...');


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

            $('#gelirGiderModal').modal('hide');
            swal.fire(title, text, data.status).then(() => {

                if (data.status === "success") {
                    table.ajax.reload();
                }
            });

        },
        error: function (xhr, status, error) {
            // Hata durumunda yapılacak işlemler
            console.error(error);
            swal.fire("Hata!", "İşlem sırasında bir hata oluştu.", "error");
        }
    });


    $this.prop('disabled', false);
    $this.html('Kaydet');


});

let __export_in_flight = false;



$("#btnGelirGiderEkle").on("click", function () {
    $.get('/pages/finans-yonetimi/gelir-gider/modal/gelir_gider_modal.php', function (data) {
        $modal = $('#gelirGiderModal');
        $('.gelir-gider-modal-content').html(data);
        $('#gelirGiderModal').modal('show');

        //Modaldaki select2'leri başlat
        $(".modal .select2").select2({
            dropdownParent: $("#gelirGiderModal"),
        });
        $(".flatpickr").flatpickr({
            dateFormat: "d.m.Y H:i",
            locale: "tr",
            enableTime: true,
            time_24hr: true,
            minuteIncrement: 1,
            static: true,
            appendTo: $modal[0],
            position: "below",
            allowInput: true
        });
    }).done(function () {
        gelirGiderKalemleriGetir();
    });
});


//Gelir gider güncelle
$(document).on('click', '.gelirGiderGuncelle', function () {
    var id = $(this).data('id');

    //Gelir_Gider_Modal.php dosyasını id parametresi ile birlikte yükle
    $.get('/pages/finans-yonetimi/gelir-gider/modal/gelir_gider_modal.php?id=' + encodeURIComponent(id), function (data) {

        var $modal = $('#gelirGiderModal');

        $('.gelir-gider-modal-content').html(data);
        $('#gelirGiderModal').modal('show');

        //Modaldaki select2'leri başlat
        $(".modal .select2").select2({
            dropdownParent: $("#gelirGiderModal"),
        });
        console.log("Flatpickr init ediliyor");

        //Flatpickr varsa init
        $(".flatpickr").flatpickr({
            dateFormat: "d.m.Y H:i",
            locale: "tr",
            enableTime: true,
            time_24hr: true,
            minuteIncrement: 1,
            static: true,
            appendTo: $modal[0],
            position: "below",
            allowInput: true
        });


        //Gelir/Gider Kalemleri Getir
        gelirGiderKalemleriGetir();
    });

    // $.ajax({
    //     url: url,
    //     type: 'POST',
    //     data: {
    //         action: 'gelir-gider-getir',
    //         islem_id: id
    //     },
    //     success: function (response) {
    //         var data = JSON.parse(response);
    //         if (data.status === "success") {
    //             var islem = data.data;
    //             console.log(islem);
    //             $('#islem_id').val(id);
    //             $('#islem_tarihi').val(islem.islem_tarihi);
    //             $('#tutar').val(islem.tutar.toString().replace('.', ','));
    //             // Select2 için özel işlem
    //             $('#kategori').val(islem.kategori).trigger('change');
    //             $('#aciklama').val(islem.aciklama);
    //             $('#islem_tipi').val(islem.islem_tipi);
    //             $('#kasa_id').val(islem.kasa_id);
    //             $('#gelirGiderModal').modal('show');


    //         } else {
    //             swal.fire("Hata!", data.message, "error");
    //         }
    //     },
    //     error: function (xhr, status, error) {
    //         console.error(error);
    //         swal.fire("Hata!", "İşlem sırasında bir hata oluştu.", "error");
    //     }
    // });
});

//Guncellenemez işlem için 
$(document).on('click', '.GuncellemeYetkisiYok', function () {
    swal.fire("Hata!", "Bu işlem buradan güncellenemez!.", "error");
});

// Gelir/Gider sil
$(document).on('click', '.gelirGiderSil', function () {
    var id = $(this).data('id');


    console.log("kasahareket id " + id);

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

// $(document).on('click', '.dropdown-item.export', function () {
//     var format = $(this).data('format');
//     var url = '/pages/finans-yonetimi/gelir-gider/export.php?format=' + format;
//     window.open(url, '_blank');

// });

$(document).on('shown.bs.modal', '#gelirGiderModal', function () {
    $(".money").inputmask("decimal", {
        radixPoint: ",",
        groupSeparator: ".",
        digits: 2,
        autoGroup: true,
        rightAlign: false,
        prefix: "₺ ",
        removeMaskOnSubmit: true,
    });
});

//islem_tipi radio butonuna tıklanınca
$(document).on('change', 'input[name="islem_tipi"]', function () {
    var islemTipi = $(this).val();
    var kategoriSelect = $('#gelir_gider_grubu');
    kategoriSelect.empty(); // Mevcut seçenekleri temizle

    /**İslem tiplerini labela ata */
    var label = islemTipi === 'gelir' ? 'Gelir' : 'Gider';
    $('.islem-tipi-grup').text(label + ' Grubu');
    $('.islem-tipi-kalem').text(label + ' Kalemi');

    /**İşlem tipi gelir ise gilze */
    if (islemTipi === 'gelir') {
        $('.islem-kalemi').addClass('d-none');
    } else {
        $('.islem-kalemi').removeClass('d-none');
    }

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'action': 'kategori-getir',
            'islem_tipi': islemTipi
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                // Gelen kategorileri option olarak ekle, define_name'i hem text'te hem de data-name attribute'unda tut
                data.kategoriler.forEach(function (kategori) {
                    var newOption = new Option(kategori.define_name, kategori.id, false, false);
                    $(newOption).attr('data-name', kategori.define_name);
                    kategoriSelect.append(newOption);
                });

                // En az bir kategori varsa ilkini seç ve alt türleri getir
                if (kategoriSelect.find('option').length > 0) {
                    kategoriSelect.prop('selectedIndex', 0);
                    // Seçilen kategoriye göre kalemleri yükle
                    gelirGiderKalemleriGetir();
                }

                //// Select2'yi güncelle
                kategoriSelect.trigger('change');
            } else {
                swal.fire("Hata!", data.message, "error");
            }
        })
});


$(document).on('change', '#gelir_gider_grubu', function (e) {
    gelirGiderKalemleriGetir();
});


/** Defines Tablosundan type değeri ve kategoriye göre alt türleri getirir */
function gelirGiderKalemleriGetir() {
    var type = $("input[name='islem_tipi']:checked").val() == "gider" ? 7 : 6;
    var $selected = $('#gelir_gider_grubu').find('option:selected');



    // Kategori seçilmediyse fonksiyondan çık
    if ($selected.length === 0 || !$selected.val()) {
        return;
    }


    // define_name'i data-name attribute'undan al; yoksa text'i kullan
    var category = $selected.data('name') || $selected.text();
    var fd = new FormData();
    fd.append('action', 'get-gelir-gider-kalemleri');
    fd.append('type', type);
    fd.append('kategori', category);


    fetch(url, {
        method: 'POST',
        body: fd
    })
        .then(response => response.json())
        .then(function (response) {
            var data = response.data;
            var options = '<option value="">Seçiniz</option>';
            $.each(data, function (index, item) {
                options += '<option value="' + item.id + '">' + item.alt_tur + '</option>';
            });
            $('#gelir_gider_kalemi').html(options);
        });
}


