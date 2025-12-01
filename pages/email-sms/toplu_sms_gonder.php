<?php
require_once dirname(__DIR__, 2) . '/configs/bootstrap.php';

use App_Helper_Date as DateAlias; // no-op to avoid unused warnings
?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Toplu SMS Gönder</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Toplu SMS Gönder</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper"></div>
        </div>
    </div>
    
</div>

<div class="main-content">
    <style>
        .sms-izni-yok td { text-decoration: line-through; color: #6c757d; }
    </style>
    <div class="row mb-5">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Sakin Listesi</h6>
                                <small class="text-muted">Kiracı/Ev Sahibi ve Aktif/Pasif filtrelenebilir; çoklu seçim yapın.</small>
                            </div>
                            <div class="d-flex gap-2">
                                <label class="btn btn-outline-primary btn-sm mb-0" for="smsExcelInput">Excelden Yükle</label>
                                <input type="file" id="smsExcelInput" accept=".xlsx,.xls,.csv" style="display:none">
                                <button class="btn btn-primary btn-sm" id="smsOpenModal"><i class="fas fa-paper-plane me-2"></i>Seçililere Mesaj</button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="p-3">
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-md-3">
                                        <select class="form-select select2" id="filterUyelik">
                                            <option value="">Üyelik: Tümü</option>
                                            <option value="Kiracı">Kiracı</option>
                                            <option value="Kat Maliki">Ev Sahibi</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <select class="form-select select2" id="filterDurum">
                                            <option value="">Durum: Tümü</option>
                                            <option value="Aktif">Aktif</option>
                                            <option value="Pasif">Pasif</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover datatables" id="smsResidentsTable">
                                        <thead>
                                            <tr>
                                                <th style="width:40px">Seç</th>
                                                <th style="width:10%">Daire Kodu</th>
                                                <th>Ad Soyad</th>
                                                <th>Üyelik Türü</th>
                                                <th>Durum</th>
                                                <th>Çıkış Tarihi</th>
                                                <th>Telefon</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="7" class="text-center">
                                                    <div class="spinner-border" role="status">
                                                        <span class="visually-hidden">Loading...</span>
                                                    </div>
                                                </td>
                                            </tr>
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
</div>

<div class="modal fade" id="SendMessage" tabindex="-1" role="dialog" aria-labelledby="modalTitleId"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content sms-gonder-modal"></div>
    </div>
</div>

<script src="/pages/email-sms/js/sms.js"></script>
<script>
    var selectedSmsIds = [];
    var selectedSmsPhones = [];
    if (typeof window.onDataTablesReady !== 'function') {
        window.onDataTablesReady = function(cb){
            var tries = 0;
            (function wait(){
                if (window.jQuery && jQuery.fn && jQuery.fn.DataTable && typeof window.initDataTable === 'function') { cb(); return; }
                if (tries++ > 20) {
                    console.error('DataTables veya initDataTable yüklenemedi');
                    (function(){
                        var url = '/pages/email-sms/api/toplu_sms_server_side.php?draw=1&start=0&length=50';
                        fetch(url).then(function(r){ return r.json(); }).then(function(data){
                            var rows = (data && data.data) ? data.data : [];
                            var tbody = document.querySelector('#smsResidentsTable tbody');
                            if (!tbody) return;
                            tbody.innerHTML = '';
                            rows.forEach(function(r){
                                var tr = document.createElement('tr');
                                tr.className = (r.DT_RowClass || '');
                                tr.innerHTML = '<td>'+ (r.sec||'') +'</td>'+
                                               '<td>'+(r.daire_kodu||'')+'</td>'+
                                               '<td>'+(r.adi_soyadi||'')+'</td>'+
                                               '<td>'+(r.uyelik_tipi||'')+'</td>'+
                                               '<td>'+(r.durum||'')+'</td>'+
                                               '<td>'+(r.cikis_tarihi||'')+'</td>'+
                                               '<td>'+(r.telefon||'')+'</td>';
                                tbody.appendChild(tr);
                            });
                        }).catch(function(e){ console.error(e); });
                    })();
                    return;
                }
                setTimeout(wait, 100);
            })();
        };
    }
    window.onDataTablesReady(function(){
        var smsDt = initDataTable('#smsResidentsTable',{
            processing: true,
            serverSide: true,
            retrieve: true,
            ajax: {
                 url: '/pages/email-sms/api/toplu_sms_server_side.php',
                type: 'GET',
                data: function(d){ d.action = 'sms_kisiler'; }
            },
            columns: [
                { data: 'sec', orderable: false },
                { data: 'daire_kodu' },
                { data: 'adi_soyadi' },
                { data: 'uyelik_tipi' },
                { data: 'durum' },
                { data: 'cikis_tarihi' },
                { data: 'telefon' }
            ],
            order: [[1, 'asc']]
        });

        // Inject select-all checkbox into the search input row (first column)
        (function injectSelectAll(){
            var tries = 0;
            (function waitRow(){
                var $row = $('#smsResidentsTable thead .search-input-row');
                if ($row.length) {
                    var $firstTh = $row.find('th').eq(0);
                    if ($firstTh && $firstTh.find('.custom-control').length === 0) {
                        var html = '<div class="item-checkbox ms-1">'
                            +'<div class="custom-control custom-checkbox">'
                            +'<input type="checkbox" class="custom-control-input checkbox" id="smsSelectAllFilter">'
                            +'<label class="custom-control-label" for="smsSelectAllFilter"></label>'
                            +'</div>'
                            +'</div>';
                        $firstTh.html(html);
                    }
                    return;
                }
                if (tries++ > 20) return; // give up quietly
                setTimeout(waitRow, 100);
            })();
        })();

        function normalizePhone(number){
            var digits = (number||'').toString().replace(/\D/g,'');
            if (!digits) return '';
            if (digits.length===11 && digits.startsWith('0')) return digits.slice(1);
            if (digits.length===12 && digits.startsWith('90')) return digits.slice(2);
            if (digits.length>10) return digits.slice(-10);
            return digits;
        }

        function reapplySelection(){
            document.querySelectorAll('#smsResidentsTable .sms-sec').forEach(function(cb){
                var id = parseInt(cb.getAttribute('data-id'));
                if (cb.disabled) { cb.checked = false; return; }
                cb.checked = selectedSmsIds.includes(id);
            });
        }

        $('#smsResidentsTable').on('draw.dt', function(){
            reapplySelection();
        });

        $('#smsResidentsTable tbody').on('click', 'tr', function(e){
            if ($(e.target).closest('.item-checkbox').length) return;
            var cb = $(this).find('.sms-sec').get(0);
            if (!cb || cb.disabled) return;
            cb.checked = !cb.checked;
            $(cb).trigger('change');
        });

        $('#filterUyelik').on('change', function(){
            var val = $(this).val();
            var dt = $('#smsResidentsTable').DataTable();
            dt.column(3).search(val).draw();
        });
        $('#filterDurum').on('change', function(){
            var val = $(this).val();
            var dt = $('#smsResidentsTable').DataTable();
            dt.column(4).search(val).draw();
        });

        $(document).on('change', '#smsResidentsTable .sms-sec', function(){
            var id = $(this).data('id');
            var phone = normalizePhone(($(this).data('phone') || '').toString());
            var daire = $(this).closest('tr').find('td:nth-child(2)').text().trim();
            if ($(this).is(':checked')) {
                if (!selectedSmsIds.includes(id)) selectedSmsIds.push(id);
                if (phone) selectedSmsPhones.push({ phone: phone, id: id, daire: daire });
            } else {
                selectedSmsIds = selectedSmsIds.filter(function(x){ return x !== id; });
                selectedSmsPhones = selectedSmsPhones.filter(function(o){ return !(o.id === id && o.phone === phone); });
            }
        });

        function updateHeaderSelectState(){
            var $vis = $('#smsResidentsTable tbody .sms-sec');
            var total = $vis.length;
            var checked = $vis.filter(':checked').length;
            var header = document.getElementById('smsSelectAllFilter');
            if (!header) return;
            header.indeterminate = false;
            if (checked === 0) { header.checked = false; }
            else if (checked === total) { header.checked = true; }
            else { header.checked = false; header.indeterminate = true; }
        }
        $('#smsResidentsTable').on('draw.dt', function(){ updateHeaderSelectState(); });
        $(document).on('change', '#smsSelectAllFilter', function(){
            var selectAll = this.checked && !this.indeterminate;
            if (selectAll) {
                var dt = $('#smsResidentsTable').DataTable();
                var params = (dt.ajax && typeof dt.ajax.params === 'function') ? dt.ajax.params() : {};
                if (params) { delete params.start; delete params.length; delete params.draw; }
                params = params || {};
                params.fetch = 'all_ids';
                $.get('/pages/email-sms/api/toplu_sms_server_side.php', params, function(resp){
                    var items = resp.items || [];
                    items.forEach(function(it){
                        var id = parseInt(it.id);
                        var phone = (it.phone||'').toString();
                        if (!Number.isNaN(id) && !selectedSmsIds.includes(id)) selectedSmsIds.push(id);
                        if (phone) selectedSmsPhones.push({ phone: phone, id: id });
                    });
                    $('#smsResidentsTable tbody .sms-sec:not(:disabled)').prop('checked', true);
                    updateHeaderSelectState();
                });
            } else {
                selectedSmsIds = [];
                selectedSmsPhones = [];
                $('#smsResidentsTable tbody .sms-sec').prop('checked', false);
                updateHeaderSelectState();
            }
        });

        $('#smsOpenModal').on('click', function(){
            var pageIds = [];
            var pagePhones = [];
            document.querySelectorAll('#smsResidentsTable .sms-sec:checked').forEach(function(cb){
                var id = parseInt(cb.getAttribute('data-id'));
                var phone = normalizePhone((cb.getAttribute('data-phone')||'').toString());
                var daire = $(cb).closest('tr').find('td:nth-child(2)').text().trim();
                if (!Number.isNaN(id)) pageIds.push(id);
                if (phone) pagePhones.push({ phone: phone, id: id, daire: daire });
            });
            pageIds.forEach(function(id){ if (!selectedSmsIds.includes(id)) selectedSmsIds.push(id); });
            pagePhones.forEach(function(obj){ selectedSmsPhones.push(obj); });
            if (selectedSmsPhones.length === 0) {
                Swal.fire({ title: 'Uyarı', text: 'Lütfen en az bir alıcı seçin.', icon: 'warning' });
                return;
            }
            $.get('/pages/email-sms/sms_gonder_modal.php',{}, function(data){
                $('.sms-gonder-modal').html(data);
                $('#SendMessage').modal('show');
                $('#SendMessage').one('shown.bs.modal', function(){
                    if (typeof window.initSmsModal === 'function') { window.initSmsModal(); }
                    if (typeof window.addPhoneToSMS === 'function') {
                        (selectedSmsPhones || []).forEach(function(o){ window.addPhoneToSMS(o); });
                    }
                    window.selectedRecipientIds = selectedSmsIds.slice();
                    window.selectedRecipientMeta = (selectedSmsPhones || []).slice();
                });
                setTimeout(function(){
                    if (typeof window.initSmsModal === 'function') {
                        window.initSmsModal();
                        window.selectedRecipientIds = selectedSmsIds.slice();
                        window.selectedRecipientMeta = (selectedSmsPhones || []).slice();
                        if (typeof window.addPhoneToSMS === 'function') {
                            (selectedSmsPhones || []).forEach(function(o){ window.addPhoneToSMS(o); });
                        }
                    }
                },100);
            });
        });

        $('#smsExcelInput').on('change', function(e){
            var f = e.target.files && e.target.files[0];
            if (!f) return;
            if (!window.XLSX) { Swal.fire({ title:'Hata', text:'Excel okuyucu yüklü değil', icon:'error' }); return; }
            var reader = new FileReader();
            reader.onload = function(evt){
                var wb = XLSX.read(evt.target.result, { type: 'binary' });
                var ws = wb.Sheets[wb.SheetNames[0]];
                var rows = XLSX.utils.sheet_to_json(ws, { header: 1 });
                var added = 0;
                rows.forEach(function(r){
                    (r||[]).forEach(function(cell){
                        var ph = normalizePhone(cell);
                        if (ph && ph.length>=10) {
                            if (!selectedSmsPhones.includes(ph)) { selectedSmsPhones.push(ph); added++; }
                        }
                    });
                });
                reapplySelection();
                Swal.fire({ title:'Yüklendi', text: added+' numara eklendi', icon:'success' });
            };
            reader.readAsBinaryString(f);
            e.target.value = '';
        });
    });
</script>
