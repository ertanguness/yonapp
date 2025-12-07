let kisiNotApiUrl = "/pages/management/peoples/api/KisiNotlarApi.php";

$(document).on("click", "#KisiNotKaydet", function () {
  var form = $("#kisiNotForm");
  var formData = new FormData(form[0]);

  formData.append("action", "NotEkle");
  formData.append("id", $("#not_id").val());

  var validator = $("#kisiNotForm").validate({
    rules: {
      kisi_id: { required: true },
      icerik: { required: true, minlength: 2 },
    },
    messages: {
      kisi_id: { required: "Lütfen kişi seçiniz" },
      icerik: { required: "Lütfen not giriniz" },
    },
  });

  if (!validator.form()) {
    return;
  }

  fetch(kisiNotApiUrl, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        let table = $("#kisiNotList").DataTable();
        let existingRow = table.row('[data-id="' + $("#not_id").val() + '"]');

        let currentSira = null;
        if (existingRow.length > 0) {
          currentSira = existingRow.node().querySelector(".sira-no")?.textContent;
          existingRow.remove().draw(false);
        }

        formData.append("sira_no", currentSira);

        let newRow = $(data.yeniNotSatiri);
        table.row.add(newRow).draw(false);

        $("#kisiNotModal").modal("hide");
        updateNoteRowNumbers();
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

$(document).on("click", ".delete-note", function () {
  let id = $(this).data("id");
  let noteName = $(this).data("name");
  let buttonElement = $(this);

  Swal.fire({
    title: "Emin misiniz?",
    html: `${noteName} <br> içeriğine sahip notu silmek istediğinize emin misiniz?`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Evet",
    cancelButtonText: "Hayır",
  }).then((result) => {
    if (result.isConfirmed) {
      var formData = new FormData();
      formData.append("action", "delete_note");
      formData.append("id", id);

      fetch(kisiNotApiUrl, {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.status === "success") {
            let table = $("#kisiNotList").DataTable();
            table.row(buttonElement.closest("tr")).remove().draw(false);
            Swal.fire("Silindi", `Not başarıyla silindi.`, "success");
          }
        });
    }
  });
});

function updateNoteRowNumbers() {
  $("#kisiNotList tbody tr").each(function (index) {
    $(this).find(".sira-no").text(index + 1);
  });
}
