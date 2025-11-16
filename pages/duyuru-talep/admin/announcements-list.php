<?php \App\Services\Gate::authorizeOrDie('announcements_admin_page'); ?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Duyurular</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Duyurular</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Geri</span>
                </a>
            </div>
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <?php
                require_once 'pages/components/search.php';
                require_once 'pages/components/download.php';
                ?>

                <a href="#" class="btn btn-primary route-link" data-page="duyuru-talep/admin/announcements-manage">
                    <i class="feather-plus me-2"></i>
                    <span>Yeni Duyuru Ekle</span>
                </a>
                <a href="/pages/duyuru-talep/admin/export/announcements.php?format=xlsx" class="btn btn-outline-success">
                    <i class="feather-file-text me-2"></i>
                    <span>Excel</span>
                </a>
                <a href="/pages/duyuru-talep/admin/export/announcements.php?format=pdf" class="btn btn-outline-danger">
                    <i class="feather-file me-2"></i>
                    <span>PDF</span>
                </a>
                <a href="/pages/duyuru-talep/admin/export/announcements.php?format=print" target="_blank" class="btn btn-outline-secondary">
                    <i class="feather-printer me-2"></i>
                    <span>Yazdır</span>
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
    <?php
    $title = "Duyuru Listesi";
    $text = "Yayınlanan duyurularınızı görüntüleyebilir, detaylara ulaşabilir ve düzenleme işlemleri yapabilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover datatables" id="announcementList">
                                    <thead>
                                        <tr class="text-center">
                                            <th>#</th>
                                            <th>Başlık</th>
                                            <th>İçerik</th>
                                            <th>Yayın Süresi</th>
                                            <th>Hedef</th>
                                            <th>Durum</th>
                                            <th>İşlem</th>
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
</div>

<script>
$(function(){
  const $tbl = $('#announcementList');
  if ($tbl.length) {
    $tbl.DataTable({
      retrieve: true,
      responsive: true,
      serverSide: true,
      processing: true,
      ajax: {
        url: '/pages/duyuru-talep/admin/api.php',
        type: 'GET',
        data: function(d){ d.action = 'announcements_datatable'; }
      },
      columns: [
        { data: 0 },
        { data: 1 },
        { data: 2 },
        { data: 3 },
        { data: 4 },
        { data: 5 },
        { data: 6 }
      ],
      dom: 'f t<"row m-2"<"col-md-4"i><"col-md-4"l><"col-md-4 float-end"p>>'
    });
  }
});
</script>
