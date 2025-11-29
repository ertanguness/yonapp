// DOMContentLoaded'ı kaldır ve fonksiyonları global yap
function initSmsModal() {
  if (!document.getElementById('toastify-custom-style')) {
    const st = document.createElement('style');
    st.id = 'toastify-custom-style';
    st.textContent = 
      '.toastify{border-radius:6px!important;background:#000!important;color:#fff!important;text-align:center!important}' +
      '.toastify{left:50%!important;right:auto!important;transform:translateX(-50%)!important}';
    document.head.appendChild(st);
  }
  // --- ELEMENTLERİ SEÇME ---
  const senderIdSelect = document.getElementById("sms_baslik");
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
        const normalizedNumber = normalizePhoneNumber(number);
        const existingNumbers = getExistingNormalizedNumbers();

        if (!normalizedNumber) {
          recipientsInput.value = "";
          if (typeof Toastify !== 'undefined') {
            Toastify({ text: "Geçersiz bir numara girdiniz." }).showToast();
          } else {
            alert("Geçersiz bir numara girdiniz.");
          }
          return;
        }

        if (!existingNumbers.includes(normalizedNumber)) {
          createTag(number);
        } else {
          if (typeof Toastify !== 'undefined') {
            Toastify({ text: "Bu numara zaten eklenmiş." }).showToast();
          } else {
            alert("Bu numara zaten eklenmiş.");
          }
        }
        recipientsInput.value = "";
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
    const displayValue = text.trim();
    const normalizedValue = normalizePhoneNumber(displayValue);
    const digitsOnly = displayValue.replace(/\D/g, '');

    if (!normalizedValue) {
      return;
    }

    const tag = document.createElement("span");
    tag.className = "tag";
    tag.textContent = displayValue;
    tag.dataset.phoneDisplay = displayValue;
    tag.dataset.phoneNormalized = normalizedValue;
    tag.dataset.phoneDigits = digitsOnly;

    const closeBtn = document.createElement("span");
    closeBtn.className = "close-tag";
    closeBtn.innerHTML = "×";
    closeBtn.onclick = function () {
      recipientsList.removeChild(tag);
    };

    tag.appendChild(closeBtn);
    recipientsList.appendChild(tag); // recipients-list içine ekle
  }

  // Global scope'da createTag'i erişilebilir yap
  window.createTag = createTag;

  function isValidPhoneNumber(number) {
    const phoneRegex = /^\d{10,15}$/;
    return phoneRegex.test(number);
  }

  function normalizePhoneNumber(number) {
    if (!number && number !== 0) return '';
    const digits = number.toString().replace(/\D/g, '');
    if (!digits) return '';

    if (digits.length === 11 && digits.startsWith('0')) {
      return digits.slice(1);
    }

    if (digits.length === 12 && digits.startsWith('90')) {
      return digits.slice(2);
    }

    if (digits.length > 12) {
      return digits.slice(-10);
    }

    if (digits.length > 10) {
      return digits.slice(-10);
    }

    return digits;
  }

  function getExistingNormalizedNumbers() {
    return Array.from(recipientsList.querySelectorAll('.tag'))
      .map(tag => tag.dataset.phoneNormalized)
      .filter(Boolean);
  }

  function handleFormSubmit(e) {
    e.preventDefault();

    const tags = recipientsList.querySelectorAll(".tag");
    const recipients = Array.from(tags)
      .map((tag) => tag.dataset.phoneDigits || tag.dataset.phoneDisplay || '')
      .filter(Boolean);

    const formData = {
      senderId: senderIdSelect.value,
      message: messageTextarea.value,
      recipients: recipients,
      csrf_token: (window.csrfToken || ''),
      recipient_ids: (window.selectedRecipientIds || [])
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
  
      if (data.status == 'success') {
      
          Swal.fire({
            title: "Başarılı!",
            text: data.message,
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
       
      }else{
        swal.fire({
          title: "Hata!",
          text: data.message,
          icon: "error",
          confirmButtonText: "Tamam"
        })
    
      }
    })
    .catch((error) => {
      console.error('SMS gönderme hatası:', error);

        Swal.fire({
          title: "Hata!",
          text: "SMS gönderilirken bir sorun oluştu.",
          icon: "error",
          confirmButtonText: "Tamam"
        });
    });
  }

  // İlk durumu başlat
  updatePreview();
  updateCharCounter();

  function setupModalSearch() {
    const offcanvas = document.getElementById('kisilerdenSecOffcanvas');
    const offcanvasBody = offcanvas?.querySelector('.kisilerden-sec-offcanvas');

    if (!offcanvas || !offcanvasBody) {
      console.warn('Kişilerden Seç offcanvas bulunamadı');
      return false;
    }

    if (offcanvas.dataset.modalSearchInitialized === 'true') {
      return true;
    }

    const searchBox = offcanvasBody.querySelector('#searchBox');
    const selectAll = offcanvasBody.querySelector('#selectAll');
    const table = offcanvasBody.querySelector('#kisilerTable');
    const selectedCount = offcanvasBody.querySelector('#selectedCount');
    const addButton = offcanvasBody.querySelector('#seciliEkleBtn');
    const filterCheckboxes = offcanvasBody.querySelectorAll('.filter-checkbox');

    if (!searchBox || !table || !addButton) {
      console.error('Kişilerden Seç modali için gerekli elemanlar bulunamadı');
      return false;
    }

    const getVisibleRows = () => table.querySelectorAll('tbody tr.kisi-row:not([style*="display: none"])');
    const getAllCheckboxes = () => table.querySelectorAll('.kisi-checkbox');
    const getVisibleCheckboxes = () => table.querySelectorAll('tbody tr.kisi-row:not([style*="display: none"]) .kisi-checkbox');

    const getCurrentRecipientNumbers = () => {
      if (!recipientsList) return [];
      return Array.from(recipientsList.querySelectorAll('.tag'))
        .map(tag => tag.dataset.phoneNormalized)
        .filter(Boolean);
    };

    const prefillSelections = () => {
      const existingNumbers = getCurrentRecipientNumbers();
      if (!existingNumbers.length) {
        updateCount();
        updateSelectAllState();
        return;
      }

      getAllCheckboxes().forEach(cb => {
        const phone = cb.getAttribute('data-phone');
        const normalizedPhone = normalizePhoneNumber(phone);
        cb.checked = normalizedPhone && existingNumbers.includes(normalizedPhone);
      });

      updateCount();
      updateSelectAllState();
    };

    const updateCount = () => {
      if (!selectedCount) return;
      const count = table.querySelectorAll('.kisi-checkbox:checked').length;
      selectedCount.textContent = String(count);
    };

    const updateSelectAllState = () => {
      if (!selectAll) return;
      const visibleCheckboxes = getVisibleCheckboxes();
      if (visibleCheckboxes.length === 0) {
        selectAll.checked = false;
        selectAll.indeterminate = false;
        return;
      }

      const totalVisible = visibleCheckboxes.length;
      const checkedVisible = Array.from(visibleCheckboxes).filter(cb => cb.checked).length;

      if (checkedVisible === 0) {
        selectAll.checked = false;
        selectAll.indeterminate = false;
      } else if (checkedVisible === totalVisible) {
        selectAll.checked = true;
        selectAll.indeterminate = false;
      } else {
        selectAll.checked = false;
        selectAll.indeterminate = true;
      }
    };

    const applyFilters = () => {
      const searchTerm = searchBox.value.trim().toLowerCase();
      const filterAktif = offcanvasBody.querySelector('#filterAktif')?.checked ?? true;
      const filterPasif = offcanvasBody.querySelector('#filterPasif')?.checked ?? true;
      const filterEvSahibi = offcanvasBody.querySelector('#filterEvSahibi')?.checked ?? true;
      const filterKiraci = offcanvasBody.querySelector('#filterKiraci')?.checked ?? true;

      table.querySelectorAll('tbody tr.kisi-row').forEach(row => {
        const daire = row.querySelector('td:nth-child(2)')?.textContent?.toLowerCase() || '';
        const adSoyad = row.querySelector('td:nth-child(3)')?.textContent?.toLowerCase() || '';
        const telefon = row.querySelector('td:nth-child(4)')?.textContent?.toLowerCase() || '';
        const durum = row.getAttribute('data-aktif') === '1' ? 'aktif' : 'pasif';
        const uyelik = (row.getAttribute('data-uyelik') || '').toLowerCase();

        const statusOk = (durum === 'aktif' && filterAktif) || (durum === 'pasif' && filterPasif);
        const typeOk = (uyelik === 'kat maliki' && filterEvSahibi) || (uyelik === 'kiracı' && filterKiraci);
        const searchOk = !searchTerm || daire.includes(searchTerm) || adSoyad.includes(searchTerm) || telefon.includes(searchTerm);

        row.style.display = statusOk && typeOk && searchOk ? '' : 'none';
      });

      if (selectAll) {
        selectAll.checked = false;
        selectAll.indeterminate = false;
      }

      updateCount();
      updateSelectAllState();
    };

    searchBox.addEventListener('input', applyFilters);
    filterCheckboxes.forEach(cb => cb.addEventListener('change', applyFilters));

    selectAll?.addEventListener('change', e => {
      const checked = e.target.checked;
      getVisibleRows().forEach(row => {
        const cb = row.querySelector('.kisi-checkbox');
        if (cb) {
          cb.checked = checked;
        }
      });
      updateCount();
      updateSelectAllState();
    });

    table.addEventListener('click', e => {
      const checkbox = e.target.closest('.kisi-checkbox');
      if (checkbox) {
        updateCount();
        updateSelectAllState();
        return;
      }

      const row = e.target.closest('tr.kisi-row');
      if (!row) return;

      const rowCheckbox = row.querySelector('.kisi-checkbox');
      if (!rowCheckbox) return;

      rowCheckbox.checked = !rowCheckbox.checked;
      updateCount();
      updateSelectAllState();
    });

    getAllCheckboxes().forEach(cb => {
      cb.addEventListener('change', () => {
        updateCount();
        updateSelectAllState();
      });
    });

    addButton.addEventListener('click', e => {
      e.preventDefault();
      const selectedCheckboxes = table.querySelectorAll('.kisi-checkbox:checked');

      if (!selectedCheckboxes.length) {
        if (typeof Toastify !== 'undefined') {
          Toastify({ text: 'Lütfen en az bir kişi seçin.' }).showToast();
        } else {
          alert('Lütfen en az bir kişi seçin.');
        }
        return;
      }

      const phones = Array.from(selectedCheckboxes)
        .map(cb => cb.getAttribute('data-phone'))
        .filter(Boolean);

      if (phones.length === 0) {
        if (typeof Toastify !== 'undefined') {
          Toastify({ text: 'Seçilen kişiler için telefon bulunamadı.' }).showToast();
        } else {
          alert('Seçilen kişiler için telefon bulunamadı.');
        }
        return;
      }

      const addPhone = window.parent?.addPhoneToSMS || window.addPhoneToSMS || window.opener?.addPhoneToSMS;

      if (typeof addPhone !== 'function') {
        console.error('addPhoneToSMS fonksiyonu bulunamadı');
        if (typeof Toastify !== 'undefined') {
          Toastify({ text: 'Sistem hatası: Telefon ekleme fonksiyonu yok.' }).showToast();
        } else {
          alert('Sistem hatası: Telefon ekleme fonksiyonu yok.');
        }
        return;
      }

      phones.forEach(addPhone);

      getAllCheckboxes().forEach(cb => (cb.checked = false));
      if (selectAll) {
        selectAll.checked = false;
        selectAll.indeterminate = false;
      }
      updateCount();

      const dismissButton = offcanvas.querySelector('[data-bs-dismiss="offcanvas"]');
      if (dismissButton) {
        dismissButton.click();
      }
    });

    applyFilters();
    prefillSelections();
    updateCount();
    updateSelectAllState();
    offcanvas.dataset.modalSearchInitialized = 'true';
    return true;
  }

  window.setupModalSearch = setupModalSearch;

  // Kişileri SMS listesine eklemek için global fonksiyon
  window.addPhoneToSMS = function(phoneNumber) {
    if (!isValidPhoneNumber(phoneNumber)) {
      console.error('Geçersiz telefon numarası:', phoneNumber);
      if (typeof Toastify !== 'undefined') {
        Toastify({ text: "Geçersiz numara: " + phoneNumber }).showToast();
      }
      return;
    }

    const normalizedPhone = normalizePhoneNumber(phoneNumber);
    if (!normalizedPhone) {
      return;
    }

    const existingNumbers = getExistingNormalizedNumbers();

    if (!existingNumbers.includes(normalizedPhone)) {
      createTag(phoneNumber);
    }
  };

  const kisiTelefon = (window.kisiTelefonNumarasi || '').trim();
  console.log('kisiTelefon:', kisiTelefon);
  if (kisiTelefon) {
    const normalized = normalizePhoneNumber(kisiTelefon);
    const existingNumbers = getExistingNormalizedNumbers();
    if (normalized && !existingNumbers.includes(normalized)) {
      createTag(kisiTelefon);
    }
  }

  // Offcanvas "Kişilerden Seç" açılırken içeriğini yükle
  const offcanvasElement = document.getElementById('kisilerdenSecOffcanvas');
  if (offcanvasElement) {
    let isLoading = false;
    let isLoaded = false;
    
    offcanvasElement.addEventListener('show.bs.offcanvas', function() {
      const offcanvasBody = this.querySelector('.kisilerden-sec-offcanvas');
      
      if (offcanvasBody && offcanvasBody.querySelector('.spinner-border') && !isLoading && !isLoaded) {
  isLoading = true;
        
        $.get("/pages/email-sms/modal/kisilerden_sec_modal.php", function(data) {
          offcanvasBody.innerHTML = data;
          isLoading = false;
          isLoaded = true;
          
          // Setup modal search
          if (typeof window.setupModalSearch === 'function') {
            window.setupModalSearch();
          }
        }).fail(function(error) {
          console.error('❌ Modal yükleme hatası:', error);
          offcanvasBody.innerHTML = '<div class="alert alert-danger m-3">Kişiler yüklenirken hata oluştu.</div>';
          isLoading = false;
        });
      }
    });
    
    offcanvasElement.addEventListener('hidden.bs.offcanvas', function() {
      isLoading = false;
      isLoaded = false;
      delete this.dataset.modalSearchInitialized;

      const offcanvasBody = this.querySelector('.kisilerden-sec-offcanvas');
      if (offcanvasBody) {
        offcanvasBody.innerHTML = '<div class="d-flex justify-content-center align-items-center" style="height: 200px; flex: 1;"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Yükleniyor...</span></div></div>';
      }
    });
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