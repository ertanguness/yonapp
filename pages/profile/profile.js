document.addEventListener('DOMContentLoaded', function () {
  const saveBtn = document.getElementById('profileSaveBtn');
  const profileForm = document.getElementById('profileForm');
  const passwordForm = document.getElementById('passwordForm');
  const changeAvatarBtn = document.getElementById('changeAvatarBtn');
  const avatarInput = document.getElementById('avatarInput');
  const profileAvatarImg = document.getElementById('profileAvatarImg');

  // Telefon alanına maske ekle (0(5xx) xxx xx xx)
  const phoneInput = document.getElementById('phone');
  if (phoneInput && $.fn.mask) {
    $(phoneInput).mask('0(000) 000 00 00');
  }

  function validate() {
    const fullName = document.getElementById('full_name').value.trim();
    const email = document.getElementById('email').value.trim();

    if (!fullName) {
      Swal.fire({ title: 'Uyarı', text: 'Ad soyad zorunludur.', icon: 'warning' });
      return false;
    }
    if (!email) {
      Swal.fire({ title: 'Uyarı', text: 'E-posta zorunludur.', icon: 'warning' });
      return false;
    }

    const cur = document.getElementById('current_password').value;
    const nw = document.getElementById('new_password').value;
    const cnf = document.getElementById('confirm_password').value;

    if (cur || nw || cnf) {
      if (!cur || !nw || !cnf) {
        Swal.fire({ title: 'Uyarı', text: 'Şifre değişikliği için tüm alanları doldurun.', icon: 'warning' });
        return false;
      }
      if (nw !== cnf) {
        Swal.fire({ title: 'Uyarı', text: 'Yeni şifreler uyuşmuyor.', icon: 'warning' });
        return false;
      }
    }
    return true;
  }

  if (saveBtn) {
    saveBtn.addEventListener('click', function () {
      if (!validate()) return;

      const fd = new FormData(profileForm);
      if (passwordForm) {
        const pwdData = new FormData(passwordForm);
        for (const [k, v] of pwdData.entries()) {
          fd.append(k, v);
        }
      }
      fd.set('action', 'updateProfile');

      fetch('/api/users/profile.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
          const icon = data.status === 'success' ? 'success' : 'error';
          Swal.fire({ title: data.status === 'success' ? 'Başarılı' : 'Hata', text: data.message, icon })
            .then(() => {
              if (data.status === 'success') {
                window.location.reload();
              }
            });
        })
        .catch(err => {
          Swal.fire({ title: 'Hata', text: err.message || 'İşlem gerçekleştirilemedi.', icon: 'error' });
        });
    });
  }

  // Avatar değiştir
  if (changeAvatarBtn && avatarInput) {
    changeAvatarBtn.addEventListener('click', function () {
      avatarInput.click();
    });

    avatarInput.addEventListener('change', function () {
      if (!avatarInput.files || !avatarInput.files[0]) return;
      const fd = new FormData();
      fd.append('avatar', avatarInput.files[0]);

      fetch('/api/users/avatar.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
          const icon = data.status === 'success' ? 'success' : 'error';
          Swal.fire({ title: data.status === 'success' ? 'Başarılı' : 'Hata', text: data.message, icon });
          if (data.status === 'success' && data.url) {
            const bust = data.url + '?v=' + Date.now();
            if (profileAvatarImg) profileAvatarImg.src = bust;
            // Header içindeki tüm kullanıcı avatarlarını güncelle
            document.querySelectorAll('img.user-avtar').forEach(img => {
              img.src = bust;
            });
          }
        })
        .catch(err => {
          Swal.fire({ title: 'Hata', text: err.message || 'Yükleme başarısız.', icon: 'error' });
        });
    });
  }
});

