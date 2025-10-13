// DOMContentLoaded'ı kaldır ve fonksiyonları global yap
function initSmsModal() {
  // --- ELEMENTLERİ SEÇME ---
  const senderIdSelect = document.getElementById("senderId");
  const recipientsInput = document.getElementById("recipients-input"); // ID düzeltildi
  const recipientsContainer = document.getElementById("recipients-container");
  const recipientsList = document.getElementById("recipients-list"); // Tag'lar buraya
  const messageTextarea = document.getElementById("message");
  const charCounter = document.getElementById("char-counter");
  const smsForm = document.getElementById("smsForm");
  const smsGonderBtn = document.getElementById("smsGonderBtn");

  // Önizleme elementleri
  const senderIdPreview = document.getElementById("sender-preview"); // ID düzeltildi
  const messagePreview = document.getElementById("message-preview");
  const phoneScreen = document.querySelector(".phone-screen");

  // Elementler yoksa fonksiyonu çalıştırma
  if (!senderIdSelect || !recipientsInput || !messageTextarea) {
    //console.warn('SMS modal elementleri bulunamadı');
    return;
  }

  // --- OLAY DİNLEYİCİLERİNİ AYARLAMA ---
  
  // Önceki event listener'ları temizle (duplikasyon önlemi)
  if (senderIdSelect.smsInitialized) return;
  senderIdSelect.smsInitialized = true;

  // Gönderen Adı değiştiğinde önizlemeyi güncelle
  senderIdSelect.addEventListener("change", updatePreview);

  // Mesaj yazıldığında önizlemeyi ve sayacı güncelle
  messageTextarea.addEventListener("input", () => {
    updatePreview();
    updateCharCounter();
  });

 const originalDescriptor = Object.getOwnPropertyDescriptor(HTMLTextAreaElement.prototype, 'value');
Object.defineProperty(messageTextarea, 'value', {
  get() {
    return originalDescriptor.get.call(this);
  },
  set(val) {
    originalDescriptor.set.call(this, val);
    updatePreview();
    updateCharCounter();
  }
});


  // Alıcı container'ına tıklandığında input'u odakla
  recipientsContainer.addEventListener("click", () => {
    recipientsInput.focus();
  });

  // Alıcı input'unda tuşa basıldığında etiket oluştur
  recipientsInput.addEventListener("keydown", handleRecipientInput);

  // Form gönderildiğinde verileri topla
  smsForm.addEventListener("submit", handleFormSubmit);

  // Gönder butonuna tıklandığında formu gönder
  smsGonderBtn.addEventListener("click", () => {
    smsForm.requestSubmit();
  });

  // --- FONKSİYONLAR ---
  
  function updatePreview() {
    if (senderIdPreview) {
      senderIdPreview.textContent = senderIdSelect.options[senderIdSelect.selectedIndex].text;
    }

    if (messagePreview) {
      const messageText = messageTextarea.value;
      messagePreview.textContent = messageText.trim() === "" ? "Mesajınız burada görünecek..." : messageText;
    }

    if (phoneScreen) {
      phoneScreen.scrollTop = phoneScreen.scrollHeight;
    }
  }

  function updateCharCounter() {
    if (!charCounter) return;
    
    const message = messageTextarea.value;
    const length = message.length;
    const hasUnicode = /[ğüşıöçĞÜŞİÖÇ]/.test(message);

    let smsCount = 0;
    let charLimit = 0;

    if (hasUnicode) {
      if (length === 0) {
        smsCount = 0;
        charLimit = 70;
      } else if (length <= 70) {
        smsCount = 1;
        charLimit = 70;
      } else if (length <= 134) {
        smsCount = 2;
        charLimit = 134;
      } else if (length <= 201) {
        smsCount = 3;
        charLimit = 201;
      } else {
        smsCount = Math.ceil(length / 67);
        charLimit = smsCount * 67;
      }
    } else {
      if (length === 0) {
        smsCount = 0;
        charLimit = 160;
      } else if (length <= 160) {
        smsCount = 1;
        charLimit = 160;
      } else if (length <= 306) {
        smsCount = 2;
        charLimit = 306;
      } else if (length <= 459) {
        smsCount = 3;
        charLimit = 459;
      } else {
        smsCount = Math.ceil(length / 153);
        charLimit = smsCount * 153;
      }
    }

    charCounter.textContent = `${length} / ${charLimit} (${smsCount} SMS)`;
  }

  function handleRecipientInput(e) {
    if (e.key === "Enter" || e.key === ",") {
      e.preventDefault();

      const number = recipientsInput.value.trim();
      if (isValidPhoneNumber(number)) {
        // Aynı numara var mı kontrol et
        const existingTags = recipientsList.querySelectorAll('.tag');
        const existingNumbers = Array.from(existingTags).map(tag => tag.textContent.slice(0, -1));
        
        if (!existingNumbers.includes(number)) {
          createTag(number);
          recipientsInput.value = "";
        } else {
          if (typeof Toastify !== 'undefined') {
            Toastify({ text: "Bu numara zaten eklenmiş." }).showToast();
          } else {
            alert("Bu numara zaten eklenmiş.");
          }
        }
      } else {
        if (typeof Toastify !== 'undefined') {
          Toastify({ text: "Geçersiz bir numara girdiniz." }).showToast();
        } else {
          alert("Geçersiz bir numara girdiniz.");
        }
      }
    }
  }

  function createTag(text) {
    const tag = document.createElement("span");
    tag.className = "tag";
    tag.textContent = text;

    const closeBtn = document.createElement("span");
    closeBtn.className = "close-tag";
    closeBtn.innerHTML = "×";
    closeBtn.onclick = function () {
      recipientsList.removeChild(tag);
    };

    tag.appendChild(closeBtn);
    recipientsList.appendChild(tag); // recipients-list içine ekle
  }

  function isValidPhoneNumber(number) {
    const phoneRegex = /^\d{10,15}$/;
    return phoneRegex.test(number);
  }

  function handleFormSubmit(e) {
    e.preventDefault();

    const tags = recipientsList.querySelectorAll(".tag");
    const recipients = Array.from(tags).map((tag) => tag.textContent.slice(0, -1));

    const formData = {
      senderId: senderIdSelect.value,
      message: messageTextarea.value,
      recipients: recipients
    };

    if (recipients.length === 0) {
      if (typeof Toastify !== 'undefined') {
        Toastify({ text: "Lütfen en az bir alıcı numarası girin." }).showToast();
      } else {
        alert("Lütfen en az bir alıcı numarası girin.");
      }
      return;
    }


    //formdata verilerini consola yazdır (test amaçlı)
    //console.log("Form Data:", formData);
    
    if (formData.message.trim() === "") {
      if (typeof Toastify !== 'undefined') {
        Toastify({ text: "Lütfen bir mesaj yazın." }).showToast();
      } else {
        alert("Lütfen bir mesaj yazın.");
      }
      return;
    }

    // AJAX isteği
    fetch("/pages/email-sms/api/APIsms.php", { // Path düzeltildi
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify(formData)
    })
    .then((response) => response.json())
    .then((data) => {
      if (data.status) {
        console.log("API Yanıtı:", data);
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            title: "Başarılı!",
            text: "SMS başarıyla gönderildi.",
            icon: "success",
            confirmButtonText: "Tamam"
          }).then(() => {
            // Formu temizle
            const tags = recipientsList.querySelectorAll(".tag");
            tags.forEach((tag) => tag.remove());
            messageTextarea.value = "";
            updatePreview();
            updateCharCounter();
          });
        } else {
          alert("SMS başarıyla gönderildi!");
        }
      }
    })
    .catch((error) => {
      console.error('SMS gönderme hatası:', error);
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          title: "Hata!",
          text: "SMS gönderilirken bir sorun oluştu.",
          icon: "error",
          confirmButtonText: "Tamam"
        });
      } else {
        alert("SMS gönderilirken bir sorun oluştu.");
      }
    });
  }

  // İlk durumu başlat
  updatePreview();
  updateCharCounter();


  // Kişi telefon numarasını data attribute'den al
  const smsCard = document.querySelector('.sms-sender-card');
  const kisiTelefon = $(smsCard).attr('data-kisi-telefon');

  console.log('Kişi telefon numarası (data-attribute):', kisiTelefon);
  
  if (kisiTelefon && kisiTelefon.trim() !== '') {
    const telefonNo = kisiTelefon.trim();
    if (isValidPhoneNumber(telefonNo)) {
      const existingTags = recipientsList.querySelectorAll('.tag');
      const existingNumbers = Array.from(existingTags).map(tag => tag.textContent.slice(0, -1));
      
      if (!existingNumbers.includes(telefonNo)) {
        createTag(telefonNo);
      }
    }
  }


  
}

// Global fonksiyonu window'a bağla
window.initSmsModal = initSmsModal;

// Sayfa yüklendiğinde çalıştır (normal sayfa yükleme için)
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initSmsModal);
} else {
  initSmsModal();
}