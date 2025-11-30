$(document).ready(function () {
  var dt = initDataTable("#leavesTable", {
    processing: true,
    serverSide: true,
    retrieve: true,
    ajax: {
      url: "/pages/personel/api/leaves_server_side.php",
      type: "GET",
      data: function (d) {
        d.person_id = window.personId || 0;
      }
    },
    columns: [
      {
        data: null,
        orderable: false,
        render: function (d, t, r, m) {
          return m.row + 1 + m.settings._iDisplayStart;
        }
      },
      { data: "type" },
      { data: "start_date" },
      { data: "end_date" },
      { data: "days" },
      { data: "description" },
      { data: "status" },
      { data: "actions", orderable: false }
    ],
    order: [[1, "asc"]]
  });
});
document
  .querySelector('a[data-bs-target="#leaveTrackingTab"]')
  .addEventListener("shown.bs.tab", function () {
    try {
      $("#leavesTable").DataTable().columns.adjust().responsive.recalc();
    } catch (e) {}
  });


/** Yeni izin ve Güncelleme */
$(document).on("click", "#newLeaveBtn,.leave-edit", function () {
  var id = $(this).data("id");
  $.get("/pages/personel/modal/leave_modal.php", { id: id })
    .done(function (html) {
      $("#leaveModal .leave-modal").html(html);
      $("#leaveModal").modal("show");
      $(".flatpickr").flatpickr({
        dateFormat: "d.m.Y",
        locale: "tr"
      });
    })
    .fail(function () {
      $("#leaveModal .leave-modal").html(
        '<div class="p-3">İçerik yüklenemedi</div>'
      );
      $("#leaveModal").modal("show");
    });
});


/** İzin Kaydetme/Modaldan */
$(document).on("click", "#saveLeaveBtn", function () {
  var form = $("#leaveForm");
  var fd = new FormData(form[0]);
  fd.append("person_id", window.personId);

  form.validate({
    rules: {
      leave_start: {
        required: true,
      },
      leave_end: {
        required: true,
      },
    },
    messages: {
      leave_start: {
        required: "İzin başlangıç tarihi gerekli",
      },
      leave_end: {
        required: "İzin bitiş tarihi gerekli",
      },
    }
  });

  if (!form.valid()) {
    return;
  }

  $.ajax({
    url: "/pages/personel/api/leaveApi.php",
    method: "POST",
    data: fd,
    processData: false,
    contentType: false,
    success: function (response) {
      var data = {};
      try { data = JSON.parse(response); } catch (e) { data = { status: "error", message: "Geçersiz cevap" }; }
      var title = data.status == "success" ? "Başarılı" : "Hata";
      swal
        .fire({
          title: title,
          text: data.message,
          icon: data.status,
          confirmButtonText: "Tamam"
        })
        .then(function () {
          if (data.status === "success") {
            $("#leaveModal").modal("hide");
            $("#leavesTable").DataTable().ajax.reload(null, false);
          }
        });
    },
    error: function (xhr, status, err) {
      console.error("AJAX error", status, err, xhr.responseText);
      alert("İstek gönderilemedi. Konsolu kontrol edin.");
    }
  });
});


/** İzin Silme */
$(document).on("click", ".leave-delete", function () {
  var id = $(this).data("id");
  swal
    .fire({
      title: "Emin misiniz?",
      text: "Bu işlem geri alınamaz!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet, sil!",
      cancelButtonText: "Hayır, iptal et"
    })
    .then(function (result) {
      if (result.isConfirmed) {
        $.post(
          "/pages/personel/api/leaveApi.php",
          { action: "deleteLeave", leave_id: id },
          function (response) {
            var data = {};
            try { data = JSON.parse(response); } catch (e) { data = { status: "error", message: "Geçersiz cevap" }; }
            var title = data.status == "success" ? "Başarılı" : "Hata";
            swal
              .fire({
                title: title,
                text: data.message,
                icon: data.status,
                confirmButtonText: "Tamam"
              })
              .then(function () {
                if (data.status === "success") {
                  $("#leavesTable").DataTable().ajax.reload(null, false);
                }
              });
          }
        );
      }
    });
});
