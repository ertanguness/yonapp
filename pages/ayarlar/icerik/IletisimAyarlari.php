<div class="card-body personal-info mb-4">
    <h6 class="mb-4">İletişim Ayarları</h6>
    <div class="row mb-3 align-items-center">
        <div class="col-lg-4">
            <input type="text" class="form-control" id="commNewName" placeholder="Yeni ayar adı (örn: personel bildirimi)">
        </div>
        <div class="col-lg-3">
            <button type="button" class="btn btn-primary" id="commAddBtn">Ekle</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table" id="communicationsTable">
            <thead>
                <tr>
                    <th>Ayar Adı</th>
                    <th>E-posta</th>
                    <th>SMS</th>
                    <th>WhatsApp</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<style>
    #communicationsTable th,#communicationsTable td{vertical-align:middle}
</style>
<script>
    (function(){
        function slugify(s){return String(s||'').toLowerCase().trim().replace(/[^a-z0-9çğıöşü\s-]/g,'').replace(/\s+/g,'_').replace(/ı/g,'i').replace(/ş/g,'s').replace(/ç/g,'c').replace(/ğ/g,'g').replace(/ö/g,'o').replace(/ü/g,'u');}
        function rowHtml(key,label,data){
            var emailOn = String((data&&data.email)||'0')==='1';
            var smsOn = String((data&&data.sms)||'0')==='1';
            var waOn = String((data&&data.whatsapp)||'0')==='1';
            return '<tr data-key="'+key+'">'
                +'<td class="fw-semibold">'+label+'</td>'
                +'<td><div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input comm-toggle" id="'+key+'_email" data-channel="email" '+(emailOn?'checked':'')+'><label class="custom-control-label" for="'+key+'_email">Aktif</label></div></td>'
                +'<td><div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input comm-toggle" id="'+key+'_sms" data-channel="sms" '+(smsOn?'checked':'')+'><label class="custom-control-label" for="'+key+'_sms">Aktif</label></div></td>'
                +'<td><div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input comm-toggle" id="'+key+'_whatsapp" data-channel="whatsapp" '+(waOn?'checked':'')+'><label class="custom-control-label" for="'+key+'_whatsapp">Aktif</label></div></td>'
                +'<td><button type="button" class="btn btn-sm btn-outline-danger comm-delete">Sil</button></td>'
            +'</tr>';
        }
        function loadList(){
            fetch('/pages/ayarlar/api.php?action=iletisim_list',{method:'GET'})
                .then(function(r){return r.json();})
                .then(function(d){
                    var items = d.items||[];
                    var tbody = document.querySelector('#communicationsTable tbody');
                    tbody.innerHTML='';
                    items.forEach(function(it){
                        tbody.insertAdjacentHTML('beforeend', rowHtml(it.key, it.label, it));
                    });
                });
        }
        document.addEventListener('change', function(e){
            if (!e.target.classList.contains('comm-toggle')) return;
            var tr = e.target.closest('tr');
            var key = tr.getAttribute('data-key');
            var channel = e.target.getAttribute('data-channel');
            var val = e.target.checked ? '1' : '0';
            var fd = new URLSearchParams();
            fd.append('action','iletisim_toggle');
            fd.append('key',key);
            fd.append('channel',channel);
            fd.append('value',val);
            fetch('/pages/ayarlar/api.php',{method:'POST', body:fd});
        });
        document.addEventListener('click', function(e){
            if (e.target.id==='commAddBtn'){
                var name = document.getElementById('commNewName').value || 'personel bildirimi';
                var key = slugify(name);
                var fd = new URLSearchParams();
                fd.append('action','iletisim_upsert');
                fd.append('key',key);
                fd.append('label',name);
                fetch('/pages/ayarlar/api.php',{method:'POST', body:fd})
                    .then(function(r){return r.json();})
                    .then(function(){ loadList(); document.getElementById('commNewName').value=''; });
            }
            if (e.target.classList.contains('comm-delete')){
                var tr = e.target.closest('tr');
                var key = tr.getAttribute('data-key');
                var fd = new URLSearchParams();
                fd.append('action','iletisim_delete');
                fd.append('key',key);
                fetch('/pages/ayarlar/api.php',{method:'POST', body:fd})
                    .then(function(){ loadList(); });
            }
        });
        loadList();
    })();
</script>
