let peoplesurl = "pages/management/peoples/api/KisilerGenelBilgilerApi.php";

$(document).on("click", "#save_peoples, #savePeoples", function () {
  var form = $("#peoplesForm");
  var formData = new FormData(form[0]);

  formData.append("action", "save_peoples");
  formData.append("id", $("#kisi_id").val());

  // // Telefon numarası için sadece rakam ve 10 hane kontrolü
  // $("#phoneNumber").on("input", function () {
  //   // Sadece rakam girilmesine izin ver
  //   this.value = this.value.replace(/\D/g, "");
  //   // Maksimum 10 karaktere sınırla
  //   if (this.value.length > 11) {
  //     this.value = this.value.slice(0, 11);
  //   }
  // });

  var validator = $("#peoplesForm").validate({
    rules: {
      blokAdi: { required: true },
      daireNo: { required: true },
      tcPassportNo: { 
        required: true,
        minlength: 6,
        maxlength: 11,
        customTcPassport: true
      },
      fullName: { required: true },
      residentType: { required: true },
      birthDate: { required: true },
      gender: { required: true },
      phoneNumber: { 
        required: true,
        digits: true,
        minlength: 11,
        maxlength: 11
      },
      entryDate: { required: true },
    },
    messages: {
      blokAdi: { required: "Lütfen blok seçiniz" },
      daireNo: { required: "Lütfen daire seçiniz" },
      tcPassportNo: {
        required: "Lütfen TC Kimlik No veya Pasaport No giriniz",
        minlength: "TC Kimlik No 11 rakamdan oluşmalı, Pasaport no 6 karakterden az olamaz",
        maxlength: "En fazla 11 karakter olmalı",
        customTcPassport: "TC kimlik no giriyorsanız sadece rakam olmalı, Pasaport no giriyorsanız harf ve rakam olabilir"
      },
      fullName: { required: "Lütfen Ad Soyad giriniz" },
      residentType: { required: "Lütfen konut sakini türü seçiniz" },
      birthDate: { required: "Lütfen doğum tarihi giriniz" },
      gender: { required: "Lütfen cinsiyet seçiniz" },
      phoneNumber: { 
        required: "Lütfen telefon numarası giriniz",
        digits: "Telefon numarası sadece rakamlardan oluşmalıdır",
        minlength: "Telefon numarası 11 haneli olmalıdır",
        maxlength: "Telefon numarası 11 haneli olmalıdır"
      },
      entryDate: { required: "Lütfen giriş tarihi giriniz" },
    },
    
  });

  // Özel kural: 11 karakter ise sadece rakam, daha kısa ise harf olabilir
  $.validator.addMethod("customTcPassport", function(value, element) {
    if (value.length === 11) {
      return /^\d{11}$/.test(value);
    }
    return true;
  }, "TC kimlik no giriyorsanız sadece rakam olmalı, Pasaport no giriyorsanız harf ve rakam olabilir.");

  // Alanlara veri girildikçe uyarıyı kaldır
  $("#peoplesForm input, #peoplesForm select").on("input change", function () {
    if ($(this).valid()) {
      $(this).removeClass('is-invalid');
      $(this).next('.error').remove();
    }
  });

  if (!validator.form()) {
    return;
  }

  fetch(peoplesurl, {
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

$(document).on("click", ".delete-peoples", function () {
  let id = $(this).data("id");
  let peopleName = $(this).data("name");
  let buttonElement = $(this); // Store reference to the clicked button
  swal
    .fire({
      title: "Emin misiniz?",
      html: `${peopleName} <br> adlı kişiyi silmek istediğinize emin misiniz?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet",
      cancelButtonText: "Hayır",
    })
    .then((result) => {
      if (result.isConfirmed) {
        var formData = new FormData();
        formData.append("action", "delete_peoples");
        formData.append("id", id);

        fetch(peoplesurl, {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.status == "success") {
              let table = $("#peoplesList").DataTable();
              table.row(buttonElement.closest("tr")).remove().draw(false);
              swal.fire(
                "Silindi",
                `${peopleName} adlı kişi  başarıyla silindi.`,
                "success"
              );
            }
          });
      }
    });
});
