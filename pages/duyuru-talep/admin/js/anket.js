;(function(){
  const API_URL = '/pages/duyuru-talep/admin/api/APIAnket.php';

  const SurveyAPI = {
    async list(){
      const r = await fetch(`${API_URL}?action=surveys_list`);
      return r.json();
    },
    async get(id){
      const r = await fetch(`${API_URL}?action=get&id=${encodeURIComponent(id)}`);
      return r.json();
    },
    async create(payload){
      const fd = new FormData();
      fd.append('action','survey_save');
      fd.append('title', payload.title);
      if (payload.description) fd.append('description', payload.description);
      if (payload.start_date) fd.append('start_date', payload.start_date);
      if (payload.end_date) fd.append('end_date', payload.end_date);
      if (payload.status) fd.append('status', payload.status);
      (payload.options||[]).forEach(o=> fd.append('options[]', o));
      const r = await fetch(API_URL, { method:'POST', body: fd });
      return r.json();
    },
    async update(id, payload){
      const fd = new FormData();
      fd.append('action','update');
      fd.append('id', id);
      Object.entries(payload).forEach(([k,v])=>{
        if (k === 'options') { (v||[]).forEach(o=> fd.append('options[]', o)); }
        else if (v !== undefined && v !== null) { fd.append(k, v); }
      });
      const r = await fetch(API_URL, { method:'POST', body: fd });
      return r.json();
    },
    async delete(idEnc){
      const fd = new FormData();
      fd.append('action','delete');
      fd.append('id', idEnc);
      const r = await fetch(API_URL, { method:'POST', body: fd });
      return r.json();
    }
  };

  const SurveyUI = {
    async initList(){
      const rows = await SurveyAPI.list();
      const $tb = $('#surveyList tbody');
      $tb.empty();
      rows.forEach(function(r, idx){
        const statusBadge = r.status === 'Aktif' ? 'success' : (r.status === 'Taslak' ? 'warning' : 'secondary');
        const tr = `<tr>
          <td>${idx+1}</td>
          <td>${r.title}</td>
          <td>${r.created_at ?? ''}</td>
          <td>${r.end_date ?? ''}</td>
          <td><span class="badge bg-${statusBadge}">${r.status}</span></td>
          <td>${r.total_votes ?? '-'}</td>
          <td>
            <div class="btn-group align-items-baseline">
              <a href="/anket-ekle?survey_id=${r.id}" class="btn btn-outline-primary btn-sm route-link"><i class="feather-edit-2"></i> Düzenle</a>
              <button class="btn btn-outline-danger btn-sm btn-del" data-id="${r.id_enc}"><i class="feather-trash-2"></i> Sil</button>
            </div>
          </td>
        </tr>`;
        $tb.append(tr);
      });
      $('#surveyList').DataTable({ retrieve:true, responsive:true, dom:'f t<"row m-2"<"col-md-4"i><"col-md-4"l><"col-md-4 float-end"p>>' });
      $tb.on('click', '.btn-del', async function(){
        const idEnc = this.getAttribute('data-id');
        const ok = await swal.fire({ title:'Silinsin mi?', text:'Bu işlem geri alınamaz', icon:'warning', showCancelButton:true, confirmButtonText:'Evet', cancelButtonText:'Hayır' });
        if (ok.isConfirmed){
          const res = await SurveyAPI.delete(idEnc);
          const title = res.status === 'success' ? 'Başarılı' : 'Hata';
          await swal.fire({ title, text: res.message, icon: res.status });
          if (res.status === 'success') { this.closest('tr').remove(); }
        }
      });
    },
    async initListServerRendered(){
      const $tb = $('#surveyList tbody');
      $('#surveyList').DataTable({ retrieve:true, responsive:true, dom:'f t<"row m-2"<"col-md-4"i><"col-md-4"l><"col-md-4 float-end"p>>' });
      $tb.on('click', '.btn-del', async function(){
        const idEnc = this.getAttribute('data-id');
        const ok = await swal.fire({ title:'Silinsin mi?', text:'Bu işlem geri alınamaz', icon:'warning', showCancelButton:true, confirmButtonText:'Evet', cancelButtonText:'Hayır' });
        if (ok.isConfirmed){
          const res = await SurveyAPI.delete(idEnc);
          const title = res.status === 'success' ? 'Başarılı' : 'Hata';
          await swal.fire({ title, text: res.message, icon: res.status });
          if (res.status === 'success') { this.closest('tr').remove(); }
        }
      });
    },
    async initManage(){
      const addBtn = document.getElementById('addOption');
      const wrapper = document.getElementById('optionsWrapper');
      addBtn.addEventListener('click', () => {
        const div = document.createElement('div');
        div.classList.add('input-group', 'mb-2');
        div.innerHTML = `
          <input type="text" name="options[]" class="form-control" placeholder="Yeni Seçenek" required>
          <button type="button" class="btn btn-outline-danger removeOption">Sil</button>
        `;
        wrapper.appendChild(div);
      });
      wrapper.addEventListener('click', function (e) {
        if (e.target.classList.contains('removeOption')) {
          e.target.closest('.input-group').remove();
        }
      });

      const params = new URLSearchParams(location.search);
      const surveyId = params.get('survey_id');
      if (surveyId) {
        const resp = await SurveyAPI.get(surveyId);
        if (resp.status === 'success') {
          const d = resp.data;
          $('#pollTitle').val(d.title||'');
          $('#pollDescription').val(d.description||'');
          $('#pollStartDate').val((d.start_date||'').substring(0,10));
          $('#pollEndDate').val((d.end_date||'').substring(0,10));
          $('#pollStatus').val(d.status||'Taslak');
          $('#optionsWrapper').empty();
          (d.options||[]).forEach((o)=>{
            const div = document.createElement('div');
            div.classList.add('input-group','mb-2');
            div.innerHTML = `
              <input type="text" name="options[]" value="${o}" class="form-control" placeholder="Seçenek" required>
              <button type="button" class="btn btn-outline-danger removeOption">Sil</button>`;
            wrapper.appendChild(div);
          });
        }
      }

      // Dışarıdaki Kaydet butonunu form submit ile ilişkilendir
      $('#saveSurvey').on('click', function(){
        const form = document.getElementById('pollForm');
        if (form.requestSubmit) { form.requestSubmit(); } else { $('#pollForm').trigger('submit'); }
      });

      $('#pollForm').on('submit', async function(e){
        e.preventDefault();
        const title = $('#pollTitle').val().trim();
        const description = $('#pollDescription').val();
        const start_date = $('#pollStartDate').val();
        const end_date = $('#pollEndDate').val();
        const status = $('#pollStatus').val();
        const options = Array.from(document.querySelectorAll('#optionsWrapper input[name="options[]"]')).map(i=> i.value.trim()).filter(Boolean);

        if (!title || options.length < 2){
          swal.fire({ title:'Eksik Bilgi', text:'Başlık ve en az iki seçenek gerekli', icon:'warning' });
          return;
        }

        let res;
        if (surveyId) {
          res = await SurveyAPI.update(surveyId, { title, description, start_date, end_date, status, options });
        } else {
          res = await SurveyAPI.create({ title, description, start_date, end_date, status, options });
        }
        const titleSw = res.status === 'success' ? 'Başarılı' : 'Hata';
        await swal.fire({ title: titleSw, text: res.message, icon: res.status });
        if (res.status === 'success') { window.location = '/anket-listesi'; }
      });
    }
  };

  window.SurveyAPI = SurveyAPI;
  window.SurveyUI = SurveyUI;
})();