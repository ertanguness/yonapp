let acilDurumKisiEkleUrl = "/pages/management/peoples/api/AcilDurumKisileriApi.php";

$(document).on("click", "#AcilDurumEkle", function () {
  var form = $("#acilDurumKisileriEkleForm");
  var formData = new FormData(form[0]);

  formData.append("action", "AcilDurumEkle");
  formData.append("id", $("#acil_kisi_id").val());

  // Telefon numarası için sadece rakam ve 10 hane kontrolü
  $("#acilDurumKisiTelefon").on("input", function () {
    // Sadece rakam girilmesine izin ver
    this.value = this.value.replace(/\D/g, "");
    // Maksimum 10 karaktere sınırla
    if (this.value.length > 10) {
      this.value = this.value.slice(0, 10);
    }
  });

  var validator = $("#acilDurumKisileriEkleForm").validate({
    rules: {
      blok_id: { required: true },
      daire_id: { required: true },
      kisi_id: { required: true },
      acilDurumKisi: { required: true },
      acilDurumKisiTelefon: { 
        required: true,
        digits: true,
        minlength: 10,
        maxlength: 10
      },     
       yakinlik: { required: true }
    },
    messages: {
      blok_id: { required: "Lütfen blok seçiniz" },
      daire_id: { required: "Lütfen daire seçiniz" },
      kisi_id: { required: "Lütfen kişi seçiniz" },
      acilDurumKisi: { required: "Lütfen acil durum kişisi adını giriniz" },
      acilDurumKisiTelefon: { 
        required: "Lütfen telefon numarası giriniz",
        digits: "Telefon numarası sadece rakamlardan oluşmalıdır",
        minlength: "Telefon numarası 10 haneli olmalıdır",
        maxlength: "Telefon numarası 10 haneli olmalıdır"
      },      
      yakinlik: { required: "Lütfen yakınlık derecesini seçiniz" }
    },
    
  });
  
  if (!validator.form()) {
    return;
  }

  fetch(acilDurumKisiEkleUrl, {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      return response.json();
    })
    .then((data) => {
      console.log(data);
      if (data.status == "success") {
        let table = $("#acilDurumKisileriList").DataTable();
        table.row.add($(data.yeniAcilDurumKisiEkle)).draw(false);
       
       
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

$(document).on("click", ".delete-acilDurumKisi", function () {
  let id = $(this).data("id");
  let acilDurumKisiName = $(this).data("name");
  let buttonElement = $(this); // Store reference to the clicked button
  swal
    .fire({
      title: "Emin misiniz?",
      html: acilDurumKisiName + " <br> adlı kişiyi silmek istediğinize emin misiniz?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet",
      cancelButtonText: "Hayır",
    })
    .then((result) => {
      if (result.isConfirmed) {
        var formData = new FormData();
        formData.append("action", "delete_acilDurumKisi");
        formData.append("id", id);

        fetch(acilDurumKisiEkleUrl, {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.status == "success") {
              let table = $("#acilDurumKisileriList").DataTable();
              table.row(buttonElement.closest("tr")).remove().draw(false);
              swal.fire(
                "Silindi",
                acilDurumKisiName + " adlı kişi başarıyla silindi.",
                "success"
              );
            }
          });
      }
    });
});