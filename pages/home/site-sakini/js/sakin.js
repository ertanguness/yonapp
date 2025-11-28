$(function () {
  $("#carModal").on("show.bs.modal", function (e) {
    var btn = $(e.relatedTarget);
    var id = btn && btn.data("car-id");
    window.__carEditing = !!id;
    if (id) {
      $.post(
        "/pages/home/site-sakini/api/CarApi.php",
        { action: "get_car", id: id },
        function (res) {
          if (res.status === "success" && res.data) {
            $("#car-id").val(id);
            $("#car-kisi-id").val(res.data.kisi_id);
            $("#car-plaka").val(res.data.plaka);
            $("#car-marka").val(res.data.marka_model);
            $("#car-renk").val(res.data.renk);
            $("#car-tipi").val(res.data.arac_tipi);
          }
        },
        "json"
      );
    } else {
      $("#car-plaka, #car-marka, #car-renk").val("");
      $("#car-tipi").val("");
    }
  });

  $("#emergencyModal").on("show.bs.modal", function (e) {
    var btn = $(e.relatedTarget);
    var id = btn && btn.data("em-id");
    window.__emEditing = !!id;
    if (id) {
      $.post(
        "/pages/home/site-sakini/api/EmergencyApi.php",
        { action: "get_em", id: id },
        function (res) {
          if (res.status === "success" && res.data) {
            $("#em-id").val(id);
            $("#em-kisi-id").val(res.data.kisi_id);
            $("#em-name").val(res.data.adi_soyadi);
            $("#em-phone").val(res.data.telefon);
            $("#yakinlik").val(res.data.yakinlik);
            $("#em-notes").val(res.data.notlar || "");
          }
        },
        "json"
      );
    } else {
      $("#em-name, #em-phone, #em-notes").val("");
      $("#yakinlik").val("");
    }
  });

  $(document).on("submit", "#car-form", function (ev) {
    ev.preventDefault();
    var id = $("#car-id").val();
    var data = new FormData(this);
    data.append("action", "save_car");
    if (window.__carEditing) {
      swal
        .fire({
          title: "Onay",
          text: "Değişiklikleri kaydetmek istiyor musunuz?",
          icon: "question",
          showCancelButton: true,
          confirmButtonText: "Evet",
          cancelButtonText: "Hayır"
        })
        .then(function (r) {
          if (r.isConfirmed) {
            $.ajax({
              url: "/pages/home/site-sakini/api/CarApi.php",
              method: "POST",
              data: data,
              processData: false,
              contentType: false,
              dataType: "json"
            }).done(function (res) {
              var title = res.status === "success" ? "Başarılı" : "Hata";
              swal
                .fire({
                  title: title,
                  text: res.message || "",
                  icon: res.status
                })
                .then(function () {
                  if (res.status === "success") {
                    location.reload();
                  }
                });
            });
          }
        });
    } else {
      $.ajax({
        url: "/pages/home/site-sakini/api/CarApi.php",
        method: "POST",
        data: data,
        processData: false,
        contentType: false,
        dataType: "json"
      }).done(function (res) {
        var title = res.status === "success" ? "Başarılı" : "Hata";
        swal
          .fire({ title: title, text: res.message || "", icon: res.status })
          .then(function () {
            if (res.status === "success") {
              location.reload();
            }
          });
      });
    }
  });

  $(document).on("click", ".delete-car", function () {
    var id = $(this).data("id");
    swal
      .fire({
        title: "Onay",
        text: "Bu aracı silmek istiyor musunuz?",
        icon: "warning",
        showCancelButton: true
      })
      .then(function (r) {
        if (r.isConfirmed) {
          $.post(
            "/pages/home/site-sakini/api/CarApi.php",
            { action: "delete_car", id: id },
            function (res) {
              var title = res.status === "success" ? "Başarılı!" : "Hata!";
              swal
                .fire({
                  title: title,
                  text: res.message || "İşlem Başarılı",
                  icon: res.status
                })
                .then(function () {
                  if (res.status === "success") {
                    location.reload();
                  }
                });
            },
            "json"
          );
        }
      });
  });

  $(document).on("submit", "#em-form", function (ev) {
    ev.preventDefault();
    var id = $("#em-id").val();
    var data = new FormData(this);
    data.append("action", "save_em");
    if (window.__emEditing) {
      swal
        .fire({
          title: "Onay",
          text: "Değişiklikleri kaydetmek istiyor musunuz?",
          icon: "question",
          showCancelButton: true,
          confirmButtonText: "Evet",
          cancelButtonText: "Hayır"
        })
        .then(function (r) {
          if (r.isConfirmed) {
            $.ajax({
              url: "/pages/home/site-sakini/api/EmergencyApi.php",
              method: "POST",
              data: data,
              processData: false,
              contentType: false,
              dataType: "json"
            }).done(function (res) {
              var title = res.status === "success" ? "Başarılı" : "Hata";
              swal
                .fire({
                  title: title,
                  text: res.message || "",
                  icon: res.status
                })
                .then(function () {
                  if (res.status === "success") {
                    location.reload();
                  }
                });
            });
          }
        });
    } else {
      $.ajax({
        url: "/pages/home/site-sakini/api/EmergencyApi.php",
        method: "POST",
        data: data,
        processData: false,
        contentType: false,
        dataType: "json"
      }).done(function (res) {
        var title = res.status === "success" ? "Başarılı" : "Hata";
        swal
          .fire({ title: title, text: res.message || "", icon: res.status })
          .then(function () {
            if (res.status === "success") {
              location.reload();
            }
          });
      });
    }
  });

  $(document).on("click", ".delete-em", function () {
    var id = $(this).data("id");
    swal
      .fire({
        title: "Onay",
        text: "Bu kişiyi silmek istiyor musunuz?",
        icon: "warning",
        showCancelButton: true
      })
      .then(function (r) {
        if (r.isConfirmed) {
          $.post(
            "/pages/home/site-sakini/api/EmergencyApi.php",
            { action: "delete_em", id: id },
            function (res) {
              var title = res.status === "success" ? "Silindi" : "Hata";
              swal
                .fire({
                  title: title,
                  text: res.message || "",
                  icon: res.status
                })
                .then(function () {
                  if (res.status === "success") {
                    location.reload();
                  }
                });
            },
            "json"
          );
        }
      });
  });
});
