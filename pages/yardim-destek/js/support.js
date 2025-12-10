$(document).ready(function () {
  var dt = initDataTable("#supportsTable", {
    processing: true,
    serverSide: true,
    retrieve: true,
    ajax: {
      url: "/pages/yardim-destek/api/supports_server_side.php",
      type: "GET"
    },
    columns: [
      {
        data: null,
        orderable: false,
        render: function (d, t, r, m) {
          return m.row + 1 + m.settings._iDisplayStart;
        }
      },
      { data: "konu" },
      { data: "aciklama" },
      { data: "actions", orderable: false }
    ],
    order: [[1, "asc"]]
  });
});

/** Talep Kaydetme */
$(document).on("click", "#saveSupportBtn", function () {
  var form = $("#supportForm");
  var fd = new FormData(form[0]);

  form.validate({
    rules: {
      support_subject: { required: true },
    },
    messages: {
      support_subject: { required: "Konu gerekli" },
    },
  });
  if (!form.valid()) {
    return;
  }

  $.ajax({
    url: "/pages/yardim-destek/api/supportApi.php",
    method: "POST",
    data: fd,
    processData: false,
    contentType: false,
    success: function (response) {
      var data = JSON.parse(response);
      var title = data.status == "success" ? "Başarılı" : "Hata";
      swal
        .fire({
          title: title,
          text: data.message,
          icon: data.status,
          confirmButtonText: "Tamam",
        })
        .then(function () {
          if (data.status === "success") {
            $("#supportModal").modal("hide");
            try { $("#supportsTable").DataTable().ajax.reload(null, false); } catch (e) {}
          }
        });
    },
    error: function (xhr, status, err) {
      console.error("AJAX error", status, err, xhr.responseText);
      alert("İstek gönderilemedi. Konsolu kontrol edin.");
    },
  });
});

/** Talep Silme */
$(document).on("click", ".support-delete", function () {
  var id = $(this).data("id");
  var name = $(this).data("name") || "";
  swal
    .fire({
      title: "Emin misiniz?",
      text: "Bu işlem geri alınamaz!" + (name ? " (" + name + ")" : ""),
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet, sil!",
      cancelButtonText: "Hayır, iptal et",
    })
    .then(function (result) {
      if (result.isConfirmed) {
        $.post(
          "/pages/yardim-destek/api/supportApi.php",
          { action: "deleteSupport", support_id: id },
          function (response) {
            var data = JSON.parse(response);
            var title = data.status == "success" ? "Başarılı" : "Hata";
            swal
              .fire({
                title: title,
                text: data.message,
                icon: data.status,
                confirmButtonText: "Tamam",
              })
              .then(function () {
                if (data.status === "success") {
                  try { $("#supportsTable").DataTable().ajax.reload(null, false); } catch (e) {}
                }
              });
          }
        );
      }
    });
});

/** Yeni Talep veya Düzenleme */
$(document).on("click", "#newSupportBtn, .support-edit", function () {
  var id = $(this).data("id") || 0;
  $.get("/pages/yardim-destek/modal/support_modal.php", { id: id })
    .done(function (html) {
      $("#supportModal .support-modal").html(html);
      $("#supportModal").modal("show");
    })
    .fail(function () {
      $("#supportModal .support-modal").html('<div class="p-3">İçerik yüklenemedi</div>');
      $("#supportModal").modal("show");
    });
});

