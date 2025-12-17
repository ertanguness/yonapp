let url = "/pages/panel/kullanicilar/api/api.php";
$(document).on("click", "#userSaveBtn", function () {
  let $btn = $(this);
  const form = $("#userForm");
  const formData = new FormData(form[0]);

  form.validate({
    rules: {
      adi_soyadi: {
        required: true,
      },
      email_adresi: {
        required: true,
        email: true,
      },
      password: {
        required: function (element) {
          // Check if there's a hidden input for user ID. If not present or empty, it's a new user, so password is required.
          // Assuming an input with id 'userId' exists for editing existing users.
          return !$("#user_id").val() == 0;
        },
        minlength: 6,
      },
    },
    messages: {
      adi_soyadi: {
        required: "Lütfen adınızı giriniz",
      },
      email_adresi: {
        required: "Lütfen e-posta adresinizi giriniz",
        email: "Lütfen geçerli bir e-posta adresi giriniz",
      },
      password: {
        required: "Lütfen şifrenizi giriniz",
        minlength: "Şifre en az 6 karakter uzunluğunda olmalıdır",
      },
    },
  });

  if (!form.valid()) {
    return;
  }

  formData.append("action", "save_user");
  buttonLoader($btn, "click");

  fetch(url, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      console.log(data);

      let title = data.status == "success" ? "Başarılı" : "Hata";

      swal.fire({
        title: title,
        text: data.message,
        icon: data.status,
        confirmButtonText: "Tamam",
      });

      buttonLoader($btn);
    });
});

/**Kullanıcı sil */
$(document).on("click", ".kullanici-sil", function () {
  let $btn = $(this);
  let id = $btn.data("id");
  let fd = new FormData();
  fd.append("action", "delete");
  fd.append("id", id);

  buttonLoader($btn, "click", false);

  swal
    .fire({
      title: "Emin misiniz?",
      text: "Bu kullanıcının tüm verileri silinecektir!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet, sil!",
      cancelButtonText: "Hayır",
    })
    .then((result) => {
      if (result.isConfirmed) {
        fetch(url, {
          method: "POST",
          body: fd,
        })
          .then((response) => response.json())
          .then((data) => {
            console.log(data);

            let title = data.status == "success" ? "Başarılı" : "Hata";

            swal.fire({
              title: title,
              text: data.message,
              icon: data.status,
              confirmButtonText: "Tamam",
            });

            table.ajax.reload();

            buttonLoader($btn, "", false);
          });
      }
    });
});

/**Akfif veya Pasif Yapma */
$(document).on("click", ".durum-degistir", function () {
  let status = $(this).data("status");
  let id = $(this).data("id");
  let formData = new FormData();
  formData.append("action", "durum-degistir");
  formData.append("id", id);
  formData.append("status", status);

  fetch(url, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      console.log(data);

      let title = data.status == "success" ? "Başarılı" : "Hata";

      swal.fire({
        title: title,
        text: data.message,
        icon: data.status,
        confirmButtonText: "Tamam",
      });

      table.ajax.reload();

      buttonLoader($(this), "", false);
    });
});

/** Butona loader ekle */
function buttonLoader(button, action, loadingText = true) {
  if (action == "click") {
    if (loadingText) {
      button.html(
        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Kaydediliyor...'
      );
    }
    button.disabled = true;
  } else {
    if (loadingText) {
      button.html('<i class="feather-save  me-2"></i> Kaydet');
    }
    button.disabled = false;
  }
}
