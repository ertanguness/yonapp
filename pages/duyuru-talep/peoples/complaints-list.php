<?php \App\Services\Gate::authorizeOrDie('complaints_peoples_page'); ?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Duyuru ve Talep</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Şikayet / Öneri</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Back</span>
                </a>
            </div>
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
              
                <a href="#" class="btn btn-primary route-link" data-page="duyuru-talep/peoples/complaints-manage">
                    <i class="feather-plus me-2"></i>
                    <span>Yeni Talep</span>
                </a>
            </div>
        </div>
        <div class="d-md-none d-flex align-items-center">
            <a href="javascript:void(0)" class="page-header-right-open-toggle">
                <i class="feather-align-right fs-20"></i>
            </a>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="container-xl">
    <?php
    $title = "Şikayet / Öneri ";
    $text = "Bu sayfada geçmişte ilettiğiniz şikayet ve önerileri görüntüleyebilir, durumlarını takip edebilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>
        <div class="row row-deck row-cards">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0">Gönderilen Bildirimler</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-vcenter card-table" id="complaintsList">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Başlık</th>
                                        <th>Tür</th>
                                        <th>Durum</th>
                                        <th>Gönderim Tarihi</th>
                                        <th>Cevap</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Cevap Modalı -->
                <div class="modal fade" id="replyModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Yönetici Cevabı</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Cevap Tarihi:</strong> 06.04.2025 - 09:30</p>
                                <p><strong>Cevap İçeriği:</strong></p>
                                <p>Merhaba, öneriniz yönetim kurulunda görüşülmüştür. Uygun bütçe sağlandığında park kurulumuna başlanacaktır.</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
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
  fetch('/pages/duyuru-talep/peoples/api.php?action=complaints_list')
    .then(r=>r.json())
    .then(rows=>{
      const $tb = $('#complaintsList tbody');
      $tb.empty();
      rows.forEach(function(r, idx){
        const typeBadge = r.type === 'Şikayet' ? 'danger' : (r.type === 'Öneri' ? 'info' : 'secondary');
        const statusBadge = r.status === 'Cevaplandı' ? 'success' : (r.status === 'İnceleniyor' ? 'warning' : 'secondary');
        const tr = `<tr>
          <td>${idx+1}</td>
          <td>${r.title}</td>
          <td><span class="badge bg-${typeBadge}">${r.type}</span></td>
          <td><span class="badge bg-${statusBadge}">${r.status}</span></td>
          <td>${r.created_at}</td>
          <td><button class="btn btn-outline-primary btn-sm" disabled><i class="feather-eye me-1"></i> Bekleniyor</button></td>
        </tr>`;
        $tb.append(tr);
      });
      $('#complaintsList').DataTable({
        retrieve:true,
        responsive:true,
        dom:'f t<"row m-2"<"col-md-4"i><"col-md-4"l><"col-md-4 float-end"p>>'
      });
    });
});
</script>
