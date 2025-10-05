let url = "/pages/dues/debit/api.php";

//Borçlandrma Kaydet
$(document).on('click', '#save_debit_single', function() {
    let formData = new FormData($("#singleDebitForm")[0]);
    formData.append("action", "save_debit_single");

//   for(var pair of formData.entries()) {
//      console.log(pair[0]+ ', '+ pair[1]); 
//   }
//   return false;
 
    fetch(url, {
        method: "POST",
        body: formData,
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.status == "success") {
                console.log(data);
                
                // Başarılı durum
                swal.fire({
                    title: "Başarılı!",
                    text: "Borçlandırma başarıyla kaydedildi.",
                    icon: "success",
                    confirmButtonText: "Tamam",
                });
            } else {
                // Hata durumu
                swal.fire({
                    title: "Hata!",
                    text: data.message || "Borçlandırma kaydedilirken bir hata oluştu.",
                    icon: "error",
                    confirmButtonText: "Tamam",
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

        formData = new FormData();
        //sitenin aktif kişilerini getir
        formData.append("action", "get_people_by_site");

        fetch(url, {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.status == "success") {
              $("#hedef_kisi").empty();
              $("#hedef_kisi").append(
                $("<option disabled>Kişi Seçiniz</option>")
              );
              $.each(data.data, function (index, person) {
                $("#hedef_kisi").append(
                  $("<option></option>")
                    .val(person.id)
                    .text(person.daire_kodu + " | " + person.adi_soyadi)
                    .attr("data-block", person.block_id)
                );
              });
              if (
                $.fn.select2 &&
                $("#hedef_kisi").hasClass("select2-hidden-accessible")
              ) {
                $("#hedef_kisi").select2("destroy");
              }

              $("#hedef_kisi").select2({
                minimumResultsForSearch: 0,
              });
            }
          });

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
        updateAlertMessage(
          "Daire tiplerine göre borclandırma yapılacaktır.(Dükkan,3+1, 2+1, vb.)"
        );
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
