<?php

use Model\SitelerModel;
use App\Helper\Helper;
use App\Helper\Security;

$sitesModel = new SitelerModel();
$creators = $sitesModel->getCreatorsSummary();

?>
<div class="container-xl">
    <?php
        $title = "Süper Admin Paneli";
        $text = "Kayıtlı siteleri ve yöneticilerini buradan yönetebilirsiniz.";
        require_once 'pages/components/alert.php'
    ?>
    
    <div class="row row-deck row-cards">
        <div class="col-12">
            <div class="card" id="registeredSitesCard">
                <div class="card-header">
                    <h3 class="card-title">Kayıtlı Siteler</h3>
                </div>
                <div class="table-responsive">
                      <table class="table table-hover" id="panelList">
                        <thead>
                            <tr>
                                <th class="w-1">No.</th>
                                <th>Oluşturan</th>
                                <th>Bu Kişinin Toplam Site Sayısı</th>
                                <th>Telefon</th>
                                <th>E-posta</th>
                                <th class="w-1">Detay</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            <?php foreach ($creators as $index => $creator): ?>
                            <tr>
                                <td><span class="text-muted"><?php echo $index + 1; ?></span></td>
                                <td>
                                    <?php echo htmlspecialchars($creator->creator_name ?? '-'); ?>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo (int)($creator->site_count ?? 0); ?></span>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($creator->creator_phone ?? '-'); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($creator->creator_email ?? '-'); ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center">
                                        <button 
                                            class="btn btn-sm btn-outline-primary show-site-detail" 
                                            data-user-id="<?php echo (int)$creator->user_id; ?>" 
                                            data-user-name="<?php echo htmlspecialchars($creator->creator_name ?? ''); ?>">
                                            Detay
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="siteDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Site Detayı</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>
      <div class="modal-body">
        <div id="siteDetailContent">
          <div class="text-center text-muted">Yükleniyor...</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
 </div>
 </div>
 </div>
 </div>
 </div>
<style>
#siteDetailModal .btn,
#siteDetailModal .btn:hover,
#siteDetailModal .btn:active,
#siteDetailModal .btn:focus {
  transition: none !important;
  transform: none !important;
}
#siteDetailModal .btn-icon {
  transition: none !important;
  transform: none !important;
}

#registeredSitesCard { margin-bottom: 5px; } 
.container-xl { padding-bottom: 60px; }
</style>

 <script>
 document.addEventListener('DOMContentLoaded', function(){
    var modalEl = document.getElementById('siteDetailModal');
    var siteDetailContent = document.getElementById('siteDetailContent');
    var bsModal;
    if (modalEl) { bsModal = new bootstrap.Modal(modalEl); }
    function initDT(){
        if (!(window.jQuery && $.fn.DataTable)) return;
        var dt;
        if ($.fn.dataTable.isDataTable('#panelList')) {
            dt = $('#panelList').DataTable();
        } else {
            dt = $('#panelList').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
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
        if (dt) { dt.page.len(10).draw(); }
    }
    function ensureDT(){
        return new Promise(function(resolve){
            if (window.jQuery && $.fn.DataTable) { resolve(); return; }
            var css = document.createElement('link');
            css.rel = 'stylesheet';
            css.href = 'https://cdn.datatables.net/v/bs5/dt-2.3.4/r-3.0.7/datatables.min.css';
            document.head.appendChild(css);
            var s = document.createElement('script');
            s.src = 'https://cdn.datatables.net/v/bs5/dt-2.3.4/r-3.0.7/datatables.min.js';
            s.onload = function(){ resolve(); };
            s.onerror = function(){ resolve(); };
            document.head.appendChild(s);
        });
    }
    ensureDT().then(function(){ initDT(); });
    document.addEventListener('click', function(e){
        var btn = e.target.closest('.show-site-detail');
        if (!btn) return;
        var userId = btn.getAttribute('data-user-id');
        var userName = btn.getAttribute('data-user-name');
            siteDetailContent.innerHTML = '<div class="text-center text-muted">Yükleniyor...</div>';
            bsModal && bsModal.show();
            var fd = new FormData();
            fd.append('action', 'creator_sites');
            fd.append('user_id', userId);
 
            fetch((window.API_BASE||'') + '/pages/panel/api.php', { method:'POST', body: fd, credentials: 'same-origin' })
                .then(function(r){ return r.text(); })
                .then(function(t){ try { return JSON.parse(t); } catch(e){ console.log(t); return {status:'error', message: 'Parse error', raw: t}; } })
                .then(function(j){
                    if (!j || j.status !== 'success') {
                        siteDetailContent.innerHTML = '<div class="text-danger">Detay yüklenemedi. ' + (j.message || '') + '</div>';
                        return;
                    }
                    var d = j.data;
                    var creator = d.creator || {};
                    var list = d.creator_sites || [];
                    var accId = 'creatorSitesAcc';
                    var avatarText = (creator.full_name || '').trim().split(' ').map(function(w){ return w[0] || ''; }).slice(0,2).join('').toUpperCase() || 'U';
                    var totalMonthly = d.total_monthly || 0;
                    var itemsHtml = list.map(function(s, idx){
                        var kt = s.kayit_tarihi ? new Date(s.kayit_tarihi).toLocaleDateString('tr-TR') : '-';
                        var statusClass = (parseInt(s.aktif_mi,10) === 1) ? 'bg-success' : 'bg-danger';
                        var statusText = (parseInt(s.aktif_mi,10) === 1) ? 'Aktif' : 'Pasif';
                        var paidSum = (s.schedule||[]).reduce(function(sum,row){ return sum + (row.paid ? (row.amount||0) : 0); }, 0);
                        var unpaidSum = (s.schedule||[]).reduce(function(sum,row){ return sum + (!row.paid ? (row.amount||0) : 0); }, 0);
                        var totalSum = paidSum + unpaidSum;
                        var aptCount = parseInt(s.apartment_count||0,10);
                        
                        return `
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100">
                                <div class="card-status-top ${statusClass}"></div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h3 class="card-title mb-1 text-truncate" title="${s.site_adi || ''}">${s.site_adi || ''}</h3>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn btn-icon send-msg btn-outline-primary" data-user-id="${d.creator.id}" data-site-id="${s.id}" data-bs-toggle="tooltip" title="Mesaj gönder">
                                                <i class="feather-send"></i>
                                            </button>
                                            <button class="btn btn-icon lock-toggle ${s.locked? 'btn-danger':'btn-outline-secondary'}" data-user-id="${d.creator.id}" data-site-id="${s.id}" data-locked="${s.locked?1:0}" data-bs-toggle="tooltip" title="${s.locked? 'Aç':'Kilitle'}">
                                                <i class="feather-${s.locked? 'lock':'unlock'}"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="text-muted small mb-2">
                                        ${s.il_ad || ''} / ${s.ilce_ad || ''}
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="d-flex align-items-center justify-content-between mb-1 small">
                                             <div class="d-flex align-items-center text-muted">
                                                 <span class="me-2"><i class="feather-phone"></i></span> ${s.telefon || '-'}
                                             </div>
                                             <div class="text-muted">Kayıt: ${kt}</div>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between small">
                                             <div class="d-flex align-items-center text-muted text-truncate" title="${s.eposta || ''}">
                                                 <span class="me-2"><i class="feather-mail"></i></span> ${s.eposta || '-'}
                                             </div>
                                             <div><span class="badge ${statusClass}">${statusText}</span></div>
                                        </div>
                                    </div>
                                    <div class="border-top my-2"></div>

                                    <div class="row g-2 align-items-end">
                                         <div class="col-6">
                                             <label class="small text-muted">Ücret/Daire</label>
                                             <input type="number" step="0.01" min="0" class="form-control form-control-sm unit-fee-input" value="${s.unit_fee || 0}" data-site-id="${s.id}" data-user-id="${d.creator.id}">
                                          </div>
                                          <div class="col-6">
                                              <label class="small text-muted">Başlangıç Tarihi</label>
                                              <input type="date" class="form-control form-control-sm start-date-input" value="${s.start_date || ''}" data-site-id="${s.id}" data-user-id="${d.creator.id}">
                                          </div>
                                          <div class="col-6">
                                              <label class="small text-muted">Vade Günü</label>
                                              <input type="number" min="1" max="31" class="form-control form-control-sm due-day-input" value="${s.due_day || ''}" data-site-id="${s.id}" data-user-id="${d.creator.id}" placeholder="1-31">
                                          </div>
                                          <div class="col-6">
                                              <label class="small text-muted">Gecikme Süresi (gün)</label>
                                              <input type="number" min="0" class="form-control form-control-sm grace-days-input" value="${s.grace_days || ''}" data-site-id="${s.id}" data-user-id="${d.creator.id}" placeholder="örn. 5">
                                          </div>
                                          <div class="col-12">
                                              <div class="small ${s.due_day && s.grace_days ? 'text-success' : 'text-muted'}">
                                                  ${s.due_day && s.grace_days ? 'Gecikme süresi dolunca otomatik kilitleme aktif' : 'Vade günü / gecikme süresi boşsa otomatik kilitleme devre dışı'}
                                              </div>
                                              <div class="border-top my-2"></div>
                                          </div>
                                          <div class="col-12">
                                              <div class="d-flex align-items-center justify-content-between mt-2">
                                                  <div class="text-muted small">Daire Sayısı</div>
                                                  <span class="badge bg-primary apartment-count" data-site-id="${s.id}">${parseInt(s.apartment_count || 0)}</span>
                                              </div>
                                              <div class="d-flex align-items-center justify-content-between mt-1">
                                                  <div class="text-muted small">Aylık Toplam</div>
                                                  <span class="badge bg-success monthly-total" data-site-id="${s.id}">${(s.monthly_total || 0).toFixed ? s.monthly_total.toFixed(2) : (s.monthly_total || 0)}</span>
                                              </div>
                                             <div class="d-flex align-items-center justify-content-between mt-1">
                                                 <div class="text-muted small">Toplam Ödenmiş</div>
                                                 <span class="badge bg-success site-paid-total" data-site-id="${s.id}">${paidSum.toFixed ? paidSum.toFixed(2) : paidSum}</span>
                                             </div>
                                             <div class="d-flex align-items-center justify-content-between mt-1">
                                                 <div class="text-muted small">Toplam Ödenecek</div>
                                                 <span class="badge bg-warning text-dark site-unpaid-total" data-site-id="${s.id}">${unpaidSum.toFixed ? unpaidSum.toFixed(2) : unpaidSum}</span>
                                             </div>
                                             <div class="d-flex align-items-center justify-content-between mt-1">
                                                 <div class="text-muted small">Genel Toplam</div>
                                                 <span class="badge bg-primary site-total-sum" data-site-id="${s.id}">${totalSum.toFixed ? totalSum.toFixed(2) : totalSum}</span>
                                          </div>
                                     </div>
                                    </div>

                                </div>
                                <div class="card-footer bg-light py-2 px-3 small text-muted text-truncate" title="${s.tam_adres || ''}">
                                    <i class="feather-map-pin me-1"></i> ${s.tam_adres || '-'}
                                </div>
                            </div>
                        </div>
                        `;
                    }).join('');
                    
                    var sumMonthly = 0;
                    list.forEach(function(s){
                      var apt = s.apartment_count||0;
                      var fee = s.unit_fee||0;
                      sumMonthly += (parseFloat(fee||0) * parseInt(apt||0,10));
                    });
                    siteDetailContent.innerHTML = `
                        <div class="card mb-4 bg-primary-lt border-0">
                            <div class="card-body py-3">
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-md me-3 rounded-circle bg-primary text-white fw-bold" style="display:inline-flex;align-items:center;justify-content:center;">${avatarText}</span>
                                    <div>
                                        <h4 class="mb-0 text-primary">${creator.full_name || ''}</h4>
                                        <div class="small text-muted">${creator.email || ''} • ${creator.phone || '-'}</div>
                                    </div>
                                    <div class="ms-auto text-end">
                                         <div class="small text-muted">Toplam Site</div>
                                         <div class="h3 mb-0 text-primary">${d.creator_site_count || 0}</div>
                                         <div class="small text-muted mt-2">Aylık Toplam</div>
                                         <div class="h4 mb-0 text-success" id="totalMonthlyAll">${(totalMonthly || 0).toFixed ? totalMonthly.toFixed(2) : (totalMonthly || 0)}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row g-3">
                            ${itemsHtml || '<div class="col-12"><div class="empty text-center p-4 text-muted">Kayıtlı site bulunamadı.</div></div>'}
                        </div>
                       <div class="card mt-3">
                               <div class="card-header d-flex align-items-center">
                                   <div class="card-title">Ödeme Programı (Son 12 Ay)</div>
                                   <div class="ms-auto d-flex align-items-center gap-2 flex-nowrap">
                                       <select class="form-select form-select-sm w-auto" id="scheduleFilter">
                                           <option value="all" selected>Tümü</option>
                                           ${list.map(function(s){ return `<option value="${s.id}">${s.site_adi||''}</option>`; }).join('')}
                                       </select>
                                       <button class="btn btn-sm btn-success" id="bulkMarkPaidBtn">Toplu Ödendi</button>
                                   </div>
                               </div>
                           <div class="card-body">
                               <div class="table-responsive">
                                   <table class="table table-sm">
                                       <thead>
                                           <tr>
                                               <th>Site</th>
                                               <th>Dönem</th>
                                               <th>Tutar</th>
                                               <th>Son Ödeme Günü</th>
                                               <th>Ödeme Günü</th>
                                               <th style="width:110px">İşlemler</th>
                                           </tr>
                                       </thead>
                                       <tbody>
                                           ${list.map(function(s){
                                               return (s.schedule||[]).map(function(row){
                                                   var dParts = row.period.split('-');
                                                   var dueDate = (function(){ try { var ds = new Date(parseInt(dParts[0],10), parseInt(dParts[1],10), 0); return ds.toLocaleDateString('tr-TR'); } catch(e){ return '-'; } })();
                                                   return `
                                                   <tr>
                                                     <td data-site-id="${s.id}">${s.site_adi||''}</td>
                                                     <td>${row.period}</td>
                                                     <td>${row.amount.toFixed ? row.amount.toFixed(2) : row.amount}</td>
                                                     <td>${dueDate}</td>
                                                     <td>${row.paid_at || '-'}</td>
                                                     <td>
                                                        <div class="btn-group btn-group-sm" role="group" aria-label="İşlemler">
                                                          <button class="btn btn-icon toggle-paid ${row.paid? 'btn-success':'btn-outline-danger'}" data-user-id="${d.creator.id}" data-site-id="${s.id}" data-period="${row.period}" data-amount="${row.amount}" data-paid="${row.paid?1:0}" data-bs-toggle="tooltip" title="${row.paid? 'Ödenmedi olarak işaretle':'Ödendi olarak işaretle'}">
                                                            <i class="feather-${row.paid? 'check-circle':'x-circle'}"></i>
                                                          </button>
                                                        </div>
                                                     </td>
                                                   </tr>
                                                   `;
                                               }).join('');
                                           }).join('')}
                                       </tbody>
                                   </table>
                               </div>
                           </div>
                       </div>
                    `;
                    var filterSel = siteDetailContent.querySelector('#scheduleFilter');
                    if (filterSel) {
                        filterSel.addEventListener('change', function(){
                            var val = this.value;
                            siteDetailContent.querySelectorAll('tbody tr').forEach(function(tr){
                                if (val === 'all') { tr.style.display = ''; return; }
                                var td = tr.querySelector('td[data-site-id]');
                                var sid = td ? td.getAttribute('data-site-id') : null;
                                tr.style.display = (sid === val) ? '' : 'none';
                            });
                        });
                    }
                    // Initialize tooltips
                    (function(){
                      var tEls = [].slice.call(siteDetailContent.querySelectorAll('[data-bs-toggle="tooltip"]'));
                      tEls.forEach(function(el){ try { new bootstrap.Tooltip(el); } catch(e){} });
                    })();
                    siteDetailContent.querySelectorAll('.unit-fee-input, .start-date-input, .due-day-input, .grace-days-input').forEach(function(inp){
                        inp.addEventListener('change', function(){
                            var siteId = this.getAttribute('data-site-id');
                            var userId = this.getAttribute('data-user-id');
                            var unitFee = siteDetailContent.querySelector('.unit-fee-input[data-site-id="'+siteId+'"]').value;
                            var startDate = siteDetailContent.querySelector('.start-date-input[data-site-id="'+siteId+'"]').value;
                            var dueDayEl = siteDetailContent.querySelector('.due-day-input[data-site-id="'+siteId+'"]');
                            var graceDaysEl = siteDetailContent.querySelector('.grace-days-input[data-site-id="'+siteId+'"]');
                            var dueDay = dueDayEl ? dueDayEl.value : '';
                            var graceDays = graceDaysEl ? graceDaysEl.value : '';
                            var fd2 = new FormData();
                            fd2.append('action','pricing_set');
                            fd2.append('user_id', userId);
                            fd2.append('site_id', siteId);
                            fd2.append('unit_fee', unitFee);
                            fd2.append('start_date', startDate);
                            if (dueDay !== '') fd2.append('due_day', dueDay);
                            if (graceDays !== '') fd2.append('grace_days', graceDays);
                            fetch((window.API_BASE||'') + '/pages/panel/api.php', { method:'POST', body: fd2, credentials: 'same-origin' })
                               .then(function(rr){ return rr.text(); })
                               .then(function(t){ try { return JSON.parse(t); } catch(e){ return {status:'error'}; } })
                               .then(function(jj){
                                    if (jj && jj.status === 'success') {
                                        var apt = 0;
                                        list.forEach(function(s){ if (String(s.id) === String(siteId)) { apt = s.apartment_count||0; } });
                                        var mt = (parseFloat(unitFee||0) * parseInt(apt||0,10));
                                        var el = siteDetailContent.querySelector('.monthly-total[data-site-id="'+siteId+'"]');
                                        if (el) el.textContent = mt.toFixed(2);
                                        var aptEl = siteDetailContent.querySelector('.apartment-count[data-site-id="'+siteId+'"]');
                                        if (aptEl) aptEl.textContent = String(parseInt(apt||0,10));
                                        var total = 0;
                                       siteDetailContent.querySelectorAll('.monthly-total').forEach(function(b){ total += parseFloat(b.textContent||'0'); });
                                       var allEl = siteDetailContent.querySelector('#totalMonthlyAll');
                                       if (allEl) allEl.textContent = total.toFixed(2);
                                   }
                               });
                        });
                    });
                    var bulkBtn = siteDetailContent.querySelector('#bulkMarkPaidBtn');
                    if (bulkBtn) {
                        bulkBtn.addEventListener('click', function(){
                            var rows = [].slice.call(siteDetailContent.querySelectorAll('tbody tr')).filter(function(tr){ return tr.style.display !== 'none'; });
                            var items = rows.map(function(tr){
                                var siteCell = tr.querySelector('td[data-site-id]');
                                var siteId = siteCell ? siteCell.getAttribute('data-site-id') : null;
                                var period = tr.children[1]?.textContent || '';
                                var amount = parseFloat(tr.children[2]?.textContent || '0');
                                var userId = d.creator.id;
                                return { user_id: userId, site_id: siteId, period: period, amount: amount, paid: 1 };
                            }).filter(function(x){ return x.site_id && x.period; });
                            if (!items.length) { return; }
                            var fdBulk = new FormData();
                            fdBulk.append('action','billing_bulk_mark');
                            fdBulk.append('items', JSON.stringify(items));
                            fetch((window.API_BASE||'') + '/pages/panel/api.php', { method:'POST', body: fdBulk, credentials: 'same-origin' })
                              .then(function(rr){ return rr.text(); })
                              .then(function(t){ try { return JSON.parse(t); } catch(e){ return {status:'error'}; } })
                              .then(function(jj){
                                if (jj && jj.status === 'success') {
                                    siteDetailContent.querySelectorAll('.toggle-paid').forEach(function(btn){
                                        var period = btn.getAttribute('data-period');
                                        var siteId = btn.getAttribute('data-site-id');
                                        var match = items.find(function(it){ return String(it.period)===String(period) && String(it.site_id)===String(siteId); });
                                        if (match) {
                                            btn.setAttribute('data-paid','1');
                                            btn.className = 'btn btn-icon btn-sm toggle-paid btn-success';
                                            var ico = btn.querySelector('i'); if (ico) ico.className = 'feather-check-circle';
                                            btn.setAttribute('title', 'Ödendi olarak işaretle');
                                            try { bootstrap.Tooltip.getInstance(btn)?.dispose(); new bootstrap.Tooltip(btn); } catch(e){}
                                        }
                                    });
                                    var siteSet = new Set(items.map(function(it){ return String(it.site_id); }));
                                    siteSet.forEach(function(sid){
                                        recalcSiteTotals(sid);
                                    });
                                }
                              });
                        });
                    }
                    siteDetailContent.querySelectorAll('.toggle-paid').forEach(function(btn){
                        btn.addEventListener('click', function(){
                            var paid = parseInt(this.getAttribute('data-paid')||'0',10);
                            var nextPaid = paid ? 0 : 1;
                            var userId = this.getAttribute('data-user-id');
                            var siteId = this.getAttribute('data-site-id');
                            var period = this.getAttribute('data-period');
                            var amount = this.getAttribute('data-amount');
                            var fd3 = new FormData();
                            fd3.append('action','billing_mark');
                            fd3.append('user_id', userId);
                            fd3.append('site_id', siteId);
                            fd3.append('period', period);
                            fd3.append('paid', String(nextPaid));
                            fd3.append('amount', amount);
                            var self = this;
                            fetch((window.API_BASE||'') + '/pages/panel/api.php', { method:'POST', body: fd3, credentials: 'same-origin' })
                              .then(function(rr){ return rr.text(); })
                              .then(function(t){ try { return JSON.parse(t); } catch(e){ return {status:'error'}; } })
                              .then(function(jj){
                                if (jj && jj.status === 'success') {
                                    self.setAttribute('data-paid', String(nextPaid));
                                    self.className = 'btn btn-icon btn-sm toggle-paid ' + (nextPaid ? 'btn-success' : 'btn-outline-danger');
                                    var ico = self.querySelector('i');
                                    if (ico) { ico.className = 'feather-' + (nextPaid ? 'check-circle' : 'x-circle'); }
                                    self.setAttribute('title', nextPaid ? 'Ödenmedi olarak işaretle' : 'Ödendi olarak işaretle');
                                    try { bootstrap.Tooltip.getInstance(self)?.dispose(); new bootstrap.Tooltip(self); } catch(e){}
                                    recalcSiteTotals(siteId);
                                }
                              });
                        });
                    });
                    function recalcSiteTotals(siteId){
                        var rows = [].slice.call(siteDetailContent.querySelectorAll('tbody tr')).filter(function(tr){
                            var td = tr.querySelector('td[data-site-id]'); var sid = td ? td.getAttribute('data-site-id') : null;
                            return String(sid) === String(siteId);
                        });
                        var paidSum = 0, unpaidSum = 0;
                        rows.forEach(function(tr){
                            var amt = parseFloat(tr.children[2]?.textContent || '0') || 0;
                            var btn = tr.querySelector('.toggle-paid');
                            var isPaid = btn ? (parseInt(btn.getAttribute('data-paid')||'0',10)===1) : false;
                            if (isPaid) paidSum += amt; else unpaidSum += amt;
                        });
                        var totalSum = paidSum + unpaidSum;
                        var pEl = siteDetailContent.querySelector('.site-paid-total[data-site-id="'+siteId+'"]');
                        var uEl = siteDetailContent.querySelector('.site-unpaid-total[data-site-id="'+siteId+'"]');
                        var tEl = siteDetailContent.querySelector('.site-total-sum[data-site-id="'+siteId+'"]');
                        if (pEl) pEl.textContent = paidSum.toFixed(2);
                        if (uEl) uEl.textContent = unpaidSum.toFixed(2);
                        if (tEl) tEl.textContent = totalSum.toFixed(2);
                    }
                    siteDetailContent.querySelectorAll('.lock-toggle').forEach(function(btn){
                        btn.addEventListener('click', function(){
                            var userId = this.getAttribute('data-user-id');
                            var siteId = this.getAttribute('data-site-id');
                            var locked = parseInt(this.getAttribute('data-locked')||'0',10);
                            var nextLock = locked ? 0 : 1;
                            var fd4 = new FormData();
                            fd4.append('action','lock_toggle');
                            fd4.append('user_id', userId);
                            fd4.append('site_id', siteId);
                            fd4.append('lock', String(nextLock));
                            fd4.append('reason', nextLock===1 ? 'Ödeme gecikmesi' : 'Kilit kaldırıldı');
                            var self = this;
                            fetch((window.API_BASE||'') + '/pages/panel/api.php', { method:'POST', body: fd4, credentials: 'same-origin' }).then(function(rr){ return rr.text(); }).then(function(txt){
                                var jj = null; try { jj = JSON.parse(txt); } catch(e) { jj = null; }
                                if (jj && jj.status === 'success') {
                                    self.setAttribute('data-locked', String(nextLock));
                                    self.className = 'btn btn-icon btn-sm lock-toggle ' + (nextLock? 'btn-danger':'btn-outline-secondary');
                                    var ico = self.querySelector('i');
                                    if (ico) ico.className = 'feather-' + (nextLock? 'lock':'unlock');
                                    self.setAttribute('title', nextLock? 'Aç':'Kilitle');
                                    try { bootstrap.Tooltip.getInstance(self)?.dispose(); new bootstrap.Tooltip(self); } catch(e){}
                                } else {
                                    alert('Kilit işleminde hata: ' + (jj && jj.message ? jj.message : 'Bilinmeyen cevap'));
                                }
                            });
                        });
                    });
                    siteDetailContent.querySelectorAll('.send-msg').forEach(function(btn){
                        btn.addEventListener('click', function(){
                            // Mesaj gönderim UI entegrasyonu ayrı sayfada yapılacak
                            alert('Mesaj gönderme yapılandırması için Süperadmin Ayarları sayfasını kullanın.');
                        });
                    });
                })
                .catch(function(){
                    siteDetailContent.innerHTML = '<div class="text-danger">Detay yüklenemedi.</div>';
                });
    });
 });
 </script>
