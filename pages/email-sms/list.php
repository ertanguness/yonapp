<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Gönderilen Bildirimler</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Bildirimler</li>
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
              
                <a href="javascript:void(0)" class="btn btn-primary mail-gonder">
                    <i class="feather-mail me-2"></i>
                    <span>Yeni Email</span>
                </a>
                <a href="#" class="btn btn-simple btn-secondary sms-gonder">
                    <i class="feather-smartphone me-2"></i>
                    <span>Yeni Sms</span>
                </a>
                <div class="dropdown">
                    <a class="btn btn-icon btn-light-brand" data-bs-toggle="dropdown" data-bs-offset="0, 12" data-bs-auto-close="outside" aria-expanded="false">
                        <i class="feather-paperclip"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="javascript:void(0);" class="dropdown-item js-export-notifications" data-format="csv">
                            <i class="feather-file-text me-3"></i>
                            <span>CSV olarak indir</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item js-export-notifications" data-format="json">
                            <i class="feather-code me-3"></i>
                            <span>JSON olarak indir</span>
                        </a>
                    </div>
                </div>
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
    $title = "Gönderilen Bildirimler";
    $text = "Gönderilen SMS ve e-posta bildirimlerinizi görüntüleyebilir, detaylarına ulaşabilir ve filtreleme işlemleri yapabilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover datatables" id="notificationsList">
                                    <thead>
                                        <tr class="text-center">
                                            <th>Sıra</th>
                                            <th>Bildirim Türü</th>
                                            <th>Kime Gönderildi</th>
                                            <th>Konu Başlığı</th>
                                            <th>Mesaj</th>
                                            <th>Gönderim Tarihi</th>
                                            <th>Durum</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Veritabanından dinamik olarak doldurulacak -->
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
<div class="modal fade-scale" id="notificationDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bildirim Detayı</h5>
                <a href="javascript:void(0)" class="avatar-text avatar-md bg-soft-danger close-icon" data-bs-dismiss="modal">
                    <i class="feather-x text-danger"></i>
                </a>
            </div>
            <div class="modal-body" id="notifDetailBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
    </div>


<script>
    function onDataTablesReady(cb){
        var tries = 0;
        (function wait(){
            if (window.jQuery && jQuery.fn && jQuery.fn.DataTable) { cb(); return; }
            if (tries++ > 50) { console.error('DataTables yüklenemedi'); return; }
            setTimeout(wait, 100);
        })();
    }

    // Arama inputları için app.js fonksiyonunu bekle

    onDataTablesReady(function(){
        function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
        function toText(html){ var d=document.createElement('div'); d.innerHTML=String(html||''); return (d.textContent||d.innerText||''); }
        $('#notificationsList').DataTable({
            retrieve: true,
            ajax: {
                url: '/pages/email-sms/api/notifications_list.php',
                type: 'GET',
                dataSrc: '',
                error: function(xhr, status, err){
                    console.error('Bildirimler yüklenirken hata:', status, err, xhr.status, xhr.responseText);
                    Swal.fire({ title: 'Hata', text: 'Bildirimler yüklenemedi.', icon: 'error' });
                }
            },
            serverSide: false,
            processing: true,
            responsive: true,
            autoWidth: false,
            dom: 't<"row m-2"<"col-md-4"i><"col-md-4"l><"col-md-4 float-end"p>>',
            language: {
                emptyTable: "Gösterilecek kayıt yok",
                info: "Toplam _TOTAL_ kayıt içerisinden _START_ - _END_ gösteriliyor",
                infoEmpty: "Kayıt yok",
                lengthMenu: "Göster _MENU_ kayıt",
                loadingRecords: "Yükleniyor...",
                processing: "Yükleniyor...",
                search: "Ara:",
                paginate: { previous: "Önceki", next: "Sonraki" }
            },
            
            order: [[0,'desc']],
            columns: [
                { data: 'id' },
                { data: 'type', render: function(d){ var i=d==='sms'?'feather-smartphone':'feather-mail'; var c=d==='sms'?'bg-soft-success text-success':'bg-soft-primary text-primary'; return '<span class="badge '+c+'"><i class="'+i+' me-1"></i>'+esc(d||'')+'</span>'; } },
                { data: 'recipients', render: function(d){ var arr; try{ arr=JSON.parse(d); }catch(e){ arr=d?[d]:[]; } if(!Array.isArray(arr)) arr=d?[d]:[]; var shown=arr.slice(0,3).map(function(s){ return '<span class="notif-chip-sm" title="'+esc(s)+'">'+esc(s)+'</span>'; }).join(' '); var more=arr.length>3? '<span class="notif-chip-sm" title="'+esc(arr.join(', '))+'">+'+(arr.length-3)+'</span>':''; return shown+(more?(' '+more):''); } },
                { data: 'subject', render: function(d){ return '<div class="truncate-200 fw-semibold">'+esc(d||'—')+'</div>'; } },
                { data: 'message', render: function(d){ var t=toText(d||''); return '<div class="truncate-300 text-muted">'+esc(t||'')+'</div>'; } },
                { data: 'created_at', render: function(d){ var val=String(d||''); var pretty=val.replace('T',' ').replace(/\.\d+Z?$/,''); return '<span class="text-muted">'+esc(pretty)+'</span>'; } },
                { data: 'status', render: function(d){ var s=String(d||'').toLowerCase(); var ok=(s==='success'||s==='başarılı'||s==='00'); var cls=ok?'bg-soft-success text-success':'bg-soft-danger text-danger'; var txt=ok?'Başarılı':'Hata'; return '<span class="badge '+cls+'">'+txt+'</span>'; } },
                { data: null, render: function(row){ return '<button class="btn btn-sm btn-outline-secondary btn-detail-notification">Detay</button>'; } }
            ],
            initComplete: function (settings, json) {
                var api = this.api();
                var tableId = settings.sTableId;
                var tries = 0;
                (function waitAttach(){
                    if (typeof window.attachDtColumnSearch === 'function') {
                        window.attachDtColumnSearch(api, tableId);
                        api.columns.adjust().responsive.recalc();
                        return;
                    }
                    if (tries++ > 50) { api.columns.adjust().responsive.recalc(); return; }
                    setTimeout(waitAttach, 100);
                })();
            },
            drawCallback: function(){ $('[data-bs-toggle="tooltip"]').each(function(){ var tt=bootstrap.Tooltip.getInstance(this); if(tt) tt.dispose(); bootstrap.Tooltip.getOrCreateInstance(this); }); }
        });

        $(document).on('click', '.js-export-notifications', function(e){
            e.preventDefault();
            var fmt = $(this).data('format') || 'csv';
            var dt = $('#notificationsList').DataTable();
            var params = { format: fmt };
            var globalSearch = dt.search();
            if (!globalSearch) {
                var $g = $('#notificationsList_filter input');
                if ($g.length) globalSearch = $g.val();
            }
            if (globalSearch) params.q = globalSearch;
            var cols = ['id','type','recipients','subject','message','created_at','status'];
            cols.forEach(function(name, idx){
                var s = dt.column(idx).search();
                if (!s) {
                    var $inp = $('#notificationsList thead .search-input-row th').eq(idx).find('input');
                    if ($inp.length) s = $inp.val();
                }
                if (s) params['f_' + name] = s;
            });
            var url = '/pages/email-sms/api/notifications_export.php?' + $.param(params);
            window.open(url, '_blank');
        });
    });
</script>

<style>
    #notificationDetail .message-box{ white-space: pre-wrap; }
    #notificationDetail .notif-block{ border:1px solid #e9ecef; border-radius:8px; }
    #notificationDetail .notif-chip{ background:#f8f9fa; border:1px solid #e9ecef; border-radius:16px; padding:6px 10px; font-size:12px; }
    .notif-chip-sm{ background:#f8f9fa; border:1px solid #e9ecef; border-radius:12px; padding:3px 8px; font-size:11px; }
    .truncate-200{ max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .truncate-300{ max-width:300px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    #notificationsList thead .search-input-row th{ padding:6px; background:#f8f9fa; border-bottom:1px solid #e9ecef; }
    #notificationsList thead .search-input-row input{ height:34px; }
    .dataTables_wrapper .dataTables_filter input{ border-radius:6px; }
</style>
<script>
    function escapeHtml(s){
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
    }
    $(document).on('click','.btn-detail-notification',function(){
        var dt = $('#notificationsList').DataTable();
        var data = dt.row($(this).closest('tr')).data() || {};
        var type = data.type || '';
        var subject = data.subject || '';
        var status = data.status || '';
        var created = data.created_at || '';
        var recRaw = data.recipients || '';
        var recArr;
        try{ recArr = JSON.parse(recRaw); } catch(e){ recArr = recRaw ? [recRaw] : []; }
        if(!Array.isArray(recArr)) recArr = [recArr];
        var chips = recArr.filter(Boolean).map(function(s){ return '<span class="notif-chip">'+escapeHtml(s)+'</span>'; }).join(' ');
        var message = data.message || '';
        var icon = (type === 'sms') ? 'feather-smartphone' : 'feather-mail';
        var avatarClass = (type === 'sms') ? 'bg-soft-success' : 'bg-soft-primary';
        var statusBadge = (status === 'success') ? '<span class="badge bg-soft-success text-success">Başarılı</span>' : '<span class="badge bg-soft-danger text-danger">Hata</span>';
        var html = ''+
            '<div class="d-flex align-items-center gap-3 mb-3">'+
                '<span class="avatar-text avatar-lg '+avatarClass+'"><i class="'+icon+'"></i></span>'+
                '<div>'+
                    '<div class="fw-bold fs-16 text-capitalize">'+escapeHtml(type)+' bildirimi</div>'+
                    '<div class="text-muted">'+statusBadge+' · '+escapeHtml(created)+'</div>'+
                '</div>'+
            '</div>'+
            '<div class="notif-block mb-3">'+
                '<div class="d-flex align-items-center gap-2 border-bottom p-2"><i class="feather-hash"></i><strong>Konu</strong></div>'+
                '<div class="p-3">'+escapeHtml(subject || '—')+'</div>'+
            '</div>'+
            '<div class="notif-block mb-3">'+
                '<div class="d-flex align-items-center gap-2 border-bottom p-2"><i class="feather-users"></i><strong>Alıcılar</strong></div>'+
                '<div class="p-3 d-flex flex-wrap gap-2">'+chips+'</div>'+
            '</div>'+
            '<div class="notif-block mb-2">'+
                '<div class="d-flex align-items-center gap-2 border-bottom p-2"><i class="feather-file-text"></i><strong>Mesaj</strong></div>'+
                '<div class="p-3 message-box">'+escapeHtml(message || '—')+'</div>'+
            '</div>';
        $('#notifDetailBody').html(html);
        $('#notificationDetail').modal('show');
    });
</script>