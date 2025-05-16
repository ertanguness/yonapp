let url = "/pages/management/sites/api.php";

$(document).on("click", "#save_sites", function () {
  var form = $("#sitesForm");
  var formData = new FormData(form[0]);

  formData.append("action", "save_sites");
  formData.append("id", $("#sites_id").val());


  var validator = $("#sitesForm").validate({
    rules: {
      sites_name: {
        required: true,
      },
      il: {
        required: true,
      },
      
      adres: {
        required: true,
      },
    },
    messages: {
      sites_name: {
        required: "Lütfen site adını giriniz",
      },
      il: {
        required: "Lütfen il seçiniz",
      },
     
      adres: {
        required: "Lütfen adres giriniz",
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

$(document).on("click", ".delete-sites", function () {
  let id = $(this).data("id");
  let sitesName = $(this).data("name");
  let buttonElement = $(this); // Store reference to the clicked button
  swal
    .fire({
      title: "Emin misiniz?",
      html: `${sitesName} <br> adlı aidat tanımını silmek istediğinize emin misiniz?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet",
      cancelButtonText: "Hayır",
    })
    .then((result) => {
      if (result.isConfirmed) {
        var formData = new FormData();
        formData.append("action", "delete_sites");
        formData.append("id", id);

        fetch(url, {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.status == "success") {
              let table = $("#sitesList").DataTable();
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
