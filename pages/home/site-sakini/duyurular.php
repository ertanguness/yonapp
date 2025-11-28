<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Duyurular</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/sakin/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Duyurular</li>
        </ul>
    </div>
    </div>

<div class="main-content">
    <div class="card rounded-3">
        <div class="card-body">
            <div id="duyuruList" class="row g-3"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    var list = document.getElementById('duyuruList');
    if (!list) return;
    fetch('/pages/duyuru-talep/admin/api/APIDuyuru.php?datatables=1')
        .then(function(r){ return r.ok ? r.json() : null; })
        .then(function(json){
            list.innerHTML = '';
            if (!json || !json.data || !Array.isArray(json.data) || json.data.length === 0) {
                var empty = document.createElement('div');
                empty.className = 'col-12';
                empty.innerHTML = '<div class="alert alert-info">Duyuru bulunamadı.</div>';
                list.appendChild(empty);
                return;
            }
            json.data.forEach(function(item){
                var col = document.createElement('div');
                col.className = 'col-12';
                var card = document.createElement('div');
                card.className = 'card rounded-3 shadow-sm';
                var body = document.createElement('div');
                body.className = 'card-body';
                var top = document.createElement('div');
                top.className = 'd-flex align-items-center justify-content-between mb-2';
                var badge = document.createElement('span');
                badge.className = 'badge bg-soft-primary text-primary';
                badge.textContent = 'Genel';
                top.appendChild(badge);
                var date = document.createElement('small');
                date.className = 'text-muted';
                date.textContent = item.baslangic_tarihi || '';
                top.appendChild(date);
                var title = document.createElement('h6');
                title.className = 'fw-bold mb-1';
                title.textContent = item.baslik || 'Duyuru';
                var summary = document.createElement('p');
                summary.className = 'mb-0 text-muted';
                summary.textContent = (item.icerik || '').substring(0, 160);
                body.appendChild(top);
                body.appendChild(title);
                body.appendChild(summary);
                var actions = document.createElement('div');
                actions.className = 'mt-3 d-flex gap-2';
                actions.innerHTML = '<a href="/duyuru-listesi" class="btn btn-light btn-sm">Detay</a>';
                body.appendChild(actions);
                card.appendChild(body);
                col.appendChild(card);
                list.appendChild(col);
            });
        })
        .catch(function(){});
});
</script>