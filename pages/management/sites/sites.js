let url = "/pages/management/sites/api.php";

$(document).on("click", "#save_sites", function () {
  var form = $("#sitesForm");
  var formData = new FormData(form[0]);

  formData.append("action", "save_sites");
  formData.append("id", $("#sites_id").val());


  var validator = $("#sitesForm").validate({
    rules: {
      sites_name: { required: true,},
      il: { required: true,},
      ilce: { required: true, },
      adres: { required: true, },
    },
    messages: {
      sites_name: { required: "Lütfen site adını giriniz", },
      il: { required: "Lütfen il seçiniz",},
      ilce: { required: "Lütfen ilçe seçiniz", },
      adres: {required: "Lütfen adres giriniz", },
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
      console.log(data);
      swal.fire({
        title: title,
        text: data.message,
        icon: data.status,
        confirmButtonText: "Tamam",
      }).then(() => {
        if(data.ilkSiteMi){
          console.log("İlk site eklendi, yönlendiriliyor...");
          window.location.href = "/site-ekle";
        }
      });
    });
});

// $(document).on("change", ".select2", function () {
//   $(this).valid(); // Trigger validation for the changed select2 element
// });



$(document).on("click", ".delete-Siteler", function () {
  let id = $(this).data("id");
  let sitesName = $(this).data("name");
  let buttonElement = $(this); // Store reference to the clicked button
  swal
    .fire({
      title: "Emin misiniz?",
      html: `${sitesName} <br> sitesini tüm verileri ile birlikte silmek istediğinize emin misiniz?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet",
      cancelButtonText: "Hayır",
    })
    .then((result) => {
      if (result.isConfirmed) {
        var formData = new FormData();
        formData.append("action", "delete-Siteler");
        formData.append("id", id);

        fetch(url, {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            let title = data.status == "success" ? "Başarılı" : "Hata";
            if (data.status == "success") {
           // console.log("Çözümlenmiş ID:", data.decrypted_id);
           //window.location.reload(); // Sayfayı yeniden yükle

              let table = $("#SitelerList").DataTable();
              table.row(buttonElement.closest("tr")).remove().draw(false);
              swal.fire(
                "Silindi",
                `${sitesName} sitesi  tanımı başarıyla silindi.`,
                "success"
              );
             }
             else{
              swal.fire(
                "Hata",
                data.message,
                "error"
              );
             }
          });
      }
    });
});
