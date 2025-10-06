let periyodikBakimurl = "/pages/repair/care/periyodikBakimApi.php";

$(document).on("click", "#periyodikBakim_kaydet", function () {
  var form = $("#periyodikBakimForm");
  var formData = new FormData(form[0]);

  formData.append("action", "periyodikBakim_kaydetme");
  formData.append("id", $("#periyodikBakim_id").val());


  var validator = $("#periyodikBakimForm").validate({
    rules: {
      bakimAdi: { required: true },
      bakimPeriyot: { required: true },
      bakimYeri: { required: true },
      blokSecimi: {
        required: function () {
          return $("#bakimYeri").val() === "Blok";
        }
      },
      sonBakimTarihi: { required: true },
      sorumluFirma: { required: true }
    },
    messages: {
      bakimAdi: { required: "Lütfen bakım adını giriniz" },
      bakimPeriyot: { required: "Lütfen bakım periyodu seçiniz" },
      bakimYeri: { required: "Lütfen bakım yapılacak yeri seçiniz" },
      blokSecimi: { required: "Lütfen blok seçiniz" },
      sonBakimTarihi: { required: "Lütfen son bakım tarihini seçiniz" },
      sorumluFirma: { required: "Lütfen sorumlu firma veya kişi adını giriniz" }
    }
  });
  if (!validator.form()) {
    return;
  }

  fetch(periyodikBakimurl, {
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



$(document).on("click", ".sil-periyodikBakim", function () {
  let id = $(this).data("id");
  let bakimNo = $(this).data("name");
  let buttonElement = $(this); // Store reference to the clicked button
  swal
    .fire({
      title: "Emin misiniz?",
      html: `Bakım No :${bakimNo} <br>  kaydını silmek istediğinize emin misiniz?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet",
      cancelButtonText: "Hayır",
    })
    .then((result) => {
      if (result.isConfirmed) {
        var formData = new FormData();
        formData.append("action", "sil-periyodikBakim");
        formData.append("id", id);

        fetch(periyodikBakimurl, {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.status == "success") {
           // console.log("Çözümlenmiş ID:", data.decrypted_id);
           //window.location.reload(); // Sayfayı yeniden yükle

              let table = $("#periyodikBakimList").DataTable();
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
