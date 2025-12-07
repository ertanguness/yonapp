;(function(){
  const API_URL = '/pages/site-sakini/api/APIAnket.php';

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
    },
    async renderList(){
      const out = await Api.list();
      const rows = out.data || [];
      const $tb = $('#userSurveyList tbody'); $tb.empty();
      rows.forEach((r, i)=>{
        const eff = r.status_effective || r.status || '';
        const statusBadge = eff==='Aktif'?'success':(eff==='Beklemede'?'warning':'secondary');
        const tr = `<tr>
          <td>${i+1}</td>
          <td>${r.title}</td>
          <td><span class="badge bg-${statusBadge}">${eff}</span></td>
          <td>${r.end_date||''}</td>
          <td>${r.approved}</td>
          <td>${r.rejected}</td>
          <td>
            <div class="btn-group">
              <a class="btn btn-outline-primary btn-sm" href="/sakin/anketler/${r.id}"><i class="feather-eye"></i> İncele</a>
            </div>
          </td>
        </tr>`;
        $tb.append(tr);
      });
    },

  };

  window.UserSurvey = UI;
})();
