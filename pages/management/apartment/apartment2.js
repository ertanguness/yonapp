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


// Debug: Check if the script is loaded
console.log("Apartment.js loaded successfully");

// Test: Global click event listener to see if clicks are being captured
$(document).on("click", function(e) {
  if ($(e.target).closest('.delete-apartment').length > 0) {
    console.log("Global click detected on delete-apartment element");
  }
});

// Silme butonları için event handler - DOM yüklendikten sonra bağla
$(document).ready(function() {
  console.log("Document ready, binding delete events");
  
  $(document).on("click", ".delete-apartment", function (e) {
    console.log("Delete button clicked - event received");
    e.preventDefault(); // Varsayılan link davranışını engelle
    
    let id = $(this).data("id");
    let apartmenName = $(this).data("name");
    let buttonElement = $(this);
    
    console.log("Delete data extracted:", { id, apartmenName });
    
    if (!id) {
      console.error("No ID found for delete operation");
      return;
    }
    
    if (!apartmenName) {
      console.error("No name found for delete operation");
      return;
    }

    // Swal kontrolü
    console.log("Swal available:", typeof swal !== 'undefined');
    console.log("Swal.fire available:", typeof swal !== 'undefined' && typeof swal.fire === 'function');

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
        console.log("Swal result:", result);
        if (result.isConfirmed) {
          var formData = new FormData();
          formData.append("action", "delete_apartment");
          formData.append("id", id);

          console.log("Sending delete request to:", urlApartment);
          console.log("Form data:", { action: "delete_apartment", id });

          fetch(urlApartment, {
            method: "POST",
            body: formData,
          })
            .then((response) => {
              console.log("Delete response received:", response);
              return response.json();
            })
            .then((data) => {
              console.log("Delete data received:", data);
              if (data.status == "success") {
                  console.log("Delete successful, removing row from table");
                  try {
                    let table = $("#apartmentsList").DataTable();
                    let row = buttonElement.closest("tr");
                    console.log("Row to remove:", row);
                    
                    if (row.length > 0) {
                      table.row(row).remove().draw(false);
                      console.log("Row removed successfully");
                    } else {
                      console.error("Could not find row to remove");
                    }
                    
                    swal.fire(
                      "Silindi",
                      `${apartmenName} numaralı daire başarıyla silindi.`,
                      "success"
                    );
                  } catch (tableError) {
                    console.error("Table removal error:", tableError);
                    swal.fire(
                      "Silindi",
                      `${apartmenName} numaralı daire başarıyla silindi. (Tablo güncellenemedi)`,
                      "success"
                    );
                  }
                } else {
                console.log("Delete failed:", data.message);
                swal.fire(
                  "Hata",
                  data.message || "Daire silinirken bir hata oluştu.",
                  "error"
                );
              }
            })
            .catch((error) => {
              console.error("Delete error:", error);
              swal.fire(
                "Hata",
                "Bir hata oluştu: " + error.message,
                "error"
              );
            });
        }
      })
      .catch((error) => {
        console.error("Swal error:", error);
      });
  });
});
