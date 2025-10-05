let aracEkleUrl = "/pages/management/peoples/api/AracBilgileriApi.php";

$(document).on("click", "#AracEkle", function () {
  var form = $("#aracEkleForm");
  var formData = new FormData(form[0]);

  formData.append("action", "AracEkle");
  formData.append("id", $("#arac_id").val());

  // Plaka inputunda harfleri otomatik olarak büyük harfe çevir
  $("#modalAracPlaka").on("input", function () {
    this.value = this.value.toUpperCase();
  });

  // FORM DOĞRULAMA
  var validator = $("#aracEkleForm").validate({
    rules: {
      blok_id: { required: true },
      daire_id: { required: true },
      kisi_id: { required: true },
      modalAracPlaka: {
        required: true,
        plakaKontrol: true
      },
      modalAracMarka: { required: false }
    },
    messages: {
      blok_id: { required: "Lütfen blok seçiniz" },
      daire_id: { required: "Lütfen daire seçiniz" },
      kisi_id: { required: "Lütfen kişi seçiniz" },
      modalAracPlaka: {
        required: "Lütfen araç plakası giriniz",
        plakaKontrol: "Geçerli bir plaka giriniz (örn: 34 ABC 123)"
      }
    }
  });

  $.validator.addMethod("plakaKontrol", function (value, element) {
    return this.optional(element) || /^[A-Z0-9\s\-]{4,12}$/i.test(value);
  }, "Lütfen geçerli bir plaka giriniz");

  if (!validator.form()) {
    return;
  }

  // AJAX İSTEĞİ
  fetch(aracEkleUrl, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {

      if (data.status === "success") {
        let table = $("#aracList").DataTable();
        let existingRow = table.row('[data-id="' + $("#arac_id").val() + '"]');
      
        let currentSira = null;
        if (existingRow.length > 0) {
          currentSira = existingRow.node().querySelector(".sira-no")?.textContent;
          existingRow.remove().draw(false);
        }
      
        // Arac güncelleme ise sira_no gönder
        formData.append("sira_no", currentSira);
      
        // Yeni satırı ekle
        let newRow = $(data.yeniAracEkle);
        table.row.add(newRow).draw(false);
      
        // Modalı kapat
        $("#aracEkleModal").modal("hide");
      
        // Sıra numaralarını güncelle
        updateRowNumbers();
      }
      

      Swal.fire({
        title: data.status === "success" ? "Başarılı" : "Hata",
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


// ARAÇ SİLME
$(document).on("click", ".delete-car", function () {
  let id = $(this).data("id");
  let carName = $(this).data("name");
  let buttonElement = $(this);

  Swal.fire({
    title: "Emin misiniz?",
    html: `${carName} <br> plakalı aracı silmek istediğinize emin misiniz?`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Evet",
    cancelButtonText: "Hayır",
  }).then((result) => {
    if (result.isConfirmed) {
      var formData = new FormData();
      formData.append("action", "delete_car");
      formData.append("id", id);

      fetch(aracEkleUrl, {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.status === "success") {
            let table = $("#aracList").DataTable();
            table.row(buttonElement.closest("tr")).remove().draw(false);
            Swal.fire("Silindi", `${carName} plakalı araç başarıyla silindi.`, "success");
          }
        });
    }
  });
});
function updateRowNumbers() {
  $("#aracList tbody tr").each(function (index) {
    $(this).find(".sira-no").text(index + 1);
  });
}
