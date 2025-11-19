// Global Lock Screen Manager
class LockScreenManager {
  constructor() {
    this.lockScreen = document.getElementById('globalLockScreen');
    this.unlockBtn = document.getElementById('globalUnlockBtn');
    this.passwordInput = document.getElementById('globalLockPassword');
    this.lockAttempts = document.getElementById('globalLockAttempts');
    this.lockAttemptsContainer = document.getElementById('globalLockAttemptsContainer');
    this.lockInfoContainer = document.getElementById('globalLockInfoContainer');
    this.headerLockBtn = document.getElementById('headerLockScreenBtn');
    
    // Kilit durumunu localStorage'dan al (sayfa refresh'de korunması için)
    // localStorage boşsa, lock-screen HTML'nin style'ına bak
    const storedState = localStorage.getItem('lockScreenState');
    if (storedState) {
      this.isLocked = storedState === 'locked';
    } else {
      // İlk açılışta HTML'nin display durumundan oku
      this.isLocked = this.lockScreen && this.lockScreen.style.display === 'flex';
    }
    
    if (!this.lockScreen) {
      console.warn('Lock screen element not found');
      return;
    }
    
    this.alertTimeout = null;  // Alert rate limiting
    
    // İlk yükleme sırasında kilit durumunu HTML'ye yansıt
    if (this.isLocked) {
      this.lockScreen.style.display = 'flex';
      document.body.style.overflow = 'hidden';
      // Kilit açıldığında info mesajını göster
      if (this.lockInfoContainer) {
        this.lockInfoContainer.style.display = 'block';
      }
    }
    
    this.init();
  }
  
  init() {
    if (this.unlockBtn) {
      this.unlockBtn.addEventListener('click', () => this.handleUnlock());
    }
    
    if (this.passwordInput) {
      this.passwordInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
          this.unlockBtn.click();
        }
      });
    }
    
    document.addEventListener('click', (e) => this.handleClick(e), true);
    document.addEventListener('submit', (e) => this.handleSubmit(e), true);
    document.addEventListener('keydown', (e) => this.handleKeydown(e), true);
    window.addEventListener('popstate', (e) => this.handlePopstate(e));
    
    if (this.headerLockBtn) {
      this.headerLockBtn.addEventListener('click', (e) => {
        e.preventDefault();
        this.lock();
      });
    }
  }
  
  handleClick(e) {
    if (!this.isLocked) return;
    
    // SweetAlert modal açıksa, click'i engelle
    const swalContainer = document.querySelector('.swal2-container');
    if (swalContainer && (swalContainer.style.display !== 'none' && swalContainer.offsetParent !== null)) {
      e.preventDefault();
      e.stopPropagation();
      return false;
    }
    
    if (this.lockScreen.contains(e.target)) {
      return;
    }
    
    e.preventDefault();
    e.stopPropagation();
    this.showAlert();
    return false;
  }
  
  handleSubmit(e) {
    if (!this.isLocked) return;
    
    // SweetAlert modal açıksa, submit'i engelle
    const swalContainer = document.querySelector('.swal2-container');
    if (swalContainer && (swalContainer.style.display !== 'none' && swalContainer.offsetParent !== null)) {
      e.preventDefault();
      e.stopPropagation();
      return false;
    }
    
    if (this.lockScreen.contains(e.target)) {
      return;
    }
    
    e.preventDefault();
    e.stopPropagation();
    this.showAlert();
    return false;
  }
  
  handleKeydown(e) {
    if (!this.isLocked) return;
    
    if (e.key === 'Escape') {
      e.preventDefault();
      return false;
    }
  }
  
  handlePopstate(e) {
    if (!this.isLocked) return;
    window.history.pushState({ locked: true }, null);
    // Rate limiting ile alert göster
    if (this.alertTimeout) {
      return;
    }
    this.showAlert();
  }
  
  lock() {
    this.isLocked = true;
    localStorage.setItem('lockScreenState', 'locked');
    this.lockScreen.style.display = 'flex';
    document.documentElement.style.overflow = 'hidden';
    document.body.style.overflow = 'hidden';
    document.body.style.margin = '0';
    document.body.style.padding = '0';
    document.documentElement.style.margin = '0';
    document.documentElement.style.padding = '0';
    
    if (this.passwordInput) {
      this.passwordInput.value = '';
      this.passwordInput.focus();
    }
    
    if (this.lockAttemptsContainer) {
      this.lockAttemptsContainer.style.display = 'none';
    }
    
    // Info mesajını göster
    if (this.lockInfoContainer) {
      this.lockInfoContainer.style.display = 'block';
    }
    
    window.history.pushState({ locked: true }, null);
  }
  
  unlock() {
    this.isLocked = false;
    localStorage.setItem('lockScreenState', 'unlocked');
    this.lockScreen.style.display = 'none';
    document.documentElement.style.overflow = 'auto';
    document.body.style.overflow = 'auto';
  }
  
  showAlert() {
    // Rate limiting - 2 saniyede bir alert göster
    if (this.alertTimeout) {
      return;
    }
    
    this.alertTimeout = true;
    setTimeout(() => {
      this.alertTimeout = null;
    }, 2000);
    
    Swal.fire({
      title: 'Kilit Aktif',
      text: 'Kilit açılana kadar siteyi kullanamıyorsunuz.',
      icon: 'warning',
      confirmButtonText: 'Tamam',
      allowOutsideClick: false,
      allowEscapeKey: false,
      timer: 1500,
      timerProgressBar: true,
    });
  }
  
  async handleUnlock() {
    const password = this.passwordInput.value.trim();
    
    if (!password) {
      // Uyarı göstermek için alertTimeout'u sıfırla
      this.alertTimeout = null;
      
      Swal.fire({
        title: 'Uyarı',
        text: 'Şifre giriniz',
        icon: 'warning',
        confirmButtonText: 'Tamam',
      });
      return;
    }
    
    this.unlockBtn.disabled = true;
    this.unlockBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Doğrulanıyor...';
    
    try {
      const response = await fetch('/api/users/verify-password.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'password=' + encodeURIComponent(password),
      });
      
      const data = await response.json();
      
      if (data.status === 'success') {
        this.unlock();
        Swal.fire({
          title: 'Başarılı',
          text: 'Şifre doğru',
          icon: 'success',
          timer: 1500,
          timerProgressBar: true,
        });
      } else {
        const remaining = data.remaining || 0;
        
        if (remaining > 0) {
          this.lockAttempts.textContent = remaining;
          if (this.lockAttemptsContainer) {
            this.lockAttemptsContainer.style.display = 'block';
          }
          
          Swal.fire({
            title: 'Hata',
            text: 'Şifre yanlış. Kalan: ' + remaining,
            icon: 'error',
            confirmButtonText: 'Tamam',
          });
          
          this.passwordInput.value = '';
          this.passwordInput.focus();
        } else {
          // 3 kez yanlış deneme - Logout yapılacak
          // Kilit durumunu pasif et
          localStorage.setItem('lockScreenState', 'unlocked');
          
          Swal.fire({
            title: 'Hata',
            text: 'Çok fazla yanlış deneme. Çıkış yapılıyor.',
            icon: 'error',
            allowOutsideClick: false,
            allowEscapeKey: false,
            confirmButtonText: 'Çıkış',
          }).then(() => {
            window.location.href = '/logout.php';
          });
        }
      }
    } catch (err) {
      console.error('Error:', err);
      Swal.fire({
        title: 'Hata',
        text: 'İşlem başarısız',
        icon: 'error',
        confirmButtonText: 'Tamam',
      });
      this.passwordInput.value = '';
      this.passwordInput.focus();
    } finally {
      this.unlockBtn.disabled = false;
      this.unlockBtn.innerHTML = '<i class="fas fa-unlock me-2"></i>Kilidi Aç';
    }
  }
}

function initLockScreen() {
  if (window.lockScreenManager) return;
  window.lockScreenManager = new LockScreenManager();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initLockScreen);
} else {
  initLockScreen();
}
