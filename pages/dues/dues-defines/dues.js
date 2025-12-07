let url = "/pages/dues/dues-defines/api.php";
import {
  getBlocksBySite,
  getPeoplesBySite,
  getApartmentTypes,
} from "/assets/js/utils/debit.js";

$(function () {
  table = new DataTable("#due_pending_list_table", {
    dom: "tip",
  });

  //Borçlandırılacak kişileri tabloya ekle
  $("#load_due_pending_list").on("click", function () {
    //Seçilen kişileri al
    var selectedBloklar = $("#block_id").val();
    var blokIds = selectedBloklar ? selectedBloklar.join(",") : "";

    var selectedDaireTipleri = $("#apartment_type").val();
    var daireTipiIds = selectedDaireTipleri
      ? selectedDaireTipleri.join(",")
      : "";

    var selectedKisiler = $("#hedef_kisi").val();
    var kisiIds = selectedKisiler ? selectedKisiler.join(",") : "";

    var tutar = $("#tutar").val();
    // tutar = tutar.replace(/\./g, '').replace(',', '.'); // Noktaları kaldır ve virgülü nokta yap
    // tutar = parseFloat(tutar).toFixed(2); // Ondalık kısmı 2 basamak yap
    tutar = tutar + " TL";
    var cezaOrani = $("#ceza_orani").val();
    var borclandirmaTipi = $("#hedef_tipi option:selected").text();
    var hedef_tipi_text = $("#hedef_tipi option:selected").val();

    var daireIds = "";
    var daireKodu = "";
    var adiSoyadi = "";
    var blokAdi = "";
    var daireTipi = "";

    //hedef_kisi multiple select ile seçilen her kişi için tabloya ekle
    selectedKisiler.forEach(function (kisi_id) {
      var kisiText = $('#hedef_kisi option[value="' + kisi_id + '"]').text();
      var parts = kisiText.split(" | ");
      adiSoyadi = adiSoyadi + parts[1] + "," || "";
    });
    adiSoyadi = adiSoyadi.trim();

    //en sondaki virgülü kaldır
    if (adiSoyadi.endsWith(",")) {
      adiSoyadi = adiSoyadi.slice(0, -1);
    }

    selectedBloklar.forEach(function (blok_id) {
      var blokText = $('#block_id option[value="' + blok_id + '"]').text();
      blokAdi = blokAdi + blokText + "," || "";
    });

    //en sondaki virgülü kaldır
    if (blokAdi.endsWith(",")) {
      blokAdi = blokAdi.slice(0, -1);
    }

    selectedDaireTipleri.forEach(function (daire_tipi_id) {
      var daireTipiText = $(
        '#apartment_type option[value="' + daire_tipi_id + '"]'
      ).text();
      daireTipi = daireTipi + daireTipiText + "," || "";
    });
    //en sondaki virgülü kaldır
    if (daireTipi.endsWith(",")) {
      daireTipi = daireTipi.slice(0, -1);
    }

    //tutar 0 ise swal ile uyarı ver ve ekleme yapma
    if (tutar == "" || parseFloat(tutar.replace(",", ".")) <= 0) {
      Swal.fire({
        icon: "warning",
        title: "Uyarı",
        text: "Lütfen geçerli bir tutar giriniz.",
      });
      return;
    }

    //Tabloda aynı borçlandırma tipi var mı kontrol et
    //Daire Kodu, Adı Soyadı, Blok Adı, Daire Tipi aynı ise uyarı ver
    var exists = false;
    table.rows().every(function (rowIdx, tableLoop, rowLoop) {
      var data = this.node();
      if ($(data).data("target") === hedef_tipi_text
      && $(data).find("td[data-daire-ids]").data("daire-ids") === daireIds
      && $(data).find("td[data-kisi-ids]").data("kisi-ids") === kisiIds
      && $(data).find("td[data-blok-ids]").data("blok-ids") === blokIds
      && $(data).find("td[data-daire-tipi-ids]").data("daire-tipi-ids") === daireTipiIds
    ) {
        exists = true;
        Swal.fire({
          icon: "warning",
          title: "Uyarı",
          text: "Bu borçlandırma tipi zaten tabloda mevcut.",
        });
        return false; // Döngüyü kır
      }
    });

    if (exists) {
      return;
    }

    //Yeni satır oluştur
    var newRow =
      '<tr data-target="' +
      hedef_tipi_text +
      '">' +
      "<td>" +
      borclandirmaTipi +
      "</td>" +
      '<td data-daire-ids="' +
      daireIds +
      '">' +
      daireKodu +
      "</td>" +
      '<td data-kisi-ids="' +
      kisiIds +
      '">' +
      adiSoyadi +
      "</td>" +
      '<td data-blok-ids="' +
      blokIds +
      '">' +
      blokAdi +
      "</td>" +
      '<td data-daire-tipi-ids="' +
      daireTipiIds +
      '">' +
      daireTipi +
      "</td>" +
      "<td>" +
      tutar +
      "</td>" +
      "<td>" +
      cezaOrani +
      "</td>" +
      '<td style="width: 8%; text-align: center;"><div class="hstack gap-2">' +
      '<a href="javascript:void(0);" class="avatar-text avatar-md delete-debit" title="Sil">' +
      '<i class="feather-trash-2"></i>' +
      "</a>" +
      "</div></td>" +
      "</tr>";

    //Tabloya ekle
    table.row.add($(newRow)).draw();
    checkSaveButtonState();
  });

  function checkSaveButtonState() {
    var due_name = $("#due_name").val();
    var start_date = $("#start_date").val();
    // Sadece gerçek veri satırlarını say (dataTables_empty class'ı hariç)
    var tableRowCount = table
      .rows({
        filter: "applied",
      })
      .count();

    if (due_name !== "" && start_date !== "" && tableRowCount > 0) {
      $("#save_dues").removeClass("disabled");
    } else {
      $("#save_dues").addClass("disabled");
    }
  }

  // Form alanları değiştiğinde kontrol et
  $("#duesForm input, #duesForm select, #duesForm textarea").on(
    "change keyup",
    checkSaveButtonState
  );

  // Satır silme işlemi için de kontrol ekleyin
  $("#due_pending_list_table").on("click", ".delete-debit", function () {
    table.row($(this).closest("tr")).remove().draw();
    // checkSaveButtonState burada bazen hemen çalışmayabilir, onun için draw eventine de ekle
    checkSaveButtonState();
  });
});

$(document).on("click", "#save_dues", function () {
  //Aidat adı kontrolü
  var due_name = $("#due_name").val();
  if (due_name.trim() === "") {
    Swal.fire({
      icon: "warning",
      title: "Uyarı",
      text: "Lütfen aidat adını giriniz.",
    });
    return;
  }

  //Başlangıç tarihi kontrolü
  var start_date = $("#start_date").val();
  if (start_date.trim() === "") {
    Swal.fire({
      icon: "warning",
      title: "Uyarı",
      text: "Lütfen başlangıç tarihini giriniz.",
    });
    return;
  }

  //Tabloda herhangi bir satır yoksa uyarı ver
  var tableRowCount = table
    .rows({
      filter: "applied",
    })
    .count();
  if (tableRowCount === 0) {
    Swal.fire({
      icon: "warning",
      title: "Uyarı",
      text: "Lütfen borçlandırılacak en az bir kişi ekleyiniz.",
    });
    return;
  }

  //tablodaki satırları json olarak bir değişkene satır olarak ata
  var duePendingList = [];
  table.rows().every(function (rowIdx, tableLoop, rowLoop) {
    var data = this.data();
    var $row = $(this.node());
    var tutar = data[5].trim();

    //Borçlandırma tipini al(all, person, block, dairetipi, evsahibi)
    var borclandirmaTipi = $row.data("target");

    //Borçlandırılacak Kişi idsini al
    var kisiIds = $row.find("td:eq(2)").data("kisi-ids") || "";

    //Blok idsini al
    var blokIds = $row.find("td:eq(3)").data("blok-ids") || "";

    //Daire tipi idsini al
    var daireTipiIds = $row.find("td:eq(4)").data("daire-tipi-ids") || "";

    duePendingList.push({
      borclandirma_tipi: borclandirmaTipi,
      daire_id: data[1].trim(),
      kisi_ids: kisiIds,
      blok_ids: blokIds,
      daire_tipi_ids: daireTipiIds,
      tutar: tutar,
      ceza_orani: data[6].trim(),
    });
  });

  //console.log(duePendingList); return;

  var form = $("#duesForm");
  var formData = new FormData(form[0]);

  formData.append("action", "save_dues");
  formData.append("id", $("#dues_id").val());
  formData.append("due_pending_list", JSON.stringify(duePendingList));

  // for (let pair of formData.entries()) {
  //   console.log(pair[0] + ", " + pair[1]);
  // }
  // return;

  addCustomValidationMethods(true); //validNumber methodu için
  var validator = $("#duesForm").validate({
    rules: {
      due_days: {
        required: true,
      },
      amount: {
        required: true,
        validNumber: true,
      },
    },
    messages: {
      due_days: {
        required: "Lütfen bir aidat günü giriniz",
      },
      amount: {
        required: "Lütfen bir tutar giriniz",
        validNumber: "Geçerli bir tutar giriniz (0 dahil).",
      },
    },
  });
  if (!validator.form()) {
    return;
  }

  fetch(url, {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      return response.json();
    })
    .then((data) => {
      var title = data.status == "success" ? "Başarılı" : "Hata";
      console.log(data);
      swal.fire({
        title: title,
        html: data.message,
        icon: data.status,
        confirmButtonText: "Tamam",
      });
    });
});

$(document).on("click", ".delete-dues", function () {
  let id = $(this).data("id");
  let dueName = $(this).data("name");
  let buttonElement = $(this); // Store reference to the clicked button
  swal
    .fire({
      title: "Emin misiniz?",
      html: `${dueName} <br> adlı aidat tanımını silmek istediğinize emin misiniz?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet",
      cancelButtonText: "Hayır",
    })
    .then((result) => {
      if (result.isConfirmed) {
        var formData = new FormData();
        formData.append("action", "delete_dues");
        formData.append("id", id);

        fetch(url, {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.status == "success") {
              let table = $("#duesTable").DataTable();
              table.row(buttonElement.closest("tr")).remove().draw(false);
              swal.fire(
                "Silindi",
                `${dueName} adlı aidat tanımı başarıyla silindi.`,
                "success"
              );
            }
          });
      }
    });
});

$(document).ready(function () {
  const $targetType = $("#hedef_tipi");
  const $targetPerson = $("#hedef_kisi");
  const $blockSelect = $("#block_id");
  const $targetDaireTipi = $("#daire_tipi");
  const $alertDescription = $(".alert-description");

  function updateAlertMessage(message) {
    $alertDescription.fadeOut(200, function () {
      $(this).text(message).fadeIn(200);
    });
  }

  function toggleElements(options) {
    $targetPerson
      .prop("disabled", options.targetPersonDisabled)
      .val(null)
      .trigger("change");
    $blockSelect
      .prop("disabled", options.blockSelectDisabled)
      .val(null)
      .trigger("change");
    $targetDaireTipi
      .prop("disabled", options.targetDaireTipiDisabled)
      .val(null)
      .trigger("change");
    $(".dairetipi-sec").toggleClass("d-none", options.hideDaireTipi);
    $(".blok-sec").toggleClass("d-none", options.hideBlokSec);
    $(".blok-sec-label").text(options.blokSecLabel || "Blok Seç:");
  }

  toggleElements({
    targetPersonDisabled: true,
    blockSelectDisabled: true,
    hideDaireTipi: true,
    hideBlokSec: false,
  });

  $targetType.on("change", function () {
    const type = $(this).val();

    switch (type) {
      case "0":
        toggleElements({
          targetPersonDisabled: true,
          blockSelectDisabled: true,
          hideDaireTipi: true,
          hideBlokSec: true,
        });
        updateAlertMessage("Borçlandırma yapmak için listeden seçim yapınız.");
        break;

      case "person":
        toggleElements({
          targetPersonDisabled: false,
          blockSelectDisabled: true,
          hideDaireTipi: true,
          hideBlokSec: false,
        });
        updateAlertMessage(
          "Kişiler listesinden seçtiğiniz kişilere borclandırma yapılacaktır."
        );

        getPeoplesBySite();
        break;

      case "all":
        const allValues = $targetPerson
          .find("option")
          .map(function () {
            return $(this).val();
          })
          .get();
        $targetPerson.val(allValues).trigger("change");
        toggleElements({
          targetPersonDisabled: true,
          blockSelectDisabled: true,
          hideDaireTipi: true,
          hideBlokSec: false,
        });
        updateAlertMessage(
          "Tüm Sakinler seçildiğinde, şu anda sitede oturan <strong>aktif</strong> ev sahibi ve kiracılara borclandırma yapılacaktır."
        );
        break;

      case "evsahibi":
        toggleElements({
          targetPersonDisabled: true,
          blockSelectDisabled: true,
          hideDaireTipi: true,
          hideBlokSec: false,
        });
        updateAlertMessage(
          "Yalnızca Ev sahiplerine borclandırma yapılacaktır."
        );
        break;

      case "dairetipi":
        toggleElements({
          targetPersonDisabled: true,
          blockSelectDisabled: true,
          hideDaireTipi: false,
          hideBlokSec: true,
          blokSecLabel: "Daire Tipi Seç:",
        });
        updateAlertMessage(
          "Daire tiplerine göre borclandırma yapılacaktır.(Dükkan,3+1, 2+1, vb.)"
        );
        //Define tablosundan daire tiplerini getir
        getApartmentTypes();
        break;

      case "block":
        //assets/js/utils/debit.js'den fonksiyon
        getBlocksBySite();
        toggleElements({
          targetPersonDisabled: true,
          blockSelectDisabled: false,
          hideDaireTipi: true,
          hideBlokSec: false,
        });
        updateAlertMessage(
          "Seçtiğiniz bloktaki kişilere veya ayrıca sadece seçilen kişilere borclandırma yapılacaktır."
        );
        break;

      default:
        toggleElements({
          targetPersonDisabled: true,
          blockSelectDisabled: true,
          hideDaireTipi: true,
          hideBlokSec: true,
        });
        break;
    }
  });

  $blockSelect.on("change", function () {
    const selectedBlock = $(this).val();
    $targetPerson.val(null).trigger("change");
    $targetPerson
      .find("option")
      .hide()
      .filter(function () {
        return $(this).data("block") == selectedBlock;
      })
      .show();
  });

  $(".select2-single").select2({
    placeholder: "Seçiniz",
    width: "100%",
    minimumResultsForSearch: Infinity,
  });

  $(".select2-multiple").select2({
    placeholder: "Kişi seçiniz",
    width: "100%",
  });
});
