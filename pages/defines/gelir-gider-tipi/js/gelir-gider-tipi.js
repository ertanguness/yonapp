let url = "/pages/defines/gelir-gider-tipi/api.php";


/** GGelir-Gider Tipi Kaydet */
$(document).on("click", "#saveGelirGiderTipi", function () {
  let id = $("#gelir_gider_tipi_id");
  let form = $("#gelirGiderTipiForm");

form.validate({
    rules: {
      gelir_gider_tipi_name: { required: true },
    },
    messages: {
      gelir_gider_tipi_name: { required: "Gelir Gider Tipi Adı boş bırakılamaz." },
    }
  });
  if (!form.valid()) {
    return false;
  }

  var formData = new FormData(form[0]);
  formData.append("gelir_gider_tipi_id", id.val());
  formData.append("action", "gelir-gider-tipi-kaydet");


  fetch(url, {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      return response.json();
    })
    .then((data) => {
      var title = data.status == "success" ? "Başarılı" : "Hata";
      id.val(data.lastInsertId);
      swal.fire({
        title: title,
        text: data.message,
        icon: data.status,
        confirmButtonText: "Tamam",
      });
    });
}); 

$(document).on("click", ".gelir-gider-tipi-sil", function () {
  const id = $(this).data("id");
  row = $(this).closest("tr");
  
  Swal.fire({
    title: 'Emin misiniz?',
    text: "Bu gelir-gider tipini silmek istediğinizden emin misiniz?",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Evet, sil!',
    cancelButtonText: 'İptal'
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: url,
        type: 'POST',
        data: {
          action: 'gelir-gider-tipi-sil',
          id: id
        },
        success: function(response) {
          try {
            const data = JSON.parse(response);
            console.log(data);
            
            if (data.status == 'success') {
              Swal.fire(
                'Silindi!',
                'Gelir-gider tipi başarıyla silindi.',
                'success'
              );
              // Satırı Sil
              table.row(row).remove().draw();
            } else {
              Swal.fire(
                data.status,
                data.message || 'Silme işlemi sırasında bir hata oluştu.',
                'error'
              );
            }
          } catch (e) {
            Swal.fire(
              'Hata!',
              'Beklenmeyen bir hata oluştu.',
              'error'
            );
            console.error(e);
          }
        },
        error: function() {
          Swal.fire(
            'Hata!',
            'Sunucu ile iletişim kurulamadı.',
            'error'
          );
        }
      });
    }
  });
});

