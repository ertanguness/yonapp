let ziyaretciurl = "pages/ziyaretci/api.php";

$(document).on("click", "#ziyaretci_kaydet", function () {
  var form = $("#ziyaretciForm");
  var formData = new FormData(form[0]);

  formData.append("action", "ziyaretci_kaydetme");
  formData.append("id", $("#ziyaretci_id").val());


  var validator = $("#ziyaretciForm").validate({
    rules: {
      "ad-soyad": { required: true },
      "giris_tarihi": { required: true },
      "giris_saati": { required: true },
      "blok_id": { required: true },
      "daire_id": { required: true },
      "kisi_id": { required: true },
     
    },
    messages: {
      "ad-soyad": { required: "Lütfen ziyaretçi ad soyadını giriniz" },
      "giris_tarihi": { required: "Lütfen giriş tarihi seçiniz" },
      "giris_saati": { required: "Lütfen giriş saatini seçiniz" },
      "blok_id": { required: "Lütfen blok seçiniz" },
      "daire_id": { required: "Lütfen daire seçiniz" },
      "kisi_id": { required: "Lütfen ziyaret edilen kişiyi seçiniz" },
     
    }
  });
  if (!validator.form()) {
    return;
  }

  fetch(ziyaretciurl, {
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



$(document).on("click", ".sil-ziyaretci", function () {
  let id = $(this).data("id");
  let ziyaretciAdi = $(this).data("name");
  let buttonElement = $(this); // Store reference to the clicked button
  swal
    .fire({
      title: "Emin misiniz?",
      html: `${ziyaretciAdi} <br> isimli ziyaretçi kaydını silmek istediğinize emin misiniz?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet",
      cancelButtonText: "Hayır",
    })
    .then((result) => {
      if (result.isConfirmed) {
        var formData = new FormData();
        formData.append("action", "sil-Ziyaretci");
        formData.append("id", id);

        fetch(ziyaretciurl, {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.status == "success") {
           // console.log("Çözümlenmiş ID:", data.decrypted_id);
           //window.location.reload(); // Sayfayı yeniden yükle

              let table = $("#ZiyaretciList").DataTable();
              table.row(buttonElement.closest("tr")).remove().draw(false);
              swal.fire(
                "Silindi",
                `${ziyaretciAdi} isimli ziyaretci  başarıyla silindi.`,
                "success"
              );
             }
          });
      }
    });
});
