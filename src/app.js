let table;
let row;
let preloader;
$(document).ready(function () {
  const $gg = $("#gelirGiderTable");
  if ($gg.length) {
    table = $gg.DataTable({
      //stateSave: true,
      responsive: true,
      autoWidth: true,
      dom: 't<"row m-2"<"col-md-4"i><"col-md-4"l><"col-md-4 float-end"p>>',
      language: {
        //url: "/assets/js/tr.json",
      },
      drawCallback: function (settings) {
        // Sadece tablonun içindeki tooltip'leri yenile (daha performanslı)
        $('#gelirGiderTable [data-bs-toggle="tooltip"]').each(function () {
          // Eski tooltip instance'ı varsa dispose et
          var existingTooltip = bootstrap.Tooltip.getInstance(this);
          if (existingTooltip) {
            existingTooltip.dispose();
          }
          // Yeni tooltip oluştur
          new bootstrap.Tooltip(this);
        });
      },

      serverSide: true,
      processing: true,
      deferRender: true,
      ajax: {
        url: "/pages/finans-yonetimi/gelir-gider/server_side_api.php",
        type: "POST",
        data: function (d) { d.action = "datatable-list"; },
        dataSrc: "data"
      },
      ordering: false,

      initComplete: function (settings, json) {
        var api = this.api();
        var tableId = settings.sTableId;

        $("#" + tableId + " thead").append(
          '<tr class="search-input-row"></tr>'
        );

        api.columns().every(function () {
          let column = this;
          let title = column.header().textContent;

          if (
            title != "İşlem" &&
            title != "Seç" &&
            title != "#" &&
            $(column.header()).find('input[type="checkbox"]').length === 0
          ) {
            // Input elementini oluştur
            let input = document.createElement("input");
            input.placeholder = title;
            input.classList.add("form-control", "form-control-sm");
            input.setAttribute("autocomplete", "off");

            // Ortalanmış <th> içine ekle
            const th = $('<th class="search text-center align-middle">').append(input);
            $("#" + tableId + " .search-input-row").append(th);

            // Event listener
            $(input).on("keyup change", function () {
              if (column.search() !== this.value) {
                column.search(this.value).draw();
              }
            });

            // Sütunun görünürlüğünü kontrol et
            const isColumnVisible =
              column.visible() && !$(column.header()).hasClass("dtr-hidden");

            if (!isColumnVisible) {
              th.hide(); // görünmüyorsa input gizle
            }
          } else {
            // İşlem / seçim sütunları için boş th
            $("#" + tableId + " .search-input-row").append("<th></th>");
          }
        });

        // Responsive resize olayı
        api.on("responsive-resize", function (e, datatable, columns) {
          $("#" + tableId + " .search-input-row th").each(function (index) {
            if (columns[index]) {
              $(this).show();
            } else {
              $(this).hide();
            }
          });
        });

        // State yükleme
        var state = api.state.loaded();
        if (state && state.sTableId === tableId) {
          console.log("State loaded for table:", tableId);
          var inputs = $("thead input");

          inputs.each(function (inputIndex) {
            var columnIndex = $(this).closest("th").index();
            var searchValue = state.columns[columnIndex]?.search?.search || "";

            if (searchValue) {
              $(this).val(searchValue);
              table.column(columnIndex).search(searchValue);
            }
          });

          api.draw();
        } else {
          api.state.clear();
        }

        api.columns.adjust().responsive.recalc();
      },
    });

    $(window).on('resize.gg', function(){
      try { table.columns.adjust().responsive.recalc(); } catch(e) {}
    });
  }
  const $others = $(".datatables").not($gg);

  if ($others.length) {
    table = $others.DataTable({
      responsive: true,
      dom: 't<"row m-2"<"col-md-4"i><"col-md-4"l><"col-md-4 float-end"p>>',
      language: {},
      drawCallback: function (settings) {},
      ...getTableSpecificOptions(),
    });
  }
});


$("#exportExcel").on("click", function () {
  table.button(".buttons-excel").trigger();
});

function getTableSpecificOptions() {
  return {
    ordering: document.getElementById("gelirGiderTable") ? false : true,
  };
}

if ($(".select2").length > 0) {
  $(".select2").select2();

  // $("#products").select2({
  //   dropdownParent: $(".modal")
  // });
  // $(".modal .select2").select2({
  //   dropdownParent: $(".modal")
  // });
  // $("#amount_money").select2({
  //   dropdownParent: $(".modal")
  // });
  // // $("#firm_cases").select2({
  // //   dropdownParent: $(".modal")
  // // });
  // $(
  //   "#wage_cut_month, #wage_cut_year,#income_month, #income_year, #payment_month, #payment_year"
  // ).select2({
  //   dropdownParent: $(".modal")
  // });

  // Modal'daki select2'lerin dropdown parent'ını modal yap
  $(".modal .select2").each(function () {
    $(this).select2({ dropdownParent: $(this).parent() });
  });
}
$(document).ready(function () {
  if ($(".summernote").length > 0) {
    var summernoteHeight = $(window).height() * 0.24; // Set height to 30% of window height
    $(".summernote").summernote({
      height: summernoteHeight,
      fontNames: [
        "inter",
        "Arial",
        "Arial Black",
        "Comic Sans MS",
        "Courier New",
      ],
      addDefaultFonts: "inter",
      callbacks: {
        onInit: function () {
          $(".summernote").summernote("height", summernoteHeight);
          $(".summernote").summernote("fontName", "inter");
        },
      },
    });
  }
});

if ($(".flatpickr:not(.time-input)").length > 0) {
  $(".flatpickr:not(.time-input)").flatpickr({
    dateFormat: "d.m.Y",
    locale: "tr", // locale for this instance only
  });



}

function formatNumber(num) {
  return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
}

$(function () {
  $('[data-toggle="tooltip"]').tooltip();
  preloader = $("#preloader");
});

$(document).on("click", ".route-link", function () {
  var page = $(this).data("page");
  var link = "index?p=" + page;

  window.location = link;
});
if ($(".select2").length > 0) {
  $(".select2.islem").select2({
    tags: true,
  });
}

function dtSearchInput(tableId, column, value) { }

//Geri dönüş yapmadan kayıt silme işlemi
function deleteRecord(
  button = this,
  action = null,
  confirmMessage = "Kayıt silinecektir!",
  url = "/api/ajax.php"
) {
  // Butonun bulunduğu satırın referansını al
  var row = $(button).closest("tr");

  //Tablo adı butonun içinde bulunduğu tablo
  var tableName = $(button).closest("table")[0].id;
  var table = $("#" + tableName).DataTable();

  var tableRow = table.row(row);

  var id = $(button).data("id");

  //formData objesi oluştur
  const formData = new FormData();
  //formData objesine action ve id elemanlarını ekle
  formData.append("action", action);
  formData.append("id", id);
  // formData.append("csrf_token", csrf_token);

  // console.log(url);

  AlertConfirm(confirmMessage).then((result) => {
    fetch(url, {
      method: "POST",
      body: formData,
    })
      //Gelen yanıtı json'a çevir
      .then((response) => response.json())

      //Sonuc olumlu ise success toast mesajı göster
      .then((data) => {
        // console.log(data);

        if (data.status == "success") {
          title = "Başarılı!";
          icon = "success";
        } else {
          title = "Hata!";
          icon = "error";
        }
        Swal.fire({
          title: title,
          html: data.message,
          icon: icon,
        }).then((result) => {
          if (result.isConfirmed) {
            if (data.status == "success") tableRow.remove().draw(false);
            return data;
          }
        });
        // createToast("success", data.message);
      })

      //Sonuc olumsuz ise error toast mesajı göster
      .catch((error) => alert("Error deleting : " + error));
  });
}

//Geri dönüş yaparak kayıt silme işlemi
async function deleteRecordByReturn(
  button,
  action = null,
  confirmMessage = "Kayıt silinecektir!",
  url = "/api/ajax.php"
) {
  // Butonun bulunduğu satırın referansını al
  var row = $(button).closest("tr");

  //Tablo adı butonun içinde bulunduğu tablo
  var tableName = $(button).closest("table")[0].id;
  var table = $("#" + tableName).DataTable();

  var tableRow = table.row(row);

  var id = $(button).data("id");

  //formData objesi oluştur
  const formData = new FormData();
  //formData objesine action ve id elemanlarını ekle
  formData.append("action", action);
  formData.append("id", id);

  const result = await AlertConfirm(confirmMessage);
  if (result) {
    try {
      const response = await fetch(url, {
        method: "POST",
        body: formData,
      });
      const data = await response.json();

      let title, icon;
      if (data.status == "success") {
        title = "Başarılı!";
        icon = "success";
      } else {
        title = "Hata!";
        icon = "error";
      }

      await Swal.fire({
        title: title,
        text: data.message,
        icon: icon,
      });

      if (data.status == "success") {
        tableRow.remove().draw(false);
      }

      return data;
    } catch (error) {
      console.error("Error deleting:", error);
      return { status: "error", message: "Bir hata oluştu." };
    }
  }
}

function AlertConfirm(confirmMessage = "Emin misiniz?") {
  return new Promise((resolve, reject) => {
    Swal.fire({
      title: "Emin misiniz?",
      html: confirmMessage,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Evet, Sil!",
    }).then((result) => {
      if (result.isConfirmed) {
        resolve(true); // Kullanıcı onayladı, işlemi devam ettir
      } else {
        reject(false); // Kullanıcı onaylamadı, işlemi durdur
      }
    });
  });
}


function getCurrentPageSlug() {
  const slug = window.location.pathname.match(/\/([^\/=]+)(?:\/|=|$)/);
  return slug ? slug[1] : '';
}

$(document).on("change", "#mySite", function () {
  var page = getCurrentPageSlug() || 'ana-sayfa';
  window.location = "/set-session.php?p=" + page + "&site_id=" + $(this).val();
});

//İl seçildiğinde ilçeleri getir
function getTowns(cityId, targetElement) {
  var formData = new FormData();
  formData.append("city_id", cityId);
  formData.append("action", "getTowns");

  console.log("Fetching towns for city ID:", cityId);
  fetch("api/il-ilce.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      let towns = data.towns;
      $(targetElement).html(towns);
    })
    .catch((error) => {
      console.error("Error:", error);
    });
}

//Personeli kaydedip kaydetmediğimize bakarız
function checkPersonId(id) {
  if (id == 0) {
    swal.fire({
      title: "Hata",
      icon: "warning",
      text: "Öncelikle personeli kaydetmeniz gerekir!",
    });
    return false;
  }
  return true;
}
//Personeli kaydedip kaydetmediğimize bakarız
function checkId(id, item) {
  if (id == 0) {
    swal.fire({
      title: "Hata",
      icon: "warning",
      text: "Öncelikle " + item + " kaydetmeniz gerekir!",
    });
    return false;
  }
  return true;
}

function goWhatsApp() {
  const phoneNumber = "905079432723";
  const message = encodeURIComponent("Merhaba, Teknik desteğe ihtiyacım var");
  const url = `https://wa.me/send?phone=${phoneNumber}&text=${message}`;
  window.open(url, "_blank");
}

function previewImage(event) {
  var reader = new FileReader();
  reader.onload = function () {
    var output = document.querySelector(".brand-img img");
    output.src = reader.result;
  };
  reader.readAsDataURL(event.target.files[0]);
}

//para birimi mask
if ($(".money").length > 0) {
  $(document).on("focus", ".money", function () {
    $(this).inputmask("decimal", {
      radixPoint: ",",
      groupSeparator: ".",
      digits: 2,
      autoGroup: true,
      rightAlign: false,
      removeMaskOnSubmit: true,
    });
  });

  //Tarih formatı için inputmask kullan
  $(document).on("focus", ".flatpickr:not(.time-input)", function () {
    $(this).inputmask("99.99.9999", {
      placeholder: "gg.aa.yyyy",
      clearIncomplete: true,
    });
  });
}

$.validator.setDefaults({
  highlight: function (element) {
    // input-group varsa, tüm input-group'u işaretle
    var $group = $(element).closest(".input-group");
    if ($group.length) {
      $group.addClass("is-invalid");
    } else {
      $(element).addClass("is-invalid");
    }
  },
  unhighlight: function (element) {
    var $group = $(element).closest(".input-group");
    if ($group.length) {
      $group.removeClass("is-invalid");
    } else {
      $(element).removeClass("is-invalid");
    }
    $(element).next(".error").remove();
  },
  errorPlacement: function (error, element) {
    var $group = $(element).closest(".input-group");
    if ($group.length) {
      error.insertAfter($group);
    } else {
      error.insertAfter(element);
    }
  },
});

//Jquery validate ile yapılan doğrulamalarda para birimi formatı için
function addCustomValidationMethods(allowZero = false) {
  $.validator.addMethod(
    "validNumber",
    function (value, element) {
      const numericValue = parseFloat(value.replace(",", "."));
      return (
        this.optional(element) ||
        (/^[0-9.,]+$/.test(value) &&
          (allowZero ? numericValue >= 0 : numericValue > 0))
      );
    },
    allowZero
      ? "Lütfen geçerli bir sayı girin ve 0 veya daha büyük bir değer girin"
      : "Lütfen geçerli bir sayı girin ve 0'dan büyük bir değer girin"
  );
}

//Jquery validate ile yapılan doğrulamalarda 0 olan değeri kabul etmemek için
function addCustomValidationValidValue() {
  $.validator.addMethod(
    "validValue",
    function (value, element) {
      return (
        this.optional(element) || parseFloat(value.replace(",", ".")) !== 0
      );
    },
    "Lütfen geçerli bir değer girin"
  );
}

/**
 * Kısayol Tuşları atamak için fonksiyon
 */
function setupShortcut(shortcutKey, callback) {
  $(document).on('keydown', function (event) {
    // Ctrl + <shortcutKey> kombinasyonunu kontrol et
    if (event.ctrlKey && event.key.toLowerCase() === shortcutKey.toLowerCase()) {
      event.preventDefault(); // Varsayılan işlemi engelle

      // Dışarıdan gelen callback fonksiyonunu çalıştır
      callback();
    }
  });

}
// Kullanım örneği:
//setupShortcut('s', function() {
// Burada yapılacak işlemi tanımlıyoruz
//$('#userSaveBtn').trigger('click');
//});

/**
 * Buton loading durumunu kontrol eden genel fonksiyon
 * @param {string} buttonSelector - Buton seçici (ID veya class)
 * @param {boolean} isLoading - Yükleme durumu (true: yükleniyor, false: normal)
 * @param {string} loadingText - Yükleme sırasında gösterilecek metin
 * @param {string} normalText - Normal durumda gösterilecek metin
 */
function setButtonLoading(buttonSelector, isLoading = false, loadingText = 'Yükleniyor...', normalText = null) {
  var $button = $(buttonSelector);

  if (isLoading) {
    // Orijinal metni sakla (eğer normalText verilmemişse)
    if (!normalText) {
      $button.data('original-text', $button.html());
    }

    // Yükleme durumunu ayarla
    $button.html(`<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${loadingText}`);
    $button.prop("disabled", true);
  } else {
    // Normal duruma döndür
    var originalText = normalText || $button.data('original-text') || 'Kaydet';
    $button.html(originalText);
    $button.prop("disabled", false);
  }
}