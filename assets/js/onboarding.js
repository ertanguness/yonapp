const fetchStatus = async () => {
  try {
    const r = await fetch('/api/onboarding.php?action=status', { credentials: 'same-origin' });
    return await r.json();
  } catch (e) { return null; }
};

const postForm = (payload) => fetch('/api/onboarding.php', {
  method: 'POST',
  credentials: 'same-origin',
  headers: { 'Accept': 'application/json' },
  body: (() => { const f = new FormData(); Object.keys(payload).forEach(k => f.append(k, payload[k])); return f; })()
}).then(r => r.json()).catch(() => null);

const renderModal = (data) => {
  const root = document.getElementById('onboarding-checklist-root');
  if (!root) return;
  const total = (data.tasks || []).length;
  const progressPct = parseInt(data.progress || 0, 10);
  const routeMap = {
    'create_default_cash_account': 'kasa-ekle',
    'add_flat_types': 'daire-turu-ekle',
    'create_site': 'site-ekle',
    'add_blocks': 'blok-ekle',
    'add_apartments': 'daire-ekle',
    'add_people': 'site-sakini-ekle',
    'set_default_cash_account': 'kasa-listesi',
    'add_dues_types': 'aidat-turu-tanimlama'
  };
  const descMap = {
    'create_default_cash_account': 'Gelir-gider ve tahsilatlar için en az bir kasa oluşturun.',
    'add_flat_types': 'Daire tiplerini tanımlayın (ör. 1+1, 2+1) ve sınıflandırın.',
    'create_site': 'Yönetimini yaptığınız siteyi temel bilgilerle ekleyin.',
    'add_blocks': 'Sitede bulunan blokları ve bağımsız bölüm sayılarını ekleyin.',
    'add_apartments': 'Daireleri blok ve daire numaralarıyla sisteme ekleyin.',
    'add_people': 'Sitede yaşayan kişiler veya malik bilgilerini ekleyin.',
    'set_default_cash_account': 'İşlemlerde varsayılan kullanılacak kasayı belirleyin.',
    'add_dues_types': 'Aidat türlerini tanımlayın (tutar, dönem, açıklama).'
  };
  const items = (data.tasks || []).map(t => (
    `<li class="list-group-item d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-2">
        <div class="rounded-circle" style="width:20px;height:20px;background:${t.is_completed ? '#22c55e' : '#e2e8f0'}"></div>
        <div>
          <span class="${t.is_completed ? 'text-decoration-line-through text-muted' : 'fw-medium'}">${t.title}</span>
          <small class="text-muted d-block">${descMap[t.key] ? descMap[t.key] : (t.description || '')}</small>
        </div>
      </div>
      <div class="d-flex align-items-center gap-2">
        ${t.is_completed ? '<span class="badge bg-success">Tamamlandı</span>' : `<a href="${routeMap[t.key] ? routeMap[t.key] : '#'}" class="btn btn-sm btn-outline-secondary onb-go">Git</a>`}
        ${t.is_completed ? '' : `<button class="btn btn-sm btn-primary complete-task" data-key="${t.key}">Tamamla</button>`}
      </div>
    </li>`
  )).join('');
  const html = `
    <div class="onb-overlay" style="position:fixed;inset:0;background:rgba(17,24,39,.55);backdrop-filter:blur(2px);z-index:9998"></div>
    <div class="onb-modal" style="position:fixed;inset:0;display:flex;align-items:center;justify-content:center;padding:16px;z-index:9999;">
      <div class="card shadow-lg" style="width:680px;max-width:100%;max-height:90vh;border-radius:14px;overflow:hidden;">
        <div class="card-header" style="background:linear-gradient(90deg,#0ea5e9,#6366f1);color:#fff;">
          <div class="d-flex align-items-center gap-2">
            <span class="d-inline-flex align-items-center justify-content-center bg-white bg-opacity-20 rounded" style="width:28px;height:28px;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4l3 3"></path></svg>
            </span>
            <div class="fw-semibold">İlk Kurulum Checklist</div>
          </div>
        </div>
        <div class="card-body" style="max-height:60vh;overflow:auto;">
          <div class="mb-2">
            <div class="progress" style="height: 14px;background:#eef2ff;">
              <div class="progress-bar" role="progressbar" style="width:${progressPct}%;background:linear-gradient(90deg,#22c55e,#06b6d4);">${progressPct}%</div>
            </div>
            <small class="text-muted" id="onb-summary">Tamamlanan ${Math.floor((progressPct*total)/100)} / ${total}</small>
          </div>
          <ul class="list-group" id="onb-list">${items}</ul>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
          <small class="text-muted">İlk kurulum adımlarını tamamlayın; tümü bittiğinde bu pencere otomatik kapanır.</small>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary onb-dismiss">Sonra Göster</button>
            <button type="button" class="btn btn-sm btn-primary onb-close">Kapat</button>
          </div>
        </div>
      </div>
    </div>`;
  root.innerHTML = html;

  const overlay = root.querySelector('.onb-overlay');
  const closeBtn = root.querySelector('.onb-close');
  const dismissBtn = root.querySelector('.onb-dismiss');
  const list = root.querySelector('#onb-list');
  const bar = root.querySelector('.progress-bar');
  const summary = root.querySelector('#onb-summary');

  const updateProgress = () => {
    const totalItems = list.querySelectorAll('.list-group-item').length;
    const remaining = list.querySelectorAll('.complete-task').length;
    const done = totalItems - remaining;
    const pct = Math.floor((done / Math.max(totalItems,1)) * 100);
    bar.style.width = pct + '%';
    bar.textContent = pct + '%';
    summary.textContent = 'Tamamlanan ' + done + ' / ' + totalItems;
    if (done === totalItems) { 
      root.innerHTML = ''; 
      const fabEl = document.querySelector('.onb-fab');
      if (fabEl) fabEl.remove();
    }
  };

  list.addEventListener('click', async (e) => {
    const btn = e.target.closest('.complete-task');
    if (!btn) return;
    const key = btn.getAttribute('data-key');
    const res = await postForm({ action: 'complete', task_key: key });
    if (res && res.status === 'success') {
      const li = btn.closest('.list-group-item');
      const title = li.querySelector('span');
      title.classList.add('text-decoration-line-through');
      title.classList.add('text-muted');
      btn.remove();
      const go = li.querySelector('.onb-go');
      if (go) go.remove();
      const badge = document.createElement('span');
      badge.className = 'badge bg-success';
      badge.textContent = 'Tamamlandı';
      li.appendChild(badge);
      updateProgress();
    }
  });

  dismissBtn.addEventListener('click', async () => {
    const res = await postForm({ action: 'dismiss' });
    root.innerHTML = '';
  });

  closeBtn.addEventListener('click', () => { root.innerHTML = ''; });
  overlay.addEventListener('click', () => { root.innerHTML = ''; });
};

const isAllowedPage = () => {
  const path = (window.location.pathname || '').toLowerCase();
  if (path.endsWith('/company-list.php')) { return false; }
  const dp = document.body ? document.body.getAttribute('data-page') : null;
  if (!dp) { return false; }
  const deny = ['sign-in','kayit-ol','logout','forgot-password','reset-password'];
  if (deny.indexOf(dp) !== -1) { return false; }
  return true;
};

(async () => {
  if (!isAllowedPage()) { return; }
  const status = await fetchStatus();
  if (status && status.status === 'success') {
    if (status.should_show) { renderModal(status); }
    const cc = parseInt(status.completed_count ?? 0, 10);
    const tc = parseInt(status.total_count ?? 0, 10);
    if (cc < tc) {
      const fab = document.createElement('button');
      fab.className = 'btn btn-primary onb-fab';
      fab.textContent = 'İlk Kurulum';
      fab.style.position = 'fixed';
      fab.style.right = '18px';
      fab.style.bottom = '18px';
      fab.style.zIndex = '9997';
      fab.style.borderRadius = '999px';
      fab.style.boxShadow = '0 8px 20px rgba(2,132,199,.35)';
      document.body.appendChild(fab);
      fab.addEventListener('click', async () => {
        const s = await fetchStatus();
        if (s && s.status === 'success') { renderModal(s); }
      });
    }
  }
})();
