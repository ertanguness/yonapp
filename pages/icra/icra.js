let icraurl = "/pages/icra/api.php";

$(document).on("click", "#icra_kaydet", function () {
  var form = $("#icraForm");
  var formData = new FormData(form[0]);

  formData.append("action", "icra_kaydetme");
  formData.append("id", $("#icra_id").val());

  var validator = $("#icraForm").validate({
    rules: {
      "dosya_no": { required: true },
      "icra_durumu": { required: true },
      "kisi_id": { required: true },
      "tc": { required: true },
      "borc_tutari": { required: true, number: true },
      "faiz_orani": { required: true, number: true },
      "baslangic_tarihi": { required: true },
      "icra_dairesi": { required: true },
    },
    messages: {
      "dosya_no": { required: "Lütfen dosya numarasını giriniz" },
      "icra_durumu": { required: "Lütfen icra durumunu seçiniz" },
      "kisi_id": { required: "Lütfen kişi seçiniz" },
      "tc": {
        required: "Site Yönetimi->Kişiler sayfasından TC Kimlik Numarasını giriniz",
      },
      "borc_tutari": { required: "Lütfen borç tutarını giriniz", number: "Geçerli bir sayı giriniz" },
      "faiz_orani": { required: "Lütfen faiz oranını giriniz", number: "Geçerli bir sayı giriniz" },
      "baslangic_tarihi": { required: "Lütfen başlangıç tarihini giriniz" },
      "icra_dairesi": { required: "Lütfen icra dairesini giriniz" },
    }
  });

  if (!validator.form()) {
    return;
  }

  fetch(icraurl, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        Swal.fire({
          title: "Başarılı",
          text: data.message,
          icon: "success",
          showCancelButton: true,
          confirmButtonText: '<i class="fa fa-arrow-left" ></i> Listeye Dön ',
          cancelButtonText: "Ödeme Planı Oluştur"
        }).then((result) => {
          if (result.isConfirmed) {
            // Listeye yönlendirme
            window.location.href = "/icra-listesi";
          } else if (result.dismiss === Swal.DismissReason.cancel) {
            // Ödeme planı sayfasına yönlendirme
            window.location.href = "/icra-detay?id=" + data.id;
          }
        });
      } else {
        Swal.fire({
          title: "Hata",
          text: data.message,
          icon: "error",
          confirmButtonText: "Tamam",
        });
      }
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
      html: `${icraDosyaAdi} dosya numaralı <br> icra kaydını silmek istediğinize emin misiniz?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet",
      cancelButtonText: "Hayır",
    })
    .then((result) => {
      if (result.isConfirmed) {
        var formData = new FormData();
        formData.append("action", "sil-icra");
        formData.append("id", id);

        fetch(icraurl, {
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
                `${icraDosyaAdi} dosya numaralı icra kaydı başarıyla silindi.`,
                "success"
              );
            }
          });
      }
    });
});
