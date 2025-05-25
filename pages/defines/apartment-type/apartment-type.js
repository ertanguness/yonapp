let apartmentTypeApiUrl = "/pages/defines/apartment-type/api.php";

$(document).on("click", "#saveApartmentType", function () {
  var form = $("#apartmentTypeForm");
  var formData = new FormData(form[0]);

  formData.append("action", "saveApartmentType");
  formData.append("id", $("#apartment-type_id").val());


  var validator = $("#apartmentTypeForm").validate({
    rules: {
      apartment_type_name: {
        required: true,
      },
    },
    messages: {
      apartment_type_name: {
        required: "Lütfen Daire Tipi adını giriniz",
      },
    },
  });
  if (!validator.form()) {
    return;
  }

  fetch(apartmentTypeApiUrl, {
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

$(document).on("click", ".delete-apartment-type", function () {
  let id = $(this).data("id");
  let apartment_type_name = $(this).data("name");
  let buttonElement = $(this); // Store reference to the clicked button
  swal
    .fire({
      title: "Emin misiniz?",
      html: `${apartment_type_name } <br> adlı aidat tanımını silmek istediğinize emin misiniz?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet",
      cancelButtonText: "Hayır",
    })
    .then((result) => {
      if (result.isConfirmed) {
        var formData = new FormData();
        formData.append("action", "delete-apartment-type");
        formData.append("id", id);

        fetch(apartmentTypeApiUrl, {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.status == "success") {
              let table = $("#apartmentTypesList").DataTable();
              table.row(buttonElement.closest("tr")).remove().draw(false);
              swal.fire(
                "Silindi",
                `${apartment_type_name } adlı aidat tanımı başarıyla silindi.`,
                "success"
              );
            }
          });
      }
    });
});
