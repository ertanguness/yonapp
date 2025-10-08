let url = "/pages/dues/dues-defines/api.php";
import { getBlocksBySite,getPeoplesBySite,getApartmentTypes } from "/assets/js/utils/debit.js";

$(document).on("click", "#save_dues", function () {
  var form = $("#duesForm");
  var formData = new FormData(form[0]);

  formData.append("action", "save_dues");
  formData.append("id", $("#dues_id").val());

  addCustomValidationMethods(true); //validNumber methodu için
  var validator = $("#duesForm").validate({
    rules: {
      due_days: {
        required: true,
      },
      amount: {
        required: true,
        validNumber : true,
        
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