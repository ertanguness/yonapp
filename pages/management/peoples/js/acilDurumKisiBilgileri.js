let acilDurumKisiEkleUrl = "/pages/management/peoples/api/AcilDurumKisileriApi.php";

$(document).on("click", "#AcilDurumEkle", function () {
  var form = $("#acilDurumKisileriEkleForm");
  var formData = new FormData(form[0]);

  formData.append("action", "AcilDurumEkle");
  formData.append("id", $("#acil_kisi_id").val());

  // Telefon numarası için sadece rakam ve 10 hane kontrolü
  $("#acilDurumKisiTelefon").on("input", function () {
    // Sadece rakam girilmesine izin ver
    this.value = this.value.replace(/\D/g, "");
    // Maksimum 10 karaktere sınırla
    if (this.value.length > 10) {
      this.value = this.value.slice(0, 10);
    }
  });

  var validator = $("#acilDurumKisileriEkleForm").validate({
    rules: {
      blok_id: { required: true },
      daire_id: { required: true },
      kisi_id: { required: true },
      acilDurumKisi: { required: true },
      acilDurumKisiTelefon: { 
        required: true,
        digits: true,
        minlength: 10,
        maxlength: 10
      },     
       yakinlik: { required: true }
    },
    messages: {
      blok_id: { required: "Lütfen blok seçiniz" },
      daire_id: { required: "Lütfen daire seçiniz" },
      kisi_id: { required: "Lütfen kişi seçiniz" },
      acilDurumKisi: { required: "Lütfen acil durum kişisi adını giriniz" },
      acilDurumKisiTelefon: { 
        required: "Lütfen telefon numarası giriniz",
        digits: "Telefon numarası sadece rakamlardan oluşmalıdır",
        minlength: "Telefon numarası 10 haneli olmalıdır",
        maxlength: "Telefon numarası 10 haneli olmalıdır"
      },      
      yakinlik: { required: "Lütfen yakınlık derecesini seçiniz" }
    },
    
  });
  
  if (!validator.form()) {
    return;
  }

  fetch(acilDurumKisiEkleUrl, {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      return response.json();
    })
    .then((data) => {
      if (data.status === "success") {
        let table = $("#acilDurumKisileriList").DataTable();
        let existingRow = table.row('[data-id="' + $("#acil_kisi_id").val() + '"]');
      
        let currentSira = null;
        if (existingRow.length > 0) {
          currentSira = existingRow.node().querySelector(".sira-no")?.textContent;
          existingRow.remove().draw(false);
        }
      
        // Arac güncelleme ise sira_no gönder
        formData.append("sira_no", currentSira);
      
        // Yeni satırı ekle
        let newRow = $(data.acilDurumKisiEkle);
        table.row.add(newRow).draw(false);
      
        // Modalı kapat
        $("#acilDurumEkleModal").modal("hide");
      
        // Sıra numaralarını güncelle
        updateRowNumbers();
      }
      var title = data.status == "success" ? "Başarılı" : "Hata";
      swal.fire({
        title: title,
        text: data.message,
        icon: data.status,
        confirmButtonText: "Tamam",
      });
    })
    .catch((error) => {
      console.error("Kayıt sırasında hata:", error);
      Swal.fire("Hata", "Kayıt sırasında bir hata oluştu.", "error");
    });
});

$(document).on("click", ".delete-acilDurumKisi", function () {
  let id = $(this).data("id");
  let acilDurumKisiName = $(this).data("name");
  let buttonElement = $(this); // Store reference to the clicked button
  swal
    .fire({
      title: "Emin misiniz?",
      html: acilDurumKisiName + " <br> adlı kişiyi silmek istediğinize emin misiniz?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet",
      cancelButtonText: "Hayır",
    })
    .then((result) => {
      if (result.isConfirmed) {
        var formData = new FormData();
        formData.append("action", "delete_acilDurumKisi");
        formData.append("id", id);

        fetch(acilDurumKisiEkleUrl, {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.status == "success") {
              let table = $("#acilDurumKisileriList").DataTable();
              table.row(buttonElement.closest("tr")).remove().draw(false);
              swal.fire(
                "Silindi",
                acilDurumKisiName + " adlı kişi başarıyla silindi.",
                "success"
              );
            }
          });
      }
    });
});
function updateRowNumbers() {
  $("#acilDurumKisileriList tbody tr").each(function (index) {
    $(this).find(".sira-no").text(index + 1);
  });
}
