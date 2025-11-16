document.addEventListener('DOMContentLoaded', function () {
  const tbl = document.querySelector('#tblAcil tbody');
  const fltName = document.getElementById('fltName');
  const fltPhone = document.getElementById('fltPhone');
  const fltRel = document.getElementById('fltRel');
  const btnFilter = document.getElementById('btnFilter');
  const btnClear = document.getElementById('btnClear');
  const btnYeni = document.getElementById('btnYeniKisi');
  const mdl = new bootstrap.Modal(document.getElementById('mdlAcil'));
  const frm = document.getElementById('frmAcil');
  const btnKaydet = document.getElementById('btnKaydet');
  const expBar = document.getElementById('expBar');
  const expProgress = document.getElementById('expProgress');

  let start = 0;
  let length = 25;
  let order = 'desc';

  function rowHtml(r, i) {
    const tel = r.telefon ? r.telefon.replace(/\D/g, '').replace(/(\d{3})(\d{3})(\d{2})(\d{2})/, '+90 $1 $2 $3 $4') : '-';
    const relText = window.RELATIONSHIP && window.RELATIONSHIP[r.yakinlik] ? window.RELATIONSHIP[r.yakinlik] : (r.yakinlik || '-');
    const date = r.kayit_tarihi || '-';
    return `<tr class="text-center">
      <td>${i}</td>
      <td>${r.adi_soyadi || '-'}</td>
      <td>${tel}</td>
      <td>${relText}</td>
      <td>${date}</td>
      <td>
        <div class="hstack gap-2">
          <a href="javascript:void(0)" class="avatar-text avatar-md btn-edit" data-id="${r.id}"><i class="feather-edit"></i></a>
          <a href="javascript:void(0)" class="avatar-text avatar-md btn-del" data-id="${r.id}" data-name="${r.adi_soyadi || ''}"><i class="feather-trash-2"></i></a>
        </div>
      </td>
    </tr>`;
  }

  function load() {
    const params = new URLSearchParams();
    params.set('start', start);
    params.set('length', length);
    params.set('order', order);
    if (fltName.value) params.set('name', fltName.value);
    if (fltPhone.value) params.set('phone', fltPhone.value);
    if (fltRel.value) params.set('relation', fltRel.value);
    fetch(`/api/acil-durum-kisileri.php?${params.toString()}`, { credentials: 'same-origin' })
      .then(r => r.json())
      .then(j => {
        tbl.innerHTML = '';
        let i = start + 1;
        (j.data || []).forEach(r => {
          tbl.insertAdjacentHTML('beforeend', rowHtml(r, i++));
        });
      });
  }

  btnFilter.addEventListener('click', function () { start = 0; load(); });
  btnClear.addEventListener('click', function () { fltName.value=''; fltPhone.value=''; fltRel.value=''; start=0; load(); });
  btnYeni.addEventListener('click', function() { frm.reset(); document.getElementById('frmId').value=''; mdl.show(); });
  btnKaydet.addEventListener('click', function() {
    const data = {
      id: document.getElementById('frmId').value || undefined,
      kisi_id: document.getElementById('frmKisiId').value,
      adi_soyadi: document.getElementById('frmName').value,
      telefon: document.getElementById('frmPhone').value,
      yakinlik: document.getElementById('frmRel').value
    };
    if (!data.kisi_id || !data.adi_soyadi || !data.telefon || !data.yakinlik) return;
    const method = data.id ? 'PUT' : 'POST';
    expProgress.style.display = 'block';
    expBar.style.width = '25%';
    fetch('/api/acil-durum-kisileri.php', {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data),
      credentials: 'same-origin'
    }).then(r=>{expBar.style.width='60%'; return r.json();})
      .then(j=>{expBar.style.width='100%'; setTimeout(()=>{expProgress.style.display='none'; expBar.style.width='0%';}, 400); if(j.status==='ok'){ mdl.hide(); load(); } });
  });

  document.addEventListener('click', function(e) {
    const ed = e.target.closest('.btn-edit');
    const dl = e.target.closest('.btn-del');
    if (ed) {
      const id = ed.getAttribute('data-id');
      fetch(`/api/acil-durum-kisileri.php?id=${id}`)
        .then(r=>r.json()).then(j=>{
          const d = j.data; if(!d) return;
          document.getElementById('frmId').value = d.id;
          document.getElementById('frmKisiId').value = d.kisi_id;
          document.getElementById('frmName').value = d.adi_soyadi;
          document.getElementById('frmPhone').value = d.telefon;
          document.getElementById('frmRel').value = d.yakinlik;
          mdl.show();
        });
    }
    if (dl) {
      const id = dl.getAttribute('data-id');
      const nm = dl.getAttribute('data-name') || '';
      if (!confirm(`${nm} silinsin mi?`)) return;
      fetch(`/api/acil-durum-kisileri.php?id=${id}`, { method: 'DELETE', credentials: 'same-origin' })
        .then(r=>r.json()).then(_=> load());
    }
  });

  load();
});