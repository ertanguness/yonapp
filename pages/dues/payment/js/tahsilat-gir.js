let url = "pages/dues/payment/api.php";

$(function () {

  $(document).on("click", "#tahsilatKaydet", function () {
    var form = $("#tahsilatForm");
    var tahsilatTuru = $("#tahsilat_turu option:selected").text();
    var formData = new FormData(form[0]);
    var kullanilacakKredi = $("#kullanilacak_kredi").val();

    formData.append("tahsilat_turu", tahsilatTuru); // Form verilerine tahsilat türünü ekle

    addCustomValidationValidValue();
    form.validate({
      rules: {
        tahsilat_turu: {
          required: true,
        },
        tutar: {
          required: true,
          validValue: true,
        },
        islem_tarihi: {
          required: true,
        },
        kasa_id: {
          required: true,
        },
      },
      messages: {
        tahsilat_turu: {
          required: "Tahsilat türü zorunludur.",
        },
        tutar: {
          required: "Tutar zorunludur.",
          validValue: "Tutar alanı zorunludur ve 0'dan büyük olmalıdır.",
        },
        islem_tarihi: {
          required: "İşlem tarihi zorunludur.",
        },
        kasa_id: {
          required: "Kasa seçimi zorunludur.",
        },
      },
    });
    if (!form.valid()) {
      return false;
    }

    formData.append("action", "tahsilat-kaydet"); // Form verilerine action ekle
    formData.append("borc_detay_ids", secilenBorcIdleri); // Form verilerine id ekle
    formData.append("kullanilacak_kredi", kullanilacakKredi); // Form verilerine kullanılacak kredi ekle


    //tahsilatKaydet butonunun içine yükleniyor spinnerı ekle
    setButtonLoading("#tahsilatKaydet", true, "Kaydediliyor...");


    Pace.restart(); // Pace.js yükleme çubuğunu başlat
    fetch(url, {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {

        console.log(data);
        
        setButtonLoading("#tahsilatKaydet", false,); // Buton yükleme durumunu kaldır
        var finansalDurum = data.finansalDurum ?? {};
        var rowData = data.rowData ?? {};
        //console.log(rowData);

        //kalan anapara 0'dan büyükse <i class="feather-trending-down fw-bold text-danger"></i> bunu da ekle


        //tablo satırnını 4.sütununa data.finansalDurum.toplam_borc değerini güncelle
        //Tablo başlığına göre getir
        //BORÇ TUTARI olan sütunu al

        // Eğer rowData varsa güncelleme yap
        if (Object.keys(rowData).length > 0) {
            row.cell(row.index(), 5).data(rowData.kalan_anapara).draw(false);
            
            //tablo satırnını 5.sütununa data.finansalDurum.toplam_odeme değerini güncelle
            row.cell(row.index(), 6).data(rowData.hesaplanan_gecikme_zammi).draw(false);
            
            row.cell(row.index(), 7).data(rowData.toplam_kalan_borc).draw(false);
            
            //tablo satırnını 6.sütununa data.finansalDurum.bakiye değerini güncelle
            row.cell(row.index(), 8).data(rowData.kredi_tutari).draw(false);
            row.cell(row.index(), 9).data(rowData.guncel_borc).draw(false);
          }




        let title = data.status  ? "Başarılı" : "Hata";

        Swal.fire({
          icon: data.status,
          title: title,
          text: data.message,
        }).then((result => {
          form[0].reset();
          secilenBorcIdleri = []; // Seçili borçları sıfırla

          // Modal içeriğini tekrar yükle
          $.get("/pages/dues/payment/tahsilat_gir_modal.php", {
            kisi_id: kisiId
          }, function (modalData) {
            $('.tahsilat-modal-body').html(modalData);

            // Select2 ve Flatpickr'ı yeniden başlat
            if ($(".select2").length > 0) {
              $(".select2").select2({
                placeholder: "Kasa Seçiniz",
                dropdownParent: $('#tahsilatGir'),
              });

              $("#tahsilat_turu").select2({
                tags: true,
                dropdownParent: $('#tahsilatGir'),
              });
            }

            if ($("#islem_tarihi").length > 0) {
              $("#islem_tarihi").flatpickr({
                dateFormat: "d.m.Y H:i",
                locale: "tr",
                enableTime: true,
                allowInput: true, // Manuel giriş için ekledik
                minuteIncrement: 1,
              });
            }
          });
        }));



      })
      .catch((error) => {
        console.error("Error:", error);
         setButtonLoading("#tahsilatKaydet", false,); // Buton yükleme durumunu kaldır
        Swal.fire({
          icon: "error",
          title: "Hata",
          text: url + " adresine istek atılırken bir hata oluştu.",
        });
      });
  });



}); // document ready end