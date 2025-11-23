let url = "/pages/duyuru-talep/users/api/APISikayet_oneri.php";

$(function () {
  $("#btnSubmit").on("click", function () {
    const type = $("#inpType").val();
    const title = $("#inpTitle").val().trim();
    const content = $("#inpContent").val().trim();
    if (!title || !content) {
      swal.fire({
        title: "Hata",
        text: "Başlık ve içerik zorunludur",
        icon: "error"
      });
      return;
    }
    const fd = new FormData($("#formSikayetOneri")[0]);
    fd.append("action", "CreateOrUpdate");

    fetch(url, {
      method: "POST",
      body: fd
    })
      .then((r) => r.json())
      .then((data) => {
        var titleMsg = data.status === "success" ? "Başarılı" : "Hata";
        swal
          .fire({
            title: titleMsg,
            text: data.message,
            icon: data.status,
            confirmButtonText: "Tamam"
          })
          .then(() => {
            if (data.status === "success") {
              window.location.href = "/sakin/sikayet-oneri-listem";
            }
          });
      });
  });

  $(document).on("click", ".sikayet-oneri-sil", function () {
      const id = $(this).data("id");
      swal
        .fire({
          title: "Silinsin mi?",
          text: "Bu işlem geri alınamaz",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Evet, sil",
          cancelButtonText: "Vazgeç"
        })
        .then((res) => {
          if (!res.isConfirmed) return;
          const fd = new FormData();
          fd.append("action", "delete");
          fd.append("id", id);
          fetch(url, {
            method: "POST",
            body: fd
          })
            .then((r) => r.json())
            .then((data) => {
              var titleMsg = data.status === "success" ? "Başarılı" : "Hata";
              swal
                .fire({
                  title: titleMsg,
                  text: data.message,
                  icon: data.status,
                  confirmButtonText: "Tamam"
                })
                .then(() => {
                  if (data.status === "success") {
                    window.location.reload();
                  }
                });
            });
        });
    });
});
