let guvenlikPersonelurl = "pages/ziyaretci/guvenlik/Personel/guvenlikPersonelApi.php";

$(document).on("click", "#guvenlikPersonel_kaydet", function () {
  var form = $("#guvenlikPersonelForm");
  var formData = new FormData(form[0]);

  formData.append("action", "guvenlikPersonel_kaydetme");
  formData.append("id", $("#guvenlikPersonel_id").val());


  var validator = $("#guvenlikPersonelForm").validate({
    ignore: [],
    rules: {
      "adi_soyadi": { required: true },
      "tc_kimlik_no": { required: true, minlength: 11, maxlength: 11, digits: true },
      "telefon": { required: true },
      "adres": { required: true },
      "gorev_yeri": { required: true },
      "durum": { required: true },
      "baslama_tarihi": { required: true, date: true },
      "acil_kisi": { required: true },
      "yakinlik": { required: true },
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
      "telefon": { required: "Lütfen telefon numarası giriniz" },
      "adres": { required: "Lütfen adres giriniz" },
      "gorev_yeri": { required: "Lütfen görev yeri seçiniz" },
      "durum": { required: "Lütfen durum seçiniz" },
      "baslama_tarihi": { required: "Lütfen başlama tarihi seçiniz" },
      "acil_kisi": { required: "Lütfen acil durumda aranacak kişiyi giriniz" },
      "yakinlik": { required: "Lütfen yakınlık derecesi seçiniz" },
      "acil_telefon": { required: "Lütfen acil telefon numarası giriniz" }
    },
    showErrors: function (errorMap, errorList) {
      this.defaultShowErrors();
  
      // Tüm tab başlıklarını temizle
      $("#personelTab .nav-link").removeClass("error-tab").find(".tab-error").remove();
  
      if (errorList.length > 0) {
        errorList.forEach(function (error) {
          // Hatanın bulunduğu input
          var element = $(error.element);
  
          // Input hangi tab-pane içinde?
          var tabPane = element.closest(".tab-pane");
          var tabId = tabPane.attr("id");
  
          // O tab’ın başlığını bul
          var tabLink = $('a[data-bs-target="#' + tabId + '"]');
  
          // Daha önce eklenmediyse hata işareti ekle
          if (!tabLink.hasClass("error-tab")) {
            tabLink.addClass("error-tab");
            tabLink.append(" <span class='tab-error text-danger'>⚠️</span>");
          }
        });
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
