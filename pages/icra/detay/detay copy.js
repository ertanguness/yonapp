let icraurl = "pages/icra/detay/detayApi.php";

// Ödeme Planı Kaydet
$(document).on("click", "#odeme_kaydet", function () {
  var form = $("#odemePlanForm");
  var formData = new FormData(form[0]);

  formData.append("action", "odeme_plan_kaydet");
  formData.append("id", $("#icra_id").val());

  var validator = $("#odemePlanForm").validate({
    rules: {
      "borc_tutari": { required: true, number: true },
      "faiz_orani": { required: true, number: true },
      "taksit": { required: true, number: true },
      "odeme_baslangic_tarihi": { required: true }
    },
    messages: {
      "borc_tutari": {
        required: "Lütfen toplam borcu giriniz",
        number: "Geçerli bir sayı giriniz"
      },
      "faiz_orani": {
        required: "Lütfen faiz oranını giriniz",
        number: "Geçerli bir sayı giriniz"
      },
      "taksit": {
        required: "Lütfen taksit sayısını giriniz",
        number: "Geçerli bir sayı giriniz"
      },
      "odeme_baslangic_tarihi": { required: "Lütfen ilk ödeme tarihini seçiniz" }
    }
  });

  if (!validator.form()) {
    return;
  }

  fetch(icraurl, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        Swal.fire({
          title: "Başarılı",
          text: data.message,
          icon: "success",
          confirmButtonText: "Tamam"
        }).then(() => {
          location.reload(); // sayfayı yenile, tabloyu güncelle
        });
      } else {
        Swal.fire({
          title: "Hata",
          text: data.message,
          icon: "error",
          confirmButtonText: "Tamam",
        });
      }
    });
});

// Durum Güncelleme
$(document).on("click", "#updateStatusBtn", function () {
  var form = $("#statusUpdateForm");
  var formData = new FormData();

  formData.append("action", "durum_guncelle");
  formData.append("id", $("#icra_id").val());
  formData.append("fileStatus", $("#fileStatusInput").val());
  formData.append("icraStatus", $("#icraStatusInput").val());

  fetch(icraurl, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        Swal.fire({
          title: "Başarılı",
          text: data.message,
          icon: "success",
          confirmButtonText: "Tamam"
        }).then(() => {
          location.reload();
        });
      } else {
        Swal.fire({
          title: "Hata",
          text: data.message,
          icon: "error",
          confirmButtonText: "Tamam",
        });
      }
    });
});
