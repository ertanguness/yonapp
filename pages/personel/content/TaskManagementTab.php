<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">Görev Listesi</h6>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm" data-id="0" id="newTaskBtn">Yeni Görev</button>
    </div>
</div>
    <div class="table-responsive w-100 overflow-visible" style="overflow: visible;">
    <table class="table table-hover dttables w-100" id="tasksTable">
        <thead>
            <tr>
                <th style="width:7%">Sıra</th>
                <th>Başlık</th>
                <th>Açıklama</th>
                <th>Başlangıç</th>
                <th>Bitiş</th>
                <th>Durum</th>
                <th style="width:10%">İşlem</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<div class="modal fade" id="taskModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content task-modal"></div>
    </div>
</div>
<style>
    /* Allow dropdowns inside this table to overflow the scrolling container */
    .table-responsive.overflow-visible {
        overflow: visible !important;
    }
    .table-responsive.overflow-visible .dropdown-menu {
        z-index: 2050; /* above modals/backdrops */
    }
</style>
<script src="/pages/personel/js/task.js?<?= filemtime("pages/personel/js/task.js") ?>"></script>
