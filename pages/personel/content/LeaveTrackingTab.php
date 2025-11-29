<div class="p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">İzin Listesi</h6>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm" id="newLeaveBtn">Yeni İzin</button>
        </div>
    </div>
    <div class="table-responsive w-100">
        <table class="table table-hover datatables w-100" id="leavesTable">
            <thead>
                <tr>
                    <th style="width:40px">Sıra</th>
                    <th>Tür</th>
                    <th>Başlangıç</th>
                    <th>Bitiş</th>
                    <th>Gün</th>
                    <th>Açıklama</th>
                    <th>Durum</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<div class="modal fade" id="leaveModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content leave-modal"></div>
    </div>
</div>
<script>
      if (typeof window.onDataTablesReady !== 'function') {
        window.onDataTablesReady = function(cb){
            var tries = 0;
            (function wait(){
                if (window.jQuery && jQuery.fn && jQuery.fn.DataTable && typeof window.initDataTable === 'function') { cb(); return; }
                if (tries++ > 100) { console.error('DataTables veya initDataTable yüklenemedi'); return; }
                setTimeout(wait, 100);
            })();
        };
    }

    window.onDataTablesReady(function(){
            var dt = initDataTable('#leavesTable',{
                processing:true,serverSide:true,retrieve:true,
                ajax:{url:'/pages/personel/api/leaves_server_side.php',type:'GET'},
                columns:[
                    {data:null,orderable:false,render:function(d,t,r,m){return m.row+1+m.settings._iDisplayStart;}},
                    {data:'type'},
                    {data:'start_date'},
                    {data:'end_date'},
                    {data:'days'},
                    {data:'description'},
                    {data:'status'},
                    {data:'actions',orderable:false}
                ],
                order:[[1,'asc']]
            });
            document.querySelector('a[data-bs-target="#leaveTrackingTab"]').addEventListener('shown.bs.tab', function(){
                try { $('#leavesTable').DataTable().columns.adjust().responsive.recalc(); } catch(e){}
            });
            $(document).on('click','#newLeaveBtn',function(){
                $.get('/pages/personel/modal/leave_modal.php')
                  .done(function(html){ $('#leaveModal .leave-modal').html(html); $('#leaveModal').modal('show'); })
                  .fail(function(){ $('#leaveModal .leave-modal').html('<div class="p-3">İçerik yüklenemedi</div>'); $('#leaveModal').modal('show'); });
            });
            $(document).on('click','.leave-edit',function(){
                var id=$(this).data('id');
                $.get('/pages/personel/modal/leave_modal.php',{id:id})
                  .done(function(html){ $('#leaveModal .leave-modal').html(html); $('#leaveModal').modal('show'); })
                  .fail(function(){ $('#leaveModal .leave-modal').html('<div class="p-3">İçerik yüklenemedi</div>'); $('#leaveModal').modal('show'); });
            });
    })();
</script>
