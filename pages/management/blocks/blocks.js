let blocksurl = "/pages/management/blocks/api.php";

$(document).on("click", "#save_blocks", function () {
  var form = $("#blocksForm");
  var formData = new FormData(form[0]);

  formData.append("action", "save_blocks");
  formData.append("id", $("#blok_id").val());


  var validator = $("#blocksForm").validate({
    rules: {
      blocksNumber: {
        required: true,
      },
      
    },
    messages: {
      blocksNumber: {
        required: "Lütfen blok sayısı giriniz",
      },
     
    },
  });
  if (!validator.form()) {
    return;
  }

  fetch(blocksurl, {
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

$(document).on("click", ".delete-blocks", function () {
  let id = $(this).data("id");
  let blocksName = $(this).data("name");
  let buttonElement = $(this); // Store reference to the clicked button
  swal
    .fire({
      title: "Emin misiniz?",
      html: `${blocksName} <br> adlı bloğu silmek istediğinize emin misiniz?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet",
      cancelButtonText: "Hayır",
    })
    .then((result) => {
      if (result.isConfirmed) {
        var formData = new FormData();
        formData.append("action", "delete_blocks");
        formData.append("id", id);

        fetch(blocksurl, {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.status == "success") {
              let table = $("#blocksList").DataTable();
              table.row(buttonElement.closest("tr")).remove().draw(false);
              swal.fire(
                "Silindi",
                `${blocksName} adlı blok  başarıyla silindi.`,
                "success"
              );
            }
          });
      }
    });
});
