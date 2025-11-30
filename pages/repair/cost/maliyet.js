let maliyeturl = "/pages/repair/cost/maliyetApi.php";

$(document).on("click", "#maliyet_kaydet", function () {
  var form = $("#maliyetForm")[0]; // raw DOM element lazım
  var formData = new FormData(form); // formdaki tüm input, dosya dahil alınır

  formData.append("action", "maliyet_kaydetme");
  formData.append("id", $("#maliyet_id").val());
  (function(){
    function sanitize(v){
      v = v == null ? "" : String(v).trim();
      if (v.indexOf(',') >= 0){ v = v.replace(/\./g,''); v = v.replace(',', '.'); }
      v = v.replace(/[^0-9.-]/g,'');
      return v === '' ? '0' : v;
    }
    var t = $("#toplamMaliyet").val();
    var o = $("#odenenTutar").val();
    var k = $("#kalanBorc").val();
    formData.set('toplamMaliyet', sanitize(t));
    formData.set('odenenTutar', sanitize(o));
    formData.set('kalanBorc', sanitize(k));
  })();
  

  var validator = $("#maliyetForm").validate({
    rules: {
      bakimTuru: { required: true },
      talepNo: { required: true },
      makbuzTuru: { required: true },
      toplamMaliyet: {  number: true, min: 0 },
      makbuzNo: { required: true },
      odenenTutar: { number: true, min: 0 },
      odemeDurumu: { required: true },
      odemeTarihi: { required: true }
    },
    messages: {
      bakimTuru: { required: "Lütfen bakım türünü seçiniz" },
      talepNo: { required: "Lütfen talep numarasını seçiniz" },
      makbuzTuru: { required: "Lütfen makbuz türünü seçiniz" },
      toplamMaliyet: { required: "Lütfen toplam maliyeti giriniz",  min: "Negatif olamaz" },
      makbuzNo: { required: "Lütfen makbuz numarasını giriniz" },
      odenenTutar: { number: "Geçerli bir sayı giriniz", min: "Negatif olamaz" },
      odemeDurumu: { required: "Lütfen ödeme durumunu seçiniz" },
      odemeTarihi: { required: "Lütfen ödeme tarihini giriniz" }
    }
  });
  if (!validator.form()) {
    return;
  }

  function toNum(v){
    v = String(v || '').trim();
    v = v.replace(/\s|₺/g,'');
    v = v.replace(/\./g,'');
    v = v.replace(',', '.');
    v = v.replace(/[^0-9.-]/g,'');
    return parseFloat(v || '0') || 0;
  }
  const tmNum = toNum($("#toplamMaliyet").val());
  const odNum = toNum($("#odenenTutar").val());
  if (odNum > tmNum) {
    swal.fire({
      title: "Uyarı",
      text: "Ödenen tutar toplam maliyetten fazla olamaz.",
      icon: "warning",
      confirmButtonText: "Tamam",
    });
    $("#odenenTutar").focus();
    return;
  }

  fetch(maliyeturl, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      var title = data.status == "success" ? "Başarılı" : "Hata";
      swal.fire({
        title: title,
        text: data.message,
        icon: data.status,
        confirmButtonText: "Tamam",
      });
    });
});

$(document).on("change", ".select2", function () {
  $(this).valid(); // Trigger validation for the changed select2 element
});



$(document).on("click", ".sil-maliyet", function () {
  let id = $(this).data("id");
  let makbuzNo = $(this).data("name");
  let buttonElement = $(this); // Store reference to the clicked button
  swal
    .fire({
      title: "Emin misiniz?",
      html: `Talep No :${makbuzNo} <br>  kaydını silmek istediğinize emin misiniz?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet",
      cancelButtonText: "Hayır",
    })
    .then((result) => {
      if (result.isConfirmed) {
        var formData = new FormData();
        formData.append("action", "sil-maliyet");
        formData.append("id", id);

        fetch(maliyeturl, {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.status == "success") {
           // console.log("Çözümlenmiş ID:", data.decrypted_id);
           //window.location.reload(); // Sayfayı yeniden yükle

              let table = $("#MaliyetList").DataTable();
              table.row(buttonElement.closest("tr")).remove().draw(false);
              swal.fire(
                "Silindi",
                `Talep No: ${makbuzNo} olan kayıt başarıyla silindi.`,
                "success"
              );
             }
          });
      }
    });
});
