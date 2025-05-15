let url = "/pages/dues/dues-defines/api.php";

$(document).on("click", "#save_dues", function () {
  var form = $("#duesForm");
  var formData = new FormData(form[0]);

  formData.append("action", "save_dues");
  formData.append("id", $("#dues_id").val());

  addCustomValidationMethods(); //validNumber methodu için
  var validator = $("#duesForm").validate({
    rules: {
      due_days: {
        required: true,
      },
      amount: {
        required: true,
        validNumber : true,
        
      },
    },
    messages: {
      due_days: {
        required: "Please enter the dues name",
      },
      amount: {
        required: "Please enter the dues amount",
        validNumber: "Please enter a valid number",
      },
    },
  });
  if (!validator.form()) {
    return;
  }

  fetch(url, {
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

$(document).on("click", ".delete-dues", function () {
  let id = $(this).data("id");
  let dueName = $(this).data("name");
  let buttonElement = $(this); // Store reference to the clicked button
  swal
    .fire({
      title: "Emin misiniz?",
      html: `${dueName} <br> adlı aidat tanımını silmek istediğinize emin misiniz?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet",
      cancelButtonText: "Hayır",
    })
    .then((result) => {
      if (result.isConfirmed) {
        var formData = new FormData();
        formData.append("action", "delete_dues");
        formData.append("id", id);

        fetch(url, {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.status == "success") {
              let table = $("#duesTable").DataTable();
              table.row(buttonElement.closest("tr")).remove().draw(false);
              swal.fire(
                "Silindi",
                `${dueName} adlı aidat tanımı başarıyla silindi.`,
                "success"
              );
            }
          });
      }
    });
});
