$(document).ready(function () {
  // Sayfadaki select2'leri başlat
  $(".blokAdi, .daireNo").select2();

  // Modal açıldığında select2'yi modal içinde başlat (çakışmayı önler)
  $("#modalDaireEkle, #modalDaireDuzenle").on("shown.bs.modal", function () {
    $(this)
      .find(".blokAdi, .daireNo")
      .select2({
        dropdownParent: $(this),
      });
  });

  // Hem sayfa hem modal içindeki blok seçimini dinle
  $(document).on("change", ".blokAdi", function () {
    const $blokSelect = $(this);
    const blokID = $blokSelect.val();

    // Form ya da modal içindeki daireNo select'ini bul
    const $container = $blokSelect.closest(
      "form, .modal, .card, .content-wrapper"
    );
    const $daireNo = $container.find(".daireNo");

    $daireNo.html('<option value="">Yükleniyor...</option>');

    if (!blokID) {
      $daireNo.html('<option value="">Daire Seçiniz</option>');
      return;
    }

    $.ajax({
      url: "api/BlokDaireCek.php",
      type: "POST",
      data: {
        action: "blokDaireleri",
        blok_id: blokID,
      },
      dataType: "json",
      success: function (response) {
        let options = '<option value="">Daire Seçiniz</option>';

        if (Array.isArray(response) && response.length > 0) {
          response.forEach(function (daire) {
            options += `<option value="${daire.id}">${daire.daire_no}</option>`;
          });
        } else {
          options = '<option value="">Bu blokta daire bulunamadı</option>';
        }

        $daireNo.html(options)
                .trigger('change.select2');
      },
      error: function () {
        $daireNo.html('<option value="">Hata oluştu</option>');
      },
    });
  });
});
