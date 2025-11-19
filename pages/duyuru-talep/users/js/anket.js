;(function(){
  const API_URL = '/pages/duyuru-talep/users/api/APIAnket.php';

  const Api = {
    async list(){
      const r = await fetch(`${API_URL}?action=surveys_to_review`);
      return r.json();
    },
    async detail(id){
      const r = await fetch(`${API_URL}?action=detail&id=${encodeURIComponent(id)}`);
      return r.json();
    },
    async approve(id, comment, selectedOption){
      const fd = new FormData(); fd.append('action','approve'); fd.append('id', id); if (comment) fd.append('comment', comment); if (selectedOption) fd.append('selected_option', selectedOption);
      const r = await fetch(API_URL, { method:'POST', body: fd });
      return r.json();
    },
    async reject(id, comment){
      const fd = new FormData(); fd.append('action','reject'); fd.append('id', id); if (comment) fd.append('comment', comment);
      const r = await fetch(API_URL, { method:'POST', body: fd });
      return r.json();
    },
    async vote(id, selectedOption){
      const fd = new FormData(); fd.append('action','vote'); fd.append('id', id); fd.append('selected_option', selectedOption);
      const r = await fetch(API_URL, { method:'POST', body: fd });
      return r.json();
    }
  };

  const UI = {
    async init(){
      await this.renderList();
      const modal = document.getElementById('surveyDetailModal');
      const bsModal = modal ? new bootstrap.Modal(modal) : null;
      let currentId = null;
      let currentSelected = null;
      $('#userSurveyList').on('click','.btn-detail', async function(){
        const id = this.getAttribute('data-id'); currentId = id;
        const resp = await Api.detail(id);
        if (resp.status === 'success'){
          $('#detailTitle').text(resp.data.title||'');
          $('#detailDesc').text(resp.data.description||'');
          const status = resp.data.status||'Taslak';
          const end = resp.data.end_date||'';
          $('#detailStatusBadge').removeClass('bg-success bg-warning bg-secondary').addClass(status==='Aktif'?'bg-success':(status==='Taslak'?'bg-warning':'bg-secondary')).text(status);
          $('#detailEndDate').text(end);
          const cont = $('#detailOptions'); cont.empty(); currentSelected = null;
          const prev = resp.data.user_vote || null;
          (resp.data.options||[]).forEach((o, idx)=>{
            const id = `opt_${idx}`;
            const checked = (prev && prev===o) ? 'checked' : '';
            cont.append(`<label class="list-group-item d-flex align-items-center gap-2" for="${id}">
              <input type="radio" name="surveyOption" id="${id}" value="${o}" class="form-check-input" ${checked}>
              <span>${o}</span>
            </label>`);
          });
          currentSelected = prev || null;
          cont.on('change','input[name=surveyOption]', function(){ currentSelected = this.value; });
          bsModal && bsModal.show();
        }
      });
      $('#btnApprove').on('click', async function(){
        if (!currentId) return; if (!currentSelected){ await swal.fire({ title:'Seçenek gerekli', text:'Lütfen bir seçenek işaretleyin', icon:'warning' }); return; }
        const res = await Api.vote(currentId, currentSelected);
        const title = res.status==='success'?'Onaylandı':'Hata';
        await swal.fire({ title, text: res.message, icon: res.status });
        if (res.status==='success'){
          const container = $('#voteStats'); const body = $('#voteStatsBody');
            body.empty();
            (res.vote_stats||[]).forEach(function(s){
              const pct = s.percent || 0;
              body.append(`
                <div class="mb-2">
                  <div class="d-flex justify-content-between small mb-1"><span>${s.option}</span><span>%${pct}</span></div>
                  <div class="progress" style="height: 8px;">
                    <div class="progress-bar" role="progressbar" style="width:${pct}%"></div>
                  </div>
                </div>
              `);
            });
            container.show();
        }
        UI.renderList();
      });
      $('#btnClose').on('click', function(){ bsModal && bsModal.hide(); });
    },
    async renderList(){
      const out = await Api.list();
      const rows = out.data || [];
      const $tb = $('#userSurveyList tbody'); $tb.empty();
      rows.forEach((r, i)=>{
        const statusBadge = r.status==='Onay Bekliyor'?'warning':(r.status==='Aktif'||r.status==='Yayında'?'success':'secondary');
        const tr = `<tr>
          <td>${i+1}</td>
          <td>${r.title}</td>
          <td><span class="badge bg-${statusBadge}">${r.status}</span></td>
          <td>${r.end_date||''}</td>
          <td>${r.approved}</td>
          <td>${r.rejected}</td>
          <td>
            <div class="btn-group">
              <button class="btn btn-outline-primary btn-sm btn-detail" data-id="${r.id}"><i class="feather-eye"></i> İncele</button>
            </div>
          </td>
        </tr>`;
        $tb.append(tr);
      });
    },
    
  };

  window.UserSurvey = UI;
})();