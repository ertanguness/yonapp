let gorevYeriurl = "pages/ziyaretci/guvenlik/GorevYeri/GorevYeriApi.php";

$(document).on("click", "#gorevYeriKaydet", function () {
  var form = $("#GorevYeriForm");
  var formData = new FormData(form[0]);

  formData.append("action", "gorevYeri_kaydetme");
  formData.append("id", $("#gorevYeri_id").val());


  var validator = $("#GorevYeriForm").validate({
    rules: {
      "ad": { required: true },
      "aciklama": { required: true },
      "durum": { required: true }
    },
    messages: {
      "ad": { required: "Lütfen görev yeri adını giriniz" },
      "aciklama": { required: "Lütfen açıklama giriniz" },
      "durum": { required: "Lütfen durum seçiniz" }
    }
  });
  if (!validator.form()) {
    return;
  }

  fetch(gorevYeriurl, {
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



$(document).on("click", ".sil-gorevYeri", function () {
  let id = $(this).data("id");
  let gorevYeriAdi = $(this).data("name");
  let buttonElement = $(this); // Store reference to the clicked button
  swal
    .fire({
      title: "Emin misiniz?",
      html: `${gorevYeriAdi} <br> isimli Görev Yeri kaydını silmek istediğinize emin misiniz?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet",
      cancelButtonText: "Hayır",
    })
    .then((result) => {
      if (result.isConfirmed) {
        var formData = new FormData();
        formData.append("action", "sil-gorevYeri");
        formData.append("id", id);

        fetch(gorevYeriurl, {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.status == "success") {
           // console.log("Çözümlenmiş ID:", data.decrypted_id);
           //window.location.reload(); // Sayfayı yeniden yükle

              let table = $("#GorevYeriList").DataTable();
              table.row(buttonElement.closest("tr")).remove().draw(false);
              swal.fire(
                "Silindi",
                `${ziyaretciAdi} isimli Görev Yeri  başarıyla silindi.`,
                "success"
              );
             }
          });
      }
    });
});
