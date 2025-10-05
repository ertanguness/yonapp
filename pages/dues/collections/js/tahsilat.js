let url = "/pages/dues/collections/api.php";

$(document).on("click", ".tahsilat-detay-sil", function () {
  var detay_id = $(this).data("id");
  alert (detay_id);
  return false;

  handleDelete(url, "tahsilat_detay_sil", detay_id, this);
});

/**
 * Genel silme işlemleri için yeniden kullanılabilir bir fonksiyon.
 * SweetAlert2 ile onay alır, fetch ile sunucuya istek gönderir ve sonucu bildirir.
 *
 * @param {string} action Sunucuya gönderilecek eylem adı (örn: "borclandirma_sil").
 * @param {string|number} id Silinecek kaydın ID'si.
 * @param {HTMLElement} clickedElement Tıklanan buton elementi ('this'). Tablo satırını bulmak için kullanılır.
 * @param {object} [options={}] SweetAlert mesajlarını özelleştirmek için opsiyonel parametreler.
 * @param {string} options.title Onay penceresi başlığı.
 * @param {string} options.text Onay penceresi metni.
 * @param {string} options.confirmButtonText Onay butonu metni.
 */
function handleDelete(url, action, id, clickedElement, options = {}) {
  // Özelleştirme için varsayılan ayarları ve gelen seçenekleri birleştir
  const config = {
    title: options.title || "Emin misin?",
    text: options.text || "Bu işlem geri alınamaz!",
    confirmButtonText: options.confirmButtonText || "Evet, sil!",
    successTitle: "Başarılı!",
    errorTitle: "Hata!",
  };

  // Silme işlemi sonrası tablodan kaldırılacak satırı bul
  const row = $(clickedElement).closest("tr");

  // Onay penceresini göster
  Swal.fire({
    title: config.title,
    text: config.text,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: config.confirmButtonText,
    cancelButtonText: "İptal",
  }).then((result) => {
    // Kullanıcı silme işlemini onayladıysa devam et
    if (result.isConfirmed) {
      // FormData hazırla
      const formData = new FormData();
      formData.append("action", action);
      formData.append("id", id);

      // Pace.js ile yükleme animasyonunu başlat
      Pace.track(() => {
        fetch(url, {
          // 'url' değişkeninin global olarak tanımlandığını varsayıyoruz
          method: "POST",
          body: formData,
        })
          .then((response) => {
            if (!response.ok) {
              // Sunucudan 200 olmayan bir durum kodu gelirse hata fırlat
              throw new Error("Sunucu hatası: " + response.status);
            }
            return response.json();
          })
          .then((data) => {
            // İşlem başarılıysa tablo satırını kaldır
            if (data.status === "success" && row.length > 0) {
              row.fadeOut(400, function () {
                $(this).remove();
              });
            }

            // Sonuç bildirimini göster
            Swal.fire({
              title:
                data.status === "success"
                  ? config.successTitle
                  : config.errorTitle,
              text: data.message,
              icon: data.status,
              confirmButtonText: "Tamam",
            });
          })
          .catch((error) => {
            // Fetch veya JSON parse hatası olursa kullanıcıyı bilgilendir
            console.error("İstek sırasında bir hata oluştu:", error);
            Swal.fire({
              title: config.errorTitle,
              text: "İşlem sırasında bir ağ hatası oluştu. Lütfen tekrar deneyin.",
              icon: "error",
              confirmButtonText: "Tamam",
            });
          });
      });
    }
  });
}
