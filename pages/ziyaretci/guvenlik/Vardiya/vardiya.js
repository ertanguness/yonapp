let vardiyaurl = "pages/ziyaretci/guvenlik/Vardiya/VardiyaApi.php";

$(document).on("click", "#vardiyaKaydet", function () {
  var form = $("#VardiyaForm");
  var formData = new FormData(form[0]);

  formData.append("action", "vardiya_kaydetme");
  formData.append("id", $("#vardiya_id").val());


  var validator = $("#VardiyaForm").validate({
    rules: {
      "gorev_yeri_id": { required: true },
      "vardiya_adi": { required: true },
      "vardiya_baslangic": { required: true },
      "vardiya_bitis": { required: true },
      "aciklama": { required: true },
      "durum": { required: true }
    },
    messages: {
      "gorev_yeri_id": { required: "Lütfen görev yeri seçiniz" },
      "vardiya_adi": { required: "Lütfen vardiya adını giriniz" },
      "vardiya_baslangic": { required: "Lütfen başlangıç saatini giriniz" },
      "vardiya_bitis": { required: "Lütfen bitiş saatini giriniz" },
      "aciklama": { required: "Lütfen açıklama giriniz" },
      "durum": { required: "Lütfen durum seçiniz" }
    }
  });
  if (!validator.form()) {
    return;
  }

  fetch(vardiyaurl, {
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



$(document).on("click", ".sil-vardiya", function () {
  let id = $(this).data("id");
  let vardiyaAdi = $(this).data("name");
  let buttonElement = $(this); // Store reference to the clicked button
  swal
    .fire({
      title: "Emin misiniz?",
      html: `${vardiyaAdi} <br> isimli vardiya kaydını silmek istediğinize emin misiniz?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet",
      cancelButtonText: "Hayır",
    })
    .then((result) => {
      if (result.isConfirmed) {
        var formData = new FormData();
        formData.append("action", "sil-vardiya");
        formData.append("id", id);

        fetch(vardiyaurl, {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.status == "success") {
           // console.log("Çözümlenmiş ID:", data.decrypted_id);
           //window.location.reload(); // Sayfayı yeniden yükle

              let table = $("#vardiyaList").DataTable();
              table.row(buttonElement.closest("tr")).remove().draw(false);
              swal.fire(
                "Silindi",
                `${vardiyaAdi} isimli vardiya  başarıyla silindi.`,
                "success"
              );
             }
          });
      }
    });
});
