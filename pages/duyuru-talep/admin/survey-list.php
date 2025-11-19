<?php \App\Services\Gate::authorizeOrDie('survey_admin_page'); ?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Anket Listesi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Anket Yönetimi</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">   
            <a href="#" class="btn btn-primary route-link" data-page="duyuru-talep/admin/survey-manage">
                    <i class="feather-plus me-2"></i>
                    <span>Yeni Anket Oluştur</span>
                </a>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body table-responsive">
                            <table class="table table-hover table-bordered align-middle" id="surveyList">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Başlık</th>
                                        <th>Bitiş Tarihi</th>
                                        <th>Durum</th>
                                        <th>Toplam Oy</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(function(){
  fetch('/pages/duyuru-talep/admin/api.php?action=surveys_list')
    .then(r=>r.json())
    .then(rows=>{
      const $tb = $('#surveyList tbody');
      $tb.empty();
      rows.forEach(function(r, idx){
        const statusBadge = r.status === 'Aktif' ? 'success' : 'secondary';
        const tr = `<tr>
          <td>${idx+1}</td>
          <td>${r.title}</td>
          <td>${r.end_date ?? ''}</td>
          <td><span class="badge bg-${statusBadge}">${r.status}</span></td>
          <td>-</td>
          <td>
            <div class="btn-group align-items-baseline">
              <a href="#" class="btn btn-outline-info btn-sm route-link" data-page="duyuru-talep/admin/survey-result/${r.id}"><i class="feather-bar-chart-2"></i> Sonuçlar</a>
            </div>
          </td>
        </tr>`;
        $tb.append(tr);
      });
      $('#surveyList').DataTable({ retrieve:true, responsive:true, dom:'f t<"row m-2"<"col-md-4"i><"col-md-4"l><"col-md-4 float-end"p>>' });
    });
});
</script>
