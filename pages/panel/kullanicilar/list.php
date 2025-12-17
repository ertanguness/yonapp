<?php

use App\Helper\Security;
use Model\UserModel;

$User = new UserModel();

// Server-side DataTables'a geçildi: ilk yüklemede tüm kullanıcıları çekmeyelim
$users = [];

?>


<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Kullanıcılar</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Kullanıcı Listesi</li>
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
                require_once 'pages/components/search.php';
                require_once 'pages/components/download.php'
                ?>

                <a href="superadmin-kullanici-ekle" class="btn btn-primary route-link">
                    <i class="feather-plus me-2"></i>
                    <span>Yeni Kullanıcı</span>
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
    $title = "Kullanıcı Listesi!";
    $text = "Seçili site için dilediğiniz kadar kullanıcı ekleyebilir ve bu
                    kullanıcılara istediğiniz yetkileri verebilirsiniz.
                    Hesap oluşturma aşamasında oluşturulan kullanıcı silinemez!";
    require_once 'pages/components/alert.php'
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover dttables" id="userTable">
                                    <thead>
                                        <tr>
                                            <th style="width:7%" data-filter="number">Sıra</th>
                                            <th style="width:10%" data-filter="string">Pozisyon</th>
                                            <th style="width:10%" data-filter="string">Site</th>
                                            <th data-filter="string">Adı Soyadı</th>
                                            <th style="width:20%" data-filter="string">Email</th>
                                            <th style="width:10%" data-filter="string">Telefon</th>
                                            <th style="width:10%">Ana Kullanıcı</th>
                                            <th style="width:7%">Durum</th>
                                            <th style="width:7%">İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
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
    (function() {
        // DataTables dosyaları vendor-scripts.php'ten yükleniyor
        function initUserTable() {
            if (!window.jQuery || !$.fn.DataTable) return;
            var $tbl = $('#userTable');
            if (!$tbl.length) return;
            if ($.fn.DataTable.isDataTable($tbl[0])) return;

            table = initDataTable('#userTable', {
                processing: true,
                serverSide: true,
                responsive: true,
                searching: true,
                pageLength: 12,
                order: [
                    [0, 'desc']
                ],
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json"
                },
                ajax: {
                    url: '/pages/panel/kullanicilar/server-side/server-side-api.php',
                    type: 'POST'
                },
                initComplete: function(settings, json) {
                    var api = this.api();
                    var tableId = settings.sTableId;
                    attachDtColumnSearch(api, tableId);
                    api.columns.adjust().responsive.recalc();
                },
                columnDefs: [{
                        targets: 0,
                        className: 'text-center'
                    },
                    {
                        targets: 6,
                        className: 'text-center',
                        orderable: false,
                        searchable: false
                    },
                    {
                        targets: 7,
                        orderable: false
                    },
                    {
                        targets: 8,
                        orderable: false,
                        searchable: false
                    }
                ]
            });
            $(window).on("resize.dt", function() {
                try {
                    table.columns.adjust().responsive.recalc();
                } catch (e) {}
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initUserTable);
        } else {
            initUserTable();
        }
    })();
</script>