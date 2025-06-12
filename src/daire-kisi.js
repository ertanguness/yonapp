

  $(document).on("change", ".daireNo", function () {
    const $daireSelect = $(this);
    const daireID = $daireSelect.val();


    const $container = $daireSelect.closest(
      "form, .modal, .card, .content-wrapper"
    );
    const $kisiSec = $container.find(".kisiSec");

    $kisiSec.html('<option value="">Yükleniyor...</option>');

    if (!daireID) {
      $kisiSec.html('<option value="">Kişi Seçiniz</option>');
      return;
    }

    $.ajax({
      url: "api/DaireKisiCek.php",
      type: "POST",
      data: {
        action: "daireKisileri",
        daire_id: daireID,
      },
      dataType: "json",
      success: function (response) {

        let options = '<option value="">Kişi Seçiniz</option>';

        if (Array.isArray(response) && response.length > 0) {
          response.forEach(function (kisi) {
            options += `<option value="${kisi.id}">${kisi.adi_soyadi}</option>`;
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
