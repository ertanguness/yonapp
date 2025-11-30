
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">Görev Listesi</h6>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm" data-id="0" id="newTaskBtn">Yeni Görev</button>
    </div>
</div>
<div class="table-responsive w-100">
    <table class="table table-hover dttables w-100" id="tasksTable">
        <thead>
            <tr>
                <th style="width:40px">Sıra</th>
                <th>Başlık</th>
                <th>Açıklama</th>
                <th>Başlangıç</th>
                <th>Bitiş</th>
                <th>Durum</th>
                <th>İşlem</th>
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
<script>
    $(document).ready(function() {
        var dt = initDataTable("#tasksTable", {
            processing: true,
            serverSide: true,
            retrieve: true,
            ajax: {
                url: '/pages/personel/api/tasks_server_side.php',
                type: 'GET',
                data: function(d) {
                    d.person_id = window.personId || 0;
                }
            },
            columns: [{
                    data: null,
                    orderable: false,
                    render: function(d, t, r, m) {
                        return m.row + 1 + m.settings._iDisplayStart;
                    }
                },
                {data: 'title'},
                {
                    data: 'description'
                },
                {
                    data: 'start_date',
                    render: function(data, type, row) {
                        if (!data) return '';
                        var date = new Date(data);
                        return date.toLocaleDateString('tr-TR');
                    }
                },
                {
                    data: 'end_date',
                    render: function(data, type, row) {
                        if (!data) return '';
                        var date = new Date(data);
                        return date.toLocaleDateString('tr-TR');
                    }
                },
                {data: 'status'},
                {data: 'actions', orderable: false}
            ],
            order: [
                [1, 'asc']
            ]
        });
    });
    document.querySelector('a[data-bs-target="#taskManagementTab"]').addEventListener('shown.bs.tab', function() {
        try {
            $('#tasksTable').DataTable().columns.adjust().responsive.recalc();
        } catch (e) {}
    });
    // $(document).on('click', '#newTaskBtn , .task-edit', function() {
    //     $.get('/pages/personel/modal/task_modal.php')
    //         .done(function(html) {
    //             $('#taskModal .task-modal').html(html);
    //             $('#taskModal').modal('show');
    //             $(".flatpickr").flatpickr({
    //                 dateFormat: "Y-m-d",
    //                 locale: "tr"
    //             });
    //         })
    //         .fail(function() {
    //             $('#taskModal .task-modal').html('<div class="p-3">İçerik yüklenemedi</div>');
    //             $('#taskModal').modal('show');
    //         });
    // });
    $(document).on('click', '#newTaskBtn , .task-edit', function() {
        var id = $(this).data('id');
        $.get('/pages/personel/modal/task_modal.php', {
                id: id
            })
            .done(function(html) {
                $('#taskModal .task-modal').html(html);
                $('#taskModal').modal('show');
                $(".flatpickr").flatpickr({
                    dateFormat: "d.m.Y",
                    locale: "tr"
                });
            })
            .fail(function() {
                $('#taskModal .task-modal').html('<div class="p-3">İçerik yüklenemedi</div>');
                $('#taskModal').modal('show');
            });
    });
</script>