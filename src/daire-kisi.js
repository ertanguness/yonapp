$(document).ready(function () {
  // Blok değiştiğinde daire ve kişi temizle
  $(document).on("change", ".blokAdi", function () {
    const $blokSelect = $(this);
    const $container = $blokSelect.closest("form, .modal, .card, .content-wrapper");
    const $daireSelect = $container.find(".daireNo");
    const $kisiSelect = $container.find(".kisiSec");
    const $telefonInput = $container.find("input#telefon");

    // Daireyi varsayılan yap
    $daireSelect.val("").trigger("change.select2");

    // Kişi selecti temizle
    $kisiSelect.html('<option value="">Kişi Seçiniz</option>').trigger("change.select2");

    // Telefon alanını temizle
    $telefonInput.val("");
  });

  // Daire değiştiğinde kişileri getir ve telefon temizle
  $(document).on("change", ".daireNo", function () {
    const $daireSelect = $(this);
    const daireID = $daireSelect.val();
    const $container = $daireSelect.closest("form, .modal, .card, .content-wrapper");
    const $kisiSec = $container.find(".kisiSec");
    const $telefonInput = $container.find("input#telefon");

    // Telefon alanını temizle
    $telefonInput.val("");

    $kisiSec.html('<option value="">Yükleniyor...</option>');

    if (!daireID) {
      $kisiSec.html('<option value="">Kişi Seçiniz</option>');
      return;
    }

    $.ajax({
      url: "api/DaireKisiCek.php",
      type: "POST",
      data: { action: "daireKisileri", daire_id: daireID },
      dataType: "json",
      success: function (response) {
        let options = '<option value="">Kişi Seçiniz</option>';
        if (Array.isArray(response) && response.length > 0) {
          response.forEach(function (kisi) {
            options += `<option value="${kisi.id}" data-phone="${kisi.telefon || ''}">${kisi.adi_soyadi}</option>`;
          });
        } else {
          options = '<option value="">Bu dairede kişi bulunamadı</option>';
        }
        $kisiSec.html(options).trigger("change.select2");
      },
      error: function () {
        $kisiSec.html('<option value="">Hata oluştu</option>');
      },
    });
  });

  // Kişi seçildiğinde telefon alanını doldur
  $(document).on("change", ".kisiSec", function () {
    const $kisiSelect = $(this);
    const $container = $kisiSelect.closest("form, .modal, .card, .content-wrapper");
    const $telefonInput = $container.find("input#telefon");

    const selectedOption = $kisiSelect.find("option:selected");
    const phone = selectedOption.data("phone") || "";

    $telefonInput.val(phone);
  });
});
