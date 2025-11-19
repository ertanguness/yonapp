$(function () {
  var peopleCache = {};

  function resetForm() {
    // $('#frmAcil')[0].reset();
    $("#frmId").val("");
    $("#selBlok").empty().append('<option value="">Seçiniz</option>');
    $("#selDaire").empty().append('<option value="">Seçiniz</option>');
    $("#selKisi").empty().append('<option value="">Seçiniz</option>');
  }

  function loadApartments(blokId, selectedId) {
    console.log("blokId:", blokId);
    var $d = $("#selDaire");
    var $k = $("#selKisi");
    $d.empty().append('<option value="">Seçiniz</option>');
    $k.empty().append('<option value="">Seçiniz</option>');
    if (!blokId) {
      $d.trigger("change");
      return;
    }
    $.ajax({
      url: "/pages/acil-durum-kisileri/api.php",
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
    });
  }

  function loadPeople(daireId, selectedId) {
    var $k = $("#selKisi");
    $k.empty().append('<option value="">Seçiniz</option>');
    peopleCache = {};
    if (!daireId) {
      $k.trigger("change");
      return;
    }
    $.ajax({
      url: "/pages/acil-durum-kisileri/api.php",
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
      if (selectedId) {
        var p = peopleCache[selectedId];
        if (p) {
          $("#frmName").val(p.adi_soyadi || "");
          $("#frmPhone").val(p.telefon || "");
        }
      }
    });
  }

  $(document).on("change", "#selBlok", function () {
    loadApartments($(this).val());
  });

  $(document).on("select2:select", "#selDaire", function (e) {
    loadPeople($(this).val());
  });
  $(document).on("change", "#selKisi", function () {
    var p = peopleCache[$(this).val()];
    if (p) {
      $("#frmName").val(p.adi_soyadi || "");
      $("#frmPhone").val(p.telefon || "");
    }
  });

  $(document).on("click", "#btnKaydet", function () {
    var form = $("#frmAcil");

    form.validate({
      rules :{
        frmName: {
          required: true
        },
        selKisi: {
          required: true
        },
        frmRel: {
          required: true
        }
      },
      messages: {
        frmName: {
          required: "Lütfen ad soyad giriniz"
        },
        selKisi: {
          required: "Lütfen Kişi giriniz"
        },
        frmRel: {
          required: "Lütfen yakınlık giriniz"
        }
      }
      
    });
    if (!form.valid()) {
      return;
    }

    var payload = {
      action: "acil-kisi-kaydet",
      id: $("#frmId").val(),
      kisi_id: $("#selKisi").val(),
      adi_soyadi: $("#frmName").val(),
      telefon: $("#frmPhone").val(),
      yakinlik: $("#frmRel").val()
    };

    console.log(payload);
    $("#expProgress").show();
    $("#expBar").css("width", "25%");
    $.ajax({
      url: "/pages/acil-durum-kisileri/api.php",
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
        url: "/pages/acil-durum-kisileri/api.php",
        type: "POST",
        data: { action: "acil-kisi-sil", id: id },
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

  (function wait() {
    if ($.fn && $.fn.DataTable) {
      $("#tblAcil").DataTable({
        retrieve: true,
        responsive: true,
        autoWidth: false,
        dom: 't<"row m-2"<"col-md-4"i><"col-md-4"l><"col-md-4 float-end"p>>',
        order: [[0, "desc"]],
        initComplete: function (settings) {
          var api = this.api();
          if (typeof window.attachDtColumnSearch === "function") {
            window.attachDtColumnSearch(api, settings.sTableId);
            api.columns.adjust().responsive.recalc();
          }
        },
        drawCallback: function () {
          $('[data-bs-toggle="tooltip"]').each(function () {
            var tt = bootstrap.Tooltip.getInstance(this);
            if (tt) tt.dispose();
            bootstrap.Tooltip.getOrCreateInstance(this);
          });
        }
      });
    } else {
      setTimeout(wait, 100);
    }
  })();

  $(document).on("shown.bs.modal", "#mdlAcil", function () {
    var $m = $(this);
    var blokId = $m.find("#selBlok").val();
    var dsAttr = $m.find("#selDaire").attr("data-selected");
    var ksAttr = $m.find("#selKisi").attr("data-selected");
    var daireSel = dsAttr && Number(dsAttr) > 0 ? dsAttr : "";
    var kisiSel = ksAttr && Number(ksAttr) > 0 ? ksAttr : "";
    if (blokId) {
      loadApartments(blokId, daireSel);
      if (daireSel) {
        loadPeople(daireSel, kisiSel);
      }
    }
  });
});
