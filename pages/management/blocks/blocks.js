let blocksurl = "/pages/management/blocks/api.php";

$(document).on("click", "#save_blocks", function () {
    var form = $("#blocksForm");
    var formData = new FormData(form[0]);

    formData.append("action", "save_blocks");
    formData.append("id", $("#blok_id").val());

    // Önce mevcut validatörü temizle
    if ($.data(form[0], 'validator')) {
        $(form).validate().destroy();
    }

    // Tüm blok adı ve bölüm sayısı inputlarını validasyon için geçici unique name'lere ayır
    $(".block-name").each(function (index) {
        $(this).attr("name", "block_names[" + index + "]");
    });

    $(".apartment-count").each(function (index) {
        $(this).attr("name", "apartment_counts[" + index + "]");
    });

    var validator = form.validate({
        rules: {
            blocksNumber: {
                required: true,
                digits: true,
                min: 1
            },
        },
        messages: {
            blocksNumber: {
                required: "Lütfen blok sayısı giriniz",
                digits: "Lütfen geçerli bir sayı giriniz",
                min: "En az 1 blok olmalıdır"
            },
        },
        // Dinamik alanlar için kuralları burada ekle
        ignore: [],
        errorPlacement: function (error, element) {
            error.insertAfter(element.closest(".input-group"));
        }
    });

    // Ek kurallar (dinamik alanlar)
    $(".block-name").each(function () {
        $(this).rules("add", {
            required: true,
            messages: {
                required: "Blok adı zorunludur"
            }
        });
    });

    $(".apartment-count").each(function () {
        $(this).rules("add", {
            required: true,
            digits: true,
            min: 1,
            messages: {
                required: "Bağımsız bölüm sayısı zorunludur",
                digits: "Sadece rakam giriniz",
                min: "En az 1 olmalıdır"
            }
        });
    });

    if (!validator.form()) {
        return;
    }

    fetch(blocksurl, {
        method: "POST",
        body: formData,
    })
        .then((response) => response.json())
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
