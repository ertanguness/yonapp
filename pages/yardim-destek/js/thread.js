$(document).on("click", "#createThreadBtn", function () {
  var form = $("#createThreadForm");
  var fd = new FormData(form[0]);
  fd.append("action", "createThread");
  form.validate({
    rules: { subject: { required: true }, message: { required: true } },
    messages: { subject: { required: "Konu gerekli" }, message: { required: "Mesaj gerekli" } },
  });
  if (!form.valid()) return;
  $.ajax({
    url: "/pages/yardim-destek/api/supportThreadApi.php",
    method: "POST",
    data: fd,
    processData: false,
    contentType: false,
    success: function (res) {
      var data = JSON.parse(res);
      var title = data.status === "success" ? "Başarılı" : "Hata";
      swal.fire({ title, text: data.message, icon: data.status }).then(function () {
        if (data.status === "success" && data.id) {
          window.location.href = "/pages/yardim-destek/manage.php?id=" + data.id;
        }
      });
    },
  });
});

$(document).on("click", "#sendReplyBtn", function () {
  var form = $("#replyForm");
  var fd = new FormData(form[0]);
  fd.append("action", "addMessage");
  fd.append("sender_type", "musteri");
  var msg = form.find("textarea[name='message']").val();
  if (!msg || msg.trim().length === 0) {
    swal.fire({ title: "Uyarı", text: "Mesaj gerekli", icon: "warning" });
    return;
  }
  $.ajax({
    url: "/pages/yardim-destek/api/supportThreadApi.php",
    method: "POST",
    data: fd,
    processData: false,
    contentType: false,
    success: function (res) {
      var data = JSON.parse(res);
      var title = data.status === "success" ? "Başarılı" : "Hata";
      swal.fire({ title, text: data.message, icon: data.status }).then(function () {
        if (data.status === "success") {
          window.location.reload();
        }
      });
    },
  });
});

$(document).on("click", "#closeThreadBtn", function () {
  var id = $(this).data("id");
  swal
    .fire({
      title: "Emin misiniz?",
      text: "Bu destek bildirimi kapatılacak.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet, kapat",
      cancelButtonText: "İptal",
    })
    .then(function (r) {
      if (!r.isConfirmed) return;
      $.post(
        "/pages/yardim-destek/api/supportThreadApi.php",
        { action: "closeThread", thread_id: id },
        function (res) {
          var data = JSON.parse(res);
          var title = data.status === "success" ? "Başarılı" : "Hata";
          swal.fire({ title, text: data.message, icon: data.status }).then(function () {
            if (data.status === "success") window.location.reload();
          });
        }
      );
    });
});

