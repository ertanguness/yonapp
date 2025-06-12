let url = "pages/dues/payment/api.php";
let selectedRow;

$(document).on("click", ".detay-goruntule", function () {
  var id = $(this).data("id");
  selectedRow = $(this).closest("tr"); // Seçilen satırı sakla

  $.get(
    "pages/dues/payment/tahsilat_onay_detay.php",
    {
      id: id,
    },
    function (data) {
      $(".tahsilat-detay").html(data);
    }
);

  $("#tahsilatDetayModal").modal("show");
});

$(document).on("click", ".tahsilat-onayla", function (event) {
  event.preventDefault(); // Sayfanın yukarı kaymasını engelle

  var id = $(this).data("id");
  var islenecek_tutar_input = $(this).closest("tr").find(".islenecek-tutar");
  var islenecek_tutar = islenecek_tutar_input.val();
  //
  var tahsilat_turu = $(this)
    .closest("tr")
    .find("select[id*='borc_baslik'] option:selected")
    .text();
  //Eğer tutar 0 ise uyarı ver
  if (islenecek_tutar == 0) {
    // Swal.fire({
    // icon: "warning",
    // title: "Uyarı!",
    // text: "İşlenecek tutar 0 olamaz!",
    // });

    $(islenecek_tutar_input).addClass("is-invalid");
    return false;
  }

  var formData = new FormData();

  formData.append("id", id);
  formData.append("islenecek_tutar", islenecek_tutar);
  formData.append("tahsilat_turu", tahsilat_turu);
  formData.append("action", "tahsilat_onayla");

  fetch(url, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      //console.log(data);
      title = data.status ? "Başarılı!" : "Hata!";
      swal.fire({
        icon: data.status,
        title: title,
        text: data.message,
      });
      if (data.status == "success") {
        var table = $("#tahsilatOnayTable").DataTable();
        //6. ve 7. sütundaki verileri güncelle
        table.cell($(this).closest("tr"), 4).data(data.islenen_tahsilatlar);
        table.cell($(this).closest("tr"), 5).data(data.kalan_tutar);

        islenecek_tutar_input.val(data.kalan_tutar); // İşlenecek tutar inputunu temizle
        // Eğer kalan tutar 0 ise, butonu gizle
        if (data.kalan_tutar == "0,00 TRY") {
          $(this).closest("tr").find(".tahsilat-onayla").hide();
        }
      }
    });
});
$(document).on("input", ".islenecek-tutar", function () {
  // Eğer veri girildiyese, is-invalid sınıfını kaldır
  if ($(this).val() != "") {
    $(this).removeClass("is-invalid");
  } else {
    // Eğer veri girilmediyse, is-invalid sınıfını ekle
    $(this).addClass("is-invalid");
  }
});

$(document).on("click", ".onayli-tahsilat-sil", function () {
  event.preventDefault(); // Sayfanın yukarı kaymasını engelle

  var id = $(this).data("id");

  var formData = new FormData();
  formData.append("id", id);
  formData.append("action", "onayli_tahsilat_sil");

  fetch(url, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      //console.log(data);
      title = data.status ? "Başarılı!" : "Hata!";
      swal.fire({
        icon: data.status,
        title: title,
        text: data.message,
      });
      if (data.status == "success") {
        var table = $("#islenenTahsilatlarTable").DataTable();
        table.row($(this).closest("tr")).remove().draw();

        selectedRow.find("td:nth-child(5)").text(data.islenen_tahsilatlar);
        selectedRow.find("td:nth-child(6)").text(data.kalan_tutar);
        selectedRow.find(".islenecek-tutar").val(data.kalan_tutar);
        selectedRow.find(".tahsilat-onayla").show();


      }
    });
});
