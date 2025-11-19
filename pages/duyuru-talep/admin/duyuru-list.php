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

                <a href="/duyuru-ekle" class="btn btn-primary">
                    <i class="feather-plus me-2"></i>
                    <span>Yeni Duyuru Ekle</span>
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
                                            <th>Başlangıç Tarihi</th>
                                            <th>Bitiş Tarihi</th>
                                            <th>Durum</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $Announcements = new \Model\DuyuruModel();
                                        $rows = $Announcements->all();
                                        foreach ($rows as $row):
                                            $icerikOzet = mb_strimwidth(strip_tags($row->icerik ?? ''), 0, 100, '...');
                                            ?>
                                            <tr>
                                                <td class="text-center"><?= (int)$row->id ?></td>
                                                <td><?= htmlspecialchars($row->baslik ?? '') ?></td>
                                                <td><?= htmlspecialchars($icerikOzet) ?></td>
                                                <td class="text-center"><?= htmlspecialchars($row->baslangic_tarihi ?? '') ?></td>
                                                <td class="text-center"><?= htmlspecialchars($row->bitis_tarihi ?? '') ?></td>
                                                <td class="text-center"><span class="badge bg-secondary text-uppercase"><?= htmlspecialchars($row->durum ?? '') ?></span></td>
                                                <td class="text-center">
<div class="hstack gap-2">
                                                        <a href="javascript:void(0);" class="avatar-text avatar-md duyuru-goruntule" data-id="<?= $enc_id ?>">
                                                            <i class="feather-eye"></i>
                                                        </a>
                                                        <a href="duyuru-duzenle/<?php echo $enc_id; ?>" class="avatar-text avatar-md duyuru-duzenle" title="Düzenle">
                                                            <i class="feather-edit"></i>
                                                        </a>
                                                        <a href="javascript:void(0);"  data-id="<?php echo $enc_id; ?>" class="avatar-text avatar-md duyuru-sil" data-id="<?php echo $enc_id; ?>" data-name="<?php echo $adi_soyadi; ?>">
                                                            <i class="feather-trash-2"></i>
                                                        </a>
                                                    </div>

                                                   
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
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
function initDuyuruList(){
  var $tbl = $('#announcementList');
  if($tbl.length){
    $tbl.DataTable({
      retrieve: true,
      responsive: true,
      processing: false,
      serverSide: false,
      autoWidth: false,
      dom: 'f t<"row m-2"<"col-md-4"i><"col-md-4"l><"col-md-4 float-end"p>>',
      order: [[0,'desc']],
      initComplete: function(settings){
        var api = this.api();
        if (typeof window.attachDtColumnSearch === 'function') {
          window.attachDtColumnSearch(api, settings.sTableId);
        } else {
          var $thead = $(api.table().header());
          var colCount = $thead.find('th').length;
          var $row = $('<tr class="search-input-row"></tr>');
          for (var i=0; i<colCount; i++) {
            var $th = $('<th class="search"></th>');
            if (i === colCount-1) { $th.html(''); }
            else {
              var $inp = $('<input type="text" class="form-control form-control-sm" placeholder="Ara" />');
              (function(ci, $input){
                $input.on('keyup change', function(){
                  var val = $(this).val();
                  api.column(ci).search(val).draw();
                });
              })(i, $inp);
              $th.append($inp);
            }
            $row.append($th);
          }
          $thead.append($row);
        }
        api.columns.adjust().responsive.recalc();
      }
    });
    $tbl.on('click','.duyuru-sil',function(){
      var id = $(this).data('id');
      swal.fire({ title:'Emin misiniz?', text:'Bu işlem geri alınamaz', icon:'warning', showCancelButton:true, confirmButtonText:'Sil', cancelButtonText:'Vazgeç' })
        .then(function(res){ if(!res.isConfirmed) return; fetch('/pages/duyuru-talep/admin/api/APIDuyuru.php', { method:'DELETE', headers:{ 'Content-Type':'application/x-www-form-urlencoded' }, body: new URLSearchParams({ id: String(id) }) })
          .then(function(r){ return r.json(); })
          .then(function(data){ var title = data.status==='success' ? 'Başarılı' : 'Hata'; swal.fire({ title, text:data.message, icon:data.status }); if(data.status==='success'){ location.reload(); } });
        });
    });
  }
}
//(function waitForJQ(){ if(typeof window.$==='function'){ $(initDuyuruList); } else { setTimeout(waitForJQ,100); } })();
</script>
