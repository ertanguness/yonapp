let urlApartment = "/pages/management/apartment/api.php";

$(document).on("click", "#save_apartment", function () {
  var form = $("#apartmentForm");
  var formData = new FormData(form[0]);

  formData.append("action", "save_apartment");
  formData.append("id", $("#apartment_id").val());


  var validator = $("#apartmentForm").validate({
    rules: {
      blockName: {
        required: true,
      },
      floor: {
        required: true,
      },
      
      flatNumber: {
        required: true,
      },
      apartment_type: {
        required: true,
      },
    },
    messages: {
      blockName: {
        required: "Lütfen blok seçiniz",
      },
      floor: {
        required: "Lütfen kat giriniz",
      },
     
      flatNumber: {
        required: "Lütfen daire no giriniz",
      },
      apartment_type: {
        required: "Lütfen daire tipi seçiniz",
      },
    },
  });
  if (!validator.form()) {
    return;
  }

  fetch(urlApartment, {
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

$(document).on("click", ".delete-apartment", function () {
  let id = $(this).data("id");
  let apartmenName = $(this).data("name");
  let buttonElement = $(this); // Store reference to the clicked button
  swal
    .fire({
      title: "Emin misiniz?",
      html: `${apartmenName} <br> adlı aidat tanımını silmek istediğinize emin misiniz?`,
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
                `${sitesName} adlı aidat tanımı başarıyla silindi.`,
                "success"
              );
            }
          });
      }
    });
});
