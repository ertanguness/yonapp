let guvenlikurl = "pages/ziyaretci/guvenlik/guvenlikApi.php";

$(document).on("click", "#guvenlik_kaydet", function () {
  var form = $("#guvenlikForm");
  var formData = new FormData(form[0]);

  formData.append("action", "guvenlik_kaydetme");
  formData.append("id", $("#guvenlik_id").val());


  var validator = $("#guvenlikForm").validate({
    rules: {
      "personel": { required: true },
      "gorev_yeri": { required: true },
      "gorev_baslangic": { required: true },
      "vardiya": { required: true },
      "durum": { required: true }
    },
    messages: {
      "personel": { required: "Lütfen personel seçiniz" },
      "gorev_yeri": { required: "Lütfen görev yerini seçiniz" },
      "gorev_baslangic": { required: "Lütfen görev başlangıç tarihini giriniz" },
      "vardiya": { required: "Lütfen vardiya seçiniz" },
      "durum": { required: "Lütfen durum seçiniz" }
    }
  });
  if (!validator.form()) {
    return;
  }

  fetch(guvenlikurl, {
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



$(document).on("click", ".sil-guvenlik", function () {
  let id = $(this).data("id");
  let guvenlik = $(this).data("name");
  let buttonElement = $(this); // Store reference to the clicked button
  swal
    .fire({
      title: "Emin misiniz?",
      html: `${guvenlik} <br> isimli görev kaydını silmek istediğinize emin misiniz?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet",
      cancelButtonText: "Hayır",
    })
    .then((result) => {
      if (result.isConfirmed) {
        var formData = new FormData();
        formData.append("action", "sil-guvenlik");
        formData.append("id", id);

        fetch(guvenlikurl, {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.status == "success") {
           // console.log("Çözümlenmiş ID:", data.decrypted_id);
           //window.location.reload(); // Sayfayı yeniden yükle

              let table = $("#guvenlikList").DataTable();
              table.row(buttonElement.closest("tr")).remove().draw(false);
              swal.fire(
                "Silindi",
                `${ziyaretciAdi} isimli görevkaydı  başarıyla silindi.`,
                "success"
              );
             }
          });
      }
    });
});
