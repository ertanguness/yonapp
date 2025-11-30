$(document).ready(function () {
  var dt = initDataTable("#tasksTable", {
    processing: true,
    serverSide: true,
    retrieve: true,
    ajax: {
      url: "/pages/personel/api/tasks_server_side.php",
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
      {data: "title"},
      {data: "description"},
      {data: "start_date"},
      {data: "end_date"},
      {data: "status"},
      {data: "actions", orderable: false}
    ],
    order: [[1, "asc"]]
  });
});
document
  .querySelector('a[data-bs-target="#taskManagementTab"]')
  .addEventListener("shown.bs.tab", function () {
    try {
      $("#tasksTable").DataTable().columns.adjust().responsive.recalc();
    } catch (e) {}
  });



/** Görev Kaydetme/Modaldan */
$(document).on("click", "#saveTaskBtn", function () {
  var form = $("#taskForm");
  var fd = new FormData(form[0]);
  fd.append("person_id", window.personId);

  /** Form validate */
  form.validate({
    rules: {
      task_title: {
        required: true,
      },
     
      task_start: {
        required: true,
      },
     
    },
    messages: {
      task_title: {
        required: "Görev başlığı gerekli",
      },
      task_start: {
        required: "Başlangıç tarihi gerekli",
      },
    },
  });
  if (!form.valid()) {
    return;
  }

  $.ajax({
    url: "/pages/personel/api/taskApi.php",
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
          confirmButtonText: "Tamam"
        })
        .then(() => {
          if (data.status === "success") {
            $("#taskModal").modal("hide");
            $("#tasksTable").DataTable().ajax.reload(null, false);
          }
        });
      /** tabloyu yeniden yükle */
    },
    error: function (xhr, status, err) {
      console.error("AJAX error", status, err, xhr.responseText);
      alert("İstek gönderilemedi. Konsolu kontrol edin.");
    }
  });
});

/** Görev Silme */
$(document).on("click", ".task-delete", function () {
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
    .then((result) => {
      if (result.isConfirmed) {
        $.post(
          "/pages/personel/api/taskApi.php",
          {
            action: "deleteTask",
            task_id: id
          },
          function (response) {
            var data = JSON.parse(response);
            var title = data.status == "success" ? "Başarılı" : "Hata";

            swal
              .fire({
                title: title,
                text: data.message,
                icon: data.status,
                confirmButtonText: "Tamam"
              })
              .then(() => {
                if (data.status === "success") {
                  $("#tasksTable").DataTable().ajax.reload(null, false);
                }
              });
          }
        );
      }
    });
});

/** Yeni Görev veya Görev Güncelleme */
$(document).on("click", "#newTaskBtn , .task-edit", function () {
  var id = $(this).data("id");
  $.get("/pages/personel/modal/task_modal.php", {
    id: id
  })
    .done(function (html) {
      $("#taskModal .task-modal").html(html);
      $("#taskModal").modal("show");
      $(".flatpickr").flatpickr({
        dateFormat: "d.m.Y",
        locale: "tr"
      });
      $(".select2").select2({
        dropdownParent: $("#taskModal")
      });
    })
    .fail(function () {
      $("#taskModal .task-modal").html(
        '<div class="p-3">İçerik yüklenemedi</div>'
      );
      $("#taskModal").modal("show");
    });
});
