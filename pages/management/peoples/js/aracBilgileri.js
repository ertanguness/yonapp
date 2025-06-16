let aracEkleUrl = "/pages/management/peoples/api/AracBilgileriApi.php";



$(document).on("click", "#AracEkle", function () {
  var form = $("#aracEkleForm");
  var formData = new FormData(form[0]);

  formData.append("action", "AracEkle");
  formData.append("id", $("#kisi_id").val());

 

  // Plaka inputunda harfleri otomatik olarak büyük harfe çevir
  $("#modalAracPlaka").on("input", function () {
    this.value = this.value.toUpperCase();
  });

  var validator = $("#aracEkleForm").validate({
    rules: {
      blok_id: { required: true },
      daire_id: { required: true },
      kisi_id: { required: true },
      modalAracPlaka: {
        required: true,
        plakaKontrol: true
      },
      modalAracMarka: { required: false }
    },
    messages: {
      blok_id: { required: "Lütfen blok seçiniz" },
      daire_id: { required: "Lütfen daire seçiniz" },
      kisi_id: { required: "Lütfen kişi seçiniz" },
      modalAracPlaka: {
        required: "Lütfen araç plakası giriniz",
        plakaKontrol: "Geçerli bir plaka giriniz (örn: 34 ABC 123 veya DE 1234 XYZ)"
      }
    },
  
  });
  
  $.validator.addMethod("plakaKontrol", function (value, element) {
    return this.optional(element) || /^[A-Z0-9\s\-]{4,12}$/i.test(value);
  }, "Lütfen geçerli bir plaka giriniz");
  

  if (!validator.form()) {
    return;
  }

  fetch(aracEkleUrl, {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      return response.json();
    })
    .then((data) => {
      console.log(data);
      if (data.status == "success") {
        let table = $("#aracList").DataTable();
        table.row.add($(data.yeniAracEkle)).draw(false);
       
       
        //Eğer işlem başarılı ve güncelleme ise tablodaki veriyi güncelle
        // let rownode = table.$(tr[data-id="${islem_id}"])[0];
        // //console.log(rownode);
        // if (rownode) {
        //   table.row(rownode).remove().draw();
        //   table.row.add($(data.son_kayit)).draw(false);
        // }
        
      }
      var title = data.status == "success" ? "Başarılı" : "Hata";
      swal.fire({
        title: title,
        text: data.message,
        icon: data.status,
        confirmButtonText: "Tamam",
      });
    });
});

$(document).on("click", ".delete-car", function () {
  let id = $(this).data("id");
  let carName = $(this).data("name");
  let buttonElement = $(this); // Store reference to the clicked button
  swal
    .fire({
      title: "Emin misiniz?",
      html: `${carName} <br> plakalı aracı silmek istediğinize emin misiniz?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet",
      cancelButtonText: "Hayır",
    })
    .then((result) => {
      if (result.isConfirmed) {
        var formData = new FormData();
        formData.append("action", "delete_car");
        formData.append("id", id);

        fetch(aracEkleUrl, {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.status == "success") {
              let table = $("#aracList").DataTable();
              table.row(buttonElement.closest("tr")).remove().draw(false);
              swal.fire(
                "Silindi",
                `${carName} plakalı araç başarıyla silindi.`,
                "success"
              );
            }
          });
      }
    });
});