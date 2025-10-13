let ayarlarUrl = "/pages/ayarlar/api.php";

$(document).on("click", "#ayarlar_kaydet", function () {
  var form = $("#ayarlarForm");
  var formData = new FormData(form[0]);

  formData.append("action", "ayarlar_kaydet");
  formData.append("id", $("#ayarlar_id").val());

  var validator = $("#ayarlarForm").validate({
    rules: {
      smtpServer: { required: true },
      smtpPort: { required: true, number: true },
      smtpUser: { required: true, email: true },
      smtpPassword: { required: true },
      smsProvider: { required: true },
      smsUsername: { required: true },
      smsPassword: { required: true },
      whatsappApiUrl: { required: true, url: true },
      whatsappToken: { required: true },
      whatsappSender: { required: true },
      
      eposta: { required: true, email: true },
      telefon: { required: true },
      acilIletisim: { required: true }
    },
    messages: {
      smtpPassword: { required: "E-posta şifresini giriniz" },
      smsProvider: { required: "SMS servis sağlayıcısını giriniz" },
      smsUsername: { required: "SMS kullanıcı adını giriniz" },
      smsPassword: { required: "SMS şifresini giriniz" },
      whatsappApiUrl: {
        required: "WhatsApp API URL'sini giriniz",
        url: "Geçerli bir URL giriniz",
      },
      whatsappToken: { required: "WhatsApp API tokenını giriniz" },
      whatsappSender: { required: "Gönderen numarasını giriniz" },

      // İletişim Bilgileri
      eposta: {
        required: "E-posta adresi yazınız",
        email: "Geçerli bir e-posta adresi giriniz"
      }, 
      telefon: { required: "Telefon numarası yazınız" },
      acilIletisim: { required: "Acil iletişim numarası yazınız" }
    },
  });

  if (!validator.form()) {
    return;
  }

  fetch(ayarlarUrl, {
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

$(document).on("click", ".sil-icra", function () {
  let id = $(this).data("id");
  let icraDosyaAdi = $(this).data("name");
  let buttonElement = $(this);

  swal
    .fire({
      title: "Emin misiniz?",
      html: `${ayarlar} dosya numaralı <br> icra kaydını silmek istediğinize emin misiniz?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet",
      cancelButtonText: "Hayır",
    })
    .then((result) => {
      if (result.isConfirmed) {
        var formData = new FormData();
        formData.append("action", "sil-ayarlar");
        formData.append("id", id);

        fetch(ayarlarUrl, {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.status == "success") {
              let table = $("#icraList").DataTable();
              table.row(buttonElement.closest("tr")).remove().draw(false);

              swal.fire(
                "Silindi",
                `${ayarlar} dosya numaralı icra kaydı başarıyla silindi.`,
                "success"
              );
            }
          });
      }
    });
});
