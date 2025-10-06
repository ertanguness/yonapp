let bakimurl = "/pages/repair/api.php";

$(document).on("click", "#bakim_kaydet", function () {
  var form = $("#bakimForm");
  var formData = new FormData(form[0]);

  formData.append("action", "bakim_kaydetme");
  formData.append("id", $("#bakim_id").val());


  var validator = $("#bakimForm").validate({
    rules: {
      talepeden: { required: true },
      taleptarihi: { required: true },
      kategori: { required: true },
      state: { required: true },
      firmakisi: { required: true },
      atandimi: { required: true }    },
    messages: {
      talepeden: { required: "Lütfen talep eden kişi/birim giriniz" },
      taleptarihi: { required: "Lütfen talep tarihi seçiniz" },
      kategori: { required: "Lütfen kategori seçiniz" },
      state: { required: "Lütfen bakım/arıza durumu seçiniz" },
      firmakisi: { required: "Lütfen firma veya kişi adı giriniz" },
      atandimi: { required: "Lütfen atama durumu seçiniz" },
    }
  });
  if (!validator.form()) {
    return;
  }

  fetch(bakimurl, {
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



$(document).on("click", ".sil-Bakim", function () {
  let id = $(this).data("id");
  let bakimNo = $(this).data("name");
  let buttonElement = $(this); // Store reference to the clicked button
  swal
    .fire({
      title: "Emin misiniz?",
      html: `Talep No :${bakimNo} <br>  kaydını silmek istediğinize emin misiniz?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet",
      cancelButtonText: "Hayır",
    })
    .then((result) => {
      if (result.isConfirmed) {
        var formData = new FormData();
        formData.append("action", "sil-Bakim");
        formData.append("id", id);

        fetch(bakimurl, {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.status == "success") {
           // console.log("Çözümlenmiş ID:", data.decrypted_id);
           //window.location.reload(); // Sayfayı yeniden yükle

              let table = $("#BakimList").DataTable();
              table.row(buttonElement.closest("tr")).remove().draw(false);
              swal.fire(
                "Silindi",
                `${bakimNo} numaralı kayıt başarıyla silindi.`,
                "success"
              );
             }
          });
      }
    });
});
