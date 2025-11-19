<?php 
use App\Controllers\AuthController;
AuthController::checkAuthentication();
?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Şikayet / Önerilerim</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Taleplerim</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <a href="index?p=sakin/sikayet-oneri-ekle" class="btn btn-primary">
            <i class="feather-plus me-2"></i> Yeni Talep Ekle
        </a>
    </div>
    </div>

<div class="main-content">
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <h5 class="card-title mb-0">Gönderdiğim Talepler</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-vcenter card-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Başlık</th>
                                            <th>İçerik</th>
                                            <th>Durum</th>
                                            <th>Oluşturulma</th>
                                        </tr>
                                    </thead>
                                    <tbody id="userComplaintsBody"></tbody>
                                </table>
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
    const tbody = $('#userComplaintsBody');
    tbody.empty();
    let i=1;
    rows.forEach(r=>{
      const contentShort = (r.content || '').length > 80 ? (r.content.substring(0, 77) + '...') : (r.content || '-');
      const tr = `
        <tr>
          <td>${i++}</td>
          <td>${r.title ?? '-'}</td>
          <td>${contentShort}</td>
          <td><span class="badge bg-secondary">${r.status ?? 'Yeni'}</span></td>
          <td>${r.created_at ?? '-'}</td>
        </tr>`;
      tbody.append(tr);
    });
  }

  function loadList(){
    fetch('/pages/duyuru-talep/users/api/APISikayet_oneri.php?action=list')
      .then(r=>r.json())
      .then(data=>{
        if(data.status==='success'){
          renderRows(data.data || []);
        }
      });
  }

  loadList();
});
</script>