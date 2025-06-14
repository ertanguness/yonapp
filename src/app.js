let table;
$(document).ready(function () {
  if ($(".datatables").length > 0) {
    table = $(".datatables").DataTable({
      stateSave: true,
      responsive: false,
      language: {
        url: "assets/js/tr.json",
      },

      ...getTableSpecificOptions(),

      initComplete: function (settings, json) {
        var api = this.api();
        var tableId = settings.sTableId;
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
            // Create input element
            let input = document.createElement("input");
            input.placeholder = title;
            input.classList.add("form-control");
            input.classList.add("form-control-sm");
            input.setAttribute("autocomplete", "off");

            // // Append input element to the new row
            // $("#" + tableId + " .search-input-row").append(
            //   $('<th class="search">').append(input)
            // );

            // Append input element to the new row
            const th = $('<th class="search">').append(input);
            $("#" + tableId + " .search-input-row").append(th);

            // Event listener for user input
            $(input).on("keyup change", function () {
              if (column.search() !== this.value) {
                column.search(this.value).draw();
              }
            });

            // Sütunun gerçekten görünür olup olmadığını kontrol et
            const isColumnVisible =
              column.visible() && !$(column.header()).hasClass("dtr-hidden");

            //  const isColumnVisible =
            //  column.visible() && $(column.header()).css("display") !== "none";

            if (!isColumnVisible) {
              th.hide(); // Sütun gerçekten görünmüyorsa input'u da gizle
            }
          } else {
            // Eğer "İşlem" sütunuysa, boş bir th ekleyin

            // Sütun görünürse <th> elemanını ekle
            $("#" + tableId + " .search-input-row").append("<th></th>");
          }
        });

        // Responsive olayını dinle
        table.on("responsive-resize", function (e, datatable, columns) {
          // Sütun görünürlüğünü kontrol et ve inputları gizle/göster
          $("#" + tableId + " .search-input-row th").each(function (index) {
            if (columns[index]) {
              $(this).show(); // Sütun görünüyorsa inputu göster
            } else {
              $(this).hide(); // Sütun gizliyse inputu gizle
            }
          });
        });

        // var state = table.state.loaded();
        // if (state) {
        //   $("input", table.table().header()).each(function (index) {
        //     var searchValue = state.columns[index]?.search?.search || ""; // Arama değerini al
        //     if (searchValue) {
        //       console.log(index, searchValue);
        //       $(this).val(searchValue);
        //     }
        //   });
        // }
        var state = table.state.loaded();
if (state) {
    // Tüm başlık inputlarını al
    var inputs = $("thead input");
    
    inputs.each(function(inputIndex) {
        // DataTable'daki gerçek sütun indeksini bul
        var columnIndex = $(this).closest('th').index();
        var searchValue = state.columns[columnIndex]?.search?.search || "";
        
        if (searchValue) {
            $(this).val(searchValue);
            // console.log("Input index:", inputIndex, 
            //            "Column index:", columnIndex, 
            //            "Value:", searchValue);
            
            
            // Arama filtrelerini uygula
            table.column(columnIndex).search(searchValue);
        }
    });
    
    // Değişiklikleri çiz
    table.draw();
}
        
      },
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

if ($(".flatpickr").length > 0) {
  $(".flatpickr").flatpickr({
    dateFormat: "d.m.Y",
    locale: "tr", // locale for this instance only
  });
}

function formatNumber(num) {
  return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
}

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

$(document).on("change", "#mySite", function () {
  var page = new URLSearchParams(window.location.search).get("p");
  window.location = "set-session.php?p=" + page + "&site_id=" + $(this).val();
});

//İl seçildiğinde ilçeleri getir
function getTowns(cityId, targetElement) {
  var formData = new FormData();
  formData.append("city_id", cityId);
  formData.append("action", "getTowns");

  fetch("/api/il-ilce.php", {
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
  //1.234,52 şeklinden regex yaz
  //$(".money").inputmask("9-a{1,3}9{1,3}"); //mask with dynamic syntax

  // $(".money").inputmask("decimal", {
  //   radixPoint: ",",
  //   groupSeparator: ".",
  //   digits: 2,
  //   autoGroup: true,
  //   rightAlign: false,
  //   removeMaskOnSubmit: true,
  // });

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
  $(document).on("focus", ".flatpickr", function () {
    $(this).inputmask("99.99.9999", {
      placeholder: "gg.aa.yyyy",
      clearIncomplete: true,
    });
  });

  //Para birimi olan alanlarda virgülü noktaya çevir
  // $('.money').on('keyup', function () {
  //   var value = $(this).val();
  //   var value = value.replace(/,/g, '.');
  //   $(this).val(value);
  // });
}

$.validator.setDefaults({
  highlight: function(element) {
    // input-group varsa, tüm input-group'u işaretle
    var $group = $(element).closest('.input-group');
    if ($group.length) {
      $group.addClass('is-invalid');
    } else {
      $(element).addClass('is-invalid');
    }
  },
  unhighlight: function(element) {
    var $group = $(element).closest('.input-group');
    if ($group.length) {
      $group.removeClass('is-invalid');
    } else {
      $(element).removeClass('is-invalid');
    }
    $(element).next('.error').remove();
  },
  errorPlacement: function(error, element) {
    var $group = $(element).closest('.input-group');
    if ($group.length) {
      error.insertAfter($group);
    } else {
      error.insertAfter(element);
    }
  }
});

//Jquery validate ile yapılan doğrulamalarda para birimi formatı için
function addCustomValidationMethods() {
  $.validator.addMethod(
    "validNumber",
    function (value, element) {
      return (
        this.optional(element) ||
        (/^[0-9.,]+$/.test(value) && parseFloat(value.replace(",", ".")) > 0)
      );
    },
    "Lütfen geçerli bir sayı girin ve 0'dan büyük bir değer girin"
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
