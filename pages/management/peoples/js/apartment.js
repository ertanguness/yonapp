let urlApartment = "/pages/management/apartment/api.php";

$(document).on("click", "#save_apartment", function () {
  var form = $("#apartmentForm");
  var formData = new FormData(form[0]);

  formData.append("action", "save_apartment");
  formData.append("id", $("#apartment_id").val());

  var validator = $("#apartmentForm").validate({
    rules: {
      blockName: { required: true },
      floor: { required: true },
      flatNumber: { required: true },
      apartment_type: { required: true },
    },
    messages: {
      blockName: { required: "Lütfen blok seçiniz" },
      floor: { required: "Lütfen kat giriniz" },
      flatNumber: { required: "Lütfen daire no giriniz" },
      apartment_type: { required: "Lütfen daire tipi seçiniz" },
    },
  });

  if (!validator.form()) return;

  sendApartmentForm(formData);
});

function sendApartmentForm(formData) {
  fetch(urlApartment, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (
        data.status === "error" &&
        data.message.includes("kod önceden oluşturulmuş")
      ) {
        let mevcutKod = data.message.match(/^([A-Z0-9]+) kod önceden/);
        mevcutKod = mevcutKod ? mevcutKod[1] : "";

        Swal.fire({
          title: "⚠️ Kod Zaten Tanımlı",
          html: `
            <div style="text-align:center;">
              <p><strong style="color:#d33;">${mevcutKod}</strong> kodu bu blokta zaten tanımlı.</p>
              <p>Lütfen farklı bir <strong>daire kodu</strong> giriniz:</p>
            </div>
          `,
          icon: "warning",
          input: "text",
          inputPlaceholder: "Yeni daire kodu girin",
          inputValue: mevcutKod + "-1",
          showCancelButton: true,
          confirmButtonText: "💾 Kaydet",
          cancelButtonText: "❌ İptal",
          customClass: {
            popup: 'swal2-popup',
            title: 'swal2-title',
            confirmButton: 'swal2-confirm',
            cancelButton: 'swal2-cancel',
            input: 'swal2-input'
          },
          inputValidator: (value) => {
            if (!value.trim()) {
              return "Kod boş olamaz.";
            }
          }
        }).then((result) => {
          if (result.isConfirmed && result.value) {
            formData.set("daire_kodu", result.value); // Yeni kodu ekle
            sendApartmentForm(formData); // Tekrar gönder
          }
        });

        return;
      }

      var title = data.status == "success" ? "Başarılı" : "Hata";
      Swal.fire({
        title: title,
        text: data.message,
        icon: data.status,
        confirmButtonText: "Tamam",
      });
    });
}


$(document).on("click", ".delete-apartment", function () {
  let id = $(this).data("id");
  let apartmenName = $(this).data("name");
  let buttonElement = $(this);

  swal
    .fire({
      title: "Emin misiniz?",
      html: `${apartmenName} <br> numaralı daireyi silmek istediğinize emin misiniz?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet",
      cancelButtonText: "Hayır",
    })
    .then((result) => {
      if (result.isConfirmed) {
        var formData = new FormData();
        formData.append("action", "delete_apartment");
        formData.append("id", id);

        fetch(urlApartment, {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.status == "success") {
              let table = $("#apartmentList").DataTable();
              table.row(buttonElement.closest("tr")).remove().draw(false);
              swal.fire(
                "Silindi",
                `${apartmenName} numaralı daire başarıyla silindi.`,
                "success"
              );
            }
          });
      }
    });
});
