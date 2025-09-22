let guvenlikPersonelurl = "pages/ziyaretci/guvenlik/Personel/guvenlikPersonelApi.php";

$(document).on("click", "#guvenlikPersonel_kaydet", function () {
  var form = $("#guvenlikPersonelForm");
  var formData = new FormData(form[0]);

  formData.append("action", "guvenlikPersonel_kaydetme");
  formData.append("id", $("#guvenlikPersonel_id").val());


  var validator = $("#guvenlikPersonelForm").validate({
    rules: {
      "adi_soyadi": { required: true },
      "tc_kimlik_no": { required: true, minlength: 11, maxlength: 11, digits: true },
      "telefon": { required: true},
      "adres": { required: true },
      "gorev_yeri": { required: true },
      "durum": { required: true },
      "baslama_tarihi": { required: true, date: true },
      "acil_kisi": { required: true },
      "yakınlik": { required: true },
      "acil_telefon": { required: true }
    },
    messages: {
      "adi_soyadi": { required: "Lütfen adı soyadı giriniz" },
      "tc_kimlik_no": {
        required: "Lütfen TC kimlik numarası giriniz",
        minlength: "TC kimlik numarası 11 haneli olmalıdır",
        maxlength: "TC kimlik numarası 11 haneli olmalıdır",
        digits: "Sadece rakam giriniz"
      },
      "telefon": {
        required: "Lütfen telefon numarası giriniz",
        minlength: "Telefon numarası en az 10 haneli olmalıdır",
      },
      "adres": { required: "Lütfen adres giriniz" },
      "gorev_yeri": { required: "Lütfen görev yeri seçiniz" },
      "durum": { required: "Lütfen durum seçiniz" },
      "baslama_tarihi": { required: "Lütfen başlama tarihi seçiniz", date: "Geçerli bir tarih giriniz" },
      "acil_kisi": { required: "Lütfen acil durumda aranacak kişiyi giriniz" },
      "yakınlik": { required: "Lütfen yakınlık derecesi seçiniz" },
      "acil_telefon": {
        required: "Lütfen acil telefon numarası giriniz",
        minlength: "Telefon numarası en az 10 haneli olmalıdır",
        digits: "Sadece rakam giriniz"
      }
    }
  });
  if (!validator.form()) {
    return;
  }

  fetch(guvenlikPersonelurl, {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      return response.json();
    })
    .then((data) => {
      var title = data.status == "success" ? "Başarılı" : "Hata";
      swal.fire({
        title: title,
        text: data.message,
        icon: data.status,
        confirmButtonText: "Tamam",
      });
    });
});

$(document).on("change", ".select2", function () {
  $(this).valid(); // Trigger validation for the changed select2 element
});



$(document).on("click", ".sil-guvenlikPersonel", function () {
  let id = $(this).data("id");
  let guvenlikPersonelAdi = $(this).data("name");
  let buttonElement = $(this); // Store reference to the clicked button
  swal
    .fire({
      title: "Emin misiniz?",
      html: `${guvenlikPersonelAdi} <br> isimli personel kaydını silmek istediğinize emin misiniz?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet",
      cancelButtonText: "Hayır",
    })
    .then((result) => {
      if (result.isConfirmed) {
        var formData = new FormData();
        formData.append("action", "sil-guvenlikPersoneli");
        formData.append("id", id);

        fetch(guvenlikPersonelurl, {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.status == "success") {
           // console.log("Çözümlenmiş ID:", data.decrypted_id);
           //window.location.reload(); // Sayfayı yeniden yükle

              let table = $("#guvenlikPersonelList").DataTable();
              table.row(buttonElement.closest("tr")).remove().draw(false);
              swal.fire(
                "Silindi",
                `${guvenlikPersonelAdi} isimli personel  başarıyla silindi.`,
                "success"
              );
             }
          });
      }
    });
});
