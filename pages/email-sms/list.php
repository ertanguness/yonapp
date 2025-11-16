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
                <?php
                // require_once 'pages/components/search.php';
                require_once 'pages/components/download.php';
                ?>

                <a href="javascript:void(0)" class="btn btn-primary mail-gonder">
                    <i class="feather-mail me-2"></i>
                    <span>Yeni Email</span>
                </a>
                <a href="#" class="btn btn-simple btn-secondary sms-gonder">
                    <i class="feather-smartphone me-2"></i>
                    <span>Yeni Sms</span>
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



<script>
    function ensureDataTables(cb){
        if ($.fn.DataTable) { cb(); return; }
        var libs = [
            '/assets/vendors/js/dataTables.min.js',
            '/assets/vendors/js/dataTables.bs5.min.js'
        ];
        var i = 0;
        function load(){
            if (i >= libs.length) { cb(); return; }
            var s = document.createElement('script');
            s.src = libs[i++];
            s.onload = load;
            document.head.appendChild(s);
        }
        load();
    }

    ensureDataTables(function(){
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
            responsive: true,
            language: {
                emptyTable: 'Henüz bildirim bulunmuyor',
                zeroRecords: 'Eşleşen kayıt bulunamadı',
                processing: 'Yükleniyor...'
            },
            order: [[0,'desc']],
            columns: [
                { data: 'id' },
                { data: 'type' },
                { data: 'recipients', render: function(d){
                    try { var arr = JSON.parse(d); return Array.isArray(arr) ? arr.join(', ') : d; } catch(e){ return d; }
                }},
                { data: 'subject' },
                { data: 'message' },
                { data: 'created_at' },
                { data: 'status' },
                { data: null, render: function(row){ return '<button class="btn btn-sm btn-outline-secondary btn-detail-notification">Detay</button>'; } }
            ],
            initComplete: function (settings, json) {
                var api = this.api();
                var tableId = settings.sTableId;
                attachDtColumnSearch(api, tableId);
                api.columns.adjust().responsive.recalc();
            },
        });
    });
</script>
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
<style>
    #notificationDetail .message-box{ white-space: pre-wrap; }
    #notificationDetail .notif-block{ border:1px solid #e9ecef; border-radius:8px; }
    #notificationDetail .notif-chip{ background:#f8f9fa; border:1px solid #e9ecef; border-radius:16px; padding:6px 10px; font-size:12px; }
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