let url = "/pages/dues/debit/api.php";

//Borçlandırma kaydet
$(document).on("click", "#save_debit", function () {
  var form = $("#debitForm");
  var formData = new FormData(form[0]);

  formData.append("action", "borclandir");
  formData.append("id", $("#borc_id").val());
  formData.append("borc_adi", $("#borc_baslik option:selected").text());

  for (let pair of formData.entries()) {
    console.log(pair[0] + ", " + pair[1]);
  }

  addCustomValidationMethods(); //validNumber methodu için
  var validator = $("#debitForm").validate({
    rules: {
      amount: {
        required: true,
        validNumber: true,
      },
    },
    messages: {
      amount: {
        required: "Lütfen borç miktarını giriniz",
        validNumber: "Lütfen geçerli bir sayı giriniz",
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
      console.log(data);
      var title = data.status == "success" ? "Başarılı" : "Hata";
      swal.fire({
        title: title,
        text: data.message,
        icon: data.status,
        confirmButtonText: "Tamam",
      });
    });
});

//List sayfasından borçlandırma silmek için
$(document).on("click", ".delete-debit", function () {
  let id = $(this).data("id");
  let Name = $(this).data("name");
  let buttonElement = $(this); // Store reference to the clicked button
  swal
    .fire({
      title: "Emin misiniz?",
      html: `${Name} <br> adlı borcu silmek istediğinize emin misiniz?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet",
      cancelButtonText: "Hayır",
    })
    .then((result) => {
      if (result.isConfirmed) {
        var formData = new FormData();
        formData.append("action", "delete_debit");
        formData.append("id", id);

        fetch(url, {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.status == "success") {
              let table = $("#debitTable").DataTable();
              table.row(buttonElement.closest("tr")).remove().draw(false);
              swal.fire("Silindi", `${Name} başarıyla silindi.`, "success");
            } else {
              swal.fire("Hata", data.message, "error");
            }
          });
      }
    });
});

//sayfa yüklenince aidat bilgilerini getir
$(document).ready(function () {
  // Manage sayfasında aidat bilgilerini getir
  let pageUrl = window.location.href;
  if (pageUrl.includes("manage")) {
    getDueInfo();
  }

  //   const $targetType = $("#hedef_tipi");
  // //Hedef Tipi seçildiğinde, hedef tipine göre alanların görünürlüğünü ayarla
  //   $targetType.on("change", function () {
  //     let type = $(this).val();
  //     switch (type) {
  //       // Daire tipi seçildiğinde, daire tiplerini getir
  //       case "dairetipi":

  //     }
  //   });
});

//Aidat adı değiştiğinde, aidatın güncel verilerini getir
$(document).on("change", "#borc_baslik", function () {
  getDueInfo();
});

//Aidat adı değiştiğinde, aidatın güncel verilerini getir
$(document).on("change", "#block_id", function () {
  getPeoplesByBlock($(this).val());
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
          "Tüm Sakinler seçildiğinde, şu anda sitede oturan ev sahibi ve kiracılara borclandırma yapılacaktır."
        );
        break;

      case "evsahibi":
        toggleElements({
          targetPersonDisabled: false,
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
        updateAlertMessage("Daire tiplerine göre borclandırma yapılacaktır.(Dükkan,3+1, 2+1, vb.)");
        //Define tablosundan daire tiplerini getir
        var formData = new FormData();
        formData.append("action", "get_apartment_types");
        fetch(url, {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.status == "success") {
              $("#apartment_type").empty();
              $("#apartment_type").append(
                 $("<option disabled>Daire Tipi seçiniz</option>")
              );
              $.each(data.data, function (index, type) {
                $("#apartment_type").append(
                  $("<option></option>").val(type.id).text(type.define_name)
                );
              });
             // Select2'yi başlat ve arama kutusunu etkinleştir
             $("#apartment_type").select2({
              minimumResultsForSearch: 0,
          });
             
            }
          });

        break;

      case "block":
        getBlocksBySite();
        toggleElements({
          targetPersonDisabled: false,
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

//Borç bilgilerini getir
function getDueInfo() {
  //dues tablosundan verileri getir
  let duesId = $("#borc_baslik").val();

  var formData = new FormData();
  formData.append("action", "get_due_info");
  formData.append("id", duesId);

  fetch(url, {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      return response.json();
    })
    .then((data) => {
      if (data.status == "success") {
        // console.log(data.data);

        $("#tutar").val(data.data.amount.replace(".", ","));
        $("#ceza_orani").val(data.data.penalty_rate);
      } else {
        swal.fire({
          title: "Hata",
          text: data.message,
          icon: "error",
          confirmButtonText: "Tamam",
        });
      }
    });
}

//Site değiştiğinde blokları getir
function getBlocksBySite(siteId) {
  var formData = new FormData();
  formData.append("action", "get_blocks");

  // for(let pair of formData.entries()) {
  // console.log(pair[0]+ ', ' + pair[1]);
  // }

  fetch(url, {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      return response.json();
    })
    .then((data) => {
      if (data.status == "success") {
        console.log(data);
        $("#block_id").empty();
        //Block Seçiniz seçeneği ekle
        $("#block_id").append(
          $("<option></option>").val(0).text("Blok Seçiniz")
        );
        $.each(data.data, function (index, block) {
          $("#block_id").append(
            $("<option></option>").val(block.id).text(block.blok_adi)
          );
        });
      }
    });
}

//Blok Seçildiğinde kişileri getir
function getPeoplesByBlock(blockId) {
  var formData = new FormData();
  formData.append("action", "get_peoples_by_block");
  formData.append("block_id", blockId);

  // for(let pair of formData.entries()) {
  // console.log(pair[0]+ ', ' + pair[1]);
  // }

  fetch(url, {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      return response.json();
    })
    .then((data) => {
      if (data.status == "success") {
        //console.log(data.data);
        $("#hedef_kisi").empty();
        //Kişi Seçiniz seçeneği ekle
        $("#hedef_kisi").append(
          $("<option disabled></option>").val(0).text("Kişileri Seçiniz")
        );
        $.each(data.data, function (index, people) {
          $("#hedef_kisi").append(
            $("<option></option>").val(people.id).text(people.adi_soyadi)
          );
        });
      }
    });
}
