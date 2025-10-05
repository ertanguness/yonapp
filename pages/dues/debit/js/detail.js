let url = "/pages/dues/debit/api.php";

//List sayfasından borçlandırma silmek için
$(document).on("click", ".delete-debit-detail", function () {
    let id = $(this).data("id");
    let Name = $(this).data("name");
    let buttonElement = $(this); // Store reference to the clicked button
    swal
      .fire({
        title: "Emin misiniz?",
        html: `${Name} <br> adlı borcu silmek istediğinize emin misiniz?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Evet",
        cancelButtonText: "Hayır",
      })
      .then((result) => {
        if (result.isConfirmed) {
          var formData = new FormData();
          formData.append("action", "delete_debit_detail");
          formData.append("id", id);
  
          fetch(url, {
            method: "POST",
            body: formData,
          })
            .then((response) => response.json())
            .then((data) => {
              if (data.status == "success") {
                let table = $("#debitTable").DataTable();
                table.row(buttonElement.closest("tr")).remove().draw(false);
                swal.fire("Silindi", `${Name} başarıyla silindi.`, "success");
              } else {
                swal.fire("Hata", data.message, "error");
              }
            });
        }
      });
  });
  