let url = "pages/dues/payment/api.php";

$(function () {

$(document).on("click", "#tahsilatKaydet", function () {
  var form = $("#tahsilatForm");
  var tahsilatTuru = $("#tahsilat_turu option:selected").text();
  var formData = new FormData(form[0]);

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

  
  Pace.restart(); // Pace.js yükleme çubuğunu başlat
  fetch(url, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      
      var finansalDurum = data.finansalDurum;
      //console.log(finansalDurum);
      
      //tablo satırnını 4.sütununa data.finansalDurum.toplam_borc değerini güncelle
      row.cell(row.index(), 3).data(finansalDurum.toplam_borc).draw(false);
      
      //tablo satırnını 5.sütununa data.finansalDurum.toplam_odeme değerini güncelle
      row.cell(row.index(), 4).data(finansalDurum.toplam_odeme).draw(false);
      
      //tablo satırnını 6.sütununa data.finansalDurum.bakiye değerini güncelle
      row.cell(row.index(), 5).data(finansalDurum.bakiye).draw(false);


      
      let title = data.status ? "Başarılı" : "Hata";

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
        text: url + " adresine istek atılırken bir hata oluştu.",
      });
    });
});

$(document).on("click", ".tahsilat-sil", function () {
  var id = $(this).data("id");
  var formData = new FormData();

  formData.append("id", id);
  formData.append("action", "tahsilat-sil"); // Form verilerine action ekle
  Pace.restart(); // Pace.js yükleme çubuğunu başlat

  fetch(url, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      //butonun olduğu satırı sil
      let tRow = $(this).closest("tr"); // Butonun bulunduğu satırı bul
      tRow.remove(); // Satırı kaldır
      
      $(".borc-etiket").text(data.borc); // Borç etiketini güncelle
      $(".tahsilat-etiket").text(data.odeme); // Tahsilat etiketini güncelle
      $(".bakiye-etiket").text(data.bakiye); // Bakiye etiketini güncelle

      $(row.node()).html(data.tableRow);

      let title = data.status == "succes" ? "Başarılı" : "Hata";

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
});

}); // document ready end