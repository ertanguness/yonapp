$(function () {
  var peopleCache = {};
  var modalUrl = "/pages/arac-yonetimi/modal/arac_modal.php";
  var _apartReq = null;
  var _peopleReq = null;

  function loadApartments(blokId, selectedId) {
    var $d = $("#selDaire");
    var $k = $("#selKisi");
    $d.empty().append('<option value="">Seçiniz</option>');
    $k.empty().append('<option value="">Seçiniz</option>');
    if (!blokId) {
      $d.trigger("change");
      return;
    }
    if (_apartReq && _apartReq.abort) { try { _apartReq.abort(); } catch(e){} }
    _apartReq = $.ajax({
      url: "/pages/arac-yonetimi/api.php",
      type: "POST",
      data: { action: "daireler-getir", blok_id: blokId },
      dataType: "json"
    }).done(function (resp) {
      var arr = resp.data || [];
      arr.forEach(function (di) {
        $d.append(
          '<option value="' + di.id + '">' + (di.daire_no || "") + "</option>"
        );
      });
      if (selectedId) {
        $d.val(String(selectedId));
      }
      $d.trigger("change");
      if (selectedId) {
        loadPeople(selectedId);
      }
    }).always(function(){ _apartReq = null; });
  }

  function loadPeople(daireId, selectedId) {
    var $k = $("#selKisi");
    $k.empty().append('<option value="">Seçiniz</option>');
    peopleCache = {};
    if (!daireId) {
      $k.trigger("change");
      return;
    }
    if (_peopleReq && _peopleReq.abort) { try { _peopleReq.abort(); } catch(e){} }
    _peopleReq = $.ajax({
      url: "/pages/arac-yonetimi/api.php",
      type: "POST",
      data: { action: "kisiler-getir", daire_id: daireId },
      dataType: "json"
    }).done(function (resp) {
      var arr = resp.data || [];
      arr.forEach(function (p) {
        peopleCache[p.id] = p;
        $k.append(
          '<option value="' + p.id + '">' + (p.adi_soyadi || "") + "</option>"
        );
      });
      if (selectedId) {
        $k.val(String(selectedId));
      }
      $k.trigger("change");
    }).always(function(){ _peopleReq = null; });
  }

  $(document).on("change", "#selBlok", function () {
    loadApartments($(this).val());
  });
  $(document).on("change", "#selDaire", function () {
    loadPeople($(this).val());
  });

  $(document).on("click", "#btnKaydet", function () {
    var form = $("#frmArac");
    form.validate({
      rules: { frmPlaka: { required: true }, selKisi: { required: true } },
      messages: {
        frmPlaka: { required: "Lütfen plaka giriniz" },
        selKisi: { required: "Lütfen kişi seçiniz" }
      }
    });
    if (!form.valid()) return;
    var payload = {
      action: "arac-kaydet",
      id: $("#frmId").val(),
      kisi_id: $("#selKisi").val(),
      plaka: $("#frmPlaka").val(),
      marka_model: $("#frmMarka").val()
    };
    $("#expProgress").show();
    $("#expBar").css("width", "25%");
    $.ajax({
      url: "/pages/arac-yonetimi/api.php",
      type: "POST",
      data: payload,
      dataType: "json"
    })
      .done(function (resp) {
        $("#expBar").css("width", "100%");
        setTimeout(function () {
          $("#expProgress").hide();
          $("#expBar").css("width", "0%");
        }, 350);
        if (resp.status === "success") {
          Swal.fire({
            title: "Başarılı",
            text: resp.message || "Kayıt başarılı",
            icon: "success"
          }).then(() => {
            location.reload();
          });
        } else {
          Swal.fire({
            title: "Hata",
            text: resp.message || "İşlem başarısız",
            icon: "error"
          });
        }
      })
      .fail(function () {
        $("#expProgress").hide();
        $("#expBar").css("width", "0%");
        Swal.fire({ title: "Hata", text: "İşlem başarısız", icon: "error" });
      });
  });

  $(document).on("click", ".btn-del", function () {
    var id = $(this).data("id");
    var name = $(this).data("name") || "";
    Swal.fire({
      title: "Emin misiniz?",
      text: name ? name + " silinsin mi?" : "Kayıt silinsin mi?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet",
      cancelButtonText: "Hayır"
    }).then(function (res) {
      if (!res.isConfirmed) return;
      $.ajax({
        url: "/pages/arac-yonetimi/api.php",
        type: "POST",
        data: { action: "arac-sil", id: id },
        dataType: "json"
      })
        .done(function (resp) {
          if (resp.status === "success") {
            Swal.fire({
              title: "Başarılı",
              text: resp.message || "Kayıt silindi",
              icon: "success"
            }).then(() => {
              location.reload();
            });
          } else {
            Swal.fire({
              title: "Hata",
              text: resp.message || "İşlem başarısız",
              icon: "error"
            });
          }
        })
        .fail(function () {
          Swal.fire({ title: "Hata", text: "İşlem başarısız", icon: "error" });
        });
    });
  });

  // (function wait() {
  //   if ($.fn && $.fn.DataTable) {
  //     $("#aracList").DataTable({
  //       retrieve: true,
  //       responsive: true,
  //       autoWidth: false,
  //       dom: 't<"row m-2"<"col-md-4"i><"col-md-4"l><"col-md-4 float-end"p>>',
  //       order: [[0, "desc"]],
  //       initComplete: function (settings) {
  //         var api = this.api();
  //         if (typeof window.attachDtColumnSearch === "function") {
  //           window.attachDtColumnSearch(api, settings.sTableId);
  //           api.columns.adjust().responsive.recalc();
  //         }
  //       }
  //     });
  //   } else {
  //     setTimeout(wait, 100);
  //   }
  // })();

  $("#btnYeniArac").on("click", function () {
    $.get(modalUrl, function (html) {
      var $modal = $("#mdlCar");
      $modal.find(".modal-content").html(html);
      $modal.modal("show");
      $modal.find(".select2").select2({ dropdownParent: $modal });
      $("#selBlok").trigger("change");
    });
  });
  $(document).on("click", ".btn-edit", function () {
    var id = $(this).data("id");
    $.get(modalUrl + "?id=" + id, function (html) {
      var $modal = $("#mdlCar");
      $modal.find(".modal-content").html(html);
      $modal.modal("show");
      $modal.find(".select2").select2({ dropdownParent: $modal });
      $("#selBlok").trigger("change");
    });
  });
});