<?php 
use App\Services\Gate;
use Model\SikayetOneriModel;

Gate::authorizeOrDie('announcements_admin_page'); 


$model = new SikayetOneriModel();

?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Şikayet / Öneri</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Yönetim</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="d-flex align-items-center gap-2">
            <a href="#" class="btn btn-outline-secondary route-link" data-page="duyuru-talep/admin/announcements-list">
                <i class="feather-arrow-left me-2"></i> Geri
            </a>
        </div>
    </div>
    </div>

<div class="main-content">
    <?php
    $title = "Şikayet ve Önerilesr";
    $text = "Kullanıcı taleplerini görüntüleyin, durum güncelleyin ve yanıt yazın.";
    require_once 'pages/components/alert.php';
   
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <h5 class="card-title mb-0">Gönderilen Talepler</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-vcenter card-table datatable" id="tableSikayetOneri">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Başlık</th>
                                            <th>Kullanıcı</th>
                                            <th>Tür</th>
                                            <th>Durum</th>
                                            <th>Gönderim Tarihi</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableSikayetOneriBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="updateModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Durum Güncelle / Yanıt Yaz</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" id="cmpId">
                                    <div class="mb-3">
                                        <label class="form-label">Durum</label>
                                        <select class="form-select" id="cmpStatus">
                                            <option value="Yeni">Yeni</option>
                                            <option value="İnceleniyor">İnceleniyor</option>
                                            <option value="Cevaplandı">Cevaplandı</option>
                                            <option value="Kapandı">Kapandı</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Cevap Mesajı</label>
                                        <textarea class="form-control" id="cmpReply" rows="4" placeholder="Yanıtınız"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                                    <button type="button" class="btn btn-primary" id="saveUpdate">Kaydet</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(function(){
  function renderRows(rows){
    const tbody = $('#tableSikayetOneriBody');
    tbody.empty();
    let i = 1;
    rows.forEach(r => {
      const typeBadge = r.type === 'Şikayet' ? 'danger' : (r.type === 'Öneri' ? 'info' : 'secondary');
      const statusBadge = r.status === 'Cevaplandı' ? 'success' : (r.status === 'İnceleniyor' ? 'warning' : 'secondary');
      const replyAttr = (r.reply_message || '').replace(/"/g, '&quot;');
      const tr = `
        <tr>
          <td>${i++}</td>
          <td>${r.title ?? '-'}</td>
          <td>${r.kisi_id ?? '-'}</td>
          <td><span class="badge bg-${typeBadge}">${r.type ?? '-'}</span></td>
          <td><span class="badge bg-${statusBadge}">${r.status ?? '-'}</span></td>
          <td>${r.created_at ?? '-'}</td>
          <td>
            <button class="btn btn-primary btn-sm do-update" data-id="${r.id || 0}" data-status="${r.status || ''}" data-reply="${replyAttr}">
              <i class="feather-edit me-1"></i> Güncelle
            </button>
            <button class="btn btn-outline-danger btn-sm do-delete" data-id="${r.id || 0}">
              <i class="feather-trash-2 me-1"></i> Sil
            </button>
          </td>
        </tr>`;
      tbody.append(tr);
    });
  }

  function loadList(){
    fetch('/pages/duyuru-talep/admin/api/APIsikayet_oneri.php?action=list')
      .then(r=>r.json())
      .then(data=>{
        if(data.status==='success'){
          renderRows(data.data || []);
        }
      });
  }

  loadList();

  $(document).off('click','.do-update').on('click','.do-update', function(){
    const id = $(this).data('id');
    const status = $(this).data('status');
    const reply = $(this).data('reply') || '';
    $('#cmpId').val(id);
    $('#cmpStatus').val(status);
    $('#cmpReply').val(reply);
    const m = new bootstrap.Modal(document.getElementById('updateModal'));
    m.show();
  });

  $('#saveUpdate').on('click', function(){
    const id = $('#cmpId').val();
    const status = $('#cmpStatus').val();
    const reply = $('#cmpReply').val();
    const fd = new FormData();
    fd.append('action','complaint_update');
    fd.append('id', id);
    fd.append('status', status);
    fd.append('reply_message', reply);
    fetch('/pages/duyuru-talep/admin/api/APIsikayet_oneri.php', { method:'POST', body: fd })
      .then(r=>r.json())
      .then(data=>{
        var title = data.status === 'success' ? 'Başarılı' : 'Hata';
        swal.fire({ title, text: data.message, icon: data.status, confirmButtonText: 'Tamam' });
        if (data.status === 'success') {
          loadList();
        }
      });
  });

  $(document).off('click','.do-delete').on('click','.do-delete', function(){
    const id = $(this).data('id');
    swal.fire({
      title:'Silinsin mi?',
      text:'Bu işlem geri alınamaz',
      icon:'warning',
      showCancelButton:true,
      confirmButtonText:'Evet, sil',
      cancelButtonText:'Vazgeç'
    }).then(res=>{
      if(!res.isConfirmed) return;
      const fd = new FormData();
      fd.append('action','delete');
      fd.append('id', id);
      fetch('/pages/duyuru-talep/admin/api/APIsikayet_oneri.php', { method:'POST', body: fd })
        .then(r=>r.json())
        .then(data=>{
          var title = data.status === 'success' ? 'Başarılı' : 'Hata';
          swal.fire({ title, text: data.message, icon: data.status, confirmButtonText: 'Tamam' });
          if (data.status === 'success') { loadList(); }
        });
    });
  });
});
</script>
