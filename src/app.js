let table;
let row;
let preloader;
$(document).ready(function () {
  //const tahsilatTable = $("#tahsilatTable");
  const $gg = $("#gelirGiderTable");
  if ($gg.length) {
    table = $gg.DataTable({
      //stateSave: true,
      responsive: true,
      searching: true,
      stateSave: true,
      info: true,
      paging: true,
      autoWidth: false,
      dom: 't<"row g-0 mt-2"<"col-md-4"i><"col-md-4 text-center"l><"col-md-4 float-end"p>>',
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
        language: {
        decimal: "",
        emptyTable: `
        <div class="dt-empty-modern">
           <svg data-id="3" xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-10 h-10 text-gray-500 dark:text-gray-400"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path></svg>

            <h4 class="mt-2">Herhangi bir kayıt yok!</h4>
            <p>Yeni bir kayıt oluşturabilirsiniz.</p>
        </div>
    `,
        info: "_TOTAL_ kayıttan _START_ - _END_ gösteriliyor",
        infoEmpty: "Kayıt bulunamadı",
        infoFiltered: "(toplam _MAX_ kayıttan filtrelendi)",
        infoPostFix: "",
        thousands: ",",
        lengthMenu: "_MENU_ kayıt göster",
        loadingRecords: "Yükleniyor...",
        processing: " İşleniyor...",
        search: "Arama:",
        zeroRecords: "Eşleşen kayıt bulunamadı",
        paginate: {
          first: "İlk",
          last: "Son",
          next: "Sonraki",
          previous: "Önceki"
        },
        aria: {
          sortAscending: ": artan sütuna sırala",
          sortDescending: ": azalan sütuna sırala"
        }
      },
      ajax: {
        url: "/pages/finans-yonetimi/gelir-gider/server_side_api.php",
        type: "POST",
        data: function (d) {
          d.action = "datatable-list";
        },
        dataSrc: "data"
      },
      ordering: false,

      initComplete: function (settings, json) {
        var api = this.api();
        var tableId = settings.sTableId;
        attachDtColumnSearch(api, tableId);
        api.columns.adjust().responsive.recalc();
        api.draw();
      }
    });

    $(window).on("resize.gg", function () {
      try {
        table.columns.adjust().responsive.recalc();
      } catch (e) {}
    });
  }

  // bazı tabloları kendi sayfasında başlatmak istediğim için burada başlatma
  const exitstsTables = [
    "mizanTable",
    "gelirGiderTable",
    "tahsilatTable",
    "notificationsList"
  ];
  const $others = $(".datatables, .datatable").not(
    "#" + exitstsTables.join(", #")
  );

  if ($others.length > 0) {
    // console.log($others);
    table = $others.DataTable({
      drawCallback: function (settings) {},
      ...getDtDefaults(),

      initComplete: function (settings, json) {
        var api = this.api();
        var tableId = settings.sTableId;
        attachDtColumnSearch(api, tableId);
        api.columns.adjust().responsive.recalc();
      }
    });

    $(window).on("resize.dt", function () {
      try {
        table.columns.adjust().responsive.recalc();
      } catch (e) {}
    });
  }
});

$("#exportExcel").on("click", function () {
  table.button(".buttons-excel").trigger();
});

/** Datatable sütun arama özelliği */
function attachDtColumnSearch(api, tableId) {
  $("#" + tableId + " thead").append('<tr class="search-input-row"></tr>');
  api.columns().every(function () {
    let column = this;
    let $header = $(column.header());
    let title = $header.text().trim() || $header.attr("data-title") || $header.attr("aria-label") || "";
    if (
      title != "İşlem" &&
      title != "Detay" &&
      title != "Seç" &&
      title != "#" &&
      title != "Sıra" &&
      $header.find('input[type="checkbox"]').length === 0
    ) {
      let input = document.createElement("input");
      input.placeholder = title || "Ara...";
      input.classList.add("form-control", "form-control-sm");
      input.setAttribute("autocomplete", "off");
      const th = $('<th class="search text-center align-middle">').append(
        input
      );
      $("#" + tableId + " thead .search-input-row").append(th);
      $(input).on("keyup change", function () {
        if (column.search() !== this.value) {
          column.search(this.value).draw();
        }
      });
      const isColumnVisible =
        column.visible() && !$header.hasClass("dtr-hidden");
      if (!isColumnVisible) {
        th.hide();
      }
    } else {
      $("#" + tableId + " thead .search-input-row").append("<th></th>");
    }
  });
  api.on("responsive-resize", function (e, datatable, columns) {
    $("#" + tableId + " thead .search-input-row th").each(function (index) {
      if (columns[index]) {
        $(this).show();
      } else {
        $(this).hide();
      }
    });
  });
  var state = api.state.loaded();
  if (state && state.sTableId === tableId) {
    var inputs = $("#" + tableId + " thead input");
    inputs.each(function () {
      var columnIndex = $(this).closest("th").index();
      var searchValue = state.columns[columnIndex]?.search?.search || "";
      if (searchValue) {
        $(this).val(searchValue);
        api.column(columnIndex).search(searchValue);
      }
    });
    api.draw();
  } else {
    api.state.clear();
  }
}

/** Datatable varsayılan ayarları */
function getDtDefaults() {
  return {
    responsive: true,
    info: true,
    paging: true,
    autoWidth: true,
    language: {
      decimal: "",
      emptyTable:
        `
        <div class="dt-empty-modern">
           <svg data-id="3" xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-10 h-10 text-gray-500 dark:text-gray-400"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path></svg>

            <h4 class="mt-2">Herhangi bir kayıt yok!</h4>
            <p>Yeni bir kayıt oluşturabilirsiniz.</p>
        </div>
    `,
      info: "_TOTAL_ kayıttan _START_ - _END_ gösteriliyor",
      infoEmpty: "Kayıt bulunamadı",
      infoFiltered: "(toplam _MAX_ kayıttan filtrelendi)",
      infoPostFix: "",
      thousands: ",",
      lengthMenu: "_MENU_ kayıt göster",
      loadingRecords: "Yükleniyor...",
      processing: '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Yükleniyor...',
      search: "Arama:",
      zeroRecords: "Eşleşen kayıt bulunamadı",
      paginate: {
        first: "İlk",
        last: "Son",
        next: "Sonraki",
        previous: "Önceki"
      },
      aria: {
        sortAscending: ": artan sütuna sırala",
        sortDescending: ": azalan sütuna sırala"
      }
    },
    dom:
      't<"row m-2"<"col-md-4"i><"col-md-4 text-center"l><"col-md-4 float-end"p>>',
    initComplete: function (settings, json) {
      var api = this.api();
      var tableId = settings.sTableId;
      if (typeof attachDtColumnSearch === "function") {
        attachDtColumnSearch(api, tableId);
      }
      api.columns.adjust().responsive.recalc();
    }
  };
}

/** Datatable başlatma ayarları */
function initDataTable(selector, overrides) {
  var base = getDtDefaults();
  var userInit = overrides && overrides.initComplete;
  if (overrides && overrides.initComplete) delete overrides.initComplete;
  var merged = $.extend(true, {}, base, overrides || {});
  var baseInit = base.initComplete;
  merged.initComplete = function (settings, json) {
    if (typeof baseInit === "function") baseInit.call(this, settings, json);
    if (typeof userInit === "function") userInit.call(this, settings, json);
  };
  return $(selector).DataTable(merged);
}

if (typeof window !== "undefined") {
  if (typeof window.attachDtColumnSearch !== "function") {
    window.attachDtColumnSearch = attachDtColumnSearch;
  }
  if (typeof window.initDataTable !== "function") {
    window.initDataTable = initDataTable;
  }
}

if ($(".select2").length > 0) {
  $(".select2").select2();

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
        "Courier New"
      ],
      addDefaultFonts: "inter",
      callbacks: {
        onInit: function () {
          $(".summernote").summernote("height", summernoteHeight);
          $(".summernote").summernote("fontName", "inter");
        }
      }
    });
  }
});

if ($(".flatpickr:not(.time-input)").length > 0) {
  $(".flatpickr:not(.time-input)").flatpickr({
    dateFormat: "d.m.Y",
    locale: "tr" // locale for this instance only
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
    tags: true
  });
}

function dtSearchInput(tableId, column, value) {}

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
      body: formData
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
          icon: icon
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
        body: formData
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
        icon: icon
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
      confirmButtonText: "Evet, Sil!"
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
  return slug ? slug[1] : "";
}

$(document).on("change", "#mySite", function () {
  var page = getCurrentPageSlug() || "ana-sayfa";
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
    body: formData
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
      text: "Öncelikle personeli kaydetmeniz gerekir!"
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
      text: "Öncelikle " + item + " kaydetmeniz gerekir!"
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
      removeMaskOnSubmit: true
    });
  });

  //Tarih formatı için inputmask kullan
  $(document).on("focus", ".flatpickr:not(.time-input)", function () {
    $(this).inputmask("99.99.9999", {
      placeholder: "gg.aa.yyyy",
      clearIncomplete: true
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
  }
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
  $(document).on("keydown", function (event) {
    // Ctrl + <shortcutKey> kombinasyonunu kontrol et
    if (
      event.ctrlKey &&
      event.key.toLowerCase() === shortcutKey.toLowerCase()
    ) {
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
function setButtonLoading(
  buttonSelector,
  isLoading = false,
  loadingText = "Yükleniyor...",
  normalText = null
) {
  var $button = $(buttonSelector);

  if (isLoading) {
    // Orijinal metni sakla (eğer normalText verilmemişse)
    if (!normalText) {
      $button.data("original-text", $button.html());
    }

    // Yükleme durumunu ayarla
    $button.html(
      `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${loadingText}`
    );
    $button.prop("disabled", true);
  } else {
    // Normal duruma döndür
    var originalText = normalText || $button.data("original-text") || "Kaydet";
    $button.html(originalText);
    $button.prop("disabled", false);
  }
}
