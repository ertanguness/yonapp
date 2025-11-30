
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">İzin Listesi</h6>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary btn-sm" data-id="0" id="newLeaveBtn">Yeni İzin</button>
    </div>
</div>
<div class="table-responsive w-100">
    <table class="table table-hover dttables w-100" id="leavesTable">
        <thead>
            <tr>
                <th style="width:7%">Sıra</th>
                <th>Tür</th>
                <th>Başlangıç</th>
                <th>Bitiş</th>
                <th>Gün</th>
                <th>Açıklama</th>
                <th>Durum</th>
                 <th style="width:10%">İşlem</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<div class="modal fade" id="leaveModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content leave-modal"></div>
    </div>
</div>


<script src="/pages/personel/js/leave.js?<?= filemtime("pages/personel/js/leave.js") ?>"></script>
