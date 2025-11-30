let periyodikBakimurl = "/pages/repair/care/periyodikBakimApi.php";

$(document).on("click", "#periyodikBakim_kaydet", function () {
  var form = $("#periyodikBakimForm");
  var formData = new FormData(form[0]);

  formData.append("action", "periyodikBakim_kaydetme");
  formData.append("id", $("#periyodikBakim_id").val());


  var validator = $("#periyodikBakimForm").validate({
    rules: {
      bakimAdi: { required: true },
      bakimPeriyot: { required: true },
      bakimYeri: { required: true },
      blokSecimi: {
        required: function () {
          return $("#bakimYeri").val() === "Blok";
        }
      },
      sonBakimTarihi: { required: true },
      sorumluFirma: { required: true }
    },
    messages: {
      bakimAdi: { required: "Lütfen bakım adını giriniz" },
      bakimPeriyot: { required: "Lütfen bakım periyodu seçiniz" },
      bakimYeri: { required: "Lütfen bakım yapılacak yeri seçiniz" },
      blokSecimi: { required: "Lütfen blok seçiniz" },
      sonBakimTarihi: { required: "Lütfen son bakım tarihini seçiniz" },
      sorumluFirma: { required: "Lütfen sorumlu firma veya kişi adını giriniz" }
    }
  });
  if (!validator.form()) {
    return;
  }

  fetch(periyodikBakimurl, {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      return response.json();
    })
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



$(document).on("click", ".sil-periyodikBakim", function () {
  let id = $(this).data("id");
  let bakimNo = $(this).data("name");
  let buttonElement = $(this); // Store reference to the clicked button
  swal
    .fire({
      title: "Emin misiniz?",
      html: `Bakım No :${bakimNo} <br>  kaydını silmek istediğinize emin misiniz?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet",
      cancelButtonText: "Hayır",
    })
    .then((result) => {
      if (result.isConfirmed) {
        var formData = new FormData();
        formData.append("action", "sil-periyodikBakim");
        formData.append("id", id);

        fetch(periyodikBakimurl, {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.status == "success") {
           // console.log("Çözümlenmiş ID:", data.decrypted_id);
           //window.location.reload(); // Sayfayı yeniden yükle

              let table = $("#periyodikBakimList").DataTable();
              table.row(buttonElement.closest("tr")).remove().draw(false);
              swal.fire(
                "Silindi",
                `${bakimNo} numaralı kayıt başarıyla silindi.`,
                "success"
              );
             }
          });
      }
  });
});

$(document).on("click", "#bakimDurumuTamamlaBtn", function () {
  const btn = $(this);
  const id = btn.data("id");
  const current = parseInt(btn.data("current") || 0);
  const nextDurum = current === 1 ? 0 : 1;
  const formData = new FormData();
  formData.append("action", "bakim_durumu_guncelle");
  formData.append("id", id);
  formData.append("durum", nextDurum);
  btn.prop("disabled", true);
  fetch(periyodikBakimurl, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        const t = $("#bakimDurumuText");
        if (nextDurum === 1) {
          t.removeClass("text-danger").addClass("text-success").text("Tamamlandı");
        } else {
          t.removeClass("text-success").addClass("text-danger").text("Tamamlanmadı");
        }
        btn.text("Bakım Durumu Değiştir");
        btn.data("current", nextDurum);
        btn.prop("disabled", false);
        swal.fire({ title: "Başarılı", text: data.message, icon: "success", confirmButtonText: "Tamam" });
      } else {
        btn.prop("disabled", false);
        swal.fire({ title: "Hata", text: data.message || "İşlem başarısız", icon: "error", confirmButtonText: "Tamam" });
      }
    })
    .catch(() => {
      btn.prop("disabled", false);
      swal.fire({ title: "Hata", text: "Sunucuya ulaşılamadı", icon: "error", confirmButtonText: "Tamam" });
    });
});
