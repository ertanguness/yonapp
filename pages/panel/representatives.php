<?php
use Model\SitelerModel;
use Model\UserModel;
?>
<div class="container-xl">
  <?php
    $title = "Temsilciler";
    $text = "Temsilci ekleyin, sitelere atayın ve komisyon takibini yapın.";
    require_once 'pages/components/alert.php';
  ?>

  <div class="row row-deck row-cards">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h3 class="card-title">Temsilci Listesi</h3>
          <button class="btn btn-primary btn-sm" id="repAddBtn">
            <i class="feather-plus"></i> Yeni Temsilci
          </button>
        </div>
        <div class="table-responsive">
          <table class="table table-hover " id="repList">
            <thead>
              <tr>
                <th class="w-1">No.</th>
                <th>Ad Soyad</th>
                <th>Telefon</th>
                <th>E-posta</th>
                <th>IBAN</th>
                <th>Kayıt Tarihi</th>
                <th class="w-1">İşlem</th>
              </tr>
            </thead>
            <tbody id="repListBody">
              <tr>
                <td class="text-center text-muted">Yükleniyor...</td>
                <td></td><td></td><td></td><td></td><td></td><td></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="repManageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Temsilci Yönetimi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>
      <div class="modal-body">
        <div id="repManageContent">
          <div class="text-center text-muted">Yükleniyor...</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
  var repModalEl = document.getElementById('repManageModal');
  var repModal = repModalEl ? new bootstrap.Modal(repModalEl) : null;
  var repContent = document.getElementById('repManageContent');
  var repListBody = document.getElementById('repListBody');

  function loadRepList(){
    var fd = new FormData();
    fd.append('action','rep_list');
    fetch((window.API_BASE||'') + '/pages/panel/api.php', { method:'POST', body: fd, credentials: 'same-origin' })
      .then(function(r){ return r.text(); })
      .then(function(t){ try { return JSON.parse(t); } catch(e){ return {status:'error'}; } })
      .then(function(j){
        if (!j || j.status !== 'success') {
          repListBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Liste yüklenemedi</td></tr>';
          return;
        }
        var list = j.data || [];
        if (!list.length) {
          repListBody.innerHTML = '<tr><td class="text-center text-muted">Temsilci bulunamadı</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
          return;
        }
        repListBody.innerHTML = list.map(function(x, idx){
          var kt = (function(){
            try { return x.created_at ? new Date(x.created_at).toLocaleDateString('tr-TR') : '-'; } catch(e){ return x.created_at || '-'; }
          })();
          var ktOrder = (function(){
            try { return x.created_at ? new Date(x.created_at).getTime() : 0; } catch(e){ return 0; }
          })();
          return `
            <tr class="text-center">
              <td><span class="text-muted">${idx+1}</span></td>
              <td>${x.full_name || '-'}</td>
              <td>${x.phone || '-'}</td>
              <td>${x.email || '-'}</td>
              <td>${x.iban || '-'}</td>
              <td data-order="${ktOrder}" data-search="${x.created_at||''}">${kt}</td>
              <td>
                <div class="btn-group btn-group-sm" role="group">
                  <button class="btn btn-outline-primary rep-detail" data-rep-id="${x.id}" title="Detay"><i class="feather-info"></i></button>
                  <button class="btn btn-outline-secondary rep-edit" data-rep-id="${x.id}" title="Düzenle"><i class="feather-edit-2"></i></button>
                  <button class="btn btn-outline-danger rep-delete" data-rep-id="${x.id}" title="Sil"><i class="feather-trash-2"></i></button>
                </div>
              </td>
            </tr>
          `;
        }).join('');
        try {
          if (list.length > 0) {
            if ($.fn.DataTable.isDataTable('#repList')) {
              $('#repList').DataTable().destroy();
            }
            $('#repList').DataTable({
              responsive: true,
              order: [[5, 'desc']],
              language: {
                sEmptyTable: "Tabloda herhangi bir veri mevcut değil",
                sInfo: "_TOTAL_ kayıttan _START_ - _END_ arasındaki kayıtlar gösteriliyor",
                sInfoEmpty: "Kayıt yok",
                sInfoFiltered: "(_MAX_ kayıt içerisinden bulunan)",
                sLengthMenu: "Sayfada _MENU_ kayıt göster",
                sLoadingRecords: "Yükleniyor...",
                sProcessing: "İşleniyor...",
                sSearch: "Ara:",
                sZeroRecords: "Eşleşen kayıt bulunamadı",
                oPaginate: {
                  sFirst: "İlk",
                  sLast: "Son",
                  sNext: "Sonraki",
                  sPrevious: "Önceki"
                }
              }
            });
          }
        } catch(e) { console.warn('DataTable init error', e); }
        document.querySelectorAll('.rep-detail').forEach(function(btn){
          btn.addEventListener('click', function(){
            var repId = this.getAttribute('data-rep-id');
            showRepManage(repId);
          });
        });
        document.querySelectorAll('.rep-edit').forEach(function(btn){
          btn.addEventListener('click', function(){
            var repId = this.getAttribute('data-rep-id');
            openRepEdit(repId);
          });
        });
        document.querySelectorAll('.rep-delete').forEach(function(btn){
          btn.addEventListener('click', function(){
            var repId = this.getAttribute('data-rep-id');
            Swal.fire({
              icon: 'warning',
              title: 'Temsilciyi Sil',
              text: 'Bu işlem temsilciyi ve ilişkili kayıtları siler. Emin misiniz?',
              showCancelButton: true,
              confirmButtonText: 'Evet, Sil',
              cancelButtonText: 'İptal'
            }).then(function(res){
          if (res.isConfirmed) {
            var fd = new FormData();
            fd.append('action','rep_delete');
            fd.append('rep_id', repId);
            fetch((window.API_BASE||'') + '/pages/panel/api.php', { method:'POST', body: fd, credentials: 'same-origin' }).then(function(r){ return r.text(); }).then(function(t){ try { return JSON.parse(t); } catch(e){ return {status:'error'}; } }).then(function(jj){
              if (jj && jj.status === 'success') { loadRepList(); }
              else { Swal.fire('Hata', (jj && jj.message) ? jj.message : 'Silme başarısız', 'error'); }
            });
          }
        });
          });
        });
      })
      .catch(function(){
        repListBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">İstek hatası</td></tr>';
      });
  }

  function openRepEdit(repId){
    var fd = new FormData();
    fd.append('action','rep_manage');
    fd.append('rep_id', repId);
    fetch((window.API_BASE||'') + '/pages/panel/api.php', { method:'POST', body: fd, credentials: 'same-origin' }).then(function(r){ return r.text(); }).then(function(t){ try { return JSON.parse(t); } catch(e){ return {status:'error'}; } }).then(function(j){
      if (!j || j.status !== 'success') { Swal.fire('Hata', 'Temsilci bilgileri yüklenemedi', 'error'); return; }
      var rep = j.data.rep || {};
      Swal.fire({
        title: '',
        html: `
          <div class="text-start">
            <div class="mb-3 d-flex align-items-center">
              <span class="avatar avatar-sm me-2 rounded-circle bg-primary text-white fw-bold" style="display:inline-flex;align-items:center;justify-content:center;">${(rep.full_name||'T').slice(0,1).toUpperCase()}</span>
              <div>
                <div class="fw-bold">Temsilci Düzenle</div>
                <div class="text-muted small">Bilgileri güncelleyin</div>
              </div>
            </div>
            <div class="row g-2">
              <div class="col-12">
                <label class="small text-muted">Ad Soyad</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="feather-user"></i></span>
                  <input type="text" id="editFullName" class="form-control" value="${rep.full_name||''}" maxlength="120">
                </div>
              </div>
              <div class="col-12">
                <label class="small text-muted">Telefon</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="feather-phone"></i></span>
                  <input type="text" id="editPhone" class="form-control" value="${rep.phone||''}" placeholder="0(5xx) xxx xx xx" maxlength="16">
                </div>
              </div>
              <div class="col-12">
                <label class="small text-muted">E-posta</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="feather-mail"></i></span>
                  <input type="email" id="editEmail" class="form-control" value="${rep.email||''}" placeholder="mail@ornek.com">
                </div>
              </div>
              <div class="col-12">
                <label class="small text-muted">IBAN</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="feather-credit-card"></i></span>
                  <input type="text" id="editIban" class="form-control" value="${rep.iban||''}" placeholder="TR00 0000 0000 0000 0000 0000 00" maxlength="29">
                </div>
              </div>
            </div>
          </div>
        `,
        confirmButtonText: 'Kaydet',
        customClass: { popup: 'rounded-4' },
        showCancelButton: true,
        cancelButtonText: 'İptal',
        didOpen: function(){
          try {
            if (window.jQuery && $.fn.mask) {
              $('#editPhone').mask('0(000) 000 00 00');
              $('#editIban').mask('TR00 0000 0000 0000 0000 0000 00');
            }
          } catch(e){}
        },
        preConfirm: function(){
          function isValidEmail(v){ return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); }
          function isValidPhone(v){ var s=v.replace(/\D+/g,''); return /^(0\d{10}|90\d{10})$/.test(s); }
          function isValidIBAN(v){ var s=v.toUpperCase().replace(/\s+/g,''); return /^TR\d{24}$/.test(s); }
          var fn = document.getElementById('editFullName').value.trim();
          var ph = document.getElementById('editPhone').value.trim();
          var em = document.getElementById('editEmail').value.trim();
          var ib = document.getElementById('editIban').value.trim().toUpperCase().replace(/\s+/g,'');
          if (!fn) { Swal.showValidationMessage('Ad Soyad zorunludur'); return false; }
          if (!ph || !isValidPhone(ph)) { Swal.showValidationMessage('Telefon formatı geçersiz'); return false; }
          if (!em || !isValidEmail(em)) { Swal.showValidationMessage('E-posta formatı geçersiz'); return false; }
          if (!ib || !isValidIBAN(ib)) { Swal.showValidationMessage('IBAN formatı geçersiz'); return false; }
          var fd = new FormData();
          fd.append('action','rep_update');
          fd.append('rep_id', rep.id);
          fd.append('full_name', fn);
          fd.append('phone', ph);
          fd.append('email', em);
          fd.append('iban', ib);
          return fetch((window.API_BASE||'') + '/pages/panel/api.php', { method:'POST', body: fd, credentials: 'same-origin' }).then(function(r){ return r.text(); }).then(function(t){ try { return JSON.parse(t); } catch(e){ return {status:'error'}; } }).then(function(jj){
            if (!jj || jj.status !== 'success') { Swal.showValidationMessage((jj && jj.message) ? jj.message : 'Güncelleme başarısız'); return false; }
            return jj;
          });
        }
      }).then(function(res){
        if (res.value && res.value.status === 'success') { loadRepList(); }
      });
    });
  }

  function showRepManage(repId){
    repContent.innerHTML = '<div class="text-center text-muted">Yükleniyor...</div>';
    repModal && repModal.show();
    var fd = new FormData();
    fd.append('action', 'rep_manage');
    fd.append('rep_id', repId);
    fetch('/pages/panel/api.php', { method:'POST', body: fd })
      .then(function(r){ return r.json(); })
      .then(function(j){
        if (!j || j.status !== 'success') {
          repContent.innerHTML = '<div class="text-danger">Detay yüklenemedi</div>';
          return;
        }
        var d = j.data;
        var rep = d.rep || {};
        var sites = d.all_sites || [];
        var assigns = d.assignments || [];
        var schedule = d.schedule || [];
        var opts = sites.map(function(s){ return `<option value="${s.id}">${s.site_adi || ''}</option>`; }).join('');
        var assignRows = assigns.map(function(a){
          return `
            <tr>
              <td><span class="site-name-strong">${a.site_adi || ''}</span></td>
              <td>
                <div class="input-group input-group-sm rate-input-group">
                  <input type="number" step="0.01" class="form-control rep-rate-input text-end" data-rep-id="${rep.id}" data-site-id="${a.site_id}" value="${a.commission_rate || 25}">
                  <span class="input-group-text">%</span>
                </div>
              </td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-danger rep-unassign" data-rep-id="${rep.id}" data-site-id="${a.site_id}">
                  <i class="feather-x"></i>
                </button>
              </td>
            </tr>
          `;
        }).join('');
        var schedRows = schedule.map(function(row){
          var ico = row.paid ? 'check-circle' : 'x-circle';
          var cls = row.paid ? 'btn-success' : 'btn-outline-danger';
          return `
            <tr>
              <td>${row.site_name || ''}</td>
              <td>${row.period}</td>
              <td>${row.site_paid ? '<span class="badge bg-success">Site Ödendi</span>' : '<span class="badge bg-warning">Site Beklemede</span>'}</td>
              <td>${row.amount.toFixed ? row.amount.toFixed(2) : row.amount}</td>
              <td>${row.paid_at || '-'}</td>
              <td class="text-end">
                <button class="btn btn-sm btn-icon rep-paid-toggle ${cls}" 
                        data-rep-id="${rep.id}" data-site-id="${row.site_id}" data-period="${row.period}" 
                        data-paid="${row.paid?1:0}" data-amount="${row.amount}">
                  <i class="feather-${ico}"></i>
                </button>
              </td>
            </tr>
          `;
        }).join('');
        var totalPayableCommission = (schedule||[]).reduce(function(sum,row){
          return sum + (!row.paid ? (row.amount||0) : 0);
        }, 0);
        var totalPaidCommission = (schedule||[]).reduce(function(sum,row){
          return sum + (row.paid ? (row.amount||0) : 0);
        }, 0);
        var sitePayableCommission = (schedule||[]).reduce(function(sum,row){
          return sum + ((row.site_paid && !row.paid) ? (row.amount||0) : 0);
        }, 0);
        repContent.innerHTML = `
          <style>
            #schedTableBody td, #schedTableBody th { white-space: normal; word-break: break-word; }
            .no-x-scroll { overflow-x: hidden !important; }
            .compact-table td, .compact-table th { padding: .4rem .5rem; font-size: .875rem; }
            .site-name-strong { font-weight: 600; font-size: 1rem; }
            .rate-input-group { flex-wrap: nowrap; white-space: nowrap; min-width: 100px; }
            .rep-rate-input { font-size: 0.8rem !important; }
          </style>
          <div class="card mb-3 bg-primary-lt border-0">
            <div class="card-body py-3 d-flex align-items-center">
              <span class="avatar avatar-md me-3 rounded-circle bg-primary text-white fw-bold" style="display:inline-flex;align-items:center;justify-content:center;">
                ${(rep.full_name||'').split(' ').map(function(w){return w[0]||'';}).slice(0,2).join('').toUpperCase() || 'T'}
              </span>
              <div>
                <h4 class="mb-0 text-primary">${rep.full_name || ''}</h4>
                <div class="small text-muted">${rep.email || ''} • ${rep.phone || '-'}</div>
              </div>
              <div class="ms-auto text-end">
                <div class="small text-muted">Atanmış Site</div>
                <div class="h3 mb-0 text-primary">${assigns.length}</div>
                <div class="small text-muted mt-2">Toplam Ödenecek Komisyon</div>
                <div class="h4 mb-0 text-success" id="repTotalUnpaid">${(totalPayableCommission||0).toFixed(2)}</div>
                <div class="small text-muted">Ödenebilir (Site ödemiş)</div>
                <div class="h5 mb-0 text-info" id="repSitePayable">${(sitePayableCommission||0).toFixed(2)}</div>
                <div class="small text-muted">Toplam Ödenmiş Komisyon</div>
                <div class="h5 mb-0 text-primary" id="repTotalPaid">${(totalPaidCommission||0).toFixed(2)}</div>
              </div>
            </div>
          </div>
          <div class="row g-3">
            <div class="col-lg-4">
              <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                  <h5 class="card-title mb-0">Site Atama</h5>
                </div>
                <div class="card-body no-x-scroll">
                  <div class="row g-2 mb-3 align-items-end">
                    <div class="col-12">
                      <label class="small text-muted">Site Seç</label>
                      <select class="form-select form-select-sm" id="assignSiteSel">${opts}</select>
                    </div>
                    <div class="col-7">
                      <label class="small text-muted">Komisyon</label>
                      <div class="input-group input-group-sm rate-input-group" style="min-width:auto;">
                        <input type="number" step="0.01" id="assignRate" class="form-control text-end" value="25">
                        <span class="input-group-text">%</span>
                      </div>
                    </div>
                    <div class="col-5">
                      <button class="btn btn-primary btn-sm w-100" id="assignAddBtn">
                        <i class="feather-plus"></i> Ata
                      </button>
                    </div>
                  </div>
                  <hr>
                  <div>
                    <table class="table table-sm compact-table">
                      <thead>
                        <tr>
                          <th style="width: 60%">Site</th>
                          <th style="width: 25%">Komisyon</th>
                          <th class="text-end" style="width: 15%">İşlem</th>
                        </tr>
                      </thead>
                      <tbody id="assignTableBody">
                        ${assignRows || '<tr><td colspan="3" class="text-muted text-center">Atama yok</td></tr>'}
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-8">
              <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                  <h5 class="card-title mb-0">Temsilci Ödeme Takibi (Son 12 Ay)</h5>
                  <div class="d-flex align-items-center gap-2 flex-nowrap">
                    <select class="form-select form-select-sm w-auto" id="schedFilter">
                      <option value="all" selected>Tümü</option>
                      ${assigns.map(function(a){ return `<option value="${a.site_id}">${a.site_adi||''}</option>`; }).join('')}
                    </select>
                    <button class="btn btn-sm btn-success rounded-pill d-inline-flex align-items-center" id="repBulkPaidBtn">
                      <i class="feather-check me-1"></i> Toplu Ödendi
                    </button>
                  </div>
                </div>
                <div class="card-body no-x-scroll">
                  <div>
                    <table class="table table-sm compact-table">
                      <thead>
                        <tr>
                          <th>Site</th>
                          <th>Dönem</th>
                          <th>Site Ödeme</th>
                          <th>Komisyon Tutarı</th>
                          <th>Ödeme Tarihi</th>
                          <th class="text-end">İşlem</th>
                        </tr>
                      </thead>
                      <tbody id="schedTableBody">
                        ${schedRows || '<tr><td colspan="6" class="text-muted text-center">Kayıt yok</td></tr>'}
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        `;
        var filterSel = repContent.querySelector('#schedFilter');
        if (filterSel) {
          filterSel.addEventListener('change', function(){
            var val = this.value;
            repContent.querySelectorAll('#schedTableBody tr').forEach(function(tr){
              if (val === 'all') { tr.style.display = ''; return; }
              var siteName = tr.children[0]?.textContent || '';
              var match = assigns.find(function(a){ return String(a.site_id) === String(val) && a.site_adi === siteName; });
              tr.style.display = match ? '' : 'none';
            });
          });
        }
        // initialize select2 for site select in modal
        try { $('#assignSiteSel').select2({ theme: 'bootstrap-5', width: '100%', placeholder: 'Site seç' }); } catch(e){}
        var assignBtn = repContent.querySelector('#assignAddBtn');
        if (assignBtn) {
          assignBtn.addEventListener('click', function(){
            var siteId = repContent.querySelector('#assignSiteSel').value;
            var rate = repContent.querySelector('#assignRate').value || '25';
            var fd2 = new FormData();
            fd2.append('action','rep_assign_site');
          fd2.append('rep_id', rep.id);
          fd2.append('site_id', siteId);
          fd2.append('commission_rate', rate);
          fetch((window.API_BASE||'') + '/pages/panel/api.php', { method:'POST', body: fd2, credentials: 'same-origin' })
            .then(function(r){ return r.text(); })
            .then(function(t){ try { return JSON.parse(t); } catch(e){ return {status:'error'}; } })
            .then(function(jj){
              if (jj && jj.status === 'success') { showRepManage(rep.id); }
            });
        });
        }
        repContent.querySelectorAll('.rep-unassign').forEach(function(btn){
          btn.addEventListener('click', function(){
            var repId = this.getAttribute('data-rep-id');
            var siteId = this.getAttribute('data-site-id');
            var fd3 = new FormData();
            fd3.append('action','rep_unassign_site');
          fd3.append('rep_id', repId);
          fd3.append('site_id', siteId);
          fetch((window.API_BASE||'') + '/pages/panel/api.php', { method:'POST', body: fd3, credentials: 'same-origin' })
            .then(function(r){ return r.text(); })
            .then(function(t){ try { return JSON.parse(t); } catch(e){ return {status:'error'}; } })
            .then(function(jj){
              if (jj && jj.status === 'success') { showRepManage(repId); }
            });
        });
        });
        repContent.querySelectorAll('.rep-rate-input').forEach(function(inp){
          inp.addEventListener('change', function(){
            var repId = this.getAttribute('data-rep-id');
            var siteId = this.getAttribute('data-site-id');
            var rate = this.value || '25';
            var fd4 = new FormData();
            fd4.append('action','rep_update_rate');
            fd4.append('rep_id', repId);
          fd4.append('site_id', siteId);
          fd4.append('commission_rate', rate);
          fetch((window.API_BASE||'') + '/pages/panel/api.php', { method:'POST', body: fd4, credentials: 'same-origin' })
            .then(function(r){ return r.text(); })
            .then(function(t){ try { return JSON.parse(t); } catch(e){ return {status:'error'}; } })
            .then(function(jj){
              if (jj && jj.status === 'success') { showRepManage(repId); }
            });
        });
        });
        function recalcRepTotals() {
          var rows = [].slice.call(repContent.querySelectorAll('#schedTableBody tr'));
          var totals = rows.reduce(function(acc, tr){
            var amt = parseFloat(tr.children[3]?.textContent || '0') || 0;
            var paidBtn = tr.querySelector('.rep-paid-toggle');
            var paid = paidBtn ? (parseInt(paidBtn.getAttribute('data-paid')||'0',10)===1) : false;
            var sitePaidBadge = tr.children[2]?.textContent || '';
            var sitePaid = sitePaidBadge.indexOf('Site Ödendi') !== -1;
            acc.paid += paid ? amt : 0;
            acc.unpaid += (!paid ? amt : 0);
            acc.sitePayable += (sitePaid && !paid) ? amt : 0;
            return acc;
          }, {paid:0, unpaid:0, sitePayable:0});
          var paidEl = repContent.querySelector('#repTotalPaid');
          var payableEl = repContent.querySelector('#repTotalUnpaid');
          var sitePayableEl = repContent.querySelector('#repSitePayable');
          if (paidEl) { paidEl.textContent = totals.paid.toFixed(2); }
          if (payableEl) { payableEl.textContent = totals.unpaid.toFixed(2); }
          if (sitePayableEl) { sitePayableEl.textContent = totals.sitePayable.toFixed(2); }
        }
        repContent.querySelectorAll('.rep-paid-toggle').forEach(function(btn){
          btn.addEventListener('click', function(){
            var paid = parseInt(this.getAttribute('data-paid')||'0',10);
            var next = paid ? 0 : 1;
            var repId = this.getAttribute('data-rep-id');
            var siteId = this.getAttribute('data-site-id');
            var period = this.getAttribute('data-period');
            var amount = this.getAttribute('data-amount');
            var fd5 = new FormData();
            fd5.append('action','rep_mark_paid');
            fd5.append('rep_id', repId);
            fd5.append('site_id', siteId);
          fd5.append('period', period);
          fd5.append('paid', String(next));
          fd5.append('amount', amount);
          fetch((window.API_BASE||'') + '/pages/panel/api.php', { method:'POST', body: fd5, credentials: 'same-origin' })
            .then(function(r){ return r.text(); })
            .then(function(t){ try { return JSON.parse(t); } catch(e){ return {status:'error'}; } })
            .then(function(jj){
              if (jj && jj.status === 'success') {
                var selfBtn = btn;
                selfBtn.setAttribute('data-paid', String(next));
                  selfBtn.className = 'btn btn-sm btn-icon rep-paid-toggle ' + (next ? 'btn-success' : 'btn-outline-danger');
                  var ico = selfBtn.querySelector('i');
                  if (ico) { ico.className = 'feather-' + (next ? 'check-circle' : 'x-circle'); }
                  var row = selfBtn.closest('tr');
                  if (row) {
                    var dateCell = row.children[4];
                    dateCell.textContent = next ? (new Date().toLocaleString('tr-TR')) : '-';
                  }
                  recalcRepTotals();
                }
              });
          });
        });
        var repBulkBtn = repContent.querySelector('#repBulkPaidBtn');
        if (repBulkBtn) {
          repBulkBtn.addEventListener('click', function(){
            var rows = [].slice.call(repContent.querySelectorAll('#schedTableBody tr')).filter(function(tr){ return tr.style.display !== 'none'; });
            var items = rows.map(function(tr){
              var siteName = tr.children[0]?.textContent || '';
              var period = tr.children[1]?.textContent || '';
              var amount = parseFloat(tr.children[3]?.textContent || '0');
              var siteId = 0;
              assigns.forEach(function(a){ if (a.site_adi === siteName) { siteId = a.site_id; } });
              return { site_id: siteId, period: period, amount: amount };
            }).filter(function(x){ return x.site_id && x.period; });
            if (!items.length) { return; }
            var fd = new FormData();
            fd.append('action','rep_bulk_mark');
          fd.append('rep_id', rep.id);
          fd.append('paid','1');
          fd.append('items', JSON.stringify(items));
          fetch((window.API_BASE||'') + '/pages/panel/api.php', { method:'POST', body: fd, credentials: 'same-origin' })
            .then(function(r){ return r.text(); })
            .then(function(t){ try { return JSON.parse(t); } catch(e){ return {status:'error'}; } })
            .then(function(jj){
              if (jj && jj.status === 'success') {
                rows.forEach(function(tr){
                  var btnEl = tr.querySelector('.rep-paid-toggle');
                  if (btnEl) {
                      btnEl.setAttribute('data-paid','1');
                      btnEl.className = 'btn btn-sm btn-icon rep-paid-toggle btn-success';
                      var ico = btnEl.querySelector('i'); if (ico) { ico.className = 'feather-check-circle'; }
                    }
                    var dateCell = tr.children[4];
                    if (dateCell) { dateCell.textContent = new Date().toLocaleString('tr-TR'); }
                  });
                  recalcRepTotals();
                }
              });
          });
        }
      });
  }

  document.getElementById('repAddBtn')?.addEventListener('click', function(){
    Swal.fire({
      title: '',
      html: `
        <div class="text-start">
          <div class="mb-3 d-flex align-items-center">
            <span class="avatar avatar-sm me-2 rounded-circle bg-primary text-white fw-bold" style="display:inline-flex;align-items:center;justify-content:center;">T</span>
            <div>
              <div class="fw-bold">Yeni Temsilci</div>
              <div class="text-muted small">Bilgileri doldurup kaydedin</div>
            </div>
          </div>
          <div class="row g-2">
            <div class="col-12">
              <label class="small text-muted">Ad Soyad</label>
              <div class="input-group">
                <span class="input-group-text"><i class="feather-user"></i></span>
                <input type="text" id="repFullName" class="form-control" placeholder="Ad Soyad" maxlength="120">
              </div>
            </div>
            <div class="col-12">
              <label class="small text-muted">Telefon</label>
              <div class="input-group">
                <span class="input-group-text"><i class="feather-phone"></i></span>
                <input type="text" id="repPhone" class="form-control" placeholder="0(5xx) xxx xx xx" maxlength="16">
              </div>
            </div>
            <div class="col-12">
              <label class="small text-muted">E-posta</label>
              <div class="input-group">
                <span class="input-group-text"><i class="feather-mail"></i></span>
                <input type="email" id="repEmail" class="form-control" placeholder="mail@ornek.com">
              </div>
            </div>
            <div class="col-12">
              <label class="small text-muted">Şifre</label>
              <div class="input-group">
                <span class="input-group-text"><i class="feather-lock"></i></span>
                <input type="text" id="repPassword" class="form-control" placeholder="Şifre (en az 6 karakter)">
                <button class="btn btn-outline-secondary" type="button" id="genPassBtn"><i class="feather-zap"></i> Şifre Oluştur</button>
              </div>
            </div>
            <div class="col-12">
              <label class="small text-muted">IBAN</label>
              <div class="input-group">
                <span class="input-group-text"><i class="feather-credit-card"></i></span>
                <input type="text" id="repIban" class="form-control" placeholder="TR00 0000 0000 0000 0000 0000 00" maxlength="29">
              </div>
            </div>
          </div>
        </div>
      `,
      confirmButtonText: 'Kaydet',
      customClass: {
        popup: 'rounded-4',
        confirmButton: 'btn btn-primary',
        cancelButton: 'btn btn-light'
      },
      showCancelButton: true,
      cancelButtonText: 'İptal',
      didOpen: function(){
        try {
          if (window.jQuery && $.fn.mask) {
            $('#repPhone').mask('0(000) 000 00 00');
            $('#repIban').mask('TR00 0000 0000 0000 0000 0000 00');
          } else {
            // Basit fallback: giriş uzunluğunu sınırlama ve otomatik biçimlendirme
            var p = document.getElementById('repPhone');
            if (p) {
              p.addEventListener('input', function(){
                var d = this.value.replace(/\D+/g,'').slice(0,11);
                var out = '';
                if (d.length > 0) out = '0';
                if (d.length > 1) out += '(' + d.slice(1,4);
                if (d.length >= 4) out += ') ' + d.slice(4,7);
                if (d.length >= 7) out += ' ' + d.slice(7,9);
                if (d.length >= 9) out += ' ' + d.slice(9,11);
                this.value = out;
              });
            }
            var ib = document.getElementById('repIban');
            if (ib) {
              ib.addEventListener('input', function(){
                var s = this.value.toUpperCase().replace(/[^A-Z0-9]+/g,'');
                if (!s.startsWith('TR')) s = 'TR' + s.replace(/^TR/,'');
                s = s.slice(0,26);
                var chunks = [s.slice(0,2), s.slice(2,4), s.slice(4,8), s.slice(8,12), s.slice(12,16), s.slice(16,20), s.slice(20,24), s.slice(24,26)].filter(Boolean);
                this.value = chunks.join(' ');
              });
            }
          }
        } catch(e){}
        var genBtn = document.getElementById('genPassBtn');
        if (genBtn) {
          genBtn.addEventListener('click', function(){
            var p = '';
            var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%^&*';
            for (var i=0;i<10;i++){ p += chars[Math.floor(Math.random()*chars.length)]; }
            var el = document.getElementById('repPassword'); if (el) el.value = p;
          });
        }
      },
      preConfirm: function(){
        function isValidEmail(v){ return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); }
        function isValidPhone(v){ var s=v.replace(/\D+/g,''); return /^(0\d{10}|90\d{10})$/.test(s); }
        function isValidIBAN(v){ 
          var s = v.toUpperCase().replace(/\s+/g,''); 
          return /^TR\d{24}$/.test(s); 
        }
        var fn = document.getElementById('repFullName').value.trim();
        var ph = document.getElementById('repPhone').value.trim();
        var em = document.getElementById('repEmail').value.trim();
        var pw = document.getElementById('repPassword').value.trim();
        var ib = document.getElementById('repIban').value.trim().toUpperCase().replace(/\s+/g,'');
        if (!fn) { Swal.showValidationMessage('Ad Soyad zorunludur'); return false; }
        if (!ph || !isValidPhone(ph)) { Swal.showValidationMessage('Telefon formatı geçersiz'); return false; }
        if (!em || !isValidEmail(em)) { Swal.showValidationMessage('E-posta formatı geçersiz'); return false; }
        if (!ib || !isValidIBAN(ib)) { Swal.showValidationMessage('IBAN formatı geçersiz'); return false; }
        var fd = new FormData();
        fd.append('action','rep_create');
        fd.append('full_name', fn);
        fd.append('phone', ph);
        fd.append('email', em);
        fd.append('iban', ib);
        if (pw) { fd.append('password', pw); }
        return fetch((window.API_BASE||'') + '/pages/panel/api.php', { method:'POST', body: fd, credentials: 'same-origin' }).then(function(r){ return r.text(); }).then(function(t){ try { return JSON.parse(t); } catch(e){ return {status:'error'}; } }).then(function(jj){
          if (!jj || jj.status !== 'success') { Swal.showValidationMessage((jj && jj.message) ? jj.message : 'Kayıt başarısız'); return false; }
          return jj;
        });
      }
    }).then(function(res){
      if (res.value && res.value.status === 'success') { loadRepList(); }
    });
  });

  loadRepList();
}); 
</script>
