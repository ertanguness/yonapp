<?php

use App\Helper\Date;
use App\Helper\Helper;
use App\Services\Gate;
use App\Helper\Security;
use Model\FinansalRaporModel;

Gate::authorizeOrDie('yonetici_aidat_odeme');
?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Gecikmiş Ödemeler</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Gecikmiş Ödemeler</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                </a>
            </div>
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <div class="dropdown">
                    <a class="btn btn-icon btn-light-brand" data-bs-toggle="dropdown" data-bs-offset="0, 12" data-bs-auto-close="outside">
                        <i class="feather-paperclip"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="/pages/dues/payment/export/gecikmis_borclar_excel.php?format=xlsx" class="dropdown-item">
                            <i class="bi bi-filetype-exe me-3"></i>
                            <span>Excele Aktar</span>
                        </a>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>
<div class="main-content">
    <?php
    $title = "Gecikmiş Ödemeler";
    $text = "Son ödeme tarihi geçmiş ve bakiyesi bulunan ödemeler.";
    require_once 'pages/components/alert.php';
    ?>
    <div class="row mb-5">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive m-3">
                                <table class="table table-hover datatables" id="gecikmisTable">
                                    <thead>
                                        <tr>
                                            <th class="all wd-20 no-sorting" style="width: 30px;">Sıra</th>
                                            <th class="all" style="min-width:80px" data-filter="string">Daire Kodu</th>
                                            <th class="all" style="min-width: 200px;" data-filter="string">Ad Soyad</th>
                                            <th data-filter="date">Giriş Tarihi</th>
                                            <th data-filter="date">Çıkış Tarihi</th>
                                            <th class="text-end" style="width:11%" data-filter="number">Borç Tutarı</th>
                                            <th class="text-end" style="width:11%" data-filter="number">Gecikme Zammı</th>
                                            <th class="text-end" style="width:11%" data-filter="number">Toplam Borç</th>
                                            <th class="text-end" style="width:11%" data-filter="number">Kredi Tutarı</th>
                                            <th class="all text-end" style="width:11%" data-filter="number">Kalan Borç</th>
                                            <th class="all">İşlem</th>
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
<div class="modal fade" id="kisiBorcDetay" tabindex="-1" role="dialog" data-bs-keyboard="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl modal-dialog-centered" role="document">
        <div class="modal-content borc-detay"></div>
    </div>
</div>

<script>
    if (typeof window.onDataTablesReady !== 'function') {
        window.onDataTablesReady = function(cb){
            var tries = 0;
            (function wait(){
                if (window.jQuery && jQuery.fn && jQuery.fn.DataTable && typeof window.initDataTable === 'function') { cb(); return; }
                if (tries++ > 100) { return; }
                setTimeout(wait, 100);
            })();
        };
    }
    window.onDataTablesReady(function(){
        var table = initDataTable('#gecikmisTable',{
            processing: true,
            serverSide: true,
            retrieve: true,
            ajax: {
                url: '/pages/dues/payment/server_processing_gecikmis.php',
                type: 'GET'
            },
            columns: [
                { data: null, orderable: false, render: function(data, type, row, meta){ return meta.row + 1 + meta.settings._iDisplayStart; } },
                { data: 'daire_kodu' },
                { data: 'adi_soyadi_html' },
                { data: 'giris_tarihi' },
                { data: 'cikis_tarihi' },
                { data: 'kalan_anapara_formatted', className: 'text-end' },
                { data: 'hesaplanan_gecikme_zammi_formatted', className: 'text-end' },
                { data: 'toplam_kalan_borc_formatted', className: 'text-end' },
                { data: 'kredi_tutari_formatted', className: 'text-end' },
                { data: 'net_borc_formatted', className: 'text-end' },
                { data: 'islem_html', orderable: false }
            ],
            order: [[1, 'asc']]
        });
        try {
            var params = new URLSearchParams(window.location.search);
            var q = params.get('q');
            if (q && $.fn.dataTable.isDataTable('#gecikmisTable')) {
                var dt = $('#gecikmisTable').DataTable();
                dt.search(q).draw();
            }
        } catch(e) {}
        $(document).on('click', '#js-filter-all', function(){
            var dt = $('#gecikmisTable').DataTable();
            dt.search('').draw();
            try {
                var url = new URL(window.location.href);
                url.searchParams.delete('q');
                window.history.replaceState(null, '', url.toString());
            } catch(e) {}
            $('#activeListFilterTag, #activeListFilterTagDropdown, #clearListFilter').remove();
        });
    });
</script>
<script>
    var kisiId;
    $(document).on('click', '.kisi-borc-detay', function() {
        var kisiEncId = $(this).data('id');
        $.get("pages/dues/payment/modal/modal_gecikmis_borc_detay.php", { kisi_id: kisiEncId }, function(data) {
            $('.borc-detay').html(data);
            $('#kisiBorcDetay').modal('show');
        });
    });
    $(document).on('click', '.tahsilat-gir', function() {
        kisiId = $(this).data('kisi-id');
        $.get("pages/dues/payment/tahsilat_gir_modal.php", { kisi_id: kisiId }, function(data) {
            $('.tahsilat-modal-body').html(data);
            if ($(".modal").length > 0) {
                $('#tahsilatGir').modal('show');
            }
            if ($(".select2").length > 0) {
                $(".select2").select2({ placeholder: "Kasa Seçiniz", dropdownParent: $('#tahsilatGir') });
                $("#tahsilat_turu").select2({ tags: true, dropdownParent: $('#tahsilatGir') });
            }
            if ($("#islem_tarihi").length > 0) {
                $("#islem_tarihi").flatpickr({ dateFormat: "d.m.Y H:i", locale: "tr", enableTime: true, minuteIncrement: 1 });
            }
        });
    });
</script>
