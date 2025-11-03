

$(document).on('click', '.borc-sil', function () {
    let id = $(this).data('id');
    row = $(this).parents('tr');

    swal.fire({
        title: 'Emin misiniz?',
        text: "Bu işlem geri alınamaz!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Evet, Sil!',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            //modalda overlay göster
            Pace.restart(); // Pace.js yükleme çubuğunu başlat

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    action: 'borc_sil',
                    id: id
                },
                success: function (response) {
                    let data = JSON.parse(response);

                    console.log(data);

                    if (data.status == 'success') {
                        row.remove();
                        $(".borc-etiket").text('-' + data.finansalDurum.toplam_borc);
                        $(".tahsilat-etiket").text(data.finansalDurum.toplam_odeme);
                        $(".bakiye-etiket").text(data.finansalDurum.bakiye);
                    }

                    let title = data.status == 'success' ? 'Başarılı!' : 'Hata!';
                    swal.fire(title, data.message, data.status)
                },
                error: function () {
                    swal.fire('Hata!', 'İşlem sırasında bir hata oluştu.', 'error');
                }
            });

        }
    });
});


//Tahsilat gir modaldan silme işlemi
$(document).on("click", ".tahsilat-sil", function () {
    var id = $(this).data("id");
    var formData = new FormData();

    formData.append("id", id);
    formData.append("action", "tahsilat-sil"); // Form verilerine action ekle


    swal.fire({
        title: "Emin misiniz?",
        text: "Bu işlem geri alınamaz!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Evet, Sil!",
        cancelButtonText: "Vazgeç",
    }).then((result) => {
        if (result.isConfirmed) {
            //modalda overlay göster(pace değil)
           // $.LoadingOverlay("show");

            fetch(url, {
                method: "POST",
                body: formData,
            })
                .then((response) => response.json())
                .then((data) => {
                    //butonun olduğu satırı sil
                    let tRow = $(this).closest("tr"); // Butonun bulunduğu satırı bul
                    tRow.remove(); // Satırı kaldır

                    //console.log(data);

                    $(".borc-etiket").text(data.borc); // Borç etiketini güncelle
                    $(".tahsilat-etiket").text(data.odeme); // Tahsilat etiketini güncelle
                    $(".bakiye-etiket").text(data.bakiye); // Bakiye etiketini güncelle

                    let rowData = data.rowData;
                    //console.log(rowData);
                    
                    //tablo satırnını 4.sütununa data.finansalDurum.toplam_borc değerini güncelle
                    row.cell(row.index(), 5).data(rowData.kalan_anapara).draw(false);

                    //tablo satırnını 5.sütununa data.finansalDurum.toplam_odeme değerini güncelle
                    row.cell(row.index(), 6).data(rowData.hesaplanan_gecikme_zammi).draw(false);

                    row.cell(row.index(), 7).data(rowData.toplam_kalan_borc).draw(false);

                    //tablo satırnını 6.sütununa data.finansalDurum.bakiye değerini güncelle
                    row.cell(row.index(), 8).data(rowData.kredi_tutari).draw(false);
                    row.cell(row.index(), 9).data(rowData.guncel_borc).draw(false);

                    let title = data.status == "success" ? "Başarılı" : "Hata";

                    Swal.fire({
                        icon: data.status,
                        title: title,
                        text: data.message,
                    });
                })
                .catch((error) => {
                    console.error("Error:", error);
                    Swal.fire({
                        icon: "error",
                        title: "Hata",
                        text: error.message || "Bir hata oluştu.",
                    });
                });
        }
    });
});