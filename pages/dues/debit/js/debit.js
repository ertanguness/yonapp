import { getBlocksBySite, getPeoplesBySite, getPeoplesByBlock,getDueInfo } from "/assets/js/utils/debit.js";

let url = "/pages/dues/debit/api.php";

//Borçlandırma kaydet
$(document).on("click", "#save_debit", function (e) {
  var form = $("#debitForm");
  e.preventDefault();
  var button = $(this);

  // Butonu devre dışı bırak ve yükleme göstergesi ekle
  button
    .prop("disabled", true)
    .html('<i class="fas fa-spinner fa-spin"></i> İşleniyor...');

  var formData = new FormData(form[0]);

  formData.append("action", "borclandir");
  formData.append("id", $("#borc_id").val());
  formData.append("borc_adi", $("#borc_baslik option:selected").text());

  //   for (let pair of formData.entries()) {
  //     console.log(pair[0] + ", " + pair[1]);
  //   }
  // return

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

  preloader.show(); // Yükleme overlay'ini göster
  Pace.track(() => {
    fetch(url, {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        return response.json();
      })
      .then((data) => {
        // Butonu tekrar aktif et
        button
          .prop("disabled", false)
          .html('<i class="feather-save  me-2"></i>Kaydet');

        var title = data.status == "success" ? "Başarılı" : "Hata";
        preloader.hide(); // Yükleme overlay'ini gizle
        console.log(data);
        swal.fire({
          title: title,
          text: data.message,
          icon: data.status,
          confirmButtonText: "Tamam",
        });
      }).catch((error) => {
        console.error("Fetch error:", error);
        preloader.hide(); // Yükleme overlay'ini gizle
        button
          .prop("disabled", false)
          .html('<i class="feather-save  me-2"></i>Kaydet');
        swal.fire({
          title: "Hata",
          text: "Bir hata oluştu. Lütfen tekrar deneyin.",
          icon: "error",
          confirmButtonText: "Tamam",
        });
      });
  });
});


//borclandirma sil
$(document).on("click", ".delete-debit", function (e) {
  row = $(this).closest("tr");

  e.preventDefault();
  var debitId = $(this).data("id");
  var formData = new FormData();
  formData.append("action", "borclandirma_sil");
  formData.append("id", debitId);
  swal.fire({
    title: "Emin misin?",
    text: "Bu işlem geri alınamaz!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Evet, sil!",
  }).then((result) => {
    if (result.isConfirmed) {
      Pace.track(() => {
        fetch(url, {
          method: "POST",
          body: formData,
        })
          .then((response) => {
            return response.json();
          })
          .then((data) => {
            console.log(data);
            preloader.hide(); // Yükleme overlay'ini gizle
            if (data.status == "success") {
              //Tablo satırını kaldır
              row.remove();

            }
            swal.fire({
              title: data.status == "success" ? "Başarılı" : "Hata",
              text: data.message,
              icon: data.status,
              confirmButtonText: "Tamam",
            });
          });
      });
    }
  });
});



//Borçlandırma kaydet(Tekil Borçlandırma için)
$(document).on("click", "#save_debit_single", function (e) {
  var form = $("#debitForm");
  e.preventDefault();
  var button = $(this);

  // Butonu devre dışı bırak ve yükleme göstergesi ekle
  button
    .prop("disabled", true)
    .html('<i class="fas fa-spinner fa-spin"></i> İşleniyor...');

  var formData = new FormData(form[0]);

  formData.append("action", "borclandir_single_consolidated");
  formData.append("borc_adi", $("#borc_baslik option:selected").text());
  formData.append("id", $("#borc_id").val());

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

  Pace.track(() => {
    fetch(url, {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        return response.json();
      })
      .then((data) => {
        // Butonu tekrar aktif et
        button
          .prop("disabled", false)
          .html('<i class="feather-save  me-2"></i>Kaydet');

        var title = data.status == "success" ? "Başarılı" : "Hata";
        swal.fire({
          title: title,
          text: data.message,
          icon: data.status,
          confirmButtonText: "Tamam",
        });
      });
  });
});


//sayfa yüklenince aidat bilgilerini getir
$(document).ready(function () {
  // Manage sayfasında aidat bilgilerini getir
  let pageUrl = window.location.href;
  if (pageUrl.includes("manage")) {
    getDueInfo();
  }


});

//Aidat adı değiştiğinde, aidatın güncel verilerini getir
$(document).on("change", "#borc_baslik", function () {
  getDueInfo();
  // Aidat adı değiştiğinde, açıklamayı güncelle
  createDescription(); // Açıklamayı oluştur
});

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


        break;

      case "block":
        toggleElements({
          targetPersonDisabled: false,
          blockSelectDisabled: false,
          hideDaireTipi: true,
          hideBlokSec: false,
        });
        updateAlertMessage(
          "Seçtiğiniz bloktaki kişilere veya ayrıca sadece seçilen kişilere borclandırma yapılacaktır."
        );
        getBlocksBySite();
        console.log($("#block_id option:selected").val());
        getPeoplesByBlock($("#block_id option:selected").val());


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



//baslangıç tarihini seçince bitiş tarihini güncelle
$(document).on("change", "#baslangic_tarihi", function () {
  let startDate = $(this).val();
  if (startDate) {
    // Bitiş tarihini başlangıç tarihinin bulunduğu ayın son gününe ayarla
    let dateParts = startDate.split(".");
    let date = new Date(dateParts[2], dateParts[1] - 1, dateParts[0]); // Gün, Ay, Yıl
    date.setMonth(date.getMonth() + 1);
    date.setDate(0); // Ayın son günü
    let endDate = date.toLocaleDateString("tr-TR"); // DD.MM.YYYY formatında al
    $("#bitis_tarihi").val(endDate);



    createDescription(); // Açıklamayı oluştur

  }




});


function createDescription() {
  let tarih = $("#baslangic_tarihi").val(); // Başlangıç tarihi seçildi mi?
  let borc_adi = $("#borc_baslik option:selected").text(); // Borç adı seçildi mi?
  let $aciklama = $("#aciklama");
  const [gun, ay, yil] = tarih.split(".");
  const tarihObjesi = new Date(`${yil}-${ay}-${gun}`);

  // Ay adını Türkçe almak için Intl kullanılır:
  const ayAdi = new Intl.DateTimeFormat('tr-TR', { month: 'long' }).format(tarihObjesi).toUpperCase();

  $aciklama.val(`${ayAdi} ${yil} ${borc_adi}`);
}
//burayı daha sonra açacağım
// /**
//  * Tekil Borçlandırmayı düzenleme butonu
//  * @param {Event} e - Tıklama olayı
//  */
// $(document).on("click", "#save_debit_single", function (e) {
//   e.preventDefault();

//   var form = $("#debitForm");
//   var formData = new FormData(form[0]);

//   formData.append("action", "update_debit_single_consolidated");
//   fetch(url, {
//     method: "POST",
//     body: formData,
//   })
//     .then((response) => response.json())
//     .then((data) => {
//       let title = data.status == "success" ? "Başarılı" : "Hata";
//       swal.fire({
//         title: title,
//         text: data.message,
//         icon: data.status,
//         confirmButtonText: "Tamam",
//       });
//     });
// });


/**
 * Form gönderimini işler ve kullanıcı etkileşimini engeller
 * @param {string|HTMLElement} element - Buton veya form elementi
 * @param {Function} fetchCall - fetch işlemini döndüren fonksiyon
 * @param {Object} [swalOptions={}] - SweetAlert için ekstra seçenekler
 */
async function handleFormWithLock(element, fetchCall, swalOptions = {}) {
  const el =
    typeof element === "string" ? document.querySelector(element) : element;
  if (!el) return;

  // Elementi kilitle
  el.disabled = true;
  const originalHtml = el.innerHTML;
  el.innerHTML = '<i class="fas fa-spinner fa-spin"></i> İşleniyor...';

  // Overlay oluştur
  const overlay = document.createElement("div");
  overlay.className = "form-overlay";
  document.body.appendChild(overlay);

  try {
    // Pace.js ile takip et
    const data = await Pace.track(async () => {
      return await fetchCall();
    });

    // Başarılı yanıt
    Swal.fire({
      title: data.status === "success" ? "Başarılı" : "Hata",
      text: data.message,
      icon: data.status,
      confirmButtonText: "Tamam",
      ...swalOptions,
    });

    return data;
  } catch (error) {
    console.error("Error:", error);
    Swal.fire({
      title: "Hata",
      text: error.message || "Bir hata oluştu",
      icon: "error",
      confirmButtonText: "Tamam",
      ...swalOptions,
    });
    throw error;
  } finally {
    // Kilidi kaldır
    el.disabled = false;
    el.innerHTML = originalHtml;
    document.body.removeChild(overlay);
  }
}
